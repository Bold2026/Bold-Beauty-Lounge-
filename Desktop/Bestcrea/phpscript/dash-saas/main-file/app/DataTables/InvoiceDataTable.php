<?php

namespace App\DataTables;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class InvoiceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('invoice_id',function(Invoice $invoice){
                if (\Laratrust::hasPermission('invoice show'))
                {
                    $url = route('invoice.show', \Crypt::encrypt($invoice->id));
                    return '<a href="'.$url.'" class="btn btn-outline-primary">'. Invoice::invoiceNumberFormat($invoice->invoice_id) .'</a>';
                }
                else{
                    return ' <a href="#" class="btn btn-outline-primary">'. Invoice::invoiceNumberFormat($invoice->invoice_id) .'</a>';
                }
            })
            ->editColumn('account_type', function (Invoice $invoice) {
                return $invoice->account_type == 'Taskly' ? 'Project': preg_replace('/([a-z])([A-Z])/', '$1 $2', $invoice->account_type);
            })
            ->editColumn('issue_date', function (Invoice $invoice) {
                return company_date_formate($invoice->issue_date);
            })
            ->editColumn('due_date', function (Invoice $invoice) {
                if ($invoice->due_date < date('Y-m-d')){
                    return '<p class="text-danger mb-0">'. company_date_formate($invoice->due_date) .'</p>';
                }
                else{
                    return company_date_formate($invoice->due_date);
                }
            })
            ->addColumn('total_amount', function (Invoice $invoice) {
                    return currency_format_with_sym($invoice->getTotal());
            })
            ->addColumn('due_amount', function (Invoice $invoice) {
                return currency_format_with_sym($invoice->getDue());
            })
            ->editColumn('status', function (Invoice $invoice) {
                if ($invoice->status == 0)
                {
                    $class = 'bg-info';
                }
                elseif($invoice->status == 1)
                {
                    $class = 'bg-primary';
                }
                elseif($invoice->status == 2)
                {
                    $class = 'bg-secondary';
                }
                elseif($invoice->status == 3)
                {
                    $class = 'bg-warning';
                }
                elseif($invoice->status == 4)
                {
                    $class = 'bg-success';
                }else
                {
                    $class = 'bg-dark';
                }
                return '<span class="badge fix_badges '.$class.' p-2 px-3">'. Invoice::$statues[$invoice->status] .'</span>';

            })
            ->addColumn('action', function (Invoice $invoice) {
                return view('invoice.action', compact('invoice'));
            })
            ->rawColumns(['invoice_id','issue_date','due_date','total_amount','due_amount','status','action']);

        return $dataTable;
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Invoice $model,Request $request): QueryBuilder
    {
        if (Auth::user()->type != 'company') {
            $query = $model->join('users', 'invoices.user_id', '=', 'users.id')
                ->where('users.id', Auth::user()->id)->select('invoices.*')
                ->where('invoices.status', '!=', 0)
                ->where('invoices.workspace', getActiveWorkSpace());
        } else {
            $query = $model->where('workspace', getActiveWorkSpace());
        }

        if (!empty($request->customer)) {

            $query->where('user_id', '=', $request->customer);
        }
        if (!empty($request->issue_date)) {
            $date_range = explode('to', $request->issue_date);
            if (count($date_range) == 2) {
                $query->whereBetween('issue_date', $date_range);
            } else {
                $query->where('issue_date', $date_range[0]);
            }
        }
        if ($request->status != null) {
            $query->where('status', $request->status);
        }

        if (!empty($request->account_type) && $request->account_type != 'all') {
            $query->where('account_type', $request->account_type);
        } else {
            $query->whereIn('account_type', ActivatedModule());
        }

        return $query->with('customers');

    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
                    ->setTableId('invoice-table')
                    ->orderBy(0)
                    ->columns($this->getColumns())
                    ->ajax([
                        'data' => 'function(d) {
                            var issue_date = $("input[name=issue_date]").val();
                            d.issue_date = issue_date

                            var customer = $("select[name=customer]").val();
                            d.customer = customer

                            var status = $("select[name=status]").val();
                            d.status = status

                            var account_type = $("select[name=account_type]").val();
                            d.account_type = account_type
                        }',
                    ])
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

                                            if (!$("input[name=issue_date]").val() && !$("select[name=customer]").val() && !$("select[name=status]").val() && !$("select[name=account_type]").val()) {
                                                toastrs("Error!", "Please select Atleast One Filter ", "error");
                                                return;
                                            }

                                            $("#invoice-table").DataTable().draw();
                                        });

                                        $("body").on("click", "#clearfilter", function() {
                                            $("input[name=issue_date]").val("")
                                            $("select[name=customer]").val("")
                                            $("select[name=status]").val("")
                                            $("select[name=account_type]").val("")
                                            $("#invoice-table").DataTable().draw();
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
        return [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('invoice_id')->title(__('Invoice')),
            Column::make('account_type')->title(__('Account Type')),
            Column::make('issue_date')->title(__('Issue Date')),
            Column::make('due_date')->title(__('Due Date')),
            Column::computed('total_amount')->title(__('Total Amount')),
            Column::computed('due_amount')->title(__('Due Amount')),
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
        return 'Invoice_' . date('YmdHis');
    }
}
