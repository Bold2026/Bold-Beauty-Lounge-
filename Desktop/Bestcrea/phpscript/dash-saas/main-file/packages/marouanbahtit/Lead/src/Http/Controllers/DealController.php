<?php

namespace Workdo\Lead\Http\Controllers;

use App\Models\User;
use App\Models\EmailTemplate;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Lead\Entities\ClientDeal;
use Workdo\Lead\Entities\ClientPermission;
use Workdo\Lead\Entities\Deal;
use Workdo\Lead\Entities\DealActivityLog;
use Workdo\Lead\Entities\DealCall;
use Workdo\Lead\Entities\DealDiscussion;
use Workdo\Lead\Entities\DealEmail;
use Workdo\Lead\Entities\DealFile;
use Workdo\Lead\Entities\DealStage;
use Workdo\Lead\Entities\DealTask;
use Workdo\Lead\Entities\Label;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\Pipeline;
use Workdo\Lead\Entities\Source;
use Workdo\Lead\Entities\User as EntitiesUser;
use Workdo\Lead\Entities\UserDeal;
use Workdo\ProductService\Entities\ProductService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Workdo\Lead\DataTables\DealDataTable;
use Workdo\Lead\Events\CreateDeal;
use Workdo\Lead\Events\CreateDealTask;
use Workdo\Lead\Events\DealAddCall;
use Workdo\Lead\Events\DealAddClient;
use Workdo\Lead\Events\DealAddDiscussion;
use Workdo\Lead\Events\DealAddEmail;
use Workdo\Lead\Events\DealAddNote;
use Workdo\Lead\Events\DealAddProduct;
use Workdo\Lead\Events\DealMoved;
use Workdo\Lead\Events\StatusChangeDealTask;
use Workdo\Lead\Events\UpdateDealTask;
use Workdo\Lead\Events\DestroyDeal;
use Workdo\Lead\Events\DestroyDealTask;
use Workdo\Lead\Events\DestroyUserDeal;
use Workdo\Lead\Events\UpdateDeal;
use Workdo\Lead\Events\DealAddUser;
use Workdo\Lead\Events\DealCallUpdate;
use Workdo\Lead\Events\DealSourceUpdate;
use Workdo\Lead\Events\DealUploadFile;
use Workdo\Lead\Events\DestroyDealCall;
use Workdo\Lead\Events\DestroyDealClient;
use Workdo\Lead\Events\DestroyDealfile;
use Workdo\Lead\Events\DestroyDealProduct;
use Workdo\Lead\Events\DestroyDealSource;

