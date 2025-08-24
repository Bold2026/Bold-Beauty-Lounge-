<?php

namespace App\DataTables;

use App\Models\CustomDomainRequest;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CustomDomainRequestDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('created_by', function (CustomDomainRequest $custom_domain_request) {
                return '<div class="font-style font-weight-bold">'. $custom_domain_request->user_name .'</div>';
            })
            ->editColumn('workspace', function (CustomDomainRequest $custom_domain_request) {
                return '<div class="font-style font-weight-bold">'. $custom_domain_request->workspace_name .'</div>';
            })
            ->editColumn('domain', function (CustomDomainRequest $custom_domain_request) {
                return '<div class="font-style font-weight-bold">'. $custom_domain_request->domain .'</div>';
            })
            ->editColumn('status', function (CustomDomainRequest $custom_domain_request) {
                if ($custom_domain_request->status == 0)
                {
                    return '<span class="badge fix_badges bg-danger p-2 px-3">'. \App\Models\CustomDomainRequest::$statues[$custom_domain_request->status] .'</span>';
                }
                elseif($custom_domain_request->status == 1){
                    return '<span class="badge fix_badges bg-primary p-2 px-3">'. \App\Models\CustomDomainRequest::$statues[$custom_domain_request->status] .'</span>';
                }
                elseif($custom_domain_request->status == 2){
                    return '<span class="badge fix_badges bg-warning p-2 px-3">'. \App\Models\CustomDomainRequest::$statues[$custom_domain_request->status] .'</span>';
                }
            })
            ->addColumn('action', function (CustomDomainRequest $custom_domain_request) {
                return view('custom_domain_request.action', compact('custom_domain_request'));
            })
            ->rawColumns(['created_by','workspace','domain','status','action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(CustomDomainRequest $model): QueryBuilder
    {
        return $model->select(['custom_domain_requests.*','users.name as user_name','work_spaces.name as workspace_name'])
                ->join('users','users.id','=','custom_domain_requests.created_by')
                ->leftjoin('work_spaces','work_spaces.id','=','custom_domain_requests.workspace');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
                    ->setTableId('customdomainrequest-table')
                    ->columns($this->getColumns())
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
        return [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('created_by')->title(__('Company Name'))->name('users.name'),
            Column::make('workspace')->title(__('Workspace Name'))->name('work_spaces.name'),
            Column::make('domain')->title(__('Custom Domain')),
            Column::make('status')->title(__('Status')),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)

        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'CustomDomainRequest_' . date('YmdHis');
    }
}
