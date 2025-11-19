<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    protected $fillable = [
        'word',
        'lang_id',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }

    public function translationsFrom()
    {
        return $this->hasMany(Translation::class, 'source_word_id');
    }

    public function translationsTo()
    {
        return $this->hasMany(Translation::class, 'target_word_id');
    }

    public function allTranslations()
    {
        return $this->hasMany(Translation::class, 'source_word_id')
            ->orWhere('target_word_id', $this->id);
    }

    public static function getTranslationsForLanguages($sourceLangId, $targetLangId)
    {
        return self::query()
            ->from('words as sw')
            ->join('translations as t', function ($join) {
                $join->on('t.source_word_id', '=', 'sw.id')
                    ->orOn('t.target_word_id', '=', 'sw.id');
            })
            ->join('words as tw', function ($join) use ($targetLangId) {
                $join->on('tw.id', '=', 't.target_word_id')
                        ->where('t.source_word_id', '=', \DB::raw('sw.id'))
                        ->where('tw.lang_id', '=', $targetLangId)
                    ->orOn(function ($q) use ($targetLangId) {
                        $q->on('tw.id', '=', 't.source_word_id')
                        ->where('t.target_word_id', '=', \DB::raw('sw.id'))
                        ->where('tw.lang_id', '=', $targetLangId);
                    });
            })
            ->where('sw.lang_id', $sourceLangId)
            ->groupBy('sw.id', 'sw.word')
            ->selectRaw('
                sw.id as id,
                sw.word as source_word,
                GROUP_CONCAT(DISTINCT tw.word ORDER BY tw.id SEPARATOR ", ") as target_word
            ')
            ->orderByDesc('sw.id');
    }

    public static function getTranslationForLanguages($wordId, $sourceLangId, $targetLangId)
    {
        return self::query()
            ->from('words as sw')
            ->leftJoin('translations as t', function ($join) {
                $join->on('t.source_word_id', '=', 'sw.id')
                    ->orOn('t.target_word_id', '=', 'sw.id');
            })
            ->leftJoin('words as tw', function ($join) use ($targetLangId) {
                $join->on('tw.id', '=', 't.target_word_id')
                        ->where('t.source_word_id', '=', \DB::raw('sw.id'))
                        ->where('tw.lang_id', '=', $targetLangId)
                    ->orOn(function ($q) use ($targetLangId) {
                        $q->on('tw.id', '=', 't.source_word_id')
                        ->where('t.target_word_id', '=', \DB::raw('sw.id'))
                        ->where('tw.lang_id', '=', $targetLangId);
                    });
            })
            ->where('sw.lang_id', $sourceLangId)
            ->where('sw.id', $wordId)
            ->groupBy('sw.id', 'sw.word')
            ->selectRaw('
                sw.id as id,
                sw.word as source_word,
                GROUP_CONCAT(DISTINCT tw.word ORDER BY tw.id SEPARATOR ", ") as target_word
            ')
            ->orderByDesc('sw.id');
    }

    public static function getTranslationsByWord($word, $sourceLangId, $targetLangId)
    {
        return self::query()
            ->from('words as sw')
            ->leftJoin('translations as t', function ($join) {
                $join->on('t.source_word_id', '=', 'sw.id')
                    ->orOn('t.target_word_id', '=', 'sw.id');
            })
            ->leftJoin('words as tw', function ($join) use ($targetLangId) {
                $join->on('tw.id', '=', 't.target_word_id')
                        ->where('t.source_word_id', '=', \DB::raw('sw.id'))
                        ->where('tw.lang_id', '=', $targetLangId)
                    ->orOn(function ($q) use ($targetLangId) {
                        $q->on('tw.id', '=', 't.source_word_id')
                        ->where('t.target_word_id', '=', \DB::raw('sw.id'))
                        ->where('tw.lang_id', '=', $targetLangId);
                    });
            })
            ->where('sw.lang_id', $sourceLangId)
            ->whereRaw('BINARY sw.word = ?', [$word]) // <-- case sensitive
            ->groupBy('sw.id', 'sw.word')
            ->selectRaw('
                sw.id as id,
                sw.word as source_word,
                GROUP_CONCAT(DISTINCT tw.word ORDER BY tw.id SEPARATOR ", ") as target_word
            ')
            ->orderByDesc('sw.id');
    }

    public static function getTranslationsByWordLike($word, $sourceLangId, $targetLangId)
    {
        return self::query()
            ->from('words as sw')
            ->leftJoin('translations as t', function ($join) {
                $join->on('t.source_word_id', '=', 'sw.id')
                    ->orOn('t.target_word_id', '=', 'sw.id');
            })
            ->join('words as tw', function ($join) use ($targetLangId) {
                $join->on('tw.id', '=', 't.target_word_id')
                        ->where('t.source_word_id', '=', \DB::raw('sw.id'))
                        ->where('tw.lang_id', '=', $targetLangId)
                    ->orOn(function ($q) use ($targetLangId) {
                        $q->on('tw.id', '=', 't.source_word_id')
                        ->where('t.target_word_id', '=', \DB::raw('sw.id'))
                        ->where('tw.lang_id', '=', $targetLangId);
                    });
            })
            ->where('sw.lang_id', $sourceLangId)
            ->where(function ($query) use ($word) {
                $query->where('sw.word',  $word)
                    ->orWhere('sw.word', 'like', "$word %");
            })
            ->groupBy('sw.id', 'sw.word')
            ->selectRaw('
                sw.id as id,
                sw.word as source_word,
                GROUP_CONCAT(DISTINCT tw.word ORDER BY tw.word SEPARATOR ", ") as target_word
            ')
            ->orderByRaw("CASE WHEN sw.word = ? THEN 0 ELSE 1 END, sw.word ASC", [$word]);
    }

    public static function searchTranslationsForLanguages($sourceLangId, $targetLangId, $keyword)
    {
        return self::query()
            ->from('words as sw')
            ->leftJoin('translations as t', function ($join) {
                $join->on('t.source_word_id', '=', 'sw.id')
                    ->orOn('t.target_word_id', '=', 'sw.id');
            })
            ->leftJoin('words as tw', function ($join) use ($targetLangId) {
                $join->on('tw.id', '=', 't.target_word_id')
                    ->where('t.source_word_id', '=', \DB::raw('sw.id'))
                    ->where('tw.lang_id', '=', $targetLangId)
                    ->orOn(function ($q) use ($targetLangId) {
                        $q->on('tw.id', '=', 't.source_word_id')
                        ->where('t.target_word_id', '=', \DB::raw('sw.id'))
                        ->where('tw.lang_id', '=', $targetLangId);
                    });
            })
            ->where('sw.lang_id', $sourceLangId)
            ->where('sw.word', 'LIKE', "%{$keyword}%") // pridany LIKE filter
            ->groupBy('sw.id', 'sw.word')
            ->selectRaw('
                sw.id as id,
                sw.word as source_word,
                GROUP_CONCAT(DISTINCT tw.word ORDER BY tw.id SEPARATOR ", ") as target_word
            ')
            ->orderByDesc('sw.id');
    }
}
