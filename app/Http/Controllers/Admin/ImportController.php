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
            'file' => ['required', 'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel'],
        ]);

        $file = $request->file;

        // process CSV file data
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

    private function insertTranslation(string $source_word, array $target_words, string $source_lang_id, string $target_lang_id)
    {
        $translationsCreated = [];

        $sourceWordResult = Word::whereRaw('BINARY words.word = ?', [trim($source_word)])
                        ->where('lang_id', $source_lang_id)
                        ->first();

        if (! $sourceWordResult) {
            $sourceWordResult = Word::create([
                'word' => trim($source_word),
                'lang_id' => $source_lang_id,
            ]);
        }

        $sourceWordId = $sourceWordResult->id;

        // Save target words
        foreach($target_words as $targetWord) {
            $targetWordTrimmed = trim($targetWord);
            $targetWordResult = Word::whereRaw('BINARY words.word = ?',[$targetWordTrimmed])
                                    ->where('lang_id', $target_lang_id)
                                    ->first();

            if (! $targetWordResult) {
                $targetWordResult = Word::create([
                    'word' => $targetWordTrimmed,
                    'lang_id' => $target_lang_id,
                ]);
            }

            $targetWordId = $targetWordResult->id;

            // If target word already exist, then get his ID
            if($targetWordId === null) {
                $targetWordId = Word::whereRaw('BINARY word = ?', [$targetWordTrimmed])
                ->where('lang_id', $target_lang_id)
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

    public function loadCsvData(Request $request)
    {
        if(!$request->hasFile('file')) {
            return response(['status' => 'error', 'message' => 'File is missing', 'data' => null]);
        }

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $delimiter = $request->input('delimiter') ?: ';';
        $row = 0;

        if(($handle = fopen($filePath, "r")) !== false) {
            while(($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                // get headers
                if($row == 0) {
                    $headers = array_map(fn($h) => str_replace(' ', '_', $h), $data);
                }
                else {
                    $entries[$row - 1] = array_combine($headers, $data);
                }

                $row++;
            }
        }

        $csv_data = [];

        foreach($entries as $key => $item) {
            foreach($headers as $col => $header) {
                $csv_data[$header][$key] = $entries[$key][$header];
            }
        }

        $loaded_data = array_map('array_filter', $csv_data);
        $languages = Language::orderBy('name')->get();

        return response(['status' => 'success', 'message' => 'Data loaded successfully', 'data' => [
            'languages' => $languages,
            'fileData' => $loaded_data,
        ]]);
    }

    public function uploadCSV(Request $request)
    {
        $jobId = $request->input('jobId');

        $sourceWordName = str_replace(' ', '_', $request->sourceWordName);
        $targetWordName = str_replace(' ', '_', $request->targetWordName);
        $sourceLangId = $request->sourceLang;
        $targetLangId = $request->targetLang;

        $errorMsg = null;

        if(!$request->hasFile('file')) {
            $errorMsg = 'File is missing';
        }

        if(!$sourceWordName) {
            $errorMsg = 'Source Word Name is required';
        }

        if(!$targetWordName) {
            $errorMsg = 'Target Word Name is required';
        }

        if(!$sourceLangId) {
            $errorMsg = 'Source Language is required';
        }

        if(!$targetLangId) {
            $errorMsg = 'Target Language is required';
        }

        if($errorMsg) {
            return response(['status' => 'error', 'message' => $errorMsg]);
        }

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $delimiter = $request->input('delimiter') ?: ";";

        $rows = [];
        if(($handle = fopen($filePath, "r")) !== false) {
            $headers = fgetcsv($handle, 0, $delimiter);
            $headers = array_map(fn($h) => str_replace(' ', '_', $h), $headers);

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
                if(empty($item[$sourceWordName]) || empty($item[$targetWordName])) continue;

                $translations = explode(',', $item[$targetWordName]) ?? [];

                $this->insertTranslation(
                    $item[$sourceWordName],
                    array_map('trim', $translations),
                    $sourceLangId,
                    $targetLangId
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
