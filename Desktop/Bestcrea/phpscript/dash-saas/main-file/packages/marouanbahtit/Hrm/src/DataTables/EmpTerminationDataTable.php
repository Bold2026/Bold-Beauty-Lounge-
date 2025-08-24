<?php

namespace Workdo\Hrm\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Termination;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class EmpTerminationDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['employee_id', 'termination_type', 'notice_date', 'termination_date', 'description'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn();
        if (in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $dataTable->editColumn('employee_id', function (Termination $terminations) {
                return $terminations->employee_id ? $terminations->users->name ?? '-' : '-';
                $rowColumn[] = 'employee_id';
            });
        }
        $dataTable->editColumn('termination_type', function (Termination $terminations) {
            return $terminations->termination_type ? $terminations->terminationType->name ?? '-' : '-';
        })
            ->editColumn('notice_date', function (Termination $terminations) {
                return $terminations->notice_date ? company_date_formate($terminations->notice_date) ?? '-' : '-';
            })
            ->editColumn('termination_date', function (Termination $terminations) {
                return $terminations->termination_date ? company_date_formate($terminations->termination_date) ?? '-' : '-';
            })
            ->editColumn('description', function (Termination $terminations) {
                $url = route('termination.description', $terminations->id);
                $html = '<a class="action-item" data-url="' . $url . '" data-ajax-popup="true" data-bs-toggle="tooltip" title="' . __('Description') . '" data-title="' . __('Description') . '"><i class="fa fa-comment"></i></a>';
                return $html;
            })
            ->filterColumn('employee_id', function ($query, $keyword) {
                $query->whereHas('users', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            })
            ->filterColumn('termination_type', function ($query, $keyword) {
                $query->whereHas('terminationType', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            });
        if (\Laratrust::hasPermission('termination edit') || \Laratrust::hasPermission('termination delete')) {
            $dataTable->addColumn('action', function (Termination $terminations) {
                return view('hrm::termination.button', compact('terminations'));
            });
            $rowColumn[] = 'action';
        }
        return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Termination $model, Request $request): QueryBuilder
    {
        if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $terminations = $model->where('user_id', '=', Auth::user()->id)->where('workspace', getActiveWorkSpace());
        } else {
            $terminations = $model->where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->with(['users', 'terminationType']);
        }

        return $terminations;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('emp-termination-table')
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
            Column::make('termination_type')->title(__('Termination Type')),
            Column::make('notice_date')->title(__('Notice Date')),
            Column::make('termination_date')->title(__('Termination Date')),
        ];
        if (in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $employee = [
                Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
                Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
                Column::make('employee_id')->title(__('Employee')),
            ];
            $column = array_merge($employee, $column);
        } else {
            $employee = [
                Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
                Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            ];
            $column = array_merge($employee, $column);
        }

        if (\Laratrust::hasPermission('termination description')) {
            $description = [
                Column::make('description')->title(__('Description')),
            ];
            $column = array_merge($column, $description);
        }
        if (
            \Laratrust::hasPermission('termination edit') ||
            \Laratrust::hasPermission('termination delete')
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
        return 'Terminations_' . date('YmdHis');
    }
}
