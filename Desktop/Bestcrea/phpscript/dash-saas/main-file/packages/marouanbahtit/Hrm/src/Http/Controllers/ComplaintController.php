<?php

namespace Workdo\Hrm\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\DataTables\EmpComplaintDataTable;
use Workdo\Hrm\Entities\Complaint;
use Workdo\Hrm\Events\CreateComplaint;
use Workdo\Hrm\Events\DestroyComplaint;
use Workdo\Hrm\Events\UpdateComplaint;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(EmpComplaintDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('complaint manage')) {
            
            return $dataTable->render('hrm::complaint.index');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('complaint create')) {
            $employees   = User::where('workspace_id', getActiveWorkSpace())->where('created_by', '=', creatorId())->emp()->get()->pluck('name', 'id');

            return view('hrm::complaint.create', compact('employees'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('complaint create')) {
            if (in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'complaint_from' => 'required',
                    ]
                );
            }

            $validator = \Validator::make(
                $request->all(),
                [
                    'complaint_against' => 'required',
                    'title' => 'required',
                    'complaint_date' => 'required|after:yesterday',
                    'description' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $complaint = new Complaint();
            if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                $complaint->complaint_from = Auth::user()->id;
            } else {
                $complaint->complaint_from = $request->complaint_from;
            }
            $complaint->complaint_against = $request->complaint_against;
            $complaint->title             = $request->title;
            $complaint->complaint_date    = $request->complaint_date;
            $complaint->description       = $request->description;
            $complaint->workspace         = getActiveWorkSpace();
            $complaint->created_by        = creatorId();
            $complaint->save();

            event(new CreateComplaint($request, $complaint));
            $company_settings = getCompanyAllSetting();

            if (!empty($company_settings['Employee Complaints']) && $company_settings['Employee Complaints']  == true) {

                $User           = User::where('id', $complaint->complaint_against)->where('workspace_id', '=',  getActiveWorkSpace())->first();


                $uArr = [
                    'employee_complaints_name' => $User->name,
                    'complaints_description' => $request->description,
                ];
                try {

                    $resp = EmailTemplate::sendEmailTemplate('Employee Complaints', [$User->email], $uArr);
                } catch (\Exception $e) {
                    $resp['error'] = $e->getMessage();
                }
                return redirect()->route('complaint.index')->with('success', __('The complaint has been created successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->route('complaint.index')->with('success', __('The complaint has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->back();
        return view('hrm::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Complaint $complaint)
    {
        if (Auth::user()->isAbleTo('complaint edit')) {
            if ($complaint->created_by == creatorId() && $complaint->workspace == getActiveWorkSpace()) {
                $employees   = User::where('workspace_id', getActiveWorkSpace())->where('created_by', '=', creatorId())->emp()->get()->pluck('name', 'id');

                return view('hrm::complaint.edit', compact('complaint', 'employees'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Complaint $complaint)
    {
        if (Auth::user()->isAbleTo('complaint edit')) {
            if ($complaint->created_by == creatorId() && $complaint->workspace == getActiveWorkSpace()) {
                if (in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'complaint_from' => 'required',
                        ]
                    );
                }

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'complaint_against' => 'required',
                        'title' => 'required',
                        'complaint_date' => 'required|after:yesterday',
                        'description' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                    $complaint->complaint_from = Auth::user()->id;
                } else {
                    $complaint->complaint_from = $request->complaint_from;
                }
                $complaint->complaint_against = $request->complaint_against;
                $complaint->title             = $request->title;
                $complaint->complaint_date    = $request->complaint_date;
                $complaint->description       = $request->description;
                $complaint->save();

                event(new UpdateComplaint($request, $complaint));

                return redirect()->route('complaint.index')->with('success', __('The complaint details are updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Complaint $complaint)
    {
        if (Auth::user()->isAbleTo('complaint delete')) {
            if ($complaint->created_by == creatorId() && $complaint->workspace == getActiveWorkSpace()) {
                event(new DestroyComplaint($complaint));

                $complaint->delete();

                return redirect()->route('complaint.index')->with('success', __('The complaint has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function description($id)
    {
        $complaints = Complaint::find($id);
        return view('hrm::complaint.description', compact('complaints'));
    }
}
