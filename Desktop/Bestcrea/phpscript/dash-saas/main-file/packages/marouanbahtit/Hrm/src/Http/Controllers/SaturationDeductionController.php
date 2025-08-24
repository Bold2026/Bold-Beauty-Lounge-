<?php

namespace Workdo\Hrm\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\DeductionOption;
use Workdo\Hrm\Entities\Employee;
use Workdo\Hrm\Entities\SaturationDeduction;
use Workdo\Hrm\Events\CreateSaturationDeduction;
use Workdo\Hrm\Events\DestroySaturationDeduction;
use Workdo\Hrm\Events\UpdateSaturationDeduction;

class SaturationDeductionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function saturationdeductionCreate($id)
    {
        if (Auth::user()->isAbleTo('saturation deduction create')) {
            $employee = Employee::find($id);
            $deduction_options = DeductionOption::where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            $saturationdeduc = SaturationDeduction::$saturationDeductiontype;
            return view('hrm::saturationdeduction.create', compact('employee', 'deduction_options', 'saturationdeduc'));
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
        return view('hrm::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('saturation deduction create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'title' => 'required',
                    'deduction_option' => 'required',
                    'type' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $saturationdeduction                   = new SaturationDeduction;
            $saturationdeduction->employee_id      = $request->employee_id;
            $saturationdeduction->deduction_option = $request->deduction_option;
            $saturationdeduction->title            = $request->title;
            $saturationdeduction->type             = $request->type;
            $saturationdeduction->amount           = $request->amount;
            $saturationdeduction->workspace        = getActiveWorkSpace();
            $saturationdeduction->created_by       = creatorId();
            $saturationdeduction->save();

            event(new CreateSaturationDeduction($request, $saturationdeduction));

            return redirect()->back()->with('success', __('The saturation deduction has been created successfully.'));
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
    public function edit(SaturationDeduction $saturationdeduction)
    {
        if (Auth::user()->isAbleTo('saturation deduction edit')) {
            if ($saturationdeduction->created_by == creatorId() && $saturationdeduction->workspace == getActiveWorkSpace()) {
                $deduction_options = DeductionOption::where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

                $saturationdeduc = SaturationDeduction::$saturationDeductiontype;

                return view('hrm::saturationdeduction.edit', compact('saturationdeduction', 'deduction_options', 'saturationdeduc'));
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
    public function update(Request $request, SaturationDeduction $saturationdeduction)
    {
        if (Auth::user()->isAbleTo('saturation deduction edit')) {
            if ($saturationdeduction->created_by == creatorId() && $saturationdeduction->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'deduction_option' => 'required',
                        'title' => 'required',
                        'type' => 'required',
                        'amount' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $saturationdeduction->deduction_option = $request->deduction_option;
                $saturationdeduction->title            = $request->title;
                $saturationdeduction->type             = $request->type;
                $saturationdeduction->amount           = $request->amount;
                $saturationdeduction->save();

                event(new UpdateSaturationDeduction($request, $saturationdeduction));

                return redirect()->back()->with('success', __('The saturation deduction details are updated successfully.'));
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
    public function destroy(SaturationDeduction $saturationdeduction)
    {
        if (Auth::user()->isAbleTo('saturation deduction delete')) {
            if ($saturationdeduction->created_by == creatorId() && $saturationdeduction->workspace == getActiveWorkSpace()) {
                event(new DestroySaturationDeduction($saturationdeduction));

                $saturationdeduction->delete();

                return redirect()->back()->with('success', __('The saturation deduction has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
