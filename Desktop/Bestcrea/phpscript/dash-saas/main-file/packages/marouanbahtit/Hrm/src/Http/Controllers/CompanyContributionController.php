<?php

namespace Workdo\Hrm\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\CompanyContribution;
use Workdo\Hrm\Entities\Employee;
use Workdo\Hrm\Events\CreateCompanyContribution;
use Workdo\Hrm\Events\DestroyCompanyContribution;
use Workdo\Hrm\Events\UpdateCompanyContribution;

class CompanyContributionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function companycontributionCreate($id)
    {
        if (Auth::user()->isAbleTo('company contribution create')) {
            $employee = Employee::find($id);
            $companycontributiontype = CompanyContribution::$companycontributiontype;
            return view('hrm::companycontribution.create', compact('employee', 'companycontributiontype'));
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
        return redirect()->back();
        return view('hrm::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('company contribution create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'title' => 'required',
                    'type' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $companycontribution              = new CompanyContribution();
            $companycontribution->employee_id = $request->employee_id;
            $companycontribution->title       = $request->title;
            $companycontribution->type        = $request->type;
            $companycontribution->amount      = $request->amount;
            $companycontribution->workspace   = getActiveWorkSpace();
            $companycontribution->created_by  = creatorId();
            $companycontribution->save();

            event(new CreateCompanyContribution($request, $companycontribution));

            return redirect()->back()->with('success', __('The company contribution has been created successfully.'));
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
    public function edit(CompanyContribution $companycontribution)
    {
        if (Auth::user()->isAbleTo('company contribution edit')) {
            if ($companycontribution->created_by == creatorId() && $companycontribution->workspace == getActiveWorkSpace()) {
                $companycontributiontype = CompanyContribution::$companycontributiontype;
                return view('hrm::companycontribution.edit', compact('companycontribution', 'companycontributiontype'));
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
    public function update(Request $request, CompanyContribution $companycontribution)
    {
        if (Auth::user()->isAbleTo('company contribution edit')) {
            if ($companycontribution->created_by == creatorId() && $companycontribution->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [

                        'title' => 'required',
                        'type' => 'required',
                        'amount' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $companycontribution->title  = $request->title;
                $companycontribution->type   = $request->type;
                $companycontribution->amount = $request->amount;
                $companycontribution->save();

                event(new UpdateCompanyContribution($request, $companycontribution));

                return redirect()->back()->with('success', __('The company contribution details are updated successfully.'));
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
    public function destroy(CompanyContribution $companycontribution)
    {
        if (Auth::user()->isAbleTo('other payment delete')) {
            if ($companycontribution->created_by == creatorId() && $companycontribution->workspace == getActiveWorkSpace()) {
                
                event(new DestroyCompanyContribution($companycontribution));

                $companycontribution->delete();

                return redirect()->back()->with('success', __('The company contribution has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
