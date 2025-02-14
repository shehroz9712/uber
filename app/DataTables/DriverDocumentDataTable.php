<?php

namespace App\DataTables;

use App\Models\DriverDocument;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

use App\Traits\DataTableTrait;

class DriverDocumentDataTable extends DataTable
{
    use DataTableTrait;
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('is_verified', function($query) {
                $is_verified = 'warning';
                switch ($query->is_verified) {
                    case 1:
                        $is_verified = 'success';
                        $is_verified_label =  __('message.approved');
                        break;
                    case 2:
                        $is_verified = 'danger';
                        $is_verified_label =  __('message.rejected');
                        break;
                    default:
                        $is_verified_label = __('message.pending');
                        break;
                }
                return '<span class="text-capitalize badge badge-light-'.$is_verified.' text-'.$is_verified.'">'.$is_verified_label.'</span>';
            })
            ->editColumn('driver_id' , function ($query) {
                return ($query->driver_id != null && isset($query->driver)) ? $query->driver->display_name : '';
            })
            ->editColumn('document_id' , function ($query) {
                return ($query->document_id != null && isset($query->document)) ? $query->document->name : '';
            })
            ->filterColumn('driver_id', function ( $query, $keyword ){
                $query->whereHas('driver',function ($q) use($keyword){
                    $q->where('display_name','like','%'.$keyword.'%');
                });
            })
            ->editColumn('created_at', function ($query) {
                return dateAgoFormate($query->created_at, true);
            })
            ->addIndexColumn()
            ->addColumn('action', 'driver_document.action')
            ->order(function ($query) {
                if (request()->has('order')) {
                    $order = request()->order[0];
                    $column_index = $order['column'];

                    $column_name = 'created_at';
                    $direction = 'desc';
                    if( $column_index != 0) {
                        $column_name = request()->columns[$column_index]['data'];
                        $direction = $order['dir'];
                    }
    
                    $query->orderBy($column_name, $direction);
                }
            })
            ->rawColumns([ 'action', 'is_verified' ]);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\DriverDocument $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $model = DriverDocument::myDocument();//->orderBy('id','desc');
        if (!empty(request()->driver_id)) {
            $model->where('driver_id', request()->driver_id);
        }

        if (request()->document_id) {
            $model->where('document_id', request('document_id'));
        }

        if (request()->has('is_verified') && request('is_verified') !== null) {
            $model->where('is_verified', request('is_verified'));
        }

        return $this->applyScopes($model);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('DT_RowIndex')
                ->searchable(false)
                ->title(__('message.srno'))
                ->orderable(false)
                ->width(60),
            Column::make('driver_id')->title( __('message.driver') ),
            Column::make('document_id')->title(__('message.document')),
            Column::make('expire_date')->title( __('message.expire_date') ),
            Column::make('created_at')->title( __('message.created_at') ),
            Column::make('is_verified')->title( __('message.is_verify') ),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'DriverDocuments_' . date('YmdHis');
    }
}
