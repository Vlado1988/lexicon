<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Word;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $languages = Language::where('status', 'active')->orderBy('name')->get();
        return view('home', compact(
            'languages',
        ));
    }

    public function translate(Request $request)
    {
        $request->validate([
            'search_text' => ['required'],
            'source_language' => ['required', 'numeric'],
            'target_language' => ['required', 'numeric'],
        ]);

        $languages = Language::where('status', 'active')->orderBy('name')->get();
        $translations = Word::getTranslationsByWordLike($request->search_text, $request->source_language, $request->target_language)->get();

        return view('home', compact(
            'languages',
            'translations'
        ));
    }
}
