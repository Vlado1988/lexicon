<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\VocabularyDataTable;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Word;
use Illuminate\Http\Request;

class VocabularyController extends Controller
{
    public function index(VocabularyDataTable $dataTable)
    {
        $languages = Language::orderBy('name')->get();
        return $dataTable->render('admin.vocabulary.index', [
            'languages' => $languages,
        ]);
    }

    public function create()
    {
        $langCode = request()->lang_code;
        $languages = Language::orderBy('name')->get();

        return view('admin.vocabulary.create', compact(
            'languages',
            'langCode'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'language' => ['required'],
            'words' => ['required'],
        ]);

        $words = explode(',', trim($request->words, ','));

        $wordsSaved = 0;

        foreach($words as $word) {
            if(!Word::where('word', $word)->where('lang_id', $request->language)->exists()) {
                $trimmedWord = trim($word);

                $wordModel = new Word();
                $wordModel->word = $trimmedWord;
                $wordModel->search_key = generate_search_key($trimmedWord);
                $wordModel->lang_id = $request->language;
                $wordModel->save();

                $wordsSaved++;
            }
        }

        $result = (object)[
            'message' => 'Data saved successfully',
            'status' => 'success',
        ];

        if($wordsSaved == 0) {
            $result = (object)[
                'message' => 'All words already exist',
                'status' => 'error',
            ];
        }

        return redirect()->to('/admin/vocabulary/' . $request->lang_code)->with($result->status, $result->message);
    }

    public function edit(Request $request)
    {
        $editWord = Word::findOrFail($request->word_id);
        $langCode = $request->lang_code;
        $languages = Language::orderBy('name')->get();

        return view('admin.vocabulary.edit', compact(
            'languages',
            'langCode',
            'editWord'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'language' => ['required'],
            'word' => ['required', 'max:255'],
        ]);

        $trimmedWord = trim($request->word);

        $wordModel = Word::findOrFail($request->word_id);
        $wordModel->word = $trimmedWord;
        $wordModel->search_key = generate_search_key($trimmedWord);
        $wordModel->lang_id = $request->language;
        $wordModel->save();

        $langCode = $request->lang_code;

        if($langCode) {
            return redirect()->to('/admin/vocabulary/' . $langCode)->with('success', 'Word updated successfully!');
        }
        return redirect()->route('admin.vocabulary.index')->with('success', 'Word updated successfully!');
    }

    public function destroy(string $id)
    {
        Word::findOrFail($id)->delete();

        return response(['status' => 'success', 'message' => 'Word deleted successfully!']);
    }
}
