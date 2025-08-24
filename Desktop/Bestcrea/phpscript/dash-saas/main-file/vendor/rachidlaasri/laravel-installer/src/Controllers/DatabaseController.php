<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use RachidLaasri\LaravelInstaller\Helpers\DatabaseManager;
use App\Facades\ModuleFacade as Module;
use App\Models\AddOn;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {
        $response = $this->databaseManager->migrateAndSeed();
        $module_json =  Module::allModules();
        foreach ($module_json as $module) {
            $addon = AddOn::where('module',$module->name)->first();
            if(empty($addon))
            {
                $addon = new AddOn;
                $addon->module = $module->name;
                $addon->name = $module->alias;
                $addon->monthly_price = $module->monthly_price ?? 0;
                $addon->yearly_price = $module->yearly_price ?? 0;
                $addon->is_enable = 1;
                $addon->package_name = $module->package_name;
                $addon->save();
            }
        }

        if (count($module_json) > 0) {
            return redirect()->route('LaravelInstaller::default_module', ['module' => 'LandingPage']);
        } else {
            return redirect()->route('LaravelInstaller::final')
                ->with(['message' => $response]);
        }
    }
}
