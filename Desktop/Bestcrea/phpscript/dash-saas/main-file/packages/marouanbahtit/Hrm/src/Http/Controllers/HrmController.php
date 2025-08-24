<?php

namespace Workdo\Hrm\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Models\WorkSpace;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Announcement;
use Workdo\Hrm\Entities\Attendance;
use Workdo\Hrm\Entities\Employee;
use Workdo\Hrm\Entities\Event;
use Workdo\Hrm\Entities\ExperienceCertificate;
use Workdo\Hrm\Entities\IpRestrict;
use Workdo\Hrm\Entities\JoiningLetter;
use Workdo\Hrm\Entities\Leave;
use Workdo\Hrm\Entities\NOC;

class HrmController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function __construct()
    {
        if (module_is_active('GoogleAuthentication')) {
            $this->middleware('2fa');
        }
    }

    public function index(Request $request)
    {
        if (Auth::check()) {
            if (Auth::user()->isAbleTo('hrm dashboard manage')) {
                $user = Auth::user();
                $events = [];
                $ActiveWorkspaceName = WorkSpace::where('id', getActiveWorkSpace())->first();
                $holidays = \Workdo\Hrm\Entities\Holiday::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace());
                if (!empty($request->date)) {
                    $date_range = explode(' to ', $request->date);
                    $holidays->where('start_date', '>=', $date_range[0]);
                    $holidays->where('end_date', '<=', $date_range[1]);
                }
                $holidays = $holidays->get();
                foreach ($holidays as $key => $holiday) {
                    $data = [
                        'title' => $holiday->occasion,
                        'start' => $holiday->start_date,
                        'end' => date('Y-m-d', strtotime($holiday->end_date . ' +1 day')),
                        'className' => 'event-danger'
                    ];
                    array_push($events, $data);
                }
                $hrm_events = \Workdo\Hrm\Entities\Event::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace());
                if (!empty($request->date)) {
                    $date_range = explode(' to ', $request->date);
                    $hrm_events->where('start_date', '>=', $date_range[0]);
                    $hrm_events->where('end_date', '<=', $date_range[1]);
                }
                $hrm_events = $hrm_events->get();
                foreach ($hrm_events as $key => $hrm_event) {
                    $data = [
                        'id'    => $hrm_event->id,
                        'title' => $hrm_event->title,
                        'start' => $hrm_event->start_date,
                        'end' => date('Y-m-d', strtotime($hrm_event->end_date . ' +1 day')),
                        'className' => $hrm_event->color
                    ];
                    array_push($events, $data);
                }

                if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                    $emp = Employee::where('user_id', '=', $user->id)->first();
                    if (!empty($emp)) {
                        $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')->whereDate('announcements.end_date', '>=', Carbon::today())->where('announcement_employees.employee_id', '=', $emp->id)->orWhere(
                            function ($q) {
                                $q->where('announcements.department_id', 0)->where('announcements.employee_id', 0);
                            }
                        )->get();
                    } else {
                        $announcements = [];
                    }

                    $date               = date("Y-m-d");
                    $time               = date("H:i:s");
                    $employeeAttendance = Attendance::orderBy('id', 'desc')->where('employee_id', '=', Auth::user()->id)->where('date', '=', $date)->first();
                    $company_settings = getCompanyAllSetting();
                    $officeTime['startTime'] = !empty($company_settings['company_start_time']) ? $company_settings['company_start_time'] : '09:00';
                    $officeTime['endTime']  = !empty($company_settings['company_end_time']) ? $company_settings['company_end_time'] : '18:00';


                    $Totalleaves   = Leave::where('employee_id' ,Auth::user()->id)->where('workspace',getActiveWorkSpace())->where('created_by',creatorId())->get()->count();

                    return view('hrm::dashboard.dashboard', compact('announcements', 'events', 'employeeAttendance', 'officeTime','ActiveWorkspaceName'));
                } else {
                    $Totalleaves   = Leave::where('workspace',getActiveWorkSpace())->where('created_by',creatorId())->get()->count();
                    $Totalevent  = Event::where('workspace',getActiveWorkSpace())->where('created_by',creatorId())->get()->count();
                    $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->whereDate('end_date', '>=', Carbon::today())->where('workspace', getActiveWorkSpace())->get();

                    $emp           = User::where('created_by', '=', Auth::user()->id)->emp()->where('workspace_id', getActiveWorkSpace())->get()->toArray();
                    $countEmployee = count($emp);
                    $emp_id = array_column($emp, 'id');

                    $user      = User::whereNotIn('id', $emp_id)->where('created_by', '=', Auth::user()->id)->where('workspace_id', getActiveWorkSpace())->get();
                    $countUser = count($user);

                    $currentDate = date('Y-m-d');

                    $notClockIn    = Attendance::where('date', '=', $currentDate)->get()->pluck('employee_id');

                    $notClockIns = User::where('created_by', '=', Auth::user()->id)->where('workspace_id', getActiveWorkSpace())->whereNotIn('id', $notClockIn)->emp()->get();

                    $company_settings = getCompanyAllSetting();
                    $officeTime['startTime'] = !empty($company_settings['company_start_time']) ? $company_settings['company_start_time'] : '09:00';
                    $officeTime['endTime']  = !empty($company_settings['company_end_time']) ? $company_settings['company_end_time'] : '18:00';


                    return view('hrm::dashboard.dashboard', compact('announcements', 'countEmployee', 'events', 'countUser', 'notClockIns','Totalleaves','Totalevent','ActiveWorkspaceName','officeTime'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
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
        //
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
    public function edit($id)
    {
        return view('hrm::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function joiningletterupdate($lang, Request $request)
    {
        $user = JoiningLetter::updateOrCreate(['lang' =>   $lang, 'created_by' =>  Auth::user()->id], ['content' => $request->joining_content, 'workspace' => getActiveWorkSpace()]);

        return redirect()->back()->with('success', __('The joing letter details are saved successfully.'));
    }

    public function experienceCertificateupdate($lang, Request $request)
    {
        $user = ExperienceCertificate::updateOrCreate(['lang' =>   $lang, 'created_by' =>  Auth::user()->id], ['content' => $request->experience_content, 'workspace' => getActiveWorkSpace()]);

        return redirect()->back()->with('success', __('The experience certificate details are saved successfully.'));
    }

    public function NOCupdate($lang, Request $request)
    {
        $user = NOC::updateOrCreate(['lang' =>   $lang, 'created_by' =>  Auth::user()->id], ['content' => $request->noc_content, 'workspace' => getActiveWorkSpace()]);

        return redirect()->back()->with('success', __('The NOC details are saved successfully.'));
    }

    public function setting(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'employee_prefix' => 'required',
                'company_start_time' => 'required',
                'company_end_time' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        } else {
            $post = $request->all();
            if (!isset($request->ip_restrict)) {
                $post['ip_restrict'] = 'off';
            }
            unset($post['_token']);
            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'workspace' => getActiveWorkSpace(),
                    'created_by' => creatorId(),
                ];
                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
            // Settings Cache forget
            comapnySettingCacheForget();
            return redirect()->back()->with('success', 'The HRM setting are saved successfully.');
        }
    }

    public function createIp()
    {
        if (Auth::user()->isAbleTo('ip restrict create')) {

            return view('hrm::restrict_ip.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function storeIp(Request $request)
    {
    }

    public function editIp($id)
    {
        if (Auth::user()->isAbleTo('ip restrict edit')) {

            $ip = IpRestrict::find($id);

            return view('hrm::restrict_ip.edit', compact('ip'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updateIp(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('ip restrict edit')) {

            if (Auth::user()->type == 'company') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'ip' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $ip     = IpRestrict::find($id);
                $ip->ip = $request->ip;
                $ip->save();

                return redirect()->back()->with('success', __('The IP details are updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroyIp($id)
    {
        if (Auth::user()->isAbleTo('ip restrict delete')) {

            if (Auth::user()->type == 'company') {
                $ip = IpRestrict::find($id);
                $ip->delete();

                return redirect()->back()->with('success', __('The IP has been deleted.'));
            } else {

                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function joiningletterindex(Request $request){
        if (Auth::user()->isAbleTo('letter joining manage')) {

            if (request()->get('joininglangs')) {
                $joininglang = request()->get('joininglangs');
            } else {
                $joininglang = "en";
            }


            //joining letter
            $Joiningletter = JoiningLetter::all();
            $currjoiningletterLang = JoiningLetter::where('created_by',  Auth::user()->id)->where('lang', $joininglang)->where('workspace', getActiveWorkSpace())->first();

            return view('hrm::joiningletter.index',compact('currjoiningletterLang','Joiningletter','joininglang'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function experiencecertificateindex(Request $request){
        if (Auth::user()->isAbleTo('letter certificate manage')) {
            if (request()->get('explangs')) {
                $explang = request()->get('explangs');
            } else {
                $explang = "en";
            }

            //Experience Certificate
            $experience_certificate = ExperienceCertificate::all();
            $curr_exp_cetificate_Lang = ExperienceCertificate::where('created_by',  Auth::user()->id)->where('lang', $explang)->where('workspace', getActiveWorkSpace())->first();

            return view('hrm::experiencecertificate.index',compact('experience_certificate','curr_exp_cetificate_Lang','explang'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

      public function hrmnocindex(Request $request){
        if (Auth::user()->isAbleTo('letter noc manage')) {
            if (request()->get('noclangs')) {
                $noclang = request()->get('noclangs');
            } else {
                $noclang = "en";
            }

            //NOC
            $noc_certificate = NOC::all();
            $currnocLang = NOC::where('created_by',  Auth::user()->id)->where('lang', $noclang)->where('workspace', getActiveWorkSpace())->first();
            $ips = IpRestrict::where('created_by', Auth::user()->id)->where('workspace', getActiveWorkSpace())->get();

            return view('hrm::hrmnoc.index',compact('noc_certificate','currnocLang','noclang'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
      }
}
