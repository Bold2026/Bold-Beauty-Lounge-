<?php

namespace Workdo\Account\DataTables;

use App\Models\User;
use Workdo\Account\Entities\Vender;
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

class VendorDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rawColumn = ['id', 'balance'];
        $dataTable = (new EloquentDataTable($query))
            ->editColumn('id', function (User $vendor) {
                $user = Vender::where('user_id',$vendor->id)->first();
                if($vendor->is_disable == 1){
                    if (!empty($user['id'])) {
                        $url = \Laratrust::hasPermission('vendor show')
                            ? route('vendors.show', \Crypt::encrypt($vendor['id']))
                            : '#!';
                        $html ='<a class="btn btn-outline-primary" href="'.$url.'">
                                '. Vender::vendorNumberFormat($user['id']) .'
                            </a>';
                    } else {
                        $url = '-';
                        $html ='--';
                    }
            }else{
                    $html = '<a class="btn btn-outline-primary" href="#!">
                    ' . (!empty($user['vendor_id']) ? Vender::vendorNumberFormat($user['vendor_id']) : '--') . '
                </a>';
            }
                return $html;

            })
            ->editColumn('balance', function (User $vendor) {
                return currency_format_with_sym($vendor->balance);
            });

        if (\Laratrust::hasPermission('vendor edit') || \Laratrust::hasPermission('vendor delete') || \Laratrust::hasPermission('vendor show')) {
            $dataTable->addColumn('action', function (User $vendor) {
                return view('account::vendor.action', compact('vendor'));
            });
            $rawColumn[] = 'action';
        }
        return $dataTable->rawColumns($rawColumn);


    }
    /**
     * Get the query source of dataTable.
     */

    public function query(User $model, Request $request)
    {
        if(Auth::user()->type == 'company')
        {
            $vendors =  $model->where('workspace_id',getActiveWorkSpace())
                            ->leftjoin('vendors', 'users.id', '=', 'vendors.user_id')
                            ->where('users.type', 'vendor')
                            ->select('users.*','vendors.*', 'users.name as name', 'users.email as email', 'users.id as id','users.mobile_no as contact','vendors.balance');
        }
        else
        {
            $vendors =  $model->where('workspace_id',getActiveWorkSpace())->where('users.id',Auth::user()->id)
            ->leftjoin('vendors', 'users.id', '=', 'vendors.user_id')
            ->where('users.type', 'vendor')
            ->select('users.*','vendors.*', 'users.name as name', 'users.email as email', 'users.id as id','users.mobile_no as contact','vendors.balance');
        }

        return $vendors;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('vendors-table')
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
                    return new bootstrap.Popover(tooltipTriggerEl);
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
        $column =[
            Column::make('id')->title(__('#'))->name('users.id'),
            Column::make('name')->title(__('Name'))->name('users.name'),
            Column::make('contact')->title(__('Contact'))->name('users.mobile_no'),
            Column::make('email')->title(__('Email'))->name('users.email'),
            Column::make('balance')->title(__('Balance'))->name('vendors.balance'),
        ];
        if (\Laratrust::hasPermission('vendor edit') ||
            \Laratrust::hasPermission('vendor delete') ||
            \Laratrust::hasPermission('vendor show'))
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
        return 'Vendors_' . date('YmdHis');
    }
}
