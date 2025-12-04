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

    private function insertTranslation(string $source_word, array $target_words, int $source_lang_id, int $target_lang_id)
    {
        $sourceWordResult = Word::firstOrCreate(
            ['word' => trim($source_word), 'lang_id' => $source_lang_id]
        );

        foreach ($target_words as $targetWord) {
            $targetWordTrimmed = trim($targetWord);

            $targetWordResult = Word::firstOrCreate(
                ['word' => $targetWordTrimmed, 'lang_id' => $target_lang_id]
            );

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
