<?php

namespace Workdo\ProductService\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Workdo\ProductService\Entities\ProductService;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ProductServiceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['image', 'sale_price', 'purchase_price', 'tax_id', 'category_id', 'unit_id', 'quantity'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('image', function (ProductService $productService) {
                if (check_file($productService->image) == false) {
                    $path = asset('packages/workdo/ProductService/src/Resources/assets/image/img01.jpg');
                } else {
                    $path = get_file($productService->image);
                }
                $html = '<a href="'. $path .'" target="_blank">
                            <img src="' . $path . '" class="rounded border-2 border border-primary
                            " style="width:100px;" id="blah3">
                        </a>';

                return $html;
            })

            ->editColumn('sale_price', function (ProductService $productService) {
                return currency_format_with_sym($productService->sale_price);
            })
            ->editColumn('purchase_price', function (ProductService $productService) {
                return currency_format_with_sym($productService->purchase_price);
            })
            ->editColumn('tax_id', function (ProductService $productService) {
                return str_replace(',', ',<br>', $productService->tax_names);
            })
            ->editColumn('category_id', function (ProductService $productService) {
                return optional($productService->categorys)->name ?? '';
            })
            ->filterColumn('category_id', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            })
            ->filterColumn('unit_id', function ($query, $keyword) {
                $query->whereHas('units', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            })
            ->filterColumn('tax_id', function ($query, $keyword) {
                $query->where('taxes.name', 'like', "%$keyword%");
            })
            ->editColumn('unit_id', function (ProductService $productService) {
                return optional($productService->units)->name ?? '';
            })
            ->editColumn('quantity', function (ProductService $productService) {
                if ($productService->type == 'product' || $productService->type == 'parts' || $productService->type == 'consignment' || $productService->type == 'rent' || $productService->type == 'music institute' || $productService->type == 'optical eyecare' || $productService->type == 'jewellery store' || $productService->type == 'hair & care studio') {
                    $quantity = $productService->quantity;
                } else {
                    $quantity = '-';
                }
                return $quantity;
            });
        if (\Laratrust::hasPermission('product&service delete') || \Laratrust::hasPermission('product&service edit') || \Laratrust::hasPermission('product&service show')) {
            $dataTable->addColumn('action', function (ProductService $productService) {
                return view('product-service::action', compact('productService'));
            });

            $rowColumn[] = 'action';
        }
        return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductService $model, Request $request)
    {
        $productServices = $model->select('product_services.*', DB::raw('GROUP_CONCAT(taxes.name) as tax_names'))
            ->leftJoin('taxes', function ($join) {
                $join->on('taxes.id', '=', DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(product_services.tax_id, ',', numbers.n), ',', -1)"))
                    ->crossJoin(DB::raw('(SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) numbers'))
                    ->whereRaw('CHAR_LENGTH(product_services.tax_id) - CHAR_LENGTH(REPLACE(product_services.tax_id, ",", "")) + 1 >= numbers.n');
            })
            ->where('product_services.created_by', creatorId())
            ->where('product_services.workspace_id', getActiveWorkSpace())
            ->groupBy('product_services.id');
        if (!empty($request->category)) {
            $productServices = $productServices->where('product_services.category_id', $request->category);
        }
        if (!empty($request->item_type)) {
            $productServices = $productServices->where('product_services.type', $request->item_type);
        }
        $productServices = $productServices->with(['categorys', 'units']);

        return $productServices;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('product-service-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                    var item_type = $("select[name=item_type]").val();
                    d.item_type = item_type

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

                    if (!$("select[name=item_type]").val() && !$("select[name=category]").val()) {
                        toastrs("Error!", "Please select Atleast One Filter ", "error");
                        return;
                    }

                    $("#product-service-table").DataTable().draw();
                });

                $("body").on("click", "#clearfilter", function() {
                    $("select[name=item_type]").val("")
                    $("select[name=category]").val("")
                    $("#product-service-table").DataTable().draw();
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
            Column::make('image')->title(__('Image'))->orderable(false)->searchable(false),
            Column::make('name')->title(__('Name')),
            Column::make('sku')->title(__('Sku')),
            Column::make('sale_price')->title(__('Sale Price')),
            Column::make('purchase_price')->title(__('Purchase Price')),
            Column::make('tax_id')->title(__('Tax')),
            Column::make('category_id')->title(__('Category')),
            Column::make('unit_id')->title(__('Unit')),
            Column::make('quantity')->title(__('Quantity')),
            Column::make('type')->title(__('Type')),
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
        return 'Product_Service_' . date('YmdHis');
    }
}
