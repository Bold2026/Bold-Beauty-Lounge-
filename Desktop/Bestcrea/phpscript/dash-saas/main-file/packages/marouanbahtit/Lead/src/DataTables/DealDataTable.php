<?php

namespace Workdo\Lead\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Lead\Entities\Deal;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DealDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['price', 'stage_id', 'tasks', 'users'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('price', function (Deal $deal) {
                return currency_format_with_sym($deal->price);
            })
            ->editColumn('stage_id', function (Deal $deal) {
                return $deal->stage->name;
            })
            ->filterColumn('stage_id', function ($query, $keyword) {
                $query->whereHas('stage', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            })
            ->editColumn('tasks', function (Deal $deal) {
                return count($deal->tasks) . '/' . count($deal->complete_tasks);
            })
            ->editColumn('users', function (Deal $deal) {
                $html = '';
                foreach ($deal->users as $user) {
                    if (check_file($user->avatar) == false) {
                        $path = get_file('uploads/users-avatar/avatar.png');
                    } else {
                        $path = get_file($user->avatar);
                    }
                    $html.= '<a href="#" class="btn btn-sm mr-1 p-0 rounded-circle">
                                <img alt="image" data-bs-toggle="tooltip" data-bs-placement="top" title="'. $user->name .'" src="'. $path .'" class="rounded-circle" width="25" height="25">
                            </a>';
                }
                return $html;
            });
            if (\Laratrust::hasPermission('deal show') ||
                \Laratrust::hasPermission('deal edit') ||
                \Laratrust::hasPermission('deal delete')) {
                    $dataTable->addColumn('action', function (Deal $deal) {
                        return view('lead::deals.deal_action', compact('deal'));
                    });
                    $rowColumn[] = 'action';
            }
            return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Deal $model, Request $request)
    {
        $user = \Auth::user();
        $user->default_pipeline = $request->default_pipeline_id;
        $user->save();
        if ($user->type == 'client') {
            $deals = Deal::select('deals.*')
                    ->join('client_deals', 'client_deals.deal_id', '=', 'deals.id')
                    ->where('client_deals.client_id', '=', $user->id)
                    ->where('deals.pipeline_id', '=', $user->default_pipeline)
                    ->where('deals.workspace_id', '=', getActiveWorkSpace());
        } else {
            $deals = Deal::select('deals.*')
                    ->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')
                    ->where('user_deals.user_id', '=', $user->id)
                    ->where('deals.pipeline_id', '=', $user->default_pipeline)
                    ->where('deals.workspace_id', '=', getActiveWorkSpace());
        }
        return $deals;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('deals-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                    var pipeline = $("select[name=default_pipeline_id]").val();
                    d.default_pipeline_id = pipeline
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
                $("body").on("change", "#change-pipeline", function() {
                    $("#deals-table").DataTable().draw();
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
        $columns = [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('name')->title(__('Name')),
            Column::make('price')->title(__('Price')),
            Column::make('stage_id')->title(__('Stages')),
            Column::make('tasks')->title(__('Tasks'))->searchable(false)->orderable(false),
            Column::make('users')->title(__('Users'))->searchable(false)->exportable(false)->orderable(false),
            Column::make('phone')->title(__('Phone No'))->visible(false)->searchable(false)->orderable(false)->printable(false),
        ];

        if (\Laratrust::hasPermission('deal show') ||
            \Laratrust::hasPermission('deal edit') ||
            \Laratrust::hasPermission('deal delete')) {
            $columns[] = Column::computed('action')
                        ->title(__('Action'))
                        ->searchable(false)
                        ->orderable(false)
                        ->exportable(false)
                        ->printable(false)
                        ->width(60)
                        
                        ->searchable(false);
        }

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Deals_' . date('YmdHis');
    }
}
