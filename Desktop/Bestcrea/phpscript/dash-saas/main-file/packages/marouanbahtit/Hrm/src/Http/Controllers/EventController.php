<?php

namespace Workdo\Hrm\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Branch;
use Workdo\Hrm\Entities\Department;
use Workdo\Hrm\Entities\Employee;
use Workdo\Hrm\Entities\Event;
use Workdo\Hrm\Entities\EventEmployee;
use Workdo\Hrm\Events\CreateEvent;
use Workdo\Hrm\Events\DestroyEvent;
use Workdo\Hrm\Events\UpdateEvent;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $creatorId = creatorId();
            $workspace = getActiveWorkSpace();

            $currentEmployee = Employee::where('user_id', Auth::id())->first();
            $branchId = $currentEmployee->branch_id ?? 0;
            $departmentId = $currentEmployee->department_id ?? 0;

            $employees = Employee::where('created_by', $creatorId)
                ->where('workspace', $workspace)
                ->where('branch_id', $branchId)
                ->where('department_id', $departmentId)
                ->get();

            $events = Event::where('created_by', $creatorId)
                ->where('workspace', $workspace)
                ->where('branch_id', $branchId)
                ->orWhere('branch_id', '0')
                ->where('department_id', 'like', '%"' . $departmentId . '"%')
                ->get();

            $today_date = date('m');
            $current_month_event = Event::select('id', 'start_date', 'end_date', 'title', 'created_at', 'color')
                ->where('workspace', $workspace)
                ->where('branch_id', $branchId)
                ->orWhere('branch_id', '0')
                ->where('department_id', 'like', '%"' . $departmentId . '"%')
                ->whereNotNull(['start_date', 'end_date'])
                ->whereMonth('start_date', $today_date)
                ->whereMonth('end_date', $today_date)
                ->get();
            $arrEvents = [];
            foreach ($events as $event) {
                $arr['id']    = $event['id'];
                $arr['title'] = $event['title'];
                $arr['start'] = $event['start_date'];
                $arr['end']       = date('Y-m-d', strtotime($event['end_date'] . ' +1 day'));
                $arr['className'] = $event['color'];
                if (Auth::user()->isAbleTo('event edit')) {
                    $arr['url']             = route('event.edit', $event['id']);
                } else {
                    $arr['url']             = route('event.show', $event['id']);
                }

                $arrEvents[] = $arr;
            }
            $arrEvents =  json_encode($arrEvents);

            return view('hrm::event.index', compact('arrEvents', 'employees', 'current_month_event', 'events'));
        } elseif (Auth::user()->isAbleTo('event manage')) {
            $employees = Employee::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get();

            $events    = Event::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get();

            $today_date = date('m');
            $current_month_event = Event::select('id', 'start_date', 'end_date', 'title', 'created_at', 'color')->where('workspace', getActiveWorkSpace())->whereNotNull(['start_date', 'end_date'])->whereMonth('start_date', $today_date)->whereMonth('end_date', $today_date)->get();
            $arrEvents = [];
            foreach ($events as $event) {

                $arr['id']    = $event['id'];
                $arr['title'] = $event['title'];
                $arr['start'] = $event['start_date'];
                $arr['end']       = date('Y-m-d', strtotime($event['end_date'] . ' +1 day'));
                $arr['className'] = $event['color'];
                if (Auth::user()->isAbleTo('event edit')) {
                    $arr['url']             = route('event.edit', $event['id']);
                }
                else{
                    $arr['url']             = route('event.show', $event['id']);
                }

                $arrEvents[] = $arr;
            }
            $arrEvents =  json_encode($arrEvents);

            return view('hrm::event.index', compact('arrEvents', 'employees', 'current_month_event', 'events'));
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
        if (Auth::user()->isAbleTo('event create')) {
            $employees   = Employee::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $branch      = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get();
            $departments = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('hrm::event.create', compact('employees', 'branch', 'departments'));
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
        if (Auth::user()->isAbleTo('event create')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'department_id' => 'required',
                    'employee_id' => 'required',
                    'title' => 'required',
                    'start_date' => 'required|after:yesterday',
                    'end_date' => 'required|after_or_equal:start_date',
                    'color' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $event                = new Event();
            $event->branch_id     = $request->branch_id;
            $event->department_id = json_encode($request->department_id);
            $event->employee_id   = json_encode($request->employee_id);
            $event->title         = $request->title;
            $event->start_date    = $request->start_date;
            $event->end_date      = $request->end_date;
            $event->color         = $request->color;
            $event->description   = $request->description;
            $event->workspace     = getActiveWorkSpace();
            $event->created_by    = creatorId();
            $event->save();

            if (in_array('0', $request->employee_id)) {
                $departmentEmployee = Employee::whereIn('department_id', $request->department_id)->get()->pluck('id');
                $departmentEmployee = $departmentEmployee;
            } else {
                $departmentEmployee = $request->employee_id;
            }
            foreach ($departmentEmployee as $employee) {
                $eventEmployee              = new EventEmployee();
                $eventEmployee->event_id    = $event->id;
                $eventEmployee->employee_id = $employee;
                $eventEmployee->created_by  = creatorId();
                $eventEmployee->save();
            }

            event(new CreateEvent($request, $event));

            return redirect()->route('event.index')->with('success', __('The event has been created successfully.'));
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
        $event = Event::find($id);
        return view('hrm::event.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($event)
    {
        if (Auth::user()->isAbleTo('event edit')) {
            $employees = Employee::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $event = Event::find($event);
            return view('hrm::event.edit', compact('event', 'employees'));
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
    public function update(Request $request, Event $event)
    {
        if (Auth::user()->isAbleTo('event edit')) {
            if ($event->created_by == creatorId() && $event->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'title' => 'required',
                        'start_date' => 'required|date',
                        'end_date' => 'required|after_or_equal:start_date',
                        'color' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $event->title       = $request->title;
                $event->start_date  = $request->start_date;
                $event->end_date    = $request->end_date;
                $event->color       = $request->color;
                $event->description = $request->description;
                $event->save();
                event(new UpdateEvent($request, $event));
                return redirect()->back()->with('success', __('The event details are updated successfully.'));
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
    public function destroy(Event $event)
    {
        if (Auth::user()->isAbleTo('event delete')) {
            if ($event->created_by == creatorId() && $event->workspace == getActiveWorkSpace()) {
                event(new DestroyEvent($event));
                $event->delete();

                return redirect()->route('event.index')->with('success', __('The event has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getdepartment(Request $request)
    {

        if ($request->branch_id == 0) {
            $departments = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->toArray();
        } else {
            $departments = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($departments);
    }

    public function getemployee(Request $request)
    {
        $employees = [];
        if (isset($request->department_id)) {
            $employees = [];
            if (isset($request->department_id)) {
                if (in_array('0', $request->department_id)) {
                    $employees = Employee::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id')->toArray();
                } else {
                    $employees = Employee::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->whereIn('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();
                }
            }
        }
        return response()->json($employees);
    }

    public function showData($id)
    {
        $employees = Employee::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
        $event = Event::find($id);

        return view('event.edit', compact('event', 'employees'));
    }
}