class DealController extends Controller
{
    public function index()
    {
        $creatorId          = creatorId();
        $getActiveWorkSpace = getActiveWorkSpace();
        $usr = Auth::user();
        $user = EntitiesUser::find($usr->id);
        if ($usr->isAbleTo('deal manage')) {
            if ($usr->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $usr->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }

            $pipelines = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
            if (Auth::user()->type == 'client') {
                $id_deals = $user->clientDeals->pluck('id');
            } else {
                $id_deals = $user->deals->pluck('id');
            }
            if (!empty($pipeline)) {
                $deals       = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->get();
                $curr_month  = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereMonth('created_at', '=', date('m'))->get();
                $curr_week   = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereBetween(
                    'created_at',
                    [
                        \Carbon\Carbon::now()->startOfWeek(),
                        \Carbon\Carbon::now()->endOfWeek(),
                    ]
                )->get();
                $last_30days = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereDate('created_at', '>', \Carbon\Carbon::now()->subDays(30))->get();
            } else {
                return redirect()->back()->with('error', __('Please create pipeline'));
            }
            // Deal Summary
            $cnt_deal                = [];
            $cnt_deal['total']       = Deal::getDealSummary($deals);
            $cnt_deal['this_month']  = Deal::getDealSummary($curr_month);
            $cnt_deal['this_week']   = Deal::getDealSummary($curr_week);
            $cnt_deal['last_30days'] = Deal::getDealSummary($last_30days);

            return view('lead::deals.index', compact('pipelines', 'pipeline', 'cnt_deal'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function deal_list(DealDataTable $dataTable)
    {
        $usr = Auth::user();
        $creatorId          = creatorId();
        $getActiveWorkSpace = getActiveWorkSpace();
        $user = EntitiesUser::find($usr->id);
        if ($usr->isAbleTo('deal manage')) {
            if ($usr->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $usr->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }

            $pipelines = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');

            if ($usr->type == 'client') {
                $id_deals = $user->clientDeals->pluck('id');
            } else {
                $id_deals = $user->deals->pluck('id');
            }
            if (!empty($pipeline)) {
                $deals       = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->get();
                $curr_month  = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereMonth('created_at', '=', date('m'))->get();
                $curr_week   = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereBetween(
                    'created_at',
                    [
                        \Carbon\Carbon::now()->startOfWeek(),
                        \Carbon\Carbon::now()->endOfWeek(),
                    ]
                )->get();
                $last_30days = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id)->whereDate('created_at', '>', \Carbon\Carbon::now()->subDays(30))->get();
            } else {
                return redirect()->back()->with('error', __('Please create pipeline'));
            }
            // Deal Summary
            $cnt_deal                = [];
            $cnt_deal['total']       = Deal::getDealSummary($deals);
            $cnt_deal['this_month']  = Deal::getDealSummary($curr_month);
            $cnt_deal['this_week']   = Deal::getDealSummary($curr_week);
            $cnt_deal['last_30days'] = Deal::getDealSummary($last_30days);

            return $dataTable->render('lead::deals.list', compact('pipelines', 'pipeline', 'deals', 'cnt_deal'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new redeal.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('deal create')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            if (module_is_active('CustomField')) {
                $customFields =  \Workdo\CustomField\Entities\CustomField::where('workspace_id', $getActiveWorkSpace)->where('module', '=', 'lead')->where('sub_module', 'deal')->get();
            } else {
                $customFields = null;
            }
            $clients      = User::where('created_by', '=', $creatorId)->where('type', '=', 'client')->get()->pluck('name', 'id');
            return view('lead::deals.create', compact('clients', 'customFields'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Store a newly created redeal in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $usr = Auth::user();
        if ($usr->isAbleTo('deal create')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $validator = \Validator::make(
                $request->all(),
                [
                    'name'  => 'required|string|max:255',
                    'price' => 'numeric|min:0',
                    'phone' => [
                        'required',
                        'regex:/^\+\d{1,3}\d{9,13}$/'
                    ],
                    'clients'   => 'required|array|min:1',
                    'clients.*' => 'integer|exists:users,id',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Default Field Value
            if ($usr->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $usr->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }

            $stage = DealStage::where('pipeline_id', '=', $pipeline->id)->first();
            // End Default Field Value

            // Check if stage are available or not in pipeline.
            if (empty($stage)) {
                return redirect()->back()->with('error', __('Please create stage for this pipeline.'));
            } else {
                $deal       = new Deal();
                $deal->name = $request->name;
                if (empty($request->price)) {
                    $deal->price = 0;
                } else {
                    $deal->price = $request->price;
                }
                $deal->pipeline_id = $pipeline->id;
                $deal->stage_id    = $stage->id;
                $deal->status      = 'Active';
                $deal->phone       = $request->phone;
                $deal->created_by  = $creatorId;
                $deal->workspace_id  = $getActiveWorkSpace;
                $deal->save();

                $clients = User::whereIN('id', array_filter($request->clients))->get()->pluck('email', 'id')->toArray();

                foreach (array_keys($clients) as $client) {
                    ClientDeal::create(
                        [
                            'deal_id' => $deal->id,
                            'client_id' => $client,
                        ]
                    );
                }

                if (Auth::user()->hasRole('company')) {
                    $usrDeals = [
                        $creatorId
                    ];
                } else {
                    $usrDeals = [
                        $creatorId,
                        Auth::user()->id,
                    ];
                }

                foreach ($usrDeals as $usrDeal) {
                    UserDeal::create(
                        [
                            'user_id' => $usrDeal,
                            'deal_id' => $deal->id,
                        ]
                    );
                }

                if (module_is_active('CustomField')) {
                    \Workdo\CustomField\Entities\CustomField::saveData($deal, $request->customField);
                }

                if (!empty(company_setting('Deal Assigned')) && company_setting('Deal Assigned')  == true) {
                    $dArr = [
                        'deal_name' => !empty($deal->name) ? $deal->name : '',
                        'deal_pipeline' => $pipeline->name,
                        'deal_stage' => $stage->name,
                        'deal_status' => $deal->status,
                        'deal_price' =>  currency_format_with_sym($deal->price),
                    ];
                    // Send Mail
                    $resp = EmailTemplate::sendEmailTemplate('Deal Assigned', $clients, $dArr);
                }

                event(new CreateDeal($request, $deal));

                $resp = null;
                $resp['is_success'] = true;
                return redirect()->back()->with('success', __('The deal has been created successfully.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Display the specified redeal.
     *
     * @param \App\Deal $deal
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Deal $deal)
    {
        if (Auth::user()->isAbleTo('deal show')) {
            if ($deal->is_active) {
                $transdate = date('Y-m-d', time());
                $calenderTasks = [];
                if (Auth::user()->isAbleTo('deal task show')) {
                    foreach ($deal->tasks as $task) {
                        $calenderTasks[] = [
                            'title' => $task->name,
                            'start' => $task->date,
                            'url' => route(
                                'deals.tasks.show',
                                [
                                    $deal->id,
                                    $task->id,
                                ]
                            ),
                            'className' => ($task->status) ? 'event-success border-success' : 'event-warning border-warning',
                        ];
                    }
                }
                $permission = [];
                if (Auth::user()->type == 'client') {
                    if ($permission) {
                        $permission = explode(',', $permission->permissions);
                    } else {
                        $permission = [];
                    }
                }

                if (module_is_active('CustomField')) {
                    $deal->customField = \Workdo\CustomField\Entities\CustomField::getData($deal, 'lead', 'deal');
                    $customFields      = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'lead')->where('sub_module', 'deal')->get();
                } else {
                    $customFields = null;
                }

                return view('lead::deals.show', compact('deal', 'transdate', 'calenderTasks', 'permission', 'customFields'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for editing the specified redeal.
     *
     * @param \App\Deal $deal
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Deal $deal)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            if ($deal->created_by == $creatorId) {
                $pipelines = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
                $pipelines->prepend(__('Select Pipeline'), '');
                $sources = Source::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
                if (module_is_active('ProductService')) {
                    $products = ProductService::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
                }
                if (module_is_active('CustomField')) {
                    $deal->customField = \Workdo\CustomField\Entities\CustomField::getData($deal, 'lead', 'deal');
                    $customFields             = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', $getActiveWorkSpace)->where('module', '=', 'lead')->where('sub_module', 'deal')->get();
                } else {
                    $customFields = null;
                }
                $deal->sources  = explode(',', $deal->sources);
                $deal->products = explode(',', $deal->products);

                return view('lead::deals.edit', compact('deal', 'pipelines', 'sources', 'products', 'customFields'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Update the specified redeal in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Deal $deal
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Deal $deal)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name'          => 'required|string|max:255',
                        'price'         => 'numeric|min:0',
                        'pipeline_id'   => 'required|integer|exists:pipelines,id',
                        'stage_id'      => 'required|integer|exists:deal_stages,id',
                        'phone'         => [
                            'required',
                            'regex:/^\+\d{1,3}\d{9,13}$/'
                        ],
                        'sources'       => 'nullable|array',
                        'sources.*'     => 'integer|exists:sources,id',
                        'products'      => 'nullable|array',
                        'products.*'    => 'integer|exists:product_services,id',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $deal->name = $request->name;
                if (empty($request->price)) {
                    $deal->price = 0;
                } else {
                    $deal->price = $request->price;
                }
                $deal->pipeline_id = $request->pipeline_id;
                $deal->stage_id    = $request->stage_id;
                $deal->phone       = $request->phone;
                $deal->sources     = isset($request->sources) && !empty($request->sources) ? implode(",", array_filter($request->sources)) : null;
                $deal->products    = isset($request->products) && !empty($request->products) ? implode(",", array_filter($request->products)) : null;
                $deal->notes       = $request->notes;
                $deal->save();

                if (module_is_active('CustomField')) {
                    \Workdo\CustomField\Entities\CustomField::saveData($deal, $request->customField);
                }
                event(new UpdateDeal($request, $deal));

                return redirect()->back()->with('success', __('The deal details are updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified redeal from storage.
     *
     * @param \App\Deal $deal
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Deal $deal)
    {
        if (Auth::user()->isAbleTo('deal delete')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {

                event(new DestroyDeal($deal));

                DealDiscussion::where('deal_id', '=', $deal->id)->delete();
                $dealfiles = DealFile::where('deal_id', '=', $deal->id)->get();
                foreach ($dealfiles as $dealfile) {

                    delete_file($dealfile->file_path);
                    $dealfile->delete();
                }
                ClientDeal::where('deal_id', '=', $deal->id)->delete();
                UserDeal::where('deal_id', '=', $deal->id)->delete();
                DealTask::where('deal_id', '=', $deal->id)->delete();
                DealActivityLog::where('deal_id', '=', $deal->id)->delete();
                ClientPermission::where('deal_id', '=', $deal->id)->delete();
                if (module_is_active('CustomField')) {
                    $customFields = \Workdo\CustomField\Entities\CustomField::where('module', 'lead')->where('sub_module', 'deal')->get();
                    foreach ($customFields as $customField) {
                        $value = \Workdo\CustomField\Entities\CustomFieldValue::where('record_id', '=', $deal->id)->where('field_id', $customField->id)->first();
                        if (!empty($value)) {
                            $value->delete();
                        }
                    }
                }
                $lead = Lead::where(['is_converted' => $deal->id])->update(['is_converted' => 0]);

                $deal->delete();

                return redirect()->route('deals.index')->with('success', __('The deal has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function order(Request $request)
    {
        try {
            $usr = Auth::user();

            if ($usr->isAbleTo('deal move')) {
                $post       = $request->all();
                $deal       = Deal::find($post['deal_id']);
                $clients    = ClientDeal::select('client_id')->where('deal_id', '=', $deal->id)->get()->pluck('client_id')->toArray();
                $deal_users = $deal->users->pluck('id')->toArray();
                $usrs       = User::whereIN('id', array_merge($deal_users, $clients))->get()->pluck('email', 'id')->toArray();

                if ($deal->stage_id != $post['stage_id']) {
                    $newStage = DealStage::find($post['stage_id']);
                    DealActivityLog::create(
                        [
                            'user_id' => $usr->id,
                            'deal_id' => $deal->id,
                            'log_type' => 'Move',
                            'remark' => json_encode(
                                [
                                    'title' => $deal->name,
                                    'old_status' => $deal->stage->name,
                                    'new_status' => $newStage->name,
                                ]
                            ),
                        ]
                    );


                    if (!empty(company_setting('Deal Moved')) && company_setting('Deal Moved')  == true) {
                        $dArr = [
                            'deal_name' => $deal->name,
                            'deal_pipeline' => $deal->pipeline->name,
                            'deal_stage' => $deal->stage->name,
                            'deal_status' => $deal->status,
                            'deal_price' => currency_format_with_sym($deal->price),
                            'deal_old_stage' => $deal->stage->name,
                            'deal_new_stage' => $newStage->name,
                        ];

                        // Send Email
                        $resp =  EmailTemplate::sendEmailTemplate('Deal Moved', $usrs, $dArr);
                    }
                    event(new DealMoved($request, $deal));
                }
                foreach ($post['order'] as $key => $item) {
                    $deal           = Deal::find($item);
                    $deal->order    = $key;
                    $deal->stage_id = $post['stage_id'];
                    $deal->save();
                }
                return response()->json(['success' => __('Deal moved successfully.')]);
            } else {
                return response()->json(['error' => __('Permission denied.')]);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => __('Something went wrong.')]);
        }
    }

    public function labels($id)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $labels   = Label::where('pipeline_id', '=', $deal->pipeline_id)->get();
                $selected = $deal->labels();
                if ($selected) {
                    $selected = $selected->pluck('name', 'id')->toArray();
                } else {
                    $selected = [];
                }

                return view('lead::deals.labels', compact('deal', 'labels', 'selected'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function labelStore($id, Request $request)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                if ($request->labels) {
                    $deal->labels = implode(',', $request->labels);
                } else {
                    $deal->labels = $request->labels;
                }
                $deal->save();

                return redirect()->back()->with('success', __('The label details are updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function userEdit($id)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $users = User::where('active_workspace', '=', $getActiveWorkSpace)->where('created_by', '=', $creatorId)->where('type', '!=', 'Client')->whereNOTIn(
                    'id',
                    function ($q) use ($deal) {
                        $q->select('user_id')->from('user_deals')->where('deal_id', '=', $deal->id);
                    }
                )->get();
                // foreach ($users as $key => $user) {
                //     if (!$user->isAbleTo('deal manage')) {
                //         $users->forget($key);
                //     }
                // }
                $users = $users->pluck('name', 'id');

                $users->prepend(__('Select Users'), '');

                return view('lead::deals.users', compact('deal', 'users'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function userUpdate($id, Request $request)
    {
        $usr = Auth::user();
        if ($usr->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            $resp = '';

            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                if (!empty($request->users)) {
                    $users = User::whereIN('id', array_filter($request->users))->get()->pluck('email', 'id')->toArray();

                    $dealArr = [
                        'deal_id' => $deal->id,
                        'name' => $deal->name,
                        'updated_by' => $usr->id,
                    ];
                    foreach (array_keys($users) as $user) {
                        UserDeal::create(
                            [
                                'deal_id' => $deal->id,
                                'user_id' => $user,
                            ]
                        );
                    }
                    if (!empty(company_setting('Deal Assigned')) && company_setting('Deal Assigned')  == true) {
                        $dArr = [
                            'deal_name' => $deal->name,
                            'deal_pipeline' => $deal->pipeline->name,
                            'deal_stage' => $deal->stage->name,
                            'deal_status' => $deal->status,
                            'deal_price' => currency_format_with_sym($deal->price),
                        ];
                        // Send Email
                        $resp = EmailTemplate::sendEmailTemplate('Deal Assigned', $users, $dArr);
                    }
                }

                event(new DealAddUser($request, $deal));

                if (!empty($users) && !empty($request->users)) {
                    return redirect()->back()->with('success', __('Users have been updated successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                } else {
                    return redirect()->back()->with('error', __('Please select valid user.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function userDestroy($id, $user_id)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                UserDeal::where('deal_id', '=', $deal->id)->where('user_id', '=', $user_id)->delete();

                event(new DestroyUserDeal($deal));

                return redirect()->back()->with('success', __('The user has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function clientEdit($id)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $clients = User::where('created_by', '=', $creatorId)->where('active_workspace', '=', $getActiveWorkSpace)->where('type', '=', 'Client')->whereNOTIn(
                    'id',
                    function ($q) use ($deal) {
                        $q->select('client_id')->from('client_deals')->where('deal_id', '=', $deal->id);
                    }
                )->get()->pluck('name', 'id');

                return view('lead::deals.clients', compact('deal', 'clients'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function clientUpdate($id, Request $request)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            $validator = \Validator::make(
                $request->all(),
                [
                    'clients'   => 'required|array|min:1',
                    'clients.*' => 'integer|exists:users,id',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                if (!empty($request->clients)) {
                    $clients = array_filter($request->clients);
                    foreach ($clients as $client) {
                        ClientDeal::create(
                            [
                                'deal_id' => $deal->id,
                                'client_id' => $client,
                            ]
                        );
                    }
                }

                event(new DealAddClient($request, $deal));

                if (!empty($clients) && !empty($request->clients)) {
                    return redirect()->back()->with('success', __('Clients have been updated successfully.'))->with('status', 'clients');
                } else {
                    return redirect()->back()->with('error', __('Please select valid clients.'))->with('status', 'clients');
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'clients');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'clients');
        }
    }

    public function clientDestroy($id, $client_id)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                ClientDeal::where('deal_id', '=', $deal->id)->where('client_id', '=', $client_id)->delete();
                ClientPermission::where('deal_id', '=', $deal->id)->where('client_id', '=', $client_id)->delete();

                event(new DestroyDealClient($deal));

                return redirect()->back()->with('success', __('The client has beeen deleted.'))->with('status', 'clients');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'clients');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'clients');
        }
    }

    public function productEdit($id)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $products = \Workdo\ProductService\Entities\ProductService::where('workspace_id', '=', $getActiveWorkSpace)->where('created_by', '=', $creatorId)->whereNOTIn('id', explode(',', $deal->products))->get()->pluck('name', 'id');

                return view('lead::deals.products', compact('deal', 'products'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function productUpdate($id, Request $request)
    {
        $usr = Auth::user();
        if ($usr->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal       = Deal::find($id);
            $clients    = ClientDeal::select('client_id')->where('deal_id', '=', $id)->get()->pluck('client_id')->toArray();
            $deal_users = $deal->users->pluck('id')->toArray();

            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                if (!empty($request->products)) {
                    $products       = array_filter($request->products);
                    $old_products   = explode(',', $deal->products);
                    $deal->products = implode(',', array_merge($old_products, $products));
                    $deal->save();

                    $objProduct = ProductService::whereIN('id', $products)->get()->pluck('name', 'id')->toArray();
                    DealActivityLog::create(
                        [
                            'user_id' => $usr->id,
                            'deal_id' => $deal->id,
                            'log_type' => 'Add Product',
                            'remark' => json_encode(['title' => implode(",", $objProduct)]),
                        ]
                    );

                    $productArr = [
                        'deal_id' => $deal->id,
                        'name' => $deal->name,
                        'updated_by' => $usr->id,
                    ];
                }

                event(new DealAddProduct($request, $deal));

                if (!empty($products) && !empty($request->products)) {
                    return redirect()->back()->with('success', __('Products have been updated successfully.'))->with('status', 'products');
                } else {
                    return redirect()->back()->with('error', __('Please select valid product.'))->with('status', 'general');
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
        }
    }

    public function productDestroy($id, $product_id)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $products = explode(',', $deal->products);
                foreach ($products as $key => $product) {
                    if ($product_id == $product) {
                        unset($products[$key]);
                    }
                }
                $deal->products = implode(',', $products);
                $deal->save();

                event(new DestroyDealProduct($deal));

                return redirect()->back()->with('success', __('The product has been deleted.'))->with('status', 'products');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
        }
    }

    public function fileUpload($id, Request $request)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {

                $file_name = $request->file->getClientOriginalName();
                $file_path = $request->deal_id . "_" . md5(time()) . "_" . $request->file->getClientOriginalName();

                $url = upload_file($request, 'file', $file_name, 'deals', []);
                if (isset($url['flag']) && $url['flag'] == 1) {

                    $file                 = DealFile::create(
                        [
                            'deal_id' => $request->deal_id,
                            'file_name' => $file_name,
                            'file_path' => $url['url'],
                        ]
                    );
                    $return               = [];
                    $return['is_success'] = true;
                    $return['download']   = get_file($url['url']);

                    $return['delete']     = route(
                        'deals.file.delete',
                        [
                            $deal->id,
                            $file->id,
                        ]
                    );

                    DealActivityLog::create(
                        [
                            'user_id' => Auth::user()->id,
                            'deal_id' => $deal->id,
                            'log_type' => 'Upload File',
                            'remark' => json_encode(['file_name' => $file_name]),
                        ]
                    );

                    event(new DealUploadFile($request, $deal));

                    return response()->json($return);
                } else {
                    return response()->json(
                        [
                            'is_success' => false,
                            'error' => $url['msg'],
                        ],
                        401
                    );
                }
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function fileDownload($id, $file_id)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $file = DealFile::find($file_id);
                if ($file) {
                    $file_path = get_base_file($file->file_path);
                    $filename  = $file->file_name;

                    return \Response::download(
                        $file_path,
                        $filename,
                        [
                            'Content-Length: ' . get_size($file_path),
                        ]
                    );
                } else {
                    return redirect()->back()->with('error', __('The file does not exist.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function fileDelete($id, $file_id)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $file = DealFile::find($file_id);
                if ($file) {
                    delete_file($file->file_path);
                    $file->delete();

                    event(new DestroyDealfile($deal));

                    return response()->json(['is_success' => true, 'success' => __('The file has been deleted.')], 200);
                } else {
                    return response()->json(
                        [
                            'is_success' => false,
                            'error' => __('The file does not exist.'),
                        ],
                        200
                    );
                }
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function noteStore($id, Request $request)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $deal->notes = $request->notes;
                $deal->save();

                event(new DealAddNote($request, $deal));

                return response()->json(
                    [
                        'is_success' => true,
                        'success' => __('The note has been saved successfully.'),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskCreate($id)
    {
        if (Auth::user()->isAbleTo('deal task create')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace  = $getActiveWorkSpace) {
                $priorities = DealTask::$priorities;
                $status     = DealTask::$status;
                return view('lead::deals.tasks', compact('deal', 'priorities', 'status'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskStore($id, Request $request)
    {

        $usr = Auth::user();
        if ($usr->isAbleTo('deal task create')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal       = Deal::find($id);
            $clients    = ClientDeal::select('client_id')->where('deal_id', '=', $id)->get()->pluck('client_id')->toArray();
            $deal_users = $deal->users->pluck('id')->toArray();
            $usrs       = User::whereIN('id', array_merge($deal_users, $clients))->get()->pluck('email', 'id')->toArray();

            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name'      => 'required|string|max:255',
                        'date'      => 'required|date',
                        'time'      => 'required|date_format:H:i',
                        'priority'  => 'required|in:1,2,3',
                        'status'    => 'required|in:0,1',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $dealTask = DealTask::create(
                    [
                        'deal_id' => $deal->id,
                        'name' => $request->name,
                        'date' => $request->date,
                        'time' => date('H:i:s', strtotime($request->date . ' ' . $request->time)),
                        'priority' => $request->priority,
                        'status' => $request->status,
                        'workspace' => $getActiveWorkSpace,
                    ]
                );

                DealActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'deal_id' => $deal->id,
                        'log_type' => 'Create Task',
                        'remark' => json_encode(['title' => $dealTask->name]),
                    ]
                );

                $taskArr = [
                    'deal_id' => $deal->id,
                    'name' => $deal->name,
                    'updated_by' => $usr->id,
                ];
                if (!empty(company_setting('New Task')) && company_setting('New Task')  == true) {
                    $tArr = [
                        'deal_name' => $deal->name,
                        'deal_pipeline' => $deal->pipeline->name,
                        'deal_stage' => $deal->stage->name,
                        'deal_status' => $deal->status,
                        'deal_price' => currency_format_with_sym($deal->price),
                        'task_name' => $dealTask->name,
                        'task_priority' => DealTask::$priorities[$dealTask->priority],
                        'task_status' => DealTask::$status[$dealTask->status],
                    ];

                    // Send Email
                    $resp = EmailTemplate::sendEmailTemplate('New Task', $usrs, $tArr);
                }

                event(new CreateDealTask($request, $dealTask, $deal));
                return redirect()->back()->with('success', __('The task has been created successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function taskShow($id, $task_id)
    {
        if (Auth::user()->isAbleTo('deal task show')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $task = DealTask::find($task_id);

                return view('lead::deals.tasksShow', compact('task', 'deal'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskEdit($id, $task_id)
    {
        if (Auth::user()->isAbleTo('deal task edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $priorities = DealTask::$priorities;
                $status     = DealTask::$status;
                $task       = DealTask::find($task_id);

                return view('lead::deals.tasks', compact('task', 'deal', 'priorities', 'status'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskUpdate($id, $task_id, Request $request)
    {
        if (Auth::user()->isAbleTo('deal task edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name'      => 'required|string|max:255',
                        'date'      => 'required|date',
                        'time'      => 'required',
                        'priority'  => 'required|in:1,2,3',
                        'status'    => 'required|in:0,1',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $task = DealTask::find($task_id);

                $task->update(
                    [
                        'name' => $request->name,
                        'date' => $request->date,
                        'time' => date('H:i:s', strtotime($request->date . ' ' . $request->time)),
                        'priority' => $request->priority,
                        'status' => $request->status,
                    ]
                );

                event(new UpdateDealTask($request, $deal, $task));

                return redirect()->back()->with('success', __('The task details are updated successfully.'))->with('status', 'tasks');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function taskUpdateStatus($id, $task_id, Request $request)
    {
        if (Auth::user()->isAbleTo('deal task edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'status' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return response()->json(
                        [
                            'is_success' => false,
                            'error' => $messages->first(),
                        ],
                        401
                    );
                }

                $task = DealTask::find($task_id);
                if ($request->status) {
                    $task->status = 0;
                } else {
                    $task->status = 1;
                }
                $task->save();

                event(new StatusChangeDealTask($request, $deal, $task));

                return response()->json(
                    [
                        'is_success' => true,
                        'success' => __('The task status are updated successfully.'),
                        'status' => $task->status,
                        'status_label' => __(DealTask::$status[$task->status]),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskDestroy($id, $task_id)
    {
        if (Auth::user()->isAbleTo('deal task delete')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $task = DealTask::find($task_id);
                $task->delete();

                event(new DestroyDealTask($deal));

                return redirect()->back()->with('success', __('The task has been deleted.'))->with('status', 'tasks');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function sourceEdit($id)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $sources  = Source::where('created_by', '=', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->get();
                $selected = $deal->sources();

                if ($selected) {
                    $selected = $selected->pluck('name', 'id')->toArray();
                }

                return view('lead::deals.sources', compact('deal', 'sources', 'selected'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function sourceUpdate($id, Request $request)
    {
        $usr = Auth::user();

        if ($usr->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal       = Deal::find($id);
            $clients    = ClientDeal::select('client_id')->where('deal_id', '=', $id)->get()->pluck('client_id')->toArray();
            $deal_users = $deal->users->pluck('id')->toArray();

            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                if (!empty($request->sources) && count($request->sources) > 0) {
                    $deal->sources = implode(',', $request->sources);
                } else {
                    $deal->sources = "";
                }

                $deal->save();
                DealActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'deal_id' => $deal->id,
                        'log_type' => 'Update Sources',
                        'remark' => json_encode(['title' => 'Update Sources']),
                    ]
                );

                $dealArr = [
                    'deal_id' => $deal->id,
                    'name' => $deal->name,
                    'updated_by' => $usr->id,
                ];

                event(new DealSourceUpdate($request, $deal));

                return redirect()->back()->with('success', __('Sources have been updated successfully.'))->with('status', 'sources');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
        }
    }

    public function sourceDestroy($id, $source_id)
    {
        if (Auth::user()->isAbleTo('deal edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $sources = explode(',', $deal->sources);
                foreach ($sources as $key => $source) {
                    if ($source_id == $source) {
                        unset($sources[$key]);
                    }
                }
                $deal->sources = implode(',', $sources);
                $deal->save();

                event(new DestroyDealSource($deal));

                return redirect()->back()->with('success', __('The source has been deleted.'))->with('status', 'sources');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
        }
    }

    public function jsonUser(Request $request)
    {
        $users = [];
        if (!empty($request->deal_id)) {
            $deal  = Deal::find($request->deal_id);
            $users = $deal->users->pluck('name', 'id');
        }

        return response()->json($users, 200);
    }

    public function changePipeline(Request $request)
    {

        $user = Auth::user();
        $user->default_pipeline = $request->default_pipeline_id;
        $user->save();

        return redirect()->back();
    }

    public function discussionCreate($id)
    {
        $creatorId          = creatorId();
        $getActiveWorkSpace = getActiveWorkSpace();
        $deal = Deal::find($id);
        if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
            return view('lead::deals.discussions', compact('deal'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function discussionStore($id, Request $request)
    {
        $creatorId          = creatorId();
        $getActiveWorkSpace = getActiveWorkSpace();
        $usr        = Auth::user();
        $deal       = Deal::find($id);

        if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
            $discussion             = new DealDiscussion();
            $discussion->comment    = $request->comment;
            $discussion->deal_id    = $deal->id;
            $discussion->created_by = creatorId();
            $discussion->save();

            $dealArr = [
                'deal_id' => $deal->id,
                'name' => $deal->name,
                'updated_by' => $usr->id,
            ];

            event(new DealAddDiscussion($request, $deal));

            return redirect()->back()->with('success', __('The message has been added successfully.'))->with('status', 'discussion');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'discussion');
        }
    }

    public function changeStatus(Request $request, $id)
    {
        $deal         = Deal::where('id', '=', $id)->first();
        $deal->status = $request->deal_status;
        $deal->save();

        return redirect()->back();
    }

    // Deal Calls
    public function callCreate($id)
    {
        if (Auth::user()->isAbleTo('deal call create')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $users = UserDeal::where('deal_id', '=', $deal->id)->get();

                return view('lead::deals.calls', compact('deal', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function callStore($id, Request $request)
    {
        $usr = Auth::user();

        if ($usr->isAbleTo('deal call create')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject'       => 'required|string|max:255',
                        'call_type'     => 'required|in:outbound,inbound',
                        'user_id'       => 'required|integer|exists:users,id',
                        'duration'      => [
                            'required',
                            'regex:/^\d{2}:\d{2}:\d{2}$/',
                        ],
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                DealCall::create(
                    [
                        'deal_id' => $deal->id,
                        'subject' => $request->subject,
                        'call_type' => $request->call_type,
                        'duration' => $request->duration,
                        'user_id' => $request->user_id,
                        'description' => $request->description,
                        'call_result' => $request->call_result,
                    ]
                );

                DealActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'deal_id' => $deal->id,
                        'log_type' => 'Create Deal Call',
                        'remark' => json_encode(['title' => 'Create new Deal Call']),
                    ]
                );

                $dealArr = [
                    'deal_id' => $deal->id,
                    'name' => $deal->name,
                    'updated_by' => $usr->id,
                ];

                event(new DealAddCall($request, $deal));

                return redirect()->back()->with('success', __('The call has been created successfully.'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
        }
    }

    public function callEdit($id, $call_id)
    {
        if (Auth::user()->isAbleTo('deal call edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $call  = DealCall::find($call_id);
                $users = UserDeal::where('deal_id', '=', $deal->id)->get();

                return view('lead::deals.calls', compact('call', 'deal', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function callUpdate($id, $call_id, Request $request)
    {
        if (Auth::user()->isAbleTo('deal call edit')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject'       => 'required|string|max:255',
                        'call_type'     => 'required|in:outbound,inbound',
                        'user_id'       => 'required|integer|exists:users,id',
                        'duration'      => [
                            'required',
                            'regex:/^\d{2}:\d{2}:\d{2}$/',
                        ],
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $call = DealCall::find($call_id);

                $call->update(
                    [
                        'subject' => $request->subject,
                        'call_type' => $request->call_type,
                        'duration' => $request->duration,
                        'user_id' => $request->user_id,
                        'description' => $request->description,
                        'call_result' => $request->call_result,
                    ]
                );

                event(new DealCallUpdate($request, $deal));

                return redirect()->back()->with('success', __('The call details are updated successfully.'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function callDestroy($id, $call_id)
    {
        if (Auth::user()->isAbleTo('deal call delete')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $task = DealCall::find($call_id);
                $task->delete();

                event(new DestroyDealCall($deal));

                return redirect()->back()->with('success', __('The call has been deleted.'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
        }
    }

    // Deal email
    public function emailCreate($id)
    {
        if (Auth::user()->isAbleTo('deal email create')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                return view('lead::deals.emails', compact('deal'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function emailStore($id, Request $request)
    {
        if (Auth::user()->isAbleTo('deal email create')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $deal = Deal::find($id);
            if ($deal->created_by == $creatorId && $deal->workspace_id == $getActiveWorkSpace) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'to'        => 'required|email|max:255',
                        'subject'   => 'required|string|max:255',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $dealEmail = DealEmail::create(
                    [
                        'deal_id' => $deal->id,
                        'to' => $request->to,
                        'subject' => $request->subject,
                        'description' => $request->description,
                    ]
                );

                if (!empty(company_setting('Deal Emails')) && company_setting('Deal Emails')  == true) {
                    $lead_users[] = $request->to;
                    $lArr = [
                        'deal_name' => $deal->name,
                        'deal_email_subject' => $request->subject,
                        'deal_email_description' => $request->description,
                    ];

                    // Send Email
                   $resp = EmailTemplate::sendEmailTemplate('Deal Emails', $lead_users, $lArr);
                }


                DealActivityLog::create(
                    [
                        'user_id' => Auth::user()->id,
                        'deal_id' => $deal->id,
                        'log_type' => 'Create Deal Email',
                        'remark' => json_encode(['title' => 'Create new Deal Email']),
                    ]
                );

                event(new DealAddEmail($request, $deal));

                return redirect()->back()->with('success', __('The email has been created successfully.') .((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'emails');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'emails');
        }
    }

    public function fileImportExport()
    {
        if (Auth::user()->isAbleTo('deal import')) {
            $user               = Auth::user();
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            if ($user->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $user->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }
            if (!empty($pipeline)) {
                $stage = DealStage::where('pipeline_id', '=', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->first();
                if (empty($stage)) {
                    return response()->json(['error' => __('Please create stage for this pipeline.')], 401);
                }
            } else {
                return response()->json(['error' => __('Please create pipeline.')], 401);
            }
            return view('lead::deals.import');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function fileImport(Request $request)
    {
        if (Auth::user()->isAbleTo('deal import')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            session_start();

            $error = '';

            $html = '';

            if ($request->file->getClientOriginalName() != '') {
                $file_array = explode(".", $request->file->getClientOriginalName());

                $extension = end($file_array);
                if ($extension == 'csv') {
                    $file_data = fopen($request->file->getRealPath(), 'r');

                    $file_header = fgetcsv($file_data);
                    $html .= '<table class="table table-bordered"><tr>';

                    for ($count = 0; $count < count($file_header); $count++) {
                        $html .= '
                                <th>
                                    <select name="set_column_data" class="form-control set_column_data" data-column_number="' . $count . '">
                                    <option value="">Set Count Data</option>
                                    <option value="name">Name</option>
                                    <option value="price">Price</option>
                                    <option value="phone">Phone No</option>
                                    </select>
                                </th>
                                ';
                    }

                    $html .= '
                                <th>
                                        <select name="set_column_data" class="form-control set_column_data client-name" data-column_number="' . $count + 1 . '">
                                            <option value="client">Client</option>
                                        </select>
                                </th>
                                ';

                    $html .= '</tr>';
                    $limit = 0;
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;

                        $html .= '<tr>';

                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . $row[$count] . '</td>';
                        }

                        $html .= '<td>
                                    <select name="client" class="form-control client-name-value">;';
                        $clients = User::where('type', 'client')->where('created_by', $creatorId)->where('workspace_id', $getActiveWorkSpace)->pluck('name', 'id');
                        foreach ($clients as $key => $client) {
                            $html .= ' <option value="' . $key . '">' . $client . '</option>';
                        }
                        $html .= '  </select>
                                </td>';

                        $html .= '</tr>';

                        $temp_data[] = $row;
                    }
                    $_SESSION['file_data'] = $temp_data;
                } else {
                    $error = 'Only <b>.csv</b> file allowed';
                }
            } else {

                $error = 'Please Select CSV File';
            }
            $output = array(
                'error' => $error,
                'output' => $html,
            );

            return json_encode($output);
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function fileImportModal()
    {
        if (Auth::user()->isAbleTo('deal import')) {
            return view('lead::deals.import_modal');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function dealImportdata(Request $request)
    {
        if (Auth::user()->isAbleTo('deal import')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            session_start();
            $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
            $flag = 0;
            $html .= '<table class="table table-bordered"><tr>';
            $file_data = $_SESSION['file_data'];

            foreach ($file_data as $validationKey => $value) {
                $validator = \Validator::make([
                    'name'    => $value[$request->name] ?? null,
                    'price'   => $value[$request->price] ?? null,
                    'phone'   => $value[$request->phone] ?? null,
                ], [
                    'name'    => 'required|string|max:255',
                    'price'   => 'required|numeric|min:0',
                    'phone'   => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first(),
                    ]);
                }
            }

            unset($_SESSION['file_data']);

            $user = Auth::user();

            if ($user->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $user->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }
            if (!empty($pipeline)) {
                $stage = DealStage::where('pipeline_id', '=', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->first();
                if (empty($stage)) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Please create stage for this pipeline.'),
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('Please create pipeline.'),
                ]);
            }
            foreach ($file_data as $key => $row) {
                $deals = Deal::where('created_by', $creatorId)->where('workspace_id', $getActiveWorkSpace)->Where('name', 'like', $row[$request->name])->get();

                if ($deals->isEmpty()) {
                    try {

                        $client = User::find($request->client[$key]);
                        if (empty($client)) {
                            $client = User::where('created_by', Auth::user()->id)->first();
                        }
                        $deal = Deal::create([
                            'name' => $row[$request->name],
                            'price' => $row[$request->price],
                            'phone' => $row[$request->phone],
                            'pipeline_id' => $pipeline->id,
                            'stage_id' => $stage->id,
                            'created_by' => $creatorId,
                            'workspace_id' => $getActiveWorkSpace,
                        ]);
                        ClientDeal::create([
                            'client_id' => $client->id,
                            'deal_id' => $deal->id,
                        ]);

                        UserDeal::create([
                            'user_id' => $creatorId,
                            'deal_id' => $deal->id,
                        ]);
                    } catch (\Exception $e) {
                        $flag = 1;
                        $html .= '<tr>';

                        $html .= '<td>' . $row[$request->name] . '</td>';
                        $html .= '<td>' . $row[$request->price] . '</td>';
                        $html .= '<td>' . $row[$request->phone] . '</td>';

                        $html .= '</tr>';
                    }
                } else {
                    $flag = 1;
                    $html .= '<tr>';

                    $html .= '<td>' . $row[$request->name] . '</td>';
                    $html .= '<td>' . $row[$request->price] . '</td>';
                    $html .= '<td>' . $row[$request->phone] . '</td>';

                    $html .= '</tr>';
                }
            }

            $html .= '
                            </table>
                            <br />
                            ';
            if ($flag == 1) {

                return response()->json([
                    'html' => true,
                    'response' => $html,
                ]);
            } else {
                return response()->json([
                    'html' => false,
                    'response' => __('Data has been imported.'),
                ]);
            }
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }
}
