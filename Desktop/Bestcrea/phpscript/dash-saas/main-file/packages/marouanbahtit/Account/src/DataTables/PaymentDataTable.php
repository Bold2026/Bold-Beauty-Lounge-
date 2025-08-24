<?php

namespace Workdo\Account\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\Payment;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PaymentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rawColumn = ['date', 'amount', 'description'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('date', function (Payment $payment) {
                return company_date_formate($payment->date);
            })
            ->editColumn('amount', function (Payment $payment) {
                return currency_format_with_sym($payment->amount);
            })
            ->editColumn('description', function (Payment $payment) {
                $url = route('payment.description', $payment->id);
                $html = '<a class="action-item" data-url="' . $url . '" data-ajax-popup="true" data-bs-toggle="tooltip" title="' . __('Description') . '" data-title="' . __('Description') . '"><i class="fa fa-comment"></i></a>';
                return $html;
            });


        if (\Laratrust::hasPermission('expense payment delete') || \Laratrust::hasPermission('expense payment edit')) {
            $dataTable->addColumn('action', function (Payment $payment) {

                return view('account::payment.action', compact('payment'));
            });
            $rawColumn[] = 'action';
        }
        return $dataTable->rawColumns($rawColumn);
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Payment $model, Request $request)
    {
        $query = $model->select('payments.*', 'bank_accounts.bank_name as bank_name', 'bank_accounts.holder_name as holder_name', 'vendors.name as vendors_name', 'categories.name as category_name')->where('payments.workspace', '=', getActiveWorkSpace());



        if (!empty($request->date)) {
            $date_range = explode(',', $request->date);
            if (count($date_range) == 2) {
                $query->whereBetween('date', $date_range);
            } else {
                $query->where('date', $date_range[0]);
            }
        }
        if (!empty($request->vendor)) {
            $query->where('payments.vendor_id', '=', $request->vendor);
        }
        if (!empty($request->account)) {
            $query->where('payments.account_id', '=', $request->account);
        }

        if (!empty($request->category)) {
            $query->where('payments.category_id', '=', $request->category);
        }
        $payments = $query->join('vendors', 'payments.vendor_id', '=', 'vendors.id')
            ->join('bank_accounts', 'payments.account_id', '=', 'bank_accounts.id')
            ->join('categories', 'payments.category_id', '=', 'categories.id');

        return $payments;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('payment-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                var date = $("input[name=date]").val();
                d.date = date

                var account = $("select[name=account]").val();
                d.account = account

                var vendor = $("select[name=vendor]").val();
                d.vendor = vendor

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

                    if (!$("input[name=date]").val() && !$("select[name=account]").val() && !$("select[name=vendor]").val() && !$("select[name=category]").val()) {
                        toastrs("Error!", "Please select Atleast One Filter ", "error");
                        return;
                    }

                    $("#payment-table").DataTable().draw();
                });

                $("body").on("click", "#clearfilter", function() {
                    $("input[name=date]").val("")
                    $("select[name=account]").val("")
                    $("select[name=vendor]").val("")
                    $("select[name=category]").val("")
                    $("#payment-table").DataTable().draw();
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
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('date')->title(__('Date')),
            Column::make('amount')->title(__('Amount')),

            Column::make('bank_name')->title(__('Account'))->name('bank_accounts.bank_name'),
            Column::make('vendors_name')->title(__('Vendor'))->name('vendors.name'),
            Column::make('category_name')->title(__('Category'))->name('categories.name'),
            Column::make('reference')->title(__('Reference')),
            Column::make('description')->title(__('Description')),
        ];
        if (\Laratrust::hasPermission('expense payment delete') || \Laratrust::hasPermission('expense payment edit')) {
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
        return 'Payments_' . date('YmdHis');
    }
}
