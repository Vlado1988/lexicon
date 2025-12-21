<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    protected $fillable = [
        'word',
        'search_key',
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
        $q1 = DB::table('words as sw')
            ->join('translations as t', 't.source_word_id', '=', 'sw.id')
            ->join('words as tw', 'tw.id', '=', 't.target_word_id')
            ->join('languages as sl', 'sw.lang_id', '=', 'sl.id')
            ->join('languages as tl', 'tw.lang_id', '=', 'tl.id')
            ->where('sw.lang_id', $sourceLangId)
            ->where('tw.lang_id', $targetLangId)
            ->select(
                'sw.id as id',
                'sl.code as source_language',
                'tl.code as target_language',
                'sw.word as source_word',
                'tw.word as target_word'
            );

        $q2 = DB::table('words as sw')
            ->join('translations as t', 't.target_word_id', '=', 'sw.id')
            ->join('words as tw', 'tw.id', '=', 't.source_word_id')
            ->join('languages as sl', 'tw.lang_id', '=', 'sl.id')
            ->join('languages as tl', 'sw.lang_id', '=', 'tl.id')
            ->where('sw.lang_id', $sourceLangId)
            ->where('tw.lang_id', $targetLangId)
            ->select(
                'sw.id as id',
                'tl.code as source_language',
                'sl.code as target_language',
                'sw.word as source_word',
                'tw.word as target_word'
            );

        $union = $q1->union($q2);

        return Word::query()
            ->fromSub($union, 'x')
            ->select(
                'x.id',
                'x.source_word',
                DB::raw('MAX(x.source_language) as source_language'),
                DB::raw('MAX(x.target_language) as target_language'),
                DB::raw('GROUP_CONCAT(DISTINCT x.target_word ORDER BY x.target_word SEPARATOR ", ") as target_word')
            )
            ->groupBy('x.id', 'x.source_word')
            ->orderByDesc('x.id');
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
                // $query->where('sw.word',  $word)
                //     ->orWhere('sw.word', 'like', "$word %");

                // $query->whereRaw(
                //         "
                //         REPLACE(
                //             REGEXP_REPLACE(sw.word, '[^[:alpha:][:digit:] ]', ''),
                //             ' ',
                //             ''
                //         )
                //         COLLATE utf8mb4_unicode_ci
                //         =
                //         REPLACE(
                //             REGEXP_REPLACE(?, '[^[:alpha:][:digit:] ]', ''),
                //             ' ',
                //             ''
                //         )
                //         ",
                //         [$word]
                //     )
                //     ->orWhereRaw(
                //         "
                //         REPLACE(
                //             REGEXP_REPLACE(sw.word, '[^[:alpha:][:digit:] ]', ''),
                //             ' ',
                //             ''
                //         )
                //         COLLATE utf8mb4_unicode_ci
                //         LIKE
                //         CONCAT(
                //             REPLACE(
                //                 REGEXP_REPLACE(?, '[^[:alpha:][:digit:] ]', ''),
                //                 ' ',
                //                 ''
                //             ),
                //             '%'
                //         )
                //         ",
                //         [$word]
                //     );

                $searchKey = strtolower(
                    preg_replace('/[^[:alpha:][:digit:] ]/u', '', $word)
                );
                $searchKey = str_replace(' ', '', $searchKey);

                $query->where(function ($query) use ($searchKey) {
                    $query->where('sw.search_key', $searchKey)
                        ->orWhere('sw.search_key', 'like', $searchKey.'%');
                });
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
