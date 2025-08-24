<?php

namespace App\DataTables;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PurchaseDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['purchase_id','vender_id','category_id','purchase_date','status','total_amount'];
        $dataTable = (new EloquentDataTable($query))
        ->addIndexColumn()
        ->editColumn('purchase_id',function(Purchase $purchase){
            $url = route('purchases.show', \Crypt::encrypt($purchase->id));
            return '<a href="'.$url.'" class="btn btn-outline-primary">'. Purchase::purchaseNumberFormat($purchase->purchase_id) .'</a>';
        })
        ->editColumn('vender_id',function(Purchase $purchase){
            if(!empty($purchase->vender_name))
            {
                return !empty( $purchase->vender_name)?$purchase->vender_name:'';
            }
            elseif(empty($purchase->vender_name))
            {
                return !empty($purchase->user->name)?$purchase->user->name:'';
            }
            else
            {
                if(module_is_active('Account')){
                    return !empty($purchase->vender) ? $purchase->vender->name : '';
                }
                else{
                    return '-';
                }
            }
        })
        ->editColumn('category_id',function(Purchase $purchase){
            return optional($purchase->category)->name ?? '';
        })
        ->editColumn('purchase_date',function(Purchase $purchase){
            return company_date_formate($purchase->purchase_date);
        })
        ->addColumn('total_amount', function (Purchase $purchase) {
            return currency_format_with_sym($purchase->getTotal());
        })
        ->filterColumn('status', function ($query, $keyword) {
            if (stripos('Draft', $keyword) !== false) {
                $query->where('status', 0);
            }
            elseif (stripos('Sent', $keyword) !== false) {
                $query->orWhere('status', 1);
            }
            elseif (stripos('Unpaid', $keyword) !== false) {
                $query->orWhere('status', 2);
            }
            elseif (stripos('Partialy Paid', $keyword) !== false) {
                $query->orWhere('status', 3);
            }
            elseif (stripos('Paid', $keyword) !== false) {
                $query->orWhere('status', 4);
            }
        })
        ->addColumn('status', function ($purchase) {
            $statuses = [
                0 => 'bg-info',
                1 => 'bg-primary',
                2 => 'bg-secondary',
                3 => 'bg-warning',
                4 => 'bg-success',
            ];

            $class = isset($statuses[$purchase->status]) ? $statuses[$purchase->status] : '';
            return '<span class="badge fix_badges ' . $class . ' p-2 px-3">' . Purchase::$statues[$purchase->status] . '</span>';

        })
        ->filterColumn('vender_id', function ($query, $keyword) {
            $query->whereHas('vender', function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%");
            });
        })
        ->filterColumn('category_id', function ($query, $keyword) {
            $query->whereHas('category', function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%");
            });
        })
        ->filterColumn('purchase_id', function ($query, $keyword) {
            $formattedValue = str_replace('#DOC', '', $keyword);
            $query->where('purchase_id', $formattedValue);
        });

        if (\Laratrust::hasPermission('purchase edit') ||
        \Laratrust::hasPermission('purchase delete') ||
        \Laratrust::hasPermission('purchase show'))
        {
            $dataTable->addColumn('action',function($purchase){
                return view('purchases.action',compact('purchase'));
            });
            $rowColumn[] = 'action';
        }
        return $dataTable->rawColumns($rowColumn);

    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Purchase $model): QueryBuilder
    {
        if (Auth::user()->type == 'company') {

            $query = $model->where('purchases.created_by', creatorId())
            ->where('purchases.workspace', getActiveWorkSpace());
        } else {
            $query = $model->newQuery()
            ->select('purchases.*', 'vendors.name as vendor_name')
            ->join('vendors', 'purchases.vender_id', '=', 'vendors.id')
            ->where('purchases.status', '!=', 0)
            ->where('vendors.user_id', Auth::user()->id)
            ->where('purchases.workspace', getActiveWorkSpace());
        }
        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('purchase-table')
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
        $column = [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('purchase_id')->title(__('Purchase')),
            Column::make('vender_id')->title(__('Vendor')),
            Column::make('account_type')->title(__('Account Type')),
            Column::make('category_id')->title(__('Category')),
            Column::computed('total_amount')->title(__('Total Amount')),
            Column::make('purchase_date')->title(__('Purchase Date')),
            Column::make('status')->title(__('Status')),
        ];
        if (\Laratrust::hasPermission('purchase edit') ||
            \Laratrust::hasPermission('purchase delete') ||
            \Laratrust::hasPermission('purchase show'))
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
        return 'Purchase_' . date('YmdHis');
    }
}
