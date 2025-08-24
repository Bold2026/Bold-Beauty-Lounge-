<?php

namespace Workdo\Hrm\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\CompanyPolicy;
use Workdo\Hrm\Entities\Employee;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CompanyPolicyDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['branch', 'title', 'attachment', 'description'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('branch', function (CompanyPolicy $companyPolicy) {
                return $companyPolicy->branch ? $companyPolicy->branches->name ?? '-' : '-';
            })
            ->editColumn('title', function (CompanyPolicy $companyPolicy) {
                return $companyPolicy->title ?? '-';
            })
            ->editColumn('attachment', function (CompanyPolicy $companyPolicy) {
                if (!empty($companyPolicy->attachment)) {
                    $get_file = get_file($companyPolicy->attachment);
                    $html = '<div class="action-btn me-2">
                                 <a class="mx-3 btn bg-primary btn-sm align-items-center" href="' . $get_file . '" download data-bs-toggle="tooltip" data-bs-original-title="' . __('Download') . '">
                                     <i class="ti ti-download text-white">
                                     </i>
                                  </a>
                             </div>
                             <div class="action-btn ">
                                 <a class="mx-3 btn bg-secondary btn-sm align-items-center" href="' . $get_file . '" target="_blank" data-bs-toggle="tooltip" data-bs-original-title="' . __('Preview') . '">
                                     <i class="ti ti-crosshair text-white" >
                                     </i>
                                 </a>
                             </div>';

                    return $html;
                } else {
                    $html = '--';
                    return $html;
                }
            })
            ->editColumn('description', function (CompanyPolicy $companyPolicy) {
                $url = route('company-policy.description', $companyPolicy->id);
                $html = '<a class="action-item" data-url="' . $url . '" data-ajax-popup="true" data-bs-toggle="tooltip" title="' . __('Description') . '" data-title="' . __('Description') . '"><i class="fa fa-comment"></i></a>';
                return $html;
            })
            ->filterColumn('branch', function ($query, $keyword) {
                $query->whereHas('branches', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            });
            if (\Laratrust::hasPermission('companypolicy edit') || \Laratrust::hasPermission('companypolicy delete')) {
                $dataTable->addColumn('action', function (CompanyPolicy $companyPolicy) {
                    return view('hrm::companyPolicy.button', compact('companyPolicy'));
                });
                $rowColumn[] = 'action';
            }
            return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(CompanyPolicy $model, Request $request): QueryBuilder
    {
        if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $currentEmployee = Employee::where('user_id', Auth::id())->first();
            $branchId = $currentEmployee->branch_id;

            $companyPolicy = $model->where('workspace', getActiveWorkSpace())->where('branch', $branchId)->where('created_by', '=', creatorId())->with('branches');

        }elseif (Auth::user()->isAbleTo('companypolicy manage')) {
            $companyPolicy = $model->where('workspace', getActiveWorkSpace())->where('created_by', '=', creatorId())->with('branches');
        }

        return $companyPolicy;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('company-policy-table')
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
        $company_settings = getCompanyAllSetting();
        $column = [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('branch')->title(!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch')),
            Column::make('title')->title(__('Title')),
            Column::make('attachment')->title(__('Attachment'))->searchable(false)->orderable(false)->exportable(false)->printable(false),
            Column::make('description')->title(__('Description')),
        ];

        if (
            \Laratrust::hasPermission('companypolicy edit') ||
            \Laratrust::hasPermission('companypolicy delete')
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
        return 'Company Policy_' . date('YmdHis');
    }
}
