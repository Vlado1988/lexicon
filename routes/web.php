<?php

use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\VocabularyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportController;
use App\Models\Language;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/translation', [HomeController::class, 'translate'])->name('home.translate');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group( function() {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    /** Profile Routes */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /** Language Routes */
    Route::post('/language/change-status', [LanguageController::class, 'changeStatus'])->name('language.change-status');
    Route::resource('/language', LanguageController::class)->names('language');

    /** Vocabulary Routes */
    Route::prefix('vocabulary')->name('vocabulary.')->group(function () {
        Route::prefix('{lang_code}')->name('lang.')->group(function () {
            Route::pattern('lang_code', collect(
                Language::pluck('code')->map(fn($c) => strtolower($c))
            )->implode('|'));

            Route::get('/', [VocabularyController::class, 'index'])->name('index');
            Route::get('/create', [VocabularyController::class, 'create'])->name('create');
            Route::post('/store', [VocabularyController::class, 'store'])->name('store');
            Route::get('/{word_id}/edit', [VocabularyController::class, 'edit'])
                ->whereNumber('word_id')
                ->name('edit');
            Route::put('/{word_id}/update', [VocabularyController::class, 'update'])
                ->whereNumber('word_id')
                ->name('update');
        });

        Route::get('/', [VocabularyController::class, 'index'])->name('index');
        Route::get('/create', [VocabularyController::class, 'create'])->name('create');
        Route::post('/store', [VocabularyController::class, 'store'])->name('store');
        Route::get('/{word_id}/edit', [VocabularyController::class, 'edit'])
            ->whereNumber('word_id')
            ->name('edit');
        Route::put('/{word_id}/update', [VocabularyController::class, 'update'])
                ->whereNumber('word_id')
                ->name('update');
        Route::delete('/{word_id}/destroy', [VocabularyController::class, 'destroy'])->name('destroy');
    });

    /** Translation Routes */
    Route::get('/translation/get-translations', [TranslationController::class, 'getTranslations'])->name('translation.get-translations');
    Route::post('/translation/get-source-word-data', [TranslationController::class, 'getSourceWordData'])->name('translation.get-source-word-data');
    Route::post('/translation/get-translations-by-source-word', [TranslationController::class, 'getTranslationsBySourceWord'])->name('translation.get-translations-by-source-word');
    Route::resource('/translation', TranslationController::class)->names('translation');

    /** Import Routes */
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import/init', [ImportController::class, 'initImport'])->name('import.init');
} );

require __DIR__.'/auth.php';
