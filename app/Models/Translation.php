<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'source_word_id',
        'target_word_id',
    ];

    public function source()
    {
        return $this->belongsTo(Word::class, 'source_word_id');
    }

    public function target()
    {
        return $this->belongsTo(Word::class, 'target_word_id');
    }

    public function allTranslations($sourceWordId, $sourceLangId, $targetLangId)
    {
        return $this
            ->with(['source', 'target'])
            ->join('words as sw', 'sw.id', '=', 'translations.source_word_id')
            ->join('words as tw', 'tw.id', '=', 'translations.target_word_id')
            ->where(function($q) use($sourceWordId, $sourceLangId, $targetLangId) {
                $q->where('sw.id', $sourceWordId)
                ->where('sw.lang_id', $sourceLangId)
                ->where('tw.lang_id', $targetLangId);
            })
            ->orWhere(function($q) use($sourceWordId, $sourceLangId, $targetLangId) {
                $q->where('tw.id', $sourceWordId)
                ->where('tw.lang_id', $targetLangId)
                ->where('sw.lang_id', $sourceLangId);
            })
            ->select('translations.id as translation_id', 'tw.word as target_word')
            ->get();
    }
}
