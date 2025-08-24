<?php

namespace Workdo\Hrm\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\DataTables\EmpTerminationDataTable;
use Workdo\Hrm\Entities\Employee;
use Workdo\Hrm\Entities\Termination;
use Workdo\Hrm\Entities\TerminationType;
use Workdo\Hrm\Events\CreateTermination;
use Workdo\Hrm\Events\DestroyTermination;
use Workdo\Hrm\Events\UpdateTermination;

class TerminationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(EmpTerminationDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('termination manage')) {

            return $dataTable->render('hrm::termination.index');
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
        if (Auth::user()->isAbleTo('termination create')) {
            $employees   = User::where('workspace_id', getActiveWorkSpace())->where('created_by', '=', creatorId())->emp()->get()->pluck('name', 'id');

            $terminationtypes      = TerminationType::where('workspace', getActiveWorkSpace())->where('created_by', '=', creatorId())->get()->pluck('name', 'id');
            return view('hrm::termination.create', compact('employees', 'terminationtypes'));
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
        if (Auth::user()->isAbleTo('termination create')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'termination_type' => 'required',
                    'notice_date' => 'required|after:yesterday',
                    'termination_date' => 'required|after_or_equal:notice_date',
                    'description' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $termination                   = new Termination();
            $employee = Employee::where('user_id', '=', $request->employee_id)->first();
            if (!empty($employee)) {
                $termination->employee_id = $employee->id;
            }

            $termination->user_id          = $request->employee_id;
            $termination->termination_type = $request->termination_type;
            $termination->notice_date      = $request->notice_date;
            $termination->termination_date = $request->termination_date;
            $termination->description      = $request->description;
            $termination->workspace        = getActiveWorkSpace();
            $termination->created_by       = creatorId();
            $termination->save();

            event(new CreateTermination($request, $termination));
            $company_settings = getCompanyAllSetting();
            if (!empty($company_settings['Employee Termination']) && $company_settings['Employee Termination']  == true) {
                $User        = User::where('id', $termination->user_id)->where('workspace_id', '=',  getActiveWorkSpace())->first();
                $terminationtypes = TerminationType::where('id', '=', $request->termination_type)->where('workspace', getActiveWorkSpace())->first();

                $uArr = [
                    'employee_termination_name' => $User->name,
                    'notice_date' => $request->notice_date,
                    'termination_date' => $request->termination_date,
                    'termination_type' => !empty($terminationtypes) ? $terminationtypes->name : '',
                ];
                try {

                    $resp = EmailTemplate::sendEmailTemplate('Employee Termination', [$User->email], $uArr);
                } catch (\Exception $e) {
                    $resp['error'] = $e->getMessage();
                }
                return redirect()->route('termination.index')->with('success', __('The termination has been created successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->route('termination.index')->with('success', __('The termination has been created successfully.'));
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
    public function edit(Termination $termination)
    {
        if (\Auth::user()->isAbleTo('termination edit')) {
            if ($termination->created_by == creatorId() && $termination->workspace == getActiveWorkSpace()) {
                $employees   = User::where('workspace_id', getActiveWorkSpace())->where('created_by', '=', creatorId())->emp()->get()->pluck('name', 'id');
                $terminationtypes      = TerminationType::where('workspace', getActiveWorkSpace())->where('created_by', '=', creatorId())->get()->pluck('name', 'id');

                return view('hrm::termination.edit', compact('termination', 'employees', 'terminationtypes'));
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
    public function update(Request $request, Termination $termination)
    {
        if (Auth::user()->isAbleTo('termination edit')) {
            if ($termination->created_by == creatorId() && $termination->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id' => 'required',
                        'termination_type' => 'required',
                        'notice_date' => 'required|after:yesterday',
                        'termination_date' => 'required|after_or_equal:notice_date',
                        'description' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $employee = Employee::where('user_id', '=', $request->employee_id)->first();
                if (!empty($employee)) {
                    $termination->employee_id = $employee->id;
                }
                $termination->user_id          = $request->employee_id;
                $termination->termination_type = $request->termination_type;
                $termination->notice_date      = $request->notice_date;
                $termination->termination_date = $request->termination_date;
                $termination->description      = $request->description;
                $termination->save();

                event(new UpdateTermination($request, $termination));

                return redirect()->route('termination.index')->with('success', __('The termination details are updated successfully.'));
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
    public function destroy(Termination $termination)
    {
        if (Auth::user()->isAbleTo('termination delete')) {
            if ($termination->created_by == creatorId() && $termination->workspace == getActiveWorkSpace()) {

                event(new DestroyTermination($termination));

                $termination->delete();

                return redirect()->route('termination.index')->with('success', __('The termination has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function description($id)
    {
        if (Auth::user()->isAbleTo('termination description')) {
            $termination = Termination::find($id);
            return view('hrm::termination.description', compact('termination'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
}
