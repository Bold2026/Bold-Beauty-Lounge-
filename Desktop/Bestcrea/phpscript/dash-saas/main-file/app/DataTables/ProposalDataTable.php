<?php

namespace App\DataTables;

use App\Models\Proposal;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Illuminate\Http\Request;
use Yajra\DataTables\Services\DataTable;

class ProposalDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['proposal_id', 'issue_date', 'status','action','total_amount'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('proposal_id', function (Proposal $proposal) {
                $url = route('proposal.show', \Crypt::encrypt($proposal->id));
                return '<a href="' . $url . '" class="btn btn-outline-primary">' . \App\Models\Proposal::proposalNumberFormat($proposal->proposal_id) . '</a>';
            })
            ->addColumn('total_amount', function (Proposal $proposal) {
                return currency_format_with_sym($proposal->getTotal());
            })
            ->editColumn('issue_date', function (Proposal $proposal) {
                return company_date_formate($proposal->issue_date);
            })
            ->editColumn('status', function (Proposal $proposal) {
                if ($proposal->status == 0) {
                    return '<span class="badge fix_badge bg-primary p-2 px-3">' . __(\App\Models\Proposal::$statues[$proposal->status]) . '</span>';
                } elseif ($proposal->status == 1) {
                    return '<span class="badge fix_badge bg-info p-2 px-3">' . __(\App\Models\Proposal::$statues[$proposal->status]) . '</span>';
                } elseif ($proposal->status == 2) {
                    return '<span class="badge fix_badge bg-secondary p-2 px-3">' . __(\App\Models\Proposal::$statues[$proposal->status]) . '</span>';
                } elseif ($proposal->status == 3) {
                    return '<span class="badge fix_badge bg-warning p-2 px-3">' . __(\App\Models\Proposal::$statues[$proposal->status]) . '</span>';
                } elseif ($proposal->status == 4) {
                    return ' <span class="badge fix_badge bg-danger p-2 px-3">' . __(\App\Models\Proposal::$statues[$proposal->status]) . '</span>';
                }
            })
            ->addColumn('action', function (Proposal $proposal) {
                return view('proposal.action', compact('proposal'));
            });
        if (Auth::user()->type != 'client') {
            $dataTable = $dataTable->editColumn('customer_id', function (Proposal $proposal) {
                return optional($proposal->customer)->name ?? '';
            });
            $rowColumn[] = 'customer_id';
        }
        return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Proposal $model, Request $request): QueryBuilder
    {
        if (Auth::user()->type != 'company') {
            $query = $model->join('users', 'proposals.customer_id', '=', 'users.id')
                ->where('users.id', Auth::user()->id)->select('proposals.*')
                ->where('proposals.workspace', getActiveWorkSpace());
        } else {
            $query = $model->where('workspace', getActiveWorkSpace());
        }

        if (!empty($request->customer)) {
            $query->where('customer_id', '=', $request->customer);
        }
        if (!empty($request->issue_date)) {
            $date_range = explode('to', $request->issue_date);
            if (count($date_range) == 2) {
                $query->whereBetween('issue_date', $date_range);
            } else {
                $query->where('issue_date', $date_range[0]);
            }
        }

        if (!empty($request->status)) {

            $query->where('status', $request->status);
        }
        return $query->with('customers');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('proposal-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                    var issue_date = $("input[name=issue_date]").val();
                    d.issue_date = issue_date

                    var customer = $("select[name=customer]").val();
                    d.customer = customer

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

                                    if (!$("input[name=issue_date]").val() && !$("select[name=customer]").val() && !$("select[name=status]").val()) {
                                        toastrs("Error!", "Please select Atleast One Filter ", "error");
                                        return;
                                    }

                                    $("#proposal-table").DataTable().draw();
                                });

                                $("body").on("click", "#clearfilter", function() {
                                    $("input[name=issue_date]").val("")
                                    $("select[name=customer]").val("")
                                    $("select[name=status]").val("")
                                    $("#proposal-table").DataTable().draw();
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
            Column::make('proposal_id')->title(__('Proposal')),

        ];
        if (Auth::user()->type != 'client') {
            $column[] = Column::make('customer_id')->title(__('Customer'));
        }
        $column[] = Column::make('account_type')->title(__('Account Type'));
        $column[] = Column::computed('total_amount')->title(__('Total Amount'));
        $column[] = Column::make('issue_date')->title(__('Issue Date'));
        $column[] = Column::make('status')->title(__('Status'));
        $column[] = Column::computed('action')
            ->exportable(false)
            ->printable(false)
            ->width(60)
            ;
        return $column;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Proposal_' . date('YmdHis');
    }
}
