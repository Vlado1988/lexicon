<?php

namespace App\DataTables;

use App\Models\Word;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class VocabularyDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Word> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function($query) {
                $langCode = request()->route('lang_code') ? request()->route('lang_code') . '/' : '';
                $editBtn = '<a href="' . url('/admin/vocabulary/' . $langCode . $query->id . '/edit') . '"><i class="fa-solid fa-pen-to-square"></i></a>';
                $deleteBtn = '
                    <form action="' . url('/admin/vocabulary/' . $query->id . '/destroy') . '" class="delete_item">
                        '. csrf_field() .'
                        '. method_field("DELETE") .'
                        <button><i class="fa-solid fa-trash"></i></button>
                    </form>';

                return '<div class="flex gap-2">' . $editBtn . $deleteBtn . '</div>';
            })
            ->addColumn('lang_code', function($query) {
                return $query->language?->code ?: '-';
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Word>
     */
    public function query(Word $model): QueryBuilder
    {
        $langCode = request()->route('lang_code') ?: null;

        if($langCode) {
            return $model->newQuery()
                        ->whereHas('language', function($query) use($langCode) {
                            $query->where('code', $langCode);
                        })
                        ->with(['language']);
        }
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('vocabulary-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
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
            Column::make('id'),
            Column::make('word'),
            Column::make('lang_code'),
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
        return 'Vocabulary_' . date('YmdHis');
    }
}
