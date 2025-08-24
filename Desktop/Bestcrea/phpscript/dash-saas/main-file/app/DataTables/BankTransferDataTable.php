<?php

namespace App\DataTables;

use App\Models\BankTransfer;
use App\Models\BankTransferPayment;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class BankTransferDataTable extends DataTable
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
            ->editColumn('created_at', function (BankTransferPayment $bank_transfer_payment) {
                return company_datetime_formate($bank_transfer_payment->created_at);
            })
            ->editColumn('user_id', function (BankTransferPayment $bank_transfer_payment) {
                return  $bank_transfer_payment->user_name ?? '';
            })
            ->editColumn('price', function (BankTransferPayment $bank_transfer_payment) {
                return  super_currency_format_with_sym($bank_transfer_payment->price);
            })
            ->editColumn('status', function (BankTransferPayment $bank_transfer_payment) {
                if ($bank_transfer_payment->status == 'Approved') {
                    $html = '<span class="badge bg-success p-2 px-3 text-white">' . ucfirst($bank_transfer_payment->status) . '</span>';
                } elseif ($bank_transfer_payment->status == 'Pending') {
                    $html = '<span class="badge bg-warning p-2 px-3 text-white">' . ucfirst($bank_transfer_payment->status) . '</span>';
                } else {
                    $html =  '<span class="badge bg-danger p-2 px-3 text-white">' . ucfirst($bank_transfer_payment->status) . '</span>';
                }

                return $html;
            })
            ->editColumn('attachment', function (BankTransferPayment $bank_transfer_payment) {
                if (!empty($bank_transfer_payment->attachment) && check_file($bank_transfer_payment->attachment)) {
                    $html = '<div class="action-btn bg-primary ms-2">
                                                    <a class="mx-3 btn btn-sm align-items-center"
                                                        href="' . get_file($bank_transfer_payment->attachment) . '" download>
                                                        <i class="ti ti-download text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn bg-secondary ms-2">
                                                    <a class="mx-3 btn btn-sm align-items-center"
                                                        href=" '.get_file($bank_transfer_payment->attachment) .'"
                                                        target="_blank">
                                                        <i class="ti ti-crosshair text-white" data-bs-toggle="tooltip"
                                                            data-bs-original-title="' . __('Preview') . '"></i>
                                                    </a>
                                                </div>';
                } else {
                    $html = '-';
                }

                return $html;
            })
            ->addColumn('action', function (BankTransferPayment $bank_transfer_payment) {
                return view('bank_transfer.action-button', compact('bank_transfer_payment'));
            })
            ->rawColumns(['created_at','user_id','price', 'status', 'attachment','action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(BankTransferPayment $model): QueryBuilder
    {
        return $model->with('User')->select( [
            'bank_transfer_payments.*',
            'users.name as user_name',
        ])
        ->join('users', 'bank_transfer_payments.user_id', '=', 'users.id')
        ->where('bank_transfer_payments.type', 'plan');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('banktransfer-table')
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
            Column::make('order_id')->title(__('Order Id')),
            Column::make('created_at')->title(__('Date')),
            Column::make('user_id')->title(__('Name'))->name('users.name'),
            Column::make('price')->title(__('Price')),
            Column::make('status')->title(__('Status')),
            Column::make('attachment')->title(__('Attachment')),
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
        return 'BankTransfer_' . date('YmdHis');
    }
}
