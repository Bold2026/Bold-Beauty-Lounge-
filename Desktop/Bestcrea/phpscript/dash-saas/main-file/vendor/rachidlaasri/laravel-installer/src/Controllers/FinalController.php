<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use RachidLaasri\LaravelInstaller\Events\LaravelInstallerFinished;
use RachidLaasri\LaravelInstaller\Helpers\EnvironmentManager;
use RachidLaasri\LaravelInstaller\Helpers\FinalInstallManager;
use RachidLaasri\LaravelInstaller\Helpers\InstalledFileManager;
use App\Models\AddOn;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use App\Facades\ModuleFacade as Module;

class FinalController extends Controller
{
    /**
     * Update installed file and display finished view.
     *
     * @param \RachidLaasri\LaravelInstaller\Helpers\InstalledFileManager $fileManager
     * @param \RachidLaasri\LaravelInstaller\Helpers\FinalInstallManager $finalInstall
     * @param \RachidLaasri\LaravelInstaller\Helpers\EnvironmentManager $environment
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function finish(InstalledFileManager $fileManager, FinalInstallManager $finalInstall, EnvironmentManager $environment)
    {
        $finalMessages = $finalInstall->runFinal();
        $finalStatusMessage = $fileManager->update();
        $finalEnvFile = $environment->getEnvContent();

        event(new LaravelInstallerFinished);

        return view('vendor.installer.finished', compact('finalMessages', 'finalStatusMessage', 'finalEnvFile'));
    }

    public function default_module(InstalledFileManager $fileManager, FinalInstallManager $finalInstall, EnvironmentManager $environment,$module)
    {
        Module::moduleCacheForget($module);
        $module_detail = Module::find($module);
        return view('vendor.installer.module-process')->with('module',$module)->with("module_detail",$module_detail);
    }
    public function default_module_active($module_name = null)
    {
        $module_json=Module::allModules();
        if(count($module_json)>0)
        {
            if($module_name)
            {
                $install_name = $module_name;
            }
            else
            {
                $install_name=array_key_first($module_json);
            }

            $module = Module::find($install_name);
            Artisan::call('package:seed '.$module->name);

            if($module->isEnabled())
            {
                $user = User::where('type','company')->first();
                $plans = Plan::where('custom_plan',1)->first();
                $user->assignPlan($plans->id,'Month',$module->name,0,$user->id);
            }
            $module->disable();

            $new_module_json=Module::all();

            if(count($new_module_json)>0)
            {
                $new_module=$new_module_json[0];
                return redirect()->route('LaravelInstaller::default_module', ['module' => $new_module->name]);
            }

            $modules =  Module::allModules();
            if(count($modules)>0)
            {
                foreach ($modules as $key => $module)
                {
                    $module->enable();
                }
            }
            return redirect()->route('LaravelInstaller::final')->with(['message' => ""]);
        }
        else
        {
            $modules =  Module::allModules();
            if(count($modules)>0)
            {
                foreach ($modules as $key => $module)
                {
                    $module->enable();
                }
            }

            return redirect()->route('LaravelInstaller::final')
                         ->with(['message' => ""]);
        }


    }
}
