<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Word;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function index()
    {
        $languages = Language::all();

        return view('admin.export.index', compact(
            'languages'
        ));
    }

    public function init(Request $request)
    {
        $translations = Word::getTranslationsForLanguages($request->source_language, $request->target_language)->get();

        return response(['status' => 'success', 'translations' => $translations]);
    }
}
