<?php

namespace Workdo\Account\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\BankAccount;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class BankAccountDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rawColumn = ['chart_account_id','opening_balance' , 'payment_name'];
        $dataTable = (new EloquentDataTable($query))
            ->editColumn('chart_account_id', function (BankAccount $account) {
                return $account->chart_account_name;
            })
            ->editColumn('payment_name', function (BankAccount $account) {
                return !empty($account->payment_name) ? $account->payment_name : '--';
            })
            ->editColumn('opening_balance', function (BankAccount $account) {
                return currency_format_with_sym($account->opening_balance);
            });

        if (\Laratrust::hasPermission('bank account edit') || \Laratrust::hasPermission('bank account delete')) {
            $dataTable->addColumn('action', function (BankAccount $account) {
                return view('account::bankAccount.action', compact('account'));
            });
            $rawColumn[] = 'action';
        }
        return $dataTable->rawColumns($rawColumn);
    }

    /**
     * Get the query source of dataTable.
     */

    public function query(BankAccount $model, Request $request)
    {

        $accounts = $model->select('bank_accounts.*', 'chart_of_accounts.name as chart_account_name')
            ->leftJoin('chart_of_accounts', 'bank_accounts.chart_account_id', '=', 'chart_of_accounts.id')
            ->where('bank_accounts.workspace', getActiveWorkSpace());

        return $accounts;
    }


    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('bankaccount-table')
            ->columns($this->getColumns())
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

        $column  = [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('chart_account_id')->title(__('Chart Of Account'))->name('chart_of_accounts.name'),
            Column::make('holder_name')->title(__('Name')),
            Column::make('bank_name')->title(__('Bank')),
            Column::make('account_number')->title(__('Account Number')),
            Column::make('opening_balance')->title(__('Current Balance')),
            Column::make('payment_name')->title(__('Payment Gateway')),
        ];
        if (\Laratrust::hasPermission('bank account edit') || \Laratrust::hasPermission('bank account delete')) {

            $action  = [
                Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)

            ];

            $column = array_merge($column,$action);
        }

        return $column;


    }

    /**
     * Get the filename for export.
     */


    protected function filename(): string
    {
        return 'Bank_Accounts_' . date('YmdHis');
    }
}
