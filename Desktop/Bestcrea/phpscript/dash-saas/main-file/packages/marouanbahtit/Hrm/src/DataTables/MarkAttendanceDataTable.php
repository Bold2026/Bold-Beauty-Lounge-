<?php

namespace Workdo\Hrm\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Attendance;
use Workdo\Hrm\Entities\Employee;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class MarkAttendanceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['date', 'status', 'clock_in', 'clock_out', 'late', 'early_leaving', 'overtime'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn();
        if (\Laratrust::hasPermission('attendance create') || \Laratrust::hasPermission('attendance edit')) {
            $dataTable->editColumn('employee_id', function (Attendance $attendances) {
                return $attendances->employee_id ? $attendances->employees->name ?? '-' : '-';
                $rowColumn[] = 'employee_id';
            })
            ->filterColumn('employee_id', function ($query, $keyword) {
                $query->whereHas('employees', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            });
        }
        $dataTable->editColumn('date', function (Attendance $attendances) {
            return $attendances->date ? company_date_formate($attendances->date) ?? '-' : '-';
        })
            ->editColumn('status', function (Attendance $attendances) {
                return $attendances->status ?? '-';
            })
            ->editColumn('clock_in', function (Attendance $attendances) {
                return $attendances->clock_in ?? '-';
            })
            ->editColumn('clock_out', function (Attendance $attendances) {
                return $attendances->clock_out ?? '-';
            })
            ->editColumn('late', function (Attendance $attendances) {
                return $attendances->late ?? '-';
            })
            ->editColumn('early_leaving', function (Attendance $attendances) {
                return $attendances->early_leaving ?? '-';
            })
            ->editColumn('overtime', function (Attendance $attendances) {
                return $attendances->overtime ?? '-';
            });
        if (\Laratrust::hasPermission('attendance edit') || \Laratrust::hasPermission('attendance delete')) {
            $dataTable->addColumn('action', function (Attendance $attendances) {
                return view('hrm::attendance.button', compact('attendances'));
            });
            $rowColumn[] = 'action';
        }
        return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Attendance $model, Request $request): QueryBuilder
    {
        if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $attendances = $model->where('employee_id', Auth::user()->id)->where('workspace', getActiveWorkSpace());
            if ($request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $attendances->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            } elseif ($request->type == 'daily' && !empty($request->date)) {
                $attendances->where('date', $request->date);
            } else {
                $month      = date('m');
                $year       = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $attendances->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            }

            $attendances = $attendances;
        } else {

            $employee = User::where('workspace_id', getActiveWorkSpace())
                ->leftjoin('employees', 'users.id', '=', 'employees.user_id')
                ->where('users.created_by', creatorId())->emp()
                ->select('users.id');
            if (!empty($request->branch)) {
                $employee->where('branch_id', $request->branch);
            }

            if (!empty($request->department)) {
                $employee->where('department_id', $request->department);
            }

            $employee = $employee->get()->pluck('id');

            $attendances = Attendance::whereIn('employee_id', $employee)->where('workspace', getActiveWorkSpace())->with('employees');

            if ($request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));

                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                $attendances->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            } elseif ($request->type == 'daily' && !empty($request->date)) {
                $attendances->where('date', $request->date);
            } else {

                $month      = date('m');
                $year       = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date($year . '-' . $month . '-t');

                $attendances->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            }
            $attendances = $attendances;
        }

        return $attendances;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('mark-attendance-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                    var type = $("input[name=type]:radio:checked").val();
                    d.type = type

                    if (type == "monthly") {
                        var month = $("input[name=month]").val();
                        d.month = month;
                    }else{
                        var date = $("input[name=date]").val();
                        d.date = date;
                    }

                    var branch = $("select[name=branch]").val();
                    d.branch = branch

                    var department = $("select[name=department]").val();
                    d.department = department
                }',
            ])
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

                $("body").on("click", "#applyfilter", function() {

                    if (!$("input[name=type]:radio:checked").val() && !$("input[name=month]").val() && !$("input[name=date]").val() && !$("select[name=branch]").val() && !$("select[name=department]").val()) {
                        toastrs("Error!", "Please select Atleast One Filter ", "error");
                        return;
                    }

                    $("#mark-attendance-table").DataTable().draw();
                });

                $("body").on("click", "#clearfilter", function() {
                    $("input[name=type]:radio:checked").val("")
                    $("input[name=month]").val()
                    $("input[name=date]").val()
                    $("select[name=branch]").val("")
                    $("select[name=department]").val("")
                    $("#mark-attendance-table").DataTable().draw();
                });

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
            Column::make('date')->title(__('Date')),
            Column::make('status')->title(__('Status')),
            Column::make('clock_in')->title(__('Clock In')),
            Column::make('clock_out')->title(__('Clock Out')),
            Column::make('late')->title(__('Late')),
            Column::make('early_leaving')->title(__('Early Leaving')),
            Column::make('overtime')->title(__('Overtime')),
        ];
        if (
            \Laratrust::hasPermission('attendance edit') ||
            \Laratrust::hasPermission('attendance delete')
            ) {
                $employee = [
                Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
                Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
                Column::make('employee_id')->title(__('Employee')),
            ];
            $column = array_merge($employee, $column);
        }
        if (
            \Laratrust::hasPermission('attendance edit') ||
            \Laratrust::hasPermission('attendance delete')
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
        return 'Mark Attendance_' . date('YmdHis');
    }
}
