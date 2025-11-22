<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use App\Models\Word;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.import.index');
    }

    public function init(Request $request)
    {
        $request->validate([
            'file' => ['required', 'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel,application/json'],
        ]);

        $file = $request->file;
        $fileExtension = $request->file('file')->getClientOriginalExtension();

        // process CSV file data
        if($fileExtension == 'csv') {
            $filePath = $file->getRealPath();
            $data = [];
            $delimiter = ";";

            if (($handle = fopen($filePath, "r")) !== false) {
                // načítaj hlavičku
                $headers = fgetcsv($handle, 0, $delimiter);

                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                    if (count($row) == 0 || trim(implode('', $row)) === '') continue; // preskoč prázdne riadky

                    $data[] = array_combine($headers, $row);
                }
                fclose($handle);
            }

            foreach($data as $item) {
                $translations = array_map('trim', $item['translation'] ?? null);

                $this->insertTranslation(
                    $item['word'],
                    $translations,
                    $item['source_lang'],
                    $item['target_lang'],
                );
            }
        }
        else if($fileExtension == 'json') {
            $filePath = $file->getRealPath();

            $data = json_decode(file_get_contents($filePath), true);
            $sourceLang = isset($data['meta']['source_lang']) ? $data['meta']['source_lang'] : null;
            $targetLang = isset($data['meta']['target_lang']) ? $data['meta']['target_lang'] : null;
            $entries = isset($data['entries']) ? $data['entries'] : null;

            foreach($entries as $item) {
                $translations = array_map('trim', $item['translation'] ?? null);

                $this->insertTranslation($item['word'], $translations, $sourceLang, $targetLang);
            }
        }
    }

    private function insertTranslation(string $source_word, array $target_words, string $source_lang, string $target_lang)
    {
        $translationsCreated = [];

        $sourceWordResult = Word::join('languages', 'languages.id', '=', 'words.lang_id')
                        ->whereRaw('BINARY words.word = ?', [trim($source_word)])
                        ->where('languages.code', $source_lang)
                        ->select('words.*')
                        ->first();

        if (! $sourceWordResult) {
            $sourceLangId = Language::where('code', $source_lang)->pluck('id')->first();
            if($sourceLangId) {
                $sourceWordResult = Word::create([
                    'word' => trim($source_word),
                    'lang_id' => $sourceLangId,
                ]);
            }
        }

        $sourceWordId = $sourceWordResult->id;

        // Save target words
        foreach($target_words as $targetWord) {
            $targetWordTrimmed = trim($targetWord);
            $targetWordResult = Word::join('languages', 'languages.id', '=', 'words.lang_id')
                                    ->whereRaw('BINARY words.word = ?',[$targetWordTrimmed])
                                    ->where('languages.code', $target_lang)
                                    ->select('words.*')
                                    ->first();

            if (! $targetWordResult) {
                $targetLangId = Language::where('code', $target_lang)->pluck('id')->first();
                if($targetLangId) {
                    $targetWordResult = Word::create([
                        'word' => $targetWordTrimmed,
                        'lang_id' => $targetLangId,
                    ]);
                }
            }

            $targetWordId = $targetWordResult->id;

            // If target word already exist, then get his ID
            if($targetWordId === null) {
                $targetWordId = Word::whereRaw('BINARY word = ?', [$targetWordTrimmed])
                ->where('lang_id', $targetLangId)
                ->value('id');
            }

            // Check if Translation exist in all directions
            $isTranslationExist = Translation::where(function ($query) use ($sourceWordId, $targetWordId) {
                $query->where('source_word_id', $sourceWordId)
                    ->where('target_word_id', $targetWordId);
                })
                ->orWhere(function ($query) use ($sourceWordId, $targetWordId) {
                    $query->where('source_word_id', $targetWordId)
                        ->where('target_word_id', $sourceWordId);
                })
                ->exists();

            if(!$isTranslationExist && $sourceWordId !== null && $targetWordId !== null) {
                $translationModel = new Translation();
                $translationModel->source_word_id = $sourceWordId;
                $translationModel->target_word_id = $targetWordId;
                $translationModel->save();

                $translationsCreated[] = $translationModel;
            }
        }

        return $translationsCreated;
    }

    public function getProgress($jobId)
    {
        $percent = cache()->get("import_progress_{$jobId}", 0);
        return response()->json(['percent' => $percent]);
    }



    public function importBatch(Request $request)
    {
        $type = $request->input('type');
        $jobId = $request->input('jobId');
        $total = (int) $request->input('total');

        if($type === 'json') {
            $batch = json_decode($request->input('batch'), true);
            $sourceLang = $request->input('sourceLang');
            $targetLang = $request->input('targetLang');

            foreach($batch as $item) {
                $translations = $item['translation'] ?? [];
                if(is_string($translations)) $translations = [$translations];

                $this->insertTranslation(
                    $item['word'],
                    $translations,
                    $sourceLang,
                    $targetLang
                );
            }

            // update progress
            $processed = cache()->get("import_progress_{$jobId}", 0);
            $processed += count($batch);
            cache()->put("import_progress_{$jobId}", $processed, 3600);

            return response(['status' => 'success']);
        }
    }

    public function uploadCSV(Request $request)
    {
        $jobId = $request->input('jobId');

        if(!$request->hasFile('file')) {
            return response(['status' => 'error', 'message' => 'File is missing']);
        }

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $delimiter = ";";

        $rows = [];
        if(($handle = fopen($filePath, "r")) !== false) {
            $headers = fgetcsv($handle, 0, $delimiter);

            while(($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if(count($row) == 0 || trim(implode('', $row)) === '') continue;
                $rows[] = array_combine($headers, $row);
            }
            fclose($handle);
        }

        $batchSize = 300;
        $total = count($rows);

        cache()->put("import_total_{$jobId}", $total, 3600);
        cache()->put("import_progress_{$jobId}", 0, 3600);

        $batches = array_chunk($rows, $batchSize);

        foreach($batches as $batch) {
            foreach($batch as $item) {
                $translations = $item['translation'] ?? [];
                if(is_string($translations)) $translations = [$translations];

                $this->insertTranslation(
                    $item['word'],
                    array_map('trim', $translations),
                    $item['source_lang'],
                    $item['target_lang']
                );
            }

            $processed = cache()->get("import_progress_{$jobId}", 0);
            $processed += count($batch);
            cache()->put("import_progress_{$jobId}", $processed, 3600);
        }

        return response(['status' => 'success']);
    }

    public function importStatus($jobId)
    {
        $total = cache()->get("import_total_{$jobId}", 1);
        $processed = cache()->get("import_progress_{$jobId}", 0);

        $percent = ($processed / $total) * 100;

        return response(['percent' => $percent]);
    }
}
