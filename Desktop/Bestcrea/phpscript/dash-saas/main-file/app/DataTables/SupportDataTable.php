<?php

namespace App\DataTables;

use App\Models\HelpdeskTicket;
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

class SupportDataTable extends DataTable
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
            ->editColumn('ticket_id', function (HelpdeskTicket $ticket) {
                if (\Laratrust::hasPermission('product&service delete')) {
                    $url = route('helpdesk.edit', $ticket->id);
                } else {
                    $url = route('helpdesk.view', [\Illuminate\Support\Facades\Crypt::encrypt($ticket->ticket_id)]);
                }
                $html = '<a class="btn btn-outline-primary" href="' . $url . '">
                                ' . $ticket->ticket_id . '
                            </a>';
                return $html;
            })
            ->editColumn('created_by', function (HelpdeskTicket $ticket) {
                return optional($ticket->createdBy)->name ?? '-';
            })
            ->editColumn('category', function (HelpdeskTicket $ticket) {
                return '<span class="badge badge-white p-2 px-3 fix_badge"
                                                style="background: ' . $ticket->color . ';">' . $ticket->category_name . '</span>';
            })
            ->editColumn('status', function (HelpdeskTicket $ticket) {
                if ($ticket->status == 'In Progress') {
                    $className  = 'bg-warning';
                } elseif ($ticket->status == 'On Hold') {
                    $className  = 'bg-danger';
                } else {
                    $className = 'bg-success';
                }
                return '<span class="badge fix_badge p-2 px-3  ' . $className . '">' . $ticket->status . '</span>';
            })
            ->editColumn('created_at', function (HelpdeskTicket $ticket) {
                return $ticket->created_at->diffForHumans();
            })
            ->addColumn('action', function (HelpdeskTicket $ticket) {
                return view('helpdesk_ticket.action', compact('ticket'));
            })
            ->rawColumns(['ticket_id', 'created_by', 'category', 'status', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(HelpdeskTicket $model, Request $request)
    {
        $tickets = $model->select(
            [
                'helpdesk_tickets.*',
                'helpdesk_ticket_categories.name as category_name',
                'helpdesk_ticket_categories.color',
            ]
        )->join('helpdesk_ticket_categories', 'helpdesk_ticket_categories.id', '=', 'helpdesk_tickets.category');
        if ($request->status == 'in-progress') {
            $tickets->where('status', '=', 'In Progress');
        } elseif ($request->status == 'on-hold') {
            $tickets->where('status', '=', 'On Hold');
        } elseif ($request->status == 'closed') {
            $tickets->where('status', '=', 'Closed');
        }
        if (Auth::user()->type == 'super admin') {
            $tickets = $tickets;
        } elseif (Auth::user()->type == 'company') {
            $tickets = $tickets->where('workspace', getActiveWorkSpace());
        }

        return $tickets;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('supports-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                    var status = $("#projects").val();
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
                        $("body").on("change", "#projects", function() {
                            $("#supports-table").DataTable().draw();
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
            Column::make('ticket_id')->title(__('Ticket ID')),
            Column::make('name')->title(__('Assigned To')),
            Column::make('email')->title(__('Email')),
            Column::make('created_by')->title(__('Created By')),
            Column::make('subject')->title(__('Subject')),
            Column::make('category')->title(__('Category')),
            Column::make('status')->title(__('Status'))->name('status'),
            Column::make('created_at')->title(__('Created')),
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
        return 'Support_' . date('YmdHis');
    }
}
