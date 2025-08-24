<?php

namespace Workdo\Lead\Http\Controllers\Api;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Workdo\Lead\Entities\User as LeadUser;
use Workdo\Lead\Entities\LeadStage;
use Workdo\Lead\Entities\Pipeline;
use Workdo\Lead\Entities\Lead;
use Carbon\Carbon;
use App\Models\User;

class HomeApiController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'workspace_id'  => 'required',
                'pipeline_id'  => 'exists:pipelines,id',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json(['status' => 0, 'message' => $messages->first()], 403);
        }

        $objUser          = Auth::user();
        $active_pipeline          = $objUser->default_pipeline ?? null;
        $pipeline_id = $request->pipeline_id ?? null;
        $currentWorkspace = $request->workspace_id;

        try {

            $pipelines = Pipeline::where('created_by', '=', creatorId())
                                    ->where('workspace_id', '=', $currentWorkspace)
                                    ->get()->map(function($pipeline){
                                        return [
                                            'id'        => $pipeline->id,
                                            'name'      => $pipeline->name,
                                            'status'    => Auth::user()->default_pipeline == $pipeline->id ? true : false,
                                            'stages'    => $pipeline->leadStages->map(function($stage) use($pipeline)  {
                                                    return [
                                                        'id'            => $stage->id,
                                                        'name'          => $stage->name,
                                                        'order'         => $stage->order,
                                                    ];
                                            }),
                                        ];
                                    });

            $totalUsers    = LeadUser::where('type', '!=', 'client')->where('created_by', creatorId())->where('workspace_id', '=', $currentWorkspace)->count();
            $totalLeads    = Lead::where('created_by', '=', creatorId())->where('workspace_id', '=', $currentWorkspace);
            if(Auth::user()->type != 'company'){
                $totalLeads = $totalLeads->where('user_id', Auth::user()->id);
            }
            $totalLeads    = $totalLeads->count();
            $latestLeads   = Lead::where('created_by', '=', creatorId())->where('workspace_id', '=', $currentWorkspace);
            if(Auth::user()->type != 'company'){
                $latestLeads = $latestLeads->where('user_id', Auth::user()->id);
            }
            if($pipeline_id){
                $latestLeads = $latestLeads->where('pipeline_id', '=', $pipeline_id);
            } else {
                if($active_pipeline){
                    $latestLeads = $latestLeads->where('pipeline_id', '=', $objUser->default_pipeline);
                }
                else {
                    $latestLeads = $latestLeads->where('pipeline_id', '=', $pipelines[0]['id']);
                }
            }
            $latestLeads = $latestLeads->limit(5)->latest()
                ->get()->map(function ($lead) {
                    return [
                        'id'            => $lead->id,
                        'name'          => $lead->name,
                        'status'        => isset($lead->stage) ? $lead->stage->name : '',
                        'created_at'    => Carbon::parse($lead->created_at)->format('Y-m-d H:i:s'),
                    ];
                });




            $leadStageData = [];
            $lead_stage = LeadStage::where('created_by', creatorId())->where('workspace_id', '=', $currentWorkspace);
            // if($active_pipeline){
            //     $lead_stage = $lead_stage->where('pipeline_id', '=', $objUser->default_pipeline);
            // }
            // else {
            //     $lead_stage = $lead_stage->where('pipeline_id', '=', $pipelines[0]['id']);
            // }
            // $lead_stage = $lead_stage->orderBy('order', 'ASC')->get();

            if($pipeline_id){
                $lead_stage = $lead_stage->where('pipeline_id', '=', $pipeline_id);
            } else {
                if($active_pipeline){
                    $lead_stage = $lead_stage->where('pipeline_id', '=', $objUser->default_pipeline);
                }
                else {
                    $lead_stage = $lead_stage->where('pipeline_id', '=', $pipelines[0]['id']);
                }
            }
            $lead_stage = $lead_stage->orderBy('order', 'ASC')->get();


            foreach ($lead_stage as $index => $lead_stage_data) {
                $lead_stage = Lead::where('created_by', creatorId())->where('workspace_id', '=', $currentWorkspace)->where('stage_id', $lead_stage_data->id);
                if(Auth::user()->type != 'company'){
                    $lead_stage = $lead_stage->where('user_id', Auth::user()->id);
                }
                $lead_stage = $lead_stage->orderBy('order', 'ASC')->count();
                $leadStageData[$index]['name'] = $lead_stage_data->name;
                $leadStageData[$index]['value'] = $lead_stage;
            }

            $data = [
                'totalUsers' => $totalUsers,
                'totalLeads' => $totalLeads,
                'chartData' => $leadStageData,
                'latestLeads' => $latestLeads,
                'pipelines' => $pipelines,
            ];

            return response()->json(['status' => 1, 'data'  => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Something went wrong!!!']);
        }
    }

	public function chartData(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'workspace_id'  => 'required',
                'pipeline_id'  => 'exists:pipelines,id',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json(['status' => 0, 'message' => $messages->first()], 403);
        }

        $objUser          = Auth::user();
        $active_pipeline          = $objUser->default_pipeline ?? null;
        $pipeline_id = $request->pipeline_id ?? null;
        $currentWorkspace = $request->workspace_id;

        try {
            $pipelines = Pipeline::where('created_by', '=', creatorId())
                                    ->where('workspace_id', '=', $currentWorkspace)
                                    ->get();

            $leadStageData = [];
            $lead_stage = LeadStage::where('created_by', creatorId())->where('workspace_id', '=', $currentWorkspace);
            if($pipeline_id){
                $pipelineId = $pipeline_id;
                $lead_stage = $lead_stage->where('pipeline_id', '=', $pipeline_id);
            } else {
                if($active_pipeline){
                    $pipelineId = $objUser->default_pipeline;
                    $lead_stage = $lead_stage->where('pipeline_id', '=', $objUser->default_pipeline);
                }
                else {
                    $pipelineId = $pipelines[0]['id'];
                    $lead_stage = $lead_stage->where('pipeline_id', '=', $pipelines[0]['id']);
                }
            }
            $lead_stage = $lead_stage->orderBy('order', 'ASC')->get();

            $pipeline = Pipeline::where('created_by', '=', creatorId())
                            ->where('workspace_id', '=', $currentWorkspace)
                            ->where('id', '=', $pipelineId)
                            ->get();

            foreach ($lead_stage as $index => $lead_stage_data) {
                $lead_stage = Lead::where('created_by', creatorId())->where('workspace_id', '=', $currentWorkspace)->where('stage_id', $lead_stage_data->id)->orderBy('order', 'ASC')->count();
                $leadStageData[$index]['name'] = $lead_stage_data->name;
                $leadStageData[$index]['value'] = $lead_stage;
            }

            $data = [
                'pipeline' => $pipeline,
                'chartData' => $leadStageData,
            ];

            return response()->json(['status' => 1, 'data'  => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Something went wrong!!!']);
        }
    }

    public function getWorkspaceUsers(Request $request)
    {
        try {

            $validator = \Validator::make(
                $request->all(),
                [
                    'workspace_id'  => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json(['status' => 0, 'message' => $messages->first()], 403);
            }

            $objUser          = Auth::user();
            $currentWorkspace = $request->workspace_id;

            $users  = User::where('created_by', creatorId())
                ->emp()
                ->where('workspace_id', $currentWorkspace)
                ->orWhere('id', Auth::user()->id)
                // ->limit(10)->offset((($request->page??1)-1)*10)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ];
                });

            return response()->json([
                'status' => 1,
                'data'   => $users,

            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'something went wrong!!!']);
        }
    }
}
