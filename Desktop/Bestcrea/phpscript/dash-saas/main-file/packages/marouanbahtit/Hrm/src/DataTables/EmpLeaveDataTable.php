<?php

namespace Workdo\Hrm\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Leave;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class EmpLeaveDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['leave_type_id', 'applied_on', 'start_date', 'end_date', 'total_leave_days', 'leave_reason', 'status'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn();
        if (in_array(\Auth::user()->type, \Auth::user()->not_emp_type)) {
            $dataTable->editColumn('employee_id', function (Leave $leaves) {
                return $leaves->employee_id ? $leaves->EmployeeName->name ?? '-' : '-';
                $rowColumn[] = 'employee_id';
            });
        }
        $dataTable->editColumn('leave_type_id', function (Leave $leaves) {
            return $leaves->leave_type_id ? $leaves->leaveType->title ?? '-' : '-';
        })
            ->editColumn('applied_on', function (Leave $leaves) {
                return $leaves->applied_on ? company_date_formate($leaves->applied_on) ?? '-' : '-';
            })
            ->editColumn('start_date', function (Leave $leaves) {
                return $leaves->start_date ? company_date_formate($leaves->start_date) ?? '-' : '-';
            })
            ->editColumn('end_date', function (Leave $leaves) {
                return $leaves->end_date ? company_date_formate($leaves->end_date) ?? '-' : '-';
            })
            ->editColumn('total_leave_days', function (Leave $leaves) {
                return $leaves->total_leave_days ?? '-';
            })
            ->editColumn('leave_reason', function (Leave $leaves) {
                $url = route('leave.description', $leaves->id);
                $html = '<a class="action-item" data-url="' . $url . '" data-ajax-popup="true" data-bs-toggle="tooltip" title="' . __('Leave Reason') . '" data-title="' . __('Leave Reason') . '"><i class="fa fa-comment"></i></a>';
                return $html;
            })
            ->editColumn('status', function (Leave $leaves) {
                if ($leaves->status == 'Pending') {
                    $status = $leaves->status ?? '-';
                    $html = '<div class="badge bg-warning p-2 px-3 status-badge5">' . $status . ' ';
                    return $html;
                } elseif ($leaves->status == 'Approved') {
                    $status = $leaves->status ?? '-';
                    $html = '<div class="badge bg-success p-2 px-3 status-badge5">' . $status . ' ';
                    return $html;
                } else {
                    $status = $leaves->status ?? '-';
                    $html = '<div class="badge bg-danger p-2 px-3 status-badge5">' . $status . ' ';
                    return $html;
                }
            })
            ->filterColumn('employee_id', function ($query, $keyword) {
                $query->whereHas('EmployeeName', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            })
            ->filterColumn('leave_type_id', function ($query, $keyword) {
                $query->whereHas('leaveType', function ($q) use ($keyword) {
                    $q->where('title', 'like', "%$keyword%");
                });
            });
        if (\Laratrust::hasPermission('leave edit') || \Laratrust::hasPermission('leave delete') || \Laratrust::hasPermission('leave approver manage')) {
            $dataTable->addColumn('action', function (Leave $leaves) {
                return view('hrm::leave.button', compact('leaves'));
            });
            $rowColumn[] = 'action';
        }
        return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Leave $model, Request $request): QueryBuilder
    {
        if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $leaves   = $model->where('user_id', '=', Auth::user()->id)->where('workspace', getActiveWorkSpace())->orderBy('id', 'desc');
        } else {
            $leaves = $model->where('leaves.workspace', getActiveWorkSpace())->with(['leaveType', 'EmployeeName'])
                ->leftJoin('employees', 'employees.user_id', '=', 'leaves.user_id')
                ->leftJoin('users', 'users.id', '=', 'leaves.user_id')
                ->where('leaves.created_by', creatorId())
                ->select('leaves.*', 'leaves.id as ID', 'employees.*', 'users.*', 'leaves.user_id as user_name', 'leaves.applied_on as applied_on', 'leaves.start_date as start_date', 'leaves.end_date as end_date', 'leaves.id as id');
        }

        return $leaves;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('emp-leave-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => "_MENU_" . __('Entries Per Page'),
                "searchPlaceholder" => __('Search...'),
                "search" => "",
                "info" => __('Showing _START_ to _END_ of _TOTAL_ entries')
            ])
            ->initComplete('function() {
                var table = this;
                var searchInput = $(\'#\'+table.api().table().container().id+\' label input[type="search"]\');
                searchInput.removeClass(\'form-control form-control-sm\');
                searchInput.addClass(\'dataTable-input\');
                var select = $(table.api().table().container()).find(".dataTables_length select").removeClass(\'custom-select custom-select-sm form-control form-control-sm\').addClass(\'dataTable-selector\');
            }');

        $exportButtonConfig = [
            'extend' => 'collection',
            'className' => 'btn btn-light-secondary dropdown-toggle',
            'text' => '<i class="ti ti-download me-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Export"></i>',
            'buttons' => [
                [
                    'extend' => 'print',
                    'text' => '<i class="fas fa-print me-2"></i> ' . __('Print'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
                [
                    'extend' => 'csv',
                    'text' => '<i class="fas fa-file-csv me-2"></i> ' . __('CSV'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
                [
                    'extend' => 'excel',
                    'text' => '<i class="fas fa-file-excel me-2"></i> ' . __('Excel'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
            ],
        ];

        $buttonsConfig = array_merge([
            $exportButtonConfig,
            [
                'extend' => 'reset',
                'className' => 'btn btn-light-danger',
            ],
            [
                'extend' => 'reload',
                'className' => 'btn btn-light-warning',
            ],
        ]);

        $dataTable->parameters([
            "dom" =>  "
        <'dataTable-top'<'dataTable-dropdown page-dropdown'l><'dataTable-botton table-btn dataTable-search tb-search  d-flex justify-content-end gap-2'Bf>>
        <'dataTable-container'<'col-sm-12'tr>>
        <'dataTable-bottom row'<'col-5'i><'col-7'p>>",
            'buttons' => $buttonsConfig,
            "drawCallback" => 'function( settings ) {
                var tooltipTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=tooltip]")
                  );
                  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                  });
                  var popoverTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=popover]")
                  );
                  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                  });
                  var toastElList = [].slice.call(document.querySelectorAll(".toast"));
                  var toastList = toastElList.map(function (toastEl) {
                    return new bootstrap.Toast(toastEl);
                  });
            }'
        ]);

        $dataTable->language([
            'buttons' => [
                'create' => __('Create'),
                'export' => __('Export'),
                'print' => __('Print'),
                'reset' => __('Reset'),
                'reload' => __('Reload'),
                'excel' => __('Excel'),
                'csv' => __('CSV'),
            ]
        ]);

        return $dataTable;
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $column = [
            Column::make('leave_type_id')->title(__('Leave Type')),
            Column::make('applied_on')->title(__('Applied On'))->name('leaves.applied_on'),
            Column::make('start_date')->title(__('Start Date'))->name('leaves.start_date'),
            Column::make('end_date')->title(__('End Date'))->name('leaves.end_date'),
            Column::make('total_leave_days')->title(__('Total days')),
            Column::make('leave_reason')->title(__('Leave Reason')),
            Column::make('status')->title(__('Status')),
        ];
        if (in_array(\Auth::user()->type, \Auth::user()->not_emp_type)) {
            $employee = [
                Column::make('id')->name('leaves.id')->searchable(false)->visible(false)->exportable(false)->printable(false),
                Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
                Column::make('employee_id')->title(__('Employee'))->name('leaves.user_id'),
            ];
            $column = array_merge($employee, $column);
        } else {
            $employee = [
                Column::make('id')->name('leaves.id')->searchable(false)->visible(false)->exportable(false)->printable(false),
                Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            ];
            $column = array_merge($employee, $column);
        }
        if (
            \Laratrust::hasPermission('leave edit') ||
            \Laratrust::hasPermission('leave delete') ||
            \Laratrust::hasPermission('leave approver manage')
        ) {
            $action = [
                Column::computed('action')
                    ->title(__('Action'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(60)
                    
            ];

            $column = array_merge($column, $action);
        }

        return $column;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Leaves_' . date('YmdHis');
    }
}
