<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Facades\ModuleFacade as Module;

class PackageSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:seed {packageName?}';

    public $name;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed a specific package or all packages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $packageName = $this->argument('packageName');
        if ($packageName) {
            $module = Module::find($packageName);
            if ($module->name) {
                $this->seedPackage($module->name);
            } else {
                $this->error("Module {$packageName} not found.");
            }
        } else {
            $this->seedAllPackages();
        }
    }

    protected function seedPackage($packageName)
    {
        $seederClass = $this->getSeederClass($packageName);

        if ($seederClass) {
            $this->info("Seeding {$packageName}...");
            Artisan::call('db:seed', ['--class' => $seederClass,'--force'=> true]);
            $this->info("{$packageName} Seeder Run Successfully!");
        } else {
            $this->error("Seeder for package {$packageName} not found.");
        }
    }

    protected function seedAllPackages()
    {
        $packages = $this->getAllPackages();
        foreach ($packages as $package) {
            $module = Module::find($package);
            if($module->name){
                $this->seedPackage($module->name);
            }
            else{
                $this->error("Module {$package} not found.");
            }
        }
    }

    protected function getSeederClass($packageName)
    {
        $seederClass = "MarouanBahtit\\{$packageName}\\Database\\Seeders\\{$packageName}DatabaseSeeder";
        if (class_exists($seederClass)) {
            return $seederClass;
        }

        return null;
    }

    protected function getAllPackages()
    {
        $packages = [];

        $vendorDir = base_path('packages/marouanbahtit');
        $directories = File::directories($vendorDir);

        foreach ($directories as $directory) {
            $package = basename($directory);
            $packages[] = $package;
        }

        return $packages;
    }
}
