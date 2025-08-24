<?php

namespace Workdo\Account\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\Bill;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class BillDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query)
    {
        $rawColumn = ['bill_id', 'bill_date', 'due_date', 'due_amount', 'status'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('bill_id', function (Bill $bill) {
                if (\Laratrust::hasPermission('bill show')) {
                    $url = route('bill.show', \Crypt::encrypt($bill->id));
                    return '<a href="' . $url . '" class="btn btn-outline-primary">' . Bill::billNumberFormat($bill->bill_id) . '</a>';
                } else {
                    return ' <a href="#" class="btn btn-outline-primary">' . Bill::billNumberFormat($bill->bill_id) . '</a>';
                }
            })
            ->editColumn('bill_date', function (Bill $bill) {
                return company_date_formate($bill->bill_date);
            })
            ->editColumn('due_date', function (Bill $bill) {
                if ($bill->due_date < date('Y-m-d')) {
                    return '<p class="text-danger mb-0">' . company_date_formate($bill->due_date) . '</p>';
                } else {
                    return company_date_formate($bill->due_date);
                }
            })
            ->addColumn('due_amount', function (Bill $bill) {
                return currency_format_with_sym($bill->getDue());
            })
            ->editColumn('status', function (Bill $bill) {
                if ($bill->status == 0) {
                    $class = 'bg-info';
                } elseif ($bill->status == 1) {
                    $class = 'bg-primary';
                } elseif ($bill->status == 2) {
                    $class = 'bg-secondary';
                } elseif ($bill->status == 3) {
                    $class = 'bg-warning';
                } elseif ($bill->status == 4) {
                    $class = 'bg-success';
                }
                return '<span class="badge ' . $class . ' p-2 px-3">' . Bill::$statues[$bill->status] . '</span>';
            })
            ->filterColumn('status', function ($query, $keyword) {
                if (stripos('Draft', $keyword) !== false) {
                    $query->where('status', 0);
                } elseif (stripos('Sent', $keyword) !== false) {
                    $query->orWhere('status', 1);
                } elseif (stripos('Unpaid', $keyword) !== false) {
                    $query->orWhere('status', 2);
                } elseif (stripos('Partialy Paid', $keyword) !== false) {
                    $query->orWhere('status', 3);
                } elseif (stripos('Paid', $keyword) !== false) {
                    $query->orWhere('status', 4);
                }
            });
        if (
            \Laratrust::hasPermission('bill edit') ||
            \Laratrust::hasPermission('bill delete') ||
            \Laratrust::hasPermission('bill show') ||
            \Laratrust::hasPermission('bill duplicate')
        ) {
            $dataTable->addColumn('action', function (Bill $bill) {

                return view('account::bill.action', compact('bill'));
            });
            $rawColumn[] = 'action';
        }
        return $dataTable->rawColumns($rawColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Bill $model, Request $request)
    {
        if (Auth::user()->type != 'company') {
            $query = $model->newQuery()
            ->select('bills.*', 'vendors.name as vendor_name')
            ->join('users', 'users.id', '=', 'bills.user_id')
            ->where('bills.status', '!=', 0)
            ->where('users.id', Auth::user()->id)
            ->where('bills.workspace', getActiveWorkSpace());

        } else {
            $query = $model->newQuery()
            ->select('bills.*', 'vendors.name as vendor_name')
            ->join('users', 'users.id', '=', 'bills.user_id')
            ->where('bills.created_by', creatorId())
            ->where('bills.workspace', getActiveWorkSpace());
        }

        if (!empty($request->vendor)) {
            $query->where('bills.vendor_id', '=', $request->vendor);
        }

        if (!empty($request->bill_date)) {
            $date_range = explode(',', $request->bill_date);
            if (count($date_range) == 2) {
                $query->whereBetween('bill_date', $date_range);
            } else {
                $query->where('bill_date', $date_range[0]);
            }
        }

        if ($request->status != null) {
            $query->where('status', '=', $request->status);
        }

        $bills = $query->join('vendors', 'bills.vendor_id', '=', 'vendors.id');
        return $bills;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('bill-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                            var bill_date = $("input[name=bill_date]").val();
                            d.bill_date = bill_date

                            var vendor = $("select[name=vendor]").val();
                            d.vendor = vendor

                            var status = $("select[name=status]").val();
                            d.status = status
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

                                            if (!$("input[name=bill_date]").val() && !$("select[name=vendor]").val() && !$("select[name=status]").val()) {
                                                toastrs("Error!", "Please select Atleast One Filter ", "error");
                                                return;
                                            }

                                            $("#bill-table").DataTable().draw();
                                        });

                                        $("body").on("click", "#clearfilter", function() {
                                            $("input[name=bill_date]").val("")
                                            $("select[name=vendor]").val("")
                                            $("select[name=status]").val("")
                                            $("#bill-table").DataTable().draw();
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
            Column::make('bill_id')->title(__('Bill')),
            Column::make('vendor_name')->title(__('Vendor'))->name('vendors.name'),
            Column::make('account_type')->title(__('Account Type')),
            Column::make('bill_date')->title(__('Bill Date')),
            Column::make('due_date')->title(__('Due Date')),
            Column::make('due_amount')->title(__('Due Amount')),
            Column::make('status')->title(__('Status')),
        ];
        if (
            \Laratrust::hasPermission('bill edit') ||
            \Laratrust::hasPermission('bill delete') ||
            \Laratrust::hasPermission('bill show') ||
            \Laratrust::hasPermission('bill duplicate')
        ) {
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
        return 'Bills_' . date('YmdHis');
    }
}
