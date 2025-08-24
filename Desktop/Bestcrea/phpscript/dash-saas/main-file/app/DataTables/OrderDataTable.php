<?php

namespace App\DataTables;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class OrderDataTable extends DataTable
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
            ->editColumn('order_id',function(Order $order){
                return $order->order_id;
            })
            ->editColumn('created_at',function(Order $order){
                return company_datetime_formate($order->created_at);
            })
            ->editColumn('price',function(Order $order){
                return  super_currency_format_with_sym($order->price);
            })
            ->editColumn('payment_status', function (Order $order) {
                if ($order->payment_status == 'succeeded')
                {
                    $html = '<span class="badge fix_badges bg-primary p-2 px-3">'. ucfirst($order->payment_status) .'</span>';

                }
                else{
                    $html = '<span class="badge fix_badges bg-danger p-2 px-3">'. ucfirst($order->payment_status) .'</span>';
                }

                return $html;
            })
            ->editColumn('coupon_code',function(Order $order){
                return !empty($order->total_coupon_used) ? (!empty($order->total_coupon_used->coupon_detail) ? $order->total_coupon_used->coupon_detail->code : '-') : '-';
            })
            ->editColumn('receipt',function(Order $order){
                if ($order->receipt != 'free coupon' && $order->payment_type == 'STRIPE')
                {
                    $html = '<a href="'. $order->receipt .'" data-bs-toggle="tooltip"
                                data-bs-original-title="'. __('Invoice') .'" target="_blank"
                                class="btn btn-sm align-items-center bg-warning">
                                <i class="ti ti-file-invoice text-white"></i>
                            </a>';

                }elseif($order->receipt != 'free coupon' && $order->payment_type == 'Coin'){
                    $html = '<a href="'. $order->receipt .'" data-bs-toggle="tooltip"
                            data-bs-original-title="'. __('Invoice') .'" target="_blank"
                            class="btn btn-sm align-items-center bg-warning">
                            <i class="ti ti-file-invoice text-white"></i>
                        </a>';
                }
                elseif($order->payment_type == 'Bank Transfer')
                {
                    $href = !empty($order->receipt) ? (check_file($order->receipt) ? get_file($order->receipt) : '#!') : '#!';
                    $html = ' <a href="'.$href.'"
                                    data-bs-toggle="tooltip" data-bs-original-title="'. __('Invoice') .'"
                                    target="_blank" class="btn btn-sm align-items-center bg-warning">
                                    <i class="ti ti-file-invoice text-white"></i>
                                </a>';
                }
                elseif($order->receipt == 'free coupon')
                {
                    $html = '<p>'.__('Used 100 % discount coupon code.').'</p>';
                }
                elseif($order->payment_type == 'Manually')
                {
                    $html = '<p>'.__('Manually plan upgraded by super admin').'</p>';
                }
                else
                {
                    $html = '-';
                }
                return $html;
            })
            ->addColumn('action', function (Order $order) {
                $user = User::find($order->user_id);
                if (Auth::user()->type == 'super admin'){
                    $userOrders = Order::select('*')
                    ->whereIn('id', function ($query) {
                        $query->selectRaw('MAX(id)')
                        ->from('orders')
                        ->groupBy('user_id');
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();
                    return view('plan_order.action', compact('userOrders','user','order'));
                }
            })
            ->rawColumns(['order_id','created_at','price','payment_status','coupon_code','receipt','action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Order $model): QueryBuilder
    {
        $user                       = \Auth::user();
        $orders = $model->select(
            [
                'orders.*',
                'users.name as user_name',
            ]
        )->join('users', 'orders.user_id', '=', 'users.id')->where(function ($query) use ($user) {
            if ($user->type != 'super admin') {
                $query->where('orders.user_id', $user->id);
            }
        });

        return $orders;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('order-table')
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
        $column =  [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('order_id')->title(__('Order Id')),
            Column::make('created_at')->title(__('Date')),
            Column::make('user_name')->title(__('Name'))->name('users.name'),
            Column::make('price')->title(__('Price')),
            Column::make('payment_status')->title(__('Status')),
            Column::make('coupon_code')->title(__('Coupon'))->orderable(false)->searchable(false),
            Column::make('receipt')->title(__('Invoice'))
        ];
        $user = \Auth::user();
        if ($user->type == 'super admin')
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
        return 'Order_' . date('YmdHis');
    }
}
