<?php

namespace Workdo\Lead\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Lead\Entities\Deal;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\Source;
use Workdo\Lead\Events\CreateSource;
use Workdo\Lead\Events\DestroySource;
use Workdo\Lead\Events\UpdateSource;
use Illuminate\Support\Facades\Auth;

class SourceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('source manage')) {
            $sources = Source::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get();

            return view('lead::sources.index')->with('sources', $sources);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('source create')) {
            return view('lead::sources.create');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('source create')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:30',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('sources.index')->with('error', $messages->first());
            }

            $source             = new Source();
            $source->name       = $request->name;
            $source->workspace_id  = getActiveWorkSpace();
            $source->created_by = creatorId();
            $source->save();

            event(new CreateSource($request, $source));

            return redirect()->route('sources.index')->with('success', __('The source has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->route('sources.index');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Source $source)
    {
        if (Auth::user()->isAbleTo('source edit')) {
            if ($source->created_by == creatorId() && $source->workspace_id  = getActiveWorkSpace()) {
                return view('lead::sources.edit', compact('source'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Source $source)
    {
        if (Auth::user()->isAbleTo('source edit')) {
            if ($source->created_by == creatorId() && $source->workspace_id  = getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|string|max:30',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('sources.index')->with('error', $messages->first());
                }

                $source->name = $request->name;
                $source->save();

                event(new UpdateSource($request, $source));

                return redirect()->route('sources.index')->with('success', __('The source is updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Source $source)
    {
        if (Auth::user()->isAbleTo('source delete')) {
            if ($source->created_by == creatorId() && $source->workspace_id  = getActiveWorkSpace()) {
                $lead = Lead::where('sources', '=', $source->id)->where('created_by', $source->created_by)->count();
                $deal = Deal::where('sources', '=', $source->id)->where('created_by', $source->created_by)->count();
                if ($lead == 0 && $deal == 0) {
                    $source->delete();

                    event(new DestroySource($source));

                    return redirect()->route('sources.index')->with('success', __('The source has been deleted.'));
                } else {
                    return redirect()->back()->with('error', __('There are some lead and deal on sources, please remove it first.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
