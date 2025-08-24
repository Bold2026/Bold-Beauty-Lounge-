<?php
// This file use for handle company setting page

namespace Workdo\Hrm\Http\Controllers\Company;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Workdo\Hrm\Entities\ExperienceCertificate;
use Workdo\Hrm\Entities\IpRestrict;
use Workdo\Hrm\Entities\JoiningLetter;
use Workdo\Hrm\Entities\NOC;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($settings)
    {
        if (Auth::check() && module_is_active('Hrm')) {
            $active_module =  ActivatedModule();
            $dependency = explode(',', 'Hrm');
            if (!empty(array_intersect($dependency, $active_module))) {

                $ips = IpRestrict::where('created_by', Auth::user()->id)->where('workspace', getActiveWorkSpace())->get();
            }
            return view('hrm::company.settings.index', compact('settings', 'ips'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }
}
