<?php

namespace App\Http\Controllers;

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

    public function initImport(Request $request)
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

            if (($handle = fopen($filePath, "r")) !== false) {
                // načítaj hlavičku
                $headers = fgetcsv($handle, 0, ";");

                while (($row = fgetcsv($handle, 0, ";")) !== false) {
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
        // process JSON file data
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
}
