<?php

namespace App\DataTables;

use App\Models\Translation;
use App\Models\Word;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class TranslationDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Translation> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function($query) {
                $sourceLangId = request()->sourceLangId;
                $targetLangId = request()->targetLangId;

                $urlParams = $sourceLangId && $targetLangId ? "?source_lang_id=$sourceLangId&target_lang_id=$targetLangId" : '';
                $deleteUrl = route('admin.translation.destroy', $query->id) . $urlParams;

                $editBtn = '<a href="' . url('/admin/translation/' . $query->id . '/edit?source_lang_id=' . $sourceLangId . '&target_lang_id=' . $targetLangId) . '"><i class="fa-solid fa-pen-to-square"></i></a>';
                // $deleteBtn = '<a href="' . $deleteUrl . '" class="delete_item delete_translation_item" data-url="' . $deleteUrl . '" data-id="'. $query->id .'"><i class="fa-solid fa-trash"></i></a>';
                $deleteBtn = '
                    <form action="' . $deleteUrl . '" class="delete_item delete_translation_item">
                        '. csrf_field() .'
                        '. method_field("DELETE") .'
                        <button><i class="fa-solid fa-trash"></i></button>
                    </form>';

                return '<div class="flex gap-2">' . $editBtn . $deleteBtn . '</div>';
            })
            ->addColumn('word_id', function($query) {
                return $query->id;
            })
            ->addColumn('source_word', function($query) {
                return $query->source_word;
            })
            ->addColumn('target_word', function($query) {
                return $query->target_word;
            })
            ->filterColumn('source_word', function($query, $keyword) {
                $query->where('source_word', 'LIKE', "%{$keyword}%");
            })
            ->filterColumn('target_word', function($query, $keyword) {
                return $query->where('target_word', 'LIKE', "%{$keyword}%");
            })
            ->rawColumns(['action', 'translation'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Translation>
     */
    public function query(Translation $model): QueryBuilder
    {
        $sourceLangId = request()->sourceLangId;
        $targetLangId = request()->targetLangId;

        if($sourceLangId && $targetLangId) {
            return Word::getTranslationsForLanguages($sourceLangId, $targetLangId);
        }

        return Word::query()->whereRaw('1=0');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('translation-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax('', "
                        data.sourceLangId = $('#source_word_language').val();
                        data.targetLangId = $('#target_word_language').val();
                    ")
                    ->serverSide(true)
                    ->processing(true)
                    ->orderBy(0)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
            Button::make('csv'),
            Button::make('pdf'),
            Button::make('print'),
            Button::make('reset'),
            Button::make('reload')
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('word_id'),
            Column::make('source_word'),
            Column::make('target_word'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Translation_' . date('YmdHis');
    }
}
