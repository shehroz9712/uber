<?php

namespace App\DataTables;

use App\Models\Complaint;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

use App\Traits\DataTableTrait;

class ComplaintDataTable extends DataTable
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
            
            ->editColumn('driver_id' , function ( $complaint ) {
                return $complaint->driver_id != null ? optional($complaint->driver)->display_name : '';
            })

            ->filterColumn('driver_id', function( $query, $keyword ){
                $query->whereHas('driver', function ($q) use($keyword){
                    $q->where('display_name', 'like' , '%'.$keyword.'%');
                });
            })

            ->editColumn('rider_id' , function ( $complaint ) {
                return $complaint->rider_id != null ? optional($complaint->rider)->display_name : '';
            })

            ->filterColumn('rider_id', function( $query, $keyword ){
                $query->whereHas('rider', function ($q) use($keyword){
                    $q->where('display_name', 'like' , '%'.$keyword.'%');
                });
            })

            ->editColumn('status', function($query) {
                $status = 'warning';
                switch ($query->status) {
                    case 'investigation':
                        $status = 'primary';
                        break;
                    case 'resolved':
                        $status = 'success';
                        break;
                    default:
                        break;
                }
                return '<span class="text-' .$status .' badge badge-light-'.$status.'">'.__('message.'.$query->status).'</span>';
            })
            ->editColumn('created_at', function ($query) {
                return dateAgoFormate($query->created_at, true);
            })
            ->addColumn('action', function($complaint){
                $id = $complaint->id;
                return view('complaint.action',compact('complaint','id'))->render();
            })
            ->addIndexColumn()
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
            ->rawColumns([ 'action', 'status' ]);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Complaint $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $model = Complaint::query();

        if (!empty(request()->driver_id)) {
            $model->where('driver_id', request()->driver_id);
        }
    
        if (!empty(request()->rider_id)) {
            $model->where('rider_id', request()->rider_id);
        }

        if (!empty(request()->complaint_by)) {
            $model->where('complaint_by', request()->complaint_by);
        }
        
        if (!empty(request()->status)) {
            $model->where('status', request()->status);
        }

        $complaint_type = isset($_GET['complaint_type']) ? $_GET['complaint_type'] : null;
        if (in_array($complaint_type,['pending','investigation','resolved'])) {
            # code...
            $model = $model->where('status',$complaint_type);
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
            Column::make('ride_request_id')->title( __('message.riderequest') ),
            Column::make('driver_id')->title( __('message.driver') ),
            Column::make('rider_id')->title( __('message.rider') ),
            Column::make('complaint_by')->title( __('message.complaint_by') ),
            Column::make('subject')->title( __('message.subject') ),
            Column::make('created_at')->title( __('message.created_at') ),
            Column::make('status')->title( __('message.status') ),
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
        return 'Complaints_' . date('YmdHis');
    }
}
