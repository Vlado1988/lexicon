<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\TranslationDataTable;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use App\Models\Word;
use App\Rules\CaseSensitiveUnique;
use DB;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class TranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TranslationDataTable $dataTable)
    {
        $languages = Language::orderBy('name')->get();
        return $dataTable->render('admin.translation.index', compact(
            'languages'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $languages = Language::where('status', 'active')->orderBy('name')->get();
        return view('admin.translation.create', compact(
            'languages'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_word_language' => ['required'],
            'target_word_language' => ['required'],
            'source_word' => ['required', 'max:255'],
            'target_words' => ['required', 'array', 'min:1'],
            'target_words.*' => ['required', 'string', 'max:255']
        ]);

        if($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('old_target_words', $request->target_words);
        }

        if($request->source_word_language === $request->target_word_language) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Source language and target language cannot be same')
                ->with('old_target_words', $request->target_words);
        }

        $sourceWord = Word::whereRaw('BINARY `word` = ? AND `lang_id` = ?', [
            trim($request->source_word),
            $request->source_word_language
        ])->first();

        if (! $sourceWord) {
            $sourceWord = Word::create([
                'word' => trim($request->source_word),
                'lang_id' => $request->source_word_language,
            ]);
        }

        $sourceWordId = $sourceWord->id;

        // Save target words
        foreach($request->target_words as $targetWord) {
            $targetWordResult = Word::whereRaw(
                'BINARY `word` = ? AND `lang_id` = ?',
                [trim($targetWord), $request->target_word_language]
            )->first();

            if (! $targetWordResult) {
                $targetWordResult = Word::create([
                    'word' => trim($targetWord),
                    'lang_id' => $request->target_word_language,
                ]);
            }

            $targetWordId = $targetWordResult->id;

            // If target word already exist, then get his ID
            if($targetWordId === null) {
                $targetWordId = Word::where('word', $targetWord)
                ->where('lang_id', $request->target_word_language)
                ->pluck('id')
                ->first();
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
            }
        }

        return redirect()->route('admin.translation.index', ['source_lang_id' => $request->source_word_language, 'target_lang_id' => $request->target_word_language])->with('success', 'Translation created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sourceLangId = request()->source_lang_id;
        $targetLangId = request()->target_lang_id;

        $editTranslation = Word::getTranslationForLanguages($id, request()->source_lang_id, request()->target_lang_id)
            ->firstOrFail();
        $languages = Language::where('status', 'active')->orderBy('name')->get();

        $targetWords = null;

        if($editTranslation->target_word) {
            $targetWords = explode(',', $editTranslation->target_word);
        }

        return view('admin.translation.edit', compact(
            'languages',
            'editTranslation',
            'targetWords',
            'sourceLangId',
            'targetLangId',
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'source_word_language' => ['required'],
            'target_word_language' => ['required'],
            'source_word' => ['required', 'max:255'],
            'target_words' => ['required', 'array', 'min:1'],
            'target_words.*' => ['required', 'string', 'max:255']
        ]);

        $sourceLangId = $request->source_word_language;
        $targetLangId = $request->target_word_language;

        if($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('old_target_words', $request->target_words);
        }

        if($request->source_word_language === $targetLangId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Source language and target language cannot be same')
                ->with('old_target_words', $request->target_words);
        }

        $sourceWord = Word::findOrFail($id);
        $sourceWord->word = $request->source_word;
        $sourceWord->lang_id = $sourceLangId;
        $sourceWord->save();

        $sourceWordId = $sourceWord->id;
        $targetWords = $request->target_words;

        // Delete old relations that not match new target words
        Translation::query()
            ->from('translations as t')
            ->select('t.*')
            ->join('words as sw', 't.source_word_id', '=', 'sw.id')
            ->join('words as tw', 't.target_word_id', '=', 'tw.id')
            ->where(function ($q) use ($sourceWordId, $targetWords, $sourceLangId, $targetLangId) {

                // 1) source → target smer
                $q->where('t.source_word_id', $sourceWordId)
                ->whereIn('t.target_word_id', Word::select('id')->whereNotIn('word', $targetWords))
                ->where('sw.lang_id', $sourceLangId)
                ->where('tw.lang_id', $targetLangId);
            })
            ->orWhere(function ($q) use ($sourceWordId, $targetWords, $sourceLangId, $targetLangId) {

                // 2) opačný smer (prehadzujú sa jazyky)
                $q->where('t.target_word_id', $sourceWordId)
                ->whereIn('t.source_word_id', Word::select('id')->whereNotIn('word', $targetWords))
                ->where('sw.lang_id', $targetLangId)
                ->where('tw.lang_id', $sourceLangId);
            })
            ->delete();

        // Save target words
        foreach($request->target_words as $targetWord) {
            $targetWord = Word::firstOrCreate(
                [
                    'word' => trim($targetWord),
                    'lang_id' => $request->target_word_language
                ]
            );

            $targetWordId = $targetWord->id;

            // If target word already exist, then get his ID
            if($targetWordId === null) {
                $targetWordId = Word::where('word', $targetWord)
                ->where('lang_id', $targetLangId)
                ->pluck('id')
                ->first();
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
            }
        }

        return redirect()->route('admin.translation.index', ['source_lang_id' => $request->source_word_language, 'target_lang_id' => $request->target_word_language])->with('success', 'Translation created successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Word::findOrFail($id)->delete();
        return response(['status' => 'success', 'message' => 'Translation deleted successfully!']);
    }

    public function getSourceWordData(Request $request)
    {
        $data = Word::whereLike('word', "%{$request->word}%")->where('lang_id', $request->sourceLangId)->get();
        return response(['status' => 'success', 'message' => 'Data loaded successfully', 'data' => $data]);
    }

    public function getTranslations(Request $request)
    {
        $translations = Word::getTranslationsForLanguages($request->sourceLangId, $request->targetLangId);

        return response(['status' => 'success', 'data' => $translations]);
    }

    public function getTranslationsBySourceWord(Request $request)
    {
        $sourceWord = $request->sourceWord;
        $sourceLangId = $request->sourceLangId;
        $targetLangId = $request->targetLangId;
        $targetWords = Word::getTranslationsByWord($sourceWord, $sourceLangId, $targetLangId)->get();

        return response(['status' => 'success', 'data' => $targetWords]);
    }
}
