<?php

namespace Workdo\Account\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\Transfer;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class TransferDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rawColumn = ['date','from_account','to_account','amount'];

        $dataTable = (new EloquentDataTable($query))
        ->editColumn('date', function (Transfer $transfer) {
            return company_date_formate( $transfer->date);
        })
        ->editColumn('from_account', function (Transfer $transfer) {
            return !empty($transfer->fromBankAccount) ? $transfer->fromBankAccount->bank_name . ' ' . $transfer->fromBankAccount->holder_name : '';
        })
        ->editColumn('to_account', function (Transfer $transfer) {
            return !empty($transfer->toBankAccount) ? $transfer->toBankAccount->bank_name . ' ' . $transfer->toBankAccount->holder_name : '' ;
        })
        ->editColumn('amount', function (Transfer $transfer) {
            return currency_format_with_sym($transfer->amount);
        });


        if (\Laratrust::hasPermission('bank transfer edit') || \Laratrust::hasPermission('bank transfer delete')) {
            $dataTable->addColumn('action', function (Transfer $transfer) {
                return view('account::transfer.action', compact('transfer'));
            });
            $rawColumn[] = 'action';
        }
        return $dataTable->rawColumns($rawColumn);

    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Transfer $model,Request $request)
    {

        $transfers = $model->where('workspace', getActiveWorkSpace());

        if (!empty($request->date)) {
            $date_range = explode(',', $request->date);
            if (count($date_range) == 2) {
                $transfers = $transfers->whereBetween('date', $date_range);
            } else {
                $transfers = $transfers->where('date', $date_range[0]);
            }
        }
        if (!empty($request->f_account)) {

            $transfers =  $transfers->where('from_account', $request->f_account);
        }

        if (!empty($request->t_account)) {
            $transfers =  $transfers->where('to_account', $request->t_account);
        }
        $transfers = $transfers->with('fromBankAccount', 'toBankAccount');

        return $transfers;
    }


    /**
     *
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

                var f_account = $("select[name=f_account]").val();
                d.f_account = f_account

                var t_account = $("select[name=t_account]").val();
                d.t_account = t_account
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

                    if (!$("input[name=date]").val() && !$("select[name=f_account]").val() && !$("select[name=t_account]").val()) {
                        toastrs("Error!", "Please select Atleast One Filter ", "error");
                        return;
                    }

                    $("#transfers-table").DataTable().draw();
                });

                $("body").on("click", "#clearfilter", function() {
                    $("input[name=date]").val("")
                    $("select[name=f_account]").val("")
                     $("select[name=t_account]").val("")
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
            Column::make('from_account')->title(__('From Account')),
            Column::make('to_account')->title(__('To Account')),
            Column::make('amount')->title(__('Amount')),
            Column::make('reference')->title(__('Reference')),
            Column::make('description')->title(__('Description')),

        ];
        if (\Laratrust::hasPermission('bank transfer edit') ||
             \Laratrust::hasPermission('bank transfer delete'))
        {
            $action = [

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
        return 'Transfer_' . date('YmdHis');
    }
}
