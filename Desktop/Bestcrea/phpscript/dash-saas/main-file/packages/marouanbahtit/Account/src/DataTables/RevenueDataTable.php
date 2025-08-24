<?php

namespace Workdo\Account\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\Revenue;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class RevenueDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rawColumn = ['date', 'amount', 'add_receipt','description'];
        $dataTable = (new EloquentDataTable($query))
            ->editColumn('date', function (Revenue $revenue) {
                return company_date_formate($revenue->date);
            })


            ->editColumn('amount', function (Revenue $revenue) {
                return currency_format_with_sym($revenue->amount);
            })
            ->editColumn('add_receipt', function (Revenue $revenue) {
                if (!empty($revenue->add_receipt)) {
                    $fileUrl = get_file($revenue->add_receipt);
                    $html = '<div class="action-btn  me-2">
                            <a href="' . htmlspecialchars($fileUrl, ENT_QUOTES, 'UTF-8') . '" download=""
                                class="mx-3 bg-primary btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                title="Download" target="_blank">
                                <i class="ti ti-download text-white"></i>
                            </a>
                        </div>
                        <div class="action-btn">
                            <a href="' . htmlspecialchars($fileUrl, ENT_QUOTES, 'UTF-8') . '"
                                class="mx-3 btn bg-secondary btn-sm align-items-center" data-bs-toggle="tooltip"
                                title="Show" target="_blank">
                                <i class="ti ti-crosshair text-white"></i>
                            </a>
                        </div>';
                } else {
                    $html = '';
                }
                return $html;
            })
            ->editColumn('description', function (Revenue $revenue) {
                // Use PHP to determine the description content
                $description = !empty($revenue->description) ? $revenue->description : '-';

                // Return the HTML with the PHP variable included
                $html = '<p style="white-space: nowrap; width: 200px; overflow: hidden; text-overflow: ellipsis;">' . htmlspecialchars($description) . '</p>';

                return $html;
            });


        if (\Laratrust::hasPermission('revenue edit') || \Laratrust::hasPermission('revenue delete')) {
            $dataTable->addColumn('action', function (Revenue $revenue) {

                return view('account::revenue.action', compact('revenue'));
            });
            $rawColumn[] = 'action';
        }
        return $dataTable->rawColumns($rawColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Revenue $model, Request $request)
    {
        $query =  $model->select('revenues.*', 'bank_accounts.bank_name as bank_name', 'bank_accounts.holder_name as holder_name', 'customers.name as customer_name', 'categories.name as category_name')->where('revenues.workspace', '=', getActiveWorkSpace());


        if (count(explode('to', $request->date)) > 1) {
            $date_range = explode(' to ', $request->date);
            $query->whereBetween('date', $date_range);
        } elseif (!empty($request->date)) {
            $date_range = [$request->date, $request->date];
            $query->whereBetween('date', $date_range);
        }

        if (!empty($request->customer)) {
            $query->where('revenues.customer_id', '=', $request->customer);
        }

        if (!empty($request->account)) {
            $query->where('revenues.account_id', '=', $request->account);
        }

        if (!empty($request->category)) {
            $query->where('revenues.category_id', '=', $request->category);
        }

        if (!empty($request->payment)) {
            $query->where('revenues.payment_method', '=', $request->payment);
        }
        $revenues = $query->join('customers', 'revenues.customer_id', '=', 'customers.id')
            ->join('bank_accounts', 'revenues.account_id', '=', 'bank_accounts.id')
            ->join('categories', 'revenues.category_id', '=', 'categories.id');


        return $revenues;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('transfers-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                var date = $("input[name=date]").val();
                d.date = date

                var account = $("select[name=account]").val();
                d.account = account

                var customer = $("select[name=customer]").val();
                d.customer = customer

                var category = $("select[name=category]").val();
                d.category = category

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

                    if (!$("input[name=date]").val() && !$("select[name=account]").val() && !$("select[name=customer]").val() && !$("select[name=category]").val()) {
                        toastrs("Error!", "Please select Atleast One Filter ", "error");
                        return;
                    }

                    $("#transfers-table").DataTable().draw();
                });

                $("body").on("click", "#clearfilter", function() {
                    $("input[name=date]").val("")
                    $("select[name=account]").val("")
                    $("select[name=customer]").val("")
                    $("select[name=category]").val("")
                    $("#transfers-table").DataTable().draw();
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
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('date')->title(__('Date')),
            Column::make('amount')->title(__('Amount')),
            Column::make('bank_name')->title(__('Account'))->name('bank_accounts.bank_name'),
            Column::make('customer_name')->title(__('Customer'))->name('customers.name'),
            Column::make('category_name')->title(__('Category'))->name('categories.name'),
            Column::make('reference')->title(__('Reference')),
            Column::make('description')->title(__('Description')),
            Column::make('add_receipt')->title(__('Payment Receipt')),
        ];
        if (\Laratrust::hasPermission('revenue edit') || \Laratrust::hasPermission('revenue delete')) {
            $action = [
                Column::computed('action')
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
        return 'Revenues_' . date('YmdHis');
    }
}
