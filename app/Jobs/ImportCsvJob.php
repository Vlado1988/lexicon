<?php

namespace App\Jobs;

use App\Models\Word;
use App\Models\Translation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ImportCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rows;
    protected $sourceWordName;
    protected $targetWordName;
    protected $sourceLangId;
    protected $targetLangId;
    protected $jobId;

    public function __construct(array $rows, string $sourceWordName, string $targetWordName, int $sourceLangId, int $targetLangId, string $jobId)
    {
        $this->rows = $rows;
        $this->sourceWordName = $sourceWordName;
        $this->targetWordName = $targetWordName;
        $this->sourceLangId = $sourceLangId;
        $this->targetLangId = $targetLangId;
        $this->jobId = $jobId;
    }

    public function handle(): void
    {
        foreach ($this->rows as $item) {
            if (empty($item[$this->sourceWordName]) || empty($item[$this->targetWordName])) {
                $this->incrementProgress();
                continue;
            }

            $translations = explode(',', $item[$this->targetWordName]) ?? [];

            $this->insertTranslation(
                $item[$this->sourceWordName],
                array_map('trim', $translations),
                $this->sourceLangId,
                $this->targetLangId
            );

            $this->incrementProgress();
        }
    }

    private function incrementProgress()
    {
        $key = "import_progress_{$this->jobId}";
        Cache::increment($key);
    }

    private function insertTranslation(string $source_words, array $target_words, int $source_lang_id, int $target_lang_id)
    {
        // explode if multiple words splitted with comma (,) occure
        // array_map to eliminate eventional white space before the first word
        $sourceWordsArr = array_map('trim', explode(',', $source_words));

        // loop through every source word
        foreach($sourceWordsArr as $source_word) {
            $trimmedSourceWord = trim($source_word);

            $sourceWordResult = Word::whereRaw('BINARY words.word = ?', [$trimmedSourceWord])
                            ->where('lang_id', $source_lang_id)
                            ->first();

            if (! $sourceWordResult) {
                $sourceWordResult = Word::create([
                    'word' => $trimmedSourceWord,
                    'search_key' => generate_search_key($trimmedSourceWord),
                    'lang_id' => $source_lang_id,
                ]);
            }

            // add translations to every source word
            foreach ($target_words as $targetWord) {
                $targetWordTrimmed = trim($targetWord);

                $targetWordResult = Word::whereRaw('BINARY words.word = ?',[$targetWordTrimmed])
                                    ->where('lang_id', $target_lang_id)
                                    ->first();

                if (! $targetWordResult) {
                    $targetWordResult = Word::create([
                        'word' => $targetWordTrimmed,
                        'search_key' => generate_search_key($targetWordTrimmed),
                        'lang_id' => $target_lang_id,
                    ]);
                }

                $exists = Translation::where(function($q) use ($sourceWordResult, $targetWordResult) {
                    $q->where('source_word_id', $sourceWordResult->id)
                    ->where('target_word_id', $targetWordResult->id);
                })
                ->orWhere(function($q) use ($sourceWordResult, $targetWordResult) {
                    $q->where('source_word_id', $targetWordResult->id)
                    ->where('target_word_id', $sourceWordResult->id);
                })
                ->exists();

                if (!$exists) {
                    Translation::create([
                        'source_word_id' => $sourceWordResult->id,
                        'target_word_id' => $targetWordResult->id
                    ]);
                }
            }
        }
    }
}
