<?php

namespace Workdo\Hrm\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Allowance;
use Workdo\Hrm\Entities\AllowanceOption;
use Workdo\Hrm\Events\CreateAllowanceOption;
use Workdo\Hrm\Events\DestroyAllowanceOption;
use Workdo\Hrm\Events\UpdateAllowanceOption;

class AllowanceOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('allowanceoption manage')) {
            $allowanceoptions = AllowanceOption::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('hrm::allowanceoption.index', compact('allowanceoptions'));
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
        if (Auth::user()->isAbleTo('allowanceoption create')) {
            return view('hrm::allowanceoption.create');
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
        if (Auth::user()->isAbleTo('allowanceoption create')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:30',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $allowanceoption             = new AllowanceOption();
            $allowanceoption->name       = $request->name;
            $allowanceoption->workspace  = getActiveWorkSpace();
            $allowanceoption->created_by = creatorId();
            $allowanceoption->save();

            event(new CreateAllowanceOption($request, $allowanceoption));

            return redirect()->route('allowanceoption.index')->with('success', __('The allowance option has been created successfully.'));
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
    public function edit(AllowanceOption $allowanceoption)
    {
        if (Auth::user()->isAbleTo('allowanceoption edit')) {
            if ($allowanceoption->created_by == creatorId() && $allowanceoption->workspace == getActiveWorkSpace()) {
                return view('hrm::allowanceoption.edit', compact('allowanceoption'));
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
    public function update(Request $request, AllowanceOption $allowanceoption)
    {
        if (Auth::user()->isAbleTo('allowanceoption edit')) {
            if ($allowanceoption->created_by == creatorId() && $allowanceoption->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|max:30',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $allowanceoption->name = $request->name;
                $allowanceoption->save();

                event(new UpdateAllowanceOption($request, $allowanceoption));

                return redirect()->route('allowanceoption.index')->with('success', __('The allowance option details are updated successfully.'));
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
    public function destroy(AllowanceOption $allowanceoption)
    {
        if (Auth::user()->isAbleTo('allowanceoption delete')) {
            if ($allowanceoption->created_by == creatorId() && $allowanceoption->workspace == getActiveWorkSpace()) {
                $allowance     = Allowance::where('allowance_option', $allowanceoption->id)->where('workspace', getActiveWorkSpace())->get();
                if (count($allowance) == 0) {
                    event(new DestroyAllowanceOption($allowanceoption));

                    $allowanceoption->delete();
                } else {
                    return redirect()->route('allowanceoption.index')->with('error', __('This Allowance Option has Allowance. Please remove the Allowance from this Allowance option.'));
                }

                return redirect()->route('allowanceoption.index')->with('success', __('The allowance option has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
