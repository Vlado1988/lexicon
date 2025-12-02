<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
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
}
