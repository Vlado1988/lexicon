<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\LanguageDataTable;
use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LanguageDataTable $dataTable)
    {
        return $dataTable->render('admin.language.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.language.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'language' => ['required', 'max:255', 'unique:languages,name'],
            'code' => ['required', 'max:10', 'unique:languages,code'],
        ]);

        $language = new Language();
        $language->name = $request->language;
        $language->code = $request->code;
        $language->save();

        return redirect()->route('admin.language.index')->with('success', 'Language created successfully');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function changeStatus(Request $request)
    {
        $language = Language::findOrFail($request->id);
        $language->status = $request->status == 'true' ? 'active' : 'inactive';
        $language->save();

        return response(['status' => 'success', 'message' => 'Status changed']);
    }
}
