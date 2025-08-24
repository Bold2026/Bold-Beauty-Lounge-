<?php

namespace Workdo\Hrm\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Employee;
use Workdo\Hrm\Entities\Overtime;
use Workdo\Hrm\Events\CreateOvertime;
use Workdo\Hrm\Events\DestroyOvertime;
use Workdo\Hrm\Events\UpdateOvertime;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function overtimeCreate($id)
    {
        if (Auth::user()->isAbleTo('overtime create')) {
            $employee = Employee::find($id);
            $overtime = Overtime::$Overtimetype;
            $status = Overtime::$status;

            return view('hrm::overtime.create', compact('employee', 'overtime', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function index()
    {
        return redirect()->back();
        return view('hrm::index');
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('hrm::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('overtime create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'title' => 'required',
                    'number_of_days' => 'required',
                    'hours' => 'required',
                    'rate' => 'required',
                    'start_date' => 'required|before_or_equal:end_date',
                    'end_date' => 'required|after_or_equal:start_date',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $overtime                 = new Overtime();
            $overtime->employee_id    = $request->employee_id;
            $overtime->title          = $request->title;
            $overtime->number_of_days = $request->number_of_days;
            $overtime->hours          = $request->hours;
            $overtime->rate           = $request->rate;
            $overtime->start_date     = $request->start_date;
            $overtime->end_date       = $request->end_date;
            $overtime->status         = $request->status;
            $overtime->workspace      = getActiveWorkSpace();
            $overtime->created_by     = creatorId();
            $overtime->save();

            event(new CreateOvertime($request, $overtime));

            return redirect()->back()->with('success', __('The overtime has been created successfully.'));
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
    public function edit(Overtime $overtime)
    {
        if (Auth::user()->isAbleTo('overtime edit')) {
            if ($overtime->created_by == creatorId() && $overtime->workspace == getActiveWorkSpace()) {
                $status = Overtime::$status;
                return view('hrm::overtime.edit', compact('overtime', 'status'));
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
    public function update(Request $request, Overtime $overtime)
    {
        if (Auth::user()->isAbleTo('overtime edit')) {
            if ($overtime->created_by == creatorId() && $overtime->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'title' => 'required',
                        'number_of_days' => 'required',
                        'hours' => 'required',
                        'rate' => 'required',
                        'start_date' => 'required|before_or_equal:end_date',
                        'end_date' => 'required|after_or_equal:start_date',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $overtime->title          = $request->title;
                $overtime->number_of_days = $request->number_of_days;
                $overtime->hours          = $request->hours;
                $overtime->rate           = $request->rate;
                $overtime->start_date     = $request->start_date;
                $overtime->end_date       = $request->end_date;
                $overtime->status         = $request->status;
                $overtime->save();

                event(new UpdateOvertime($request, $overtime));

                return redirect()->back()->with('success', __('The overtime details are updated successfully.'));
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
    public function destroy(Overtime $overtime)
    {
        if (Auth::user()->isAbleTo('overtime delete')) {
            if ($overtime->created_by == creatorId() && $overtime->workspace == getActiveWorkSpace()) {
                event(new DestroyOvertime($overtime));

                $overtime->delete();

                return redirect()->back()->with('success', __('The overtime has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
