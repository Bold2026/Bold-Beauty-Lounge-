<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use RachidLaasri\LaravelInstaller\Helpers\DatabaseManager;
use RachidLaasri\LaravelInstaller\Helpers\InstalledFileManager;
use App\Facades\ModuleFacade as Module;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class UpdateController extends Controller
{
    use \RachidLaasri\LaravelInstaller\Helpers\MigrationsHelper;

    /**
     * Display the updater welcome page.
     *
     * @return \Illuminate\View\View
     */
    public function welcome()
    {
        return view('vendor.installer.update.welcome');
    }

    /**
     * Display the updater overview page.
     *
     * @return \Illuminate\View\View
     */
    public function overview()
    {
        // update custom code

        $ranMigrations = \DB::table('migrations')->pluck('migration');
        $modules = Module::allModules();

        $migrationFiles = collect(File::glob(database_path('migrations/*.php')))
            ->map(function ($path) {
                return File::name($path);
            });
        foreach ($modules as $key => $module) {
            // Get the module directorie in your project
            $directory = "packages/workdo/" . $module->name . "/src/Database/Migrations";

            $files = collect(File::glob("{$directory}/*.php"))
                ->map(function ($path) {
                    return File::name($path);
                });
            $migrationFiles = $migrationFiles->merge($files);
        }
        // Calculate the pending migrations by diffing the two lists
        $pendingMigrations = $migrationFiles->diff($ranMigrations);

        return view('vendor.installer.update.overview', ['numberOfUpdatesPending' => count($pendingMigrations)]);
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {
        $databaseManager = new DatabaseManager;
        $response = $databaseManager->migrateAndSeed();
        $module = Module::find('LandingPage');
        if ($module) {
            $module->enable();
            Artisan::call('package:seed LandingPage');
        }

        return redirect()->route('LaravelUpdater::final')
            ->with(['message' => $response]);
    }

    /**
     * Update installed file and display finished view.
     *
     * @param InstalledFileManager $fileManager
     * @return \Illuminate\View\View
     */
    public function finish(InstalledFileManager $fileManager)
    {
        $fileManager->update();

        return view('vendor.installer.update.finished');
    }
}
