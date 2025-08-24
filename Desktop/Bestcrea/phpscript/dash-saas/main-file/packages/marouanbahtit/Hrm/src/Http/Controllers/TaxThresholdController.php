<?php

namespace Workdo\Hrm\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\TaxThreshold;
use Workdo\Hrm\Events\CreateTaxThreshold;
use Workdo\Hrm\Events\DestroyTaxThreshold;
use Workdo\Hrm\Events\UpdateTaxThreshold;

class TaxThresholdController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('tax threshold manage')) {
            $taxthresholds = TaxThreshold::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('hrm::taxthreshold.index', compact('taxthresholds'));
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
        if (Auth::user()->isAbleTo('tax threshold create')) {
            return view('hrm::taxthreshold.create');
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
        if (Auth::user()->isAbleTo('tax threshold create')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'description' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $taxthreshold              = new TaxThreshold();
            $taxthreshold->description = $request->description;
            $taxthreshold->amount      = $request->amount;
            $taxthreshold->workspace   = getActiveWorkSpace();
            $taxthreshold->created_by  = creatorId();
            $taxthreshold->save();

            event(new CreateTaxThreshold($request, $taxthreshold));

            return redirect()->route('taxthreshold.index')->with('success', __('The tax threshold has been created successfully.'));
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
    public function edit(TaxThreshold $taxthreshold)
    {
        if (Auth::user()->isAbleTo('tax threshold edit')) {
            if ($taxthreshold->created_by == creatorId() && $taxthreshold->workspace == getActiveWorkSpace()) {
                return view('hrm::taxthreshold.edit', compact('taxthreshold'));
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
    public function update(Request $request, TaxThreshold $taxthreshold)
    {
        if (Auth::user()->isAbleTo('tax threshold edit')) {
            if ($taxthreshold->created_by == creatorId() && $taxthreshold->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'description' => 'required',
                        'amount' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $taxthreshold->description = $request->description;
                $taxthreshold->amount      = $request->amount;
                $taxthreshold->save();

                event(new UpdateTaxThreshold($request, $taxthreshold));

                return redirect()->route('taxthreshold.index')->with('success', __('The tax threshold details are updated successfully.'));
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
    public function destroy(TaxThreshold $taxthreshold)
    {
        if (Auth::user()->isAbleTo('tax threshold delete')) {
            if ($taxthreshold->created_by == creatorId() && $taxthreshold->workspace == getActiveWorkSpace()) {

                event(new DestroyTaxThreshold($taxthreshold));

                $taxthreshold->delete();

                return redirect()->route('taxthreshold.index')->with('success', __('The tax threshold has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
