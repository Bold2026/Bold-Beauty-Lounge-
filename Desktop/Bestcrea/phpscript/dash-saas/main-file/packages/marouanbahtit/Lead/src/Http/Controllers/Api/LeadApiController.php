<?php

namespace Workdo\Lead\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Lead\Entities\Pipeline;
use Workdo\Lead\Entities\LeadUtility;
use Workdo\Lead\Entities\LeadStage;
use Workdo\Lead\Entities\DealStage;
use Workdo\Lead\Entities\Lead;
use Illuminate\Support\Facades\Auth;
use Workdo\Lead\Entities\Label;
use Workdo\Lead\Entities\UserLead;
use Workdo\Lead\Entities\LeadActivityLog;
use Workdo\Lead\Entities\LeadCall;
use Workdo\Lead\Entities\LeadDiscussion;
use Workdo\Lead\Entities\LeadEmail;
use Workdo\Lead\Entities\LeadFile;
use Workdo\Lead\Entities\LeadTask;

class LeadApiController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function leadboard(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                'workspace_id'  => 'required',
                'pipeline_id'  => 'required|exists:pipelines,id',
            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return response()->json(['status' => 0, 'message' => $messages->first()] , 403);
        }

        try{

            $objUser          = Auth::user();
            $pipelineId       = $request->pipeline_id;
            $currentWorkspace = $request->workspace_id;

            $pipeline = Pipeline::where('created_by', '=', creatorId())
                                    ->where('workspace_id', '=', $currentWorkspace)
                                    ->where('id', '=', $pipelineId)
                                    ->first();

            $leadStages = $pipeline->leadStages->map(function($stage){
                                                        return (object) [
                                                            'id' => $stage->id,
                                                            'name' => $stage->name,
                                                            'order' => $stage->order,
                                                        ];
                                                    });;

            $data = [];

            foreach ($leadStages as $key => $stage)
            {
                $lead = Lead::where('workspace_id', $currentWorkspace)
                ->where('pipeline_id', $pipelineId)
                ->where('stage_id', $stage->id);

                if(Auth::user()->type == 'company'){
                    $lead = $lead->where('created_by', '=', creatorId());
                } else {
                    $lead = $lead->where('user_id', Auth::user()->id);
                }
                $lead = $lead->get();
                $stage->leads =  $lead->map(function($lead)use($key ,$leadStages){

                    return [
                        'id'                => $lead->id,
                        'name'              => $lead->name,
                        'order'             => $lead->order,
						'email'				=> $lead->email,
						'subject'				=> $lead->subject,
						'phone'				=> $lead->phone,
                        'previous_stage'    => isset($leadStages[$key-1]) ? $leadStages[$key-1]->id : 0,
                        'current_stage'     => $leadStages[$key]->id,
                        'next_stage'        => isset($leadStages[$key+1]) ? $leadStages[$key+1]->id : 0,
						'follow_up_date'    => $lead->follow_up_date,
						'total_tasks'       => $lead->tasks->count() .'/'. $lead->tasks->where('status',0)->count()  ,
						'total_products'    => !empty($lead->products) ? count(explode(',', $lead->products)) : 0,
						'total_sources'    => !empty($lead->sources) ? count(explode(',', $lead->sources)) : 0,
                        'labels'            => $lead->labels()?$lead->labels()->map(function($label){
                                                    return [
                                                        'id'    => $label->id,
                                                        'name'  => $label->name,
                                                        'color' => Label::$colorCode[$label->color],
                                                    ]   ;
                                                }) : [],
                        // 'assign_user'       => $lead->assign_user()->pluck('user_id')->first(),
                        // 'users'             => $lead->assign_user->map(function($user){
                        'users'             => $lead->users->map(function($user){
													// if ($user->type != 'company') {
														return [
															'id'        => $user->id,
															'name'      => $user->name,
															'avatar'    => check_file($user->avatar) ? get_file($user->avatar) : get_file('uploads/users-avatar/avatar.png'),
														];
												// 	}
												// 	return null;
                                                // })->filter(function($user) {
												// 	return !is_null($user); // Filter out null values
												// })->values(),
                                            }),
                    ];
                });
            }

            return response()->json(['status' => 1, 'data' => $leadStages] , 200);

        } catch (\Exception $e) {
            return response()->json(['status'=> 0 ,'message' => 'something went wrong!!!']);
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('lead::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validation = [
            'workspace_id'  => 'required|exists:work_spaces,id',
            'pipeline_id'   => 'required|exists:pipelines,id',
            'phone'         => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'subject'       => 'required',
            'name'          => 'required',
            'email'         => 'required',
            'follow_up_date'=> 'required|date_format:Y-m-d',
            'lead_id'       => 'exists:leads,id',
        ];

        if(Auth::user()->type == 'company'){
            $validation['user'] = 'required|exists:users,id';
        }

        $validator = \Validator::make(
            $request->all(), $validation
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return response()->json(['status' => 0, 'message' => $messages->first()] , 403);
        }

        // // $userIds = explode(',', $request->user);
        // // $userIds = json_decode($request->user, true);
        // $userIds = $request->user;

        // $existingUsers = User::whereIn('id', $userIds)->pluck('id')->toArray();
        // $nonExistingIds = array_diff($userIds, $existingUsers);
        // if (!empty($nonExistingIds)) {
        //     $errorMessage = 'The selected user ' . implode(',', $nonExistingIds) . ' is invalid .';
        //     return response()->json(['status' => 0, 'message' => $errorMessage], 403);
        // }

        try{

            $objUser          = Auth::user();
            $pipelineId       = $request->pipeline_id;
            $currentWorkspace = $request->workspace_id;

            // Default Field Value
            $pipeline = Pipeline::where('created_by', '=', creatorId())
            ->where('workspace_id', $currentWorkspace)
            ->where('id', $pipelineId)
            ->first();

            if (!empty($pipeline)) {
                $stage = LeadStage::where('pipeline_id', '=', $pipelineId)
                ->where('workspace_id', $currentWorkspace)
                ->first();
            } else {
                return response()->json(['status' => 0, 'message' => 'Please Create Pipeline.'] , 403);
            }

            if (empty($stage)) {
                return response()->json(['status' => 0, 'message' => 'Please Create Stage for This Pipeline'] , 403);
            } else {
                if ($objUser->type == 'company') {
                    $user_id = $request->user;
                } else {
                    $user_id = $request->user_id;
                }

                if(empty($request->lead_id))
                {
                    $lead                 = new Lead();
                    $lead->name           = $request->name;
                    $lead->email          = $request->email;
                    $lead->subject        = $request->subject;
                    $lead->user_id        = $user_id;
                    $lead->pipeline_id    = $pipelineId;
                    $lead->stage_id       = $stage->id;
                    $lead->phone          = $request->phone;
                    $lead->created_by     = creatorId();
                    $lead->workspace_id   = $currentWorkspace;
                    $lead->date           = date('Y-m-d');
                    $lead->follow_up_date = $request->follow_up_date;
                    $lead->save();

                    if (Auth::user()->hasRole('company')) {
                        $usrLeads = [
                            $objUser->id,
                            $user_id,
                        ];
                    } else {
                        $usrLeads = [
                            creatorId(),
                            $user_id,
                        ];
                    }

                    foreach ($usrLeads as $usrLead) {
                        UserLead::create(
                            [
                                'user_id' => $usrLead,
                                'lead_id' => $lead->id,
                            ]
                        );
                    }

                    return response()->json(['status' => 1 , 'message' => 'Lead created Successfully.'] , 200);
                }else{

                    $lead                 = Lead::where('created_by', '=', creatorId())
                                                    ->where('workspace_id', $currentWorkspace)
                                                    ->where('id',$request->lead_id )
                                                    ->first();

                    if($lead !== null){
                        $stage_id = $lead->stage_id;

                        $lead->name           = $request->name;
                        $lead->email          = $request->email;
                        $lead->subject        = $request->subject;
                        $lead->user_id        = $user_id;
                        $lead->pipeline_id    = $pipelineId;
                        $lead->stage_id       = $stage_id;
                        $lead->phone          = $request->phone;
                        $lead->follow_up_date = $request->follow_up_date;
                        $lead->save();

						if (Auth::user()->hasRole('company')) {
							$usrLeads = [
								$objUser->id,
								$user_id,
							];

						} else {
							$usrLeads = [
								creatorId(),
								$user_id,
							];
						}

                        // $users = explode(',', $usrLeads[1]);
                        // $users = json_decode($usrLeads[1], true);
                        // $users = $usrLeads[1];


						$user_leads = UserLead::where('lead_id',$lead->id)->delete();
						// $UserLeads->delete();

						foreach ($usrLeads as $usrLead) {
                            UserLead::updateOrCreate(
								[
									'user_id' => $usrLead,
									'lead_id' => $lead->id,
								]
							);
						}

                    }else{

                        return response()->json(['status'=> 0 ,'message' => 'Lead not found!!!']);
                    }

                    return response()->json(['status' => 1 , 'message' => 'Lead updated Successfully.'] , 200);
                }
            }

            return response()->json(['status' => 1, 'data' => $pipeline] , 200);

        } catch (\Exception $e) {
            return response()->json(['status'=> 0 ,'message' => 'something went wrong!!!']);
        }
    }

    public function leadDetails(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                'workspace_id'  => 'required|exists:work_spaces,id',
                'pipeline_id'   => 'required|exists:pipelines,id',
                'lead_id'       => 'required|exists:leads,id',
            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return response()->json(['status' => 0, 'message' => $messages->first()] , 403);
        }

        $objUser          = Auth::user();
        $pipelineId       = $request->pipeline_id;
        $currentWorkspace = $request->workspace_id;
        $leadId           = $request->lead_id;


        try{

            $lead = Lead::where('created_by', '=', creatorId())->where('workspace_id', $currentWorkspace)->where('pipeline_id', $pipelineId)->where('id', $leadId)->first();

			$stageCnt      = LeadStage::where('pipeline_id', '=', $lead->pipeline_id)->where('created_by', '=', $lead->created_by)->get();
            $i             = 0;
            foreach ($stageCnt as $stage) {
                $i++;
                if ($stage->id == $lead->stage_id) {
                    break;
                }
            }
            $precentage = number_format(($i * 100) / count($stageCnt));

            $data = [
                'id'                => $lead->id,
                'name'              => $lead->name,
                'email'             => $lead->email,
                'subject'           => $lead->subject,
                'pipeline_id'       => $lead->pipeline_id,
				'pipeline_name'     => $lead->pipeline->name,
                'stage_id'          => $lead->stage_id,
				'stage_name'        => $lead->stage->name,
                'order'             => $lead->order,
                'phone'             => $lead->phone,
				'created_at'        => company_date_formate($lead->created_at),
                'follow_up_date'    => $lead->follow_up_date != 'null' ? $lead->follow_up_date : '-',
				'percentage'        => $precentage .'%',
                'tasks_list'        => $lead->tasks->map(function($task){
                                            return [
                                                'id'        => $task->id,
                                                'name'      => $task->name,
                                                'date'      => $task->date,
                                                'time'      => $task->time,
                                                'priority'  => LeadTask::$priorities[$task->priority],
                                                'status'    => LeadTask::$status[$task->status],
                                            ];
                                        }),

                'lead_activity'     => $lead->activities->map(function($activity){
                                                return [
                                                    'id'        => $activity->id,
                                                    // 'log_type'  => $activity->log_type,
                                                    'remark'    => strip_tags($activity->getLeadRemark()),
                                                    'time'      => $activity->created_at->diffForHumans(),
                                            ];
                                        }),
            ];

            return response()->json(['status' => 1, 'data' => $data] , 200);

        } catch (\Exception $e) {
            return response()->json(['status'=> 0 ,'message' => 'something went wrong!!!']);
        }
    }

    public function leadStageUpdate(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                'workspace_id'  => 'required|exists:work_spaces,id',
                'pipeline_id'   => 'required|exists:pipelines,id',
                'lead_id'       => 'required|exists:leads,id',
                'new_status'    => 'required|exists:lead_stages,id',
                // 'old_status'    => 'required',
            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return response()->json(['status' => 0, 'message' => $messages->first()] , 403);
        }

        try{

            $objUser          = Auth::user();
            $pipelineId       = $request->pipeline_id;
            $currentWorkspace = $request->workspace_id;
            $leadId           = $request->lead_id;

            $lead = Lead::where('created_by', '=', creatorId())->where('workspace_id', $currentWorkspace)->where('pipeline_id', $pipelineId)->where('id', $leadId)->first();

            if ($request->new_status != $lead->stage_id) {

                $new_status   = LeadStage::where('workspace_id',$currentWorkspace)->where('created_by',creatorId())->where('id',$request->new_status)->first();
                $lead->stage_id = $request->new_status;
                $lead->save();
            }
            return response()->json(['status' => 1 ,'message' => 'Lead stage update successfully.']);

        } catch (\Exception $e) {
            return response()->json(['status' => 0 ,'message' => 'something went wrong!!!']);
        }
    }


    public function destroy(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                'workspace_id'  => 'required|exists:work_spaces,id',
                'pipeline_id'   => 'required|exists:pipelines,id',
                'lead_id'       => 'required|exists:leads,id',
            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return response()->json(['status' => 0, 'message' => $messages->first()] , 403);
        }

        try{

            $objUser          = Auth::user();
            $pipelineId       = $request->pipeline_id;
            $currentWorkspace = $request->workspace_id;
            $leadId           = $request->lead_id;

            $lead = Lead::where('created_by', '=', creatorId())->where('workspace_id', $currentWorkspace)->where('pipeline_id', $pipelineId)->where('id', $leadId)->first();

            LeadDiscussion::where('lead_id', '=', $lead->id)->delete();
            UserLead::where('lead_id', '=', $lead->id)->delete();
            LeadActivityLog::where('lead_id', '=', $lead->id)->delete();
            $leadfiles = LeadFile::where('lead_id', '=', $lead->id)->get();

            foreach ($leadfiles as $leadfile) {

                delete_file($leadfile->file_path);
                $leadfile->delete();
            }

            $lead->delete();

            return response()->json(['status' => 1 , 'message' => 'Lead Delete Successfully.'] , 200);

        } catch (\Exception $e) {
            return response()->json(['status'=> 0 ,'message' => 'something went wrong!!!']);
        }
    }
}
