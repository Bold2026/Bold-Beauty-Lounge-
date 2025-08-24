<?php

namespace Workdo\Hrm\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Employee;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class EmployeeDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['employee_id', 'name', 'email', 'branch_id', 'department_id', 'designation_id', 'company_doj'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('employee_id', function (User $employees) {
                if (!empty($employees->employee_id)) {
                    if (\Laratrust::hasPermission('employee show') && $employees->is_disable == 1) {
                        $url = route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employees->id));
                        $emp_id = Employee::employeeIdFormat($employees->employee_id);
                        $html = '<a class="btn btn-outline-primary" href="' . $url . '">
                                        ' . $emp_id . '
                                    </a>';
                        return $html;
                    } else {
                        $emp_id = Employee::employeeIdFormat($employees->employee_id);
                        $html = '<a href="#" class="btn btn-outline-primary">' . $emp_id . '</a>';
                        return $html;
                    }
                } else {
                    $html = '--';
                    return $html;
                }
            })
            ->editColumn('name', function (User $employees) {
                return $employees->name ?? '-';
            })
            ->editColumn('email', function (User $employees) {
                return $employees->email ?? '-';
            })
            ->editColumn('branch_id', function (User $employees) {
                return $employees->branches_name ?? '-';
            })
            ->editColumn('department_id', function (User $employees) {
                return $employees->departments_name ?? '-';
            })
            ->editColumn('designation_id', function (User $employees) {
                return $employees->designations_name ?? '-';
            })
            ->editColumn('company_doj', function (User $employees) {
                return $employees->company_doj ? company_date_formate($employees->company_doj ?? '-') : '-';
            });
        if (\Laratrust::hasPermission('employee show') || \Laratrust::hasPermission('employee edit') || \Laratrust::hasPermission('employee delete')) {
            $dataTable->addColumn('action', function (User $employees) {
                return view('hrm::employee.button', compact('employees'));
            });
            $rowColumn[] = 'action';
        }
        return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model, Request $request): QueryBuilder
    {
        if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $employees = $model->where('workspace_id', getActiveWorkSpace())
                ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
                ->leftJoin('branches', 'employees.branch_id', '=', 'branches.id')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
                ->where('users.id', Auth::user()->id)
                ->select('users.*', 'users.id as ID', 'employees.*', 'users.name as name', 'users.email as email', 'users.id as id', 'branches.name as branches_name', 'departments.name as departments_name', 'designations.name as designations_name');
        } elseif (Auth::user()->isAbleTo('employee manage')) {
            $employees = $model->where('workspace_id', getActiveWorkSpace())
                ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
                ->leftJoin('branches', 'employees.branch_id', '=', 'branches.id')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
                ->where('users.created_by', creatorId())->emp()
                ->select('users.*', 'users.id as ID', 'employees.*', 'users.name as name', 'users.email as email', 'users.id as id', 'branches.name as branches_name', 'departments.name as departments_name', 'designations.name as designations_name');
        }

        return $employees;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('employees-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
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
        $company_settings = getCompanyAllSetting();
        $column = [
            Column::make('id')->name('users.id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('employee_id')->title(__('Employee ID'))->name('users.id'),
            Column::make('name')->title(__('Name'))->name('users.name'),
            Column::make('email')->title(__('Email'))->name('users.email'),
            Column::make('branch_id')->title(!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'))->name('branches.name'),
            Column::make('department_id')->title(!empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department'))->name('departments.name'),
            Column::make('designation_id')->title(!empty($company_settings['hrm_designation_name']) ? $company_settings['hrm_designation_name'] : __('Designation'))->name(('designations.name')),
            Column::make('company_doj')->title(__('Date Of Joining'))->name('employees.company_doj'),
        ];
        if (
            \Laratrust::hasPermission('employee show') ||
            \Laratrust::hasPermission('employee edit') ||
            \Laratrust::hasPermission('employee delete')
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
        return 'Employees_' . date('YmdHis');
    }
}
