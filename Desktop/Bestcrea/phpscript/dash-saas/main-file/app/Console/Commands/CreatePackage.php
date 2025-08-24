<?php

namespace App\Console\Commands;

use App\Models\AddOn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;

class CreatePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:make {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new package with the specified folder structure';

    /**
     * Execute the console command.
     */

    protected $files;
    public $LowerName;
    public $UpperName;
    public $packageName;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');
        $this->LowerName = strtolower($name);
        $this->UpperName = $name;
        $this->packageName = $this->camelToKebab($name);

        $packagePath = base_path("packages/marouanbahtit/{$name}");

        if (File::exists($packagePath)) {
            $this->error("Package {$name} already exists!");
            return;
        }

        File::makeDirectory($packagePath, 0755, true);

        $folders = [
            'src/Database/Migrations',
            'src/Database/Seeders',
            'src/Entities',
            'src/Events',
            'src/Http/Controllers/Company',
            'src/Http/Controllers/SuperAdmin',
            'src/Listeners',
            'src/Providers',
            'src/Resources/lang',
            'src/Resources/views/company/settings',
            'src/Resources/views/layouts',
            'src/Resources/views/marketplace',
            'src/Resources/views/super-admin/settings',
            'src/Routes',
            'src/DataTables'
        ];

        foreach ($folders as $folder) {
            File::makeDirectory("{$packagePath}/{$folder}", 0755, true);
        }

        $this->createStubFiles($packagePath);

        $this->createFiles();

        $addon = AddOn::where('module',$this->UpperName)->first();
        if(empty($addon))
        {
            $addon = new AddOn;
            $addon->module = $this->UpperName;
            $addon->name = $this->UpperName;
            $addon->monthly_price = 0;
            $addon->yearly_price = 0;
            $addon->is_enable = 0;
            $addon->package_name = $this->packageName;
            $addon->save();
        }


        $this->info("Package {$name} created successfully!");
    }

    function camelToKebab($name)
    {
        $packageName = preg_replace('/([a-z])([A-Z])/', '$1-$2', $name);
        return strtolower($packageName);
    }

    protected function getComposerJsonStub()
    {
        $name = "marouanbahtit/{$this->packageName}";
        $description = "Description for {$this->packageName} package";
        $namespace = "MarouanBahtit\\\\{$this->UpperName}\\\\Providers\\\\{$this->UpperName}ServiceProvider";

        return <<<EOT
        {
            "name": "{$name}",
            "description": "{$description}",
            "type": "library",
            "license": "MIT",
            "require": {},
            "autoload": {
                "psr-4": {
                    "MarouanBahtit\\\\{$this->UpperName}\\\\": "src/"
                }
            },
            "authors": [
                {
                                "name": "Marouan Bahtit",
            "email": "marouan.bahtit@example.com"
                }
            ],
            "extra": {
                "laravel": {
                    "providers": [
                        "{$namespace}"
                    ]
                }
            }
        }
        EOT;
    }

    protected function getModuleJsonStub()
    {
        return <<<EOT
        {
            "name": "{$this->UpperName}",
            "alias": "{$this->UpperName}",
            "description": "",
            "priority": 0,
            "version":1.0,
            "monthly_price": 0,
            "yearly_price": 0,
            "package_name":"{$this->packageName}"
        }
        EOT;
    }

    protected function createStubFiles($packagePath)
    {
        $composerJson = $this->getComposerJsonStub();
        $this->files->put($packagePath . "/composer.json", $composerJson);

        $moduleJson = $this->getModuleJsonStub();
        $this->files->put($packagePath . "/module.json", $moduleJson);

        $serviceProviderStub = $this->getServiceProviderStub();
        $this->files->put($packagePath . "/src/Providers/{$this->UpperName}ServiceProvider.php", $serviceProviderStub);

        $seederStub = $this->getSeederStub();
        $this->files->put($packagePath."/src/Database/Seeders/{$this->UpperName}DatabaseSeeder.php",$seederStub);
    }

    protected function createFiles()
    {
        $files = [
            'listener/companymenu.stub' => 'src/Listeners/CompanyMenuListener.php',
            'listener/companysetting.stub' => 'src/Listeners/CompanySettingListener.php',
            'listener/companysettingmenu.stub' => 'src/Listeners/CompanySettingMenuListener.php',
            'listener/superadminmenu.stub' => 'src/Listeners/SuperAdminMenuListener.php',
            'listener/superadminsetting.stub' => 'src/Listeners/SuperAdminSettingListener.php',
            'listener/superadminsettingmenu.stub' => 'src/Listeners/SuperAdminSettingMenuListener.php',
            'http/controllers/company/settingscontroller.stub' => 'src/Http/Controllers/Company/SettingsController.php',
            'http/controllers/superadmin/settingscontroller.stub' => 'src/Http/Controllers/SuperAdmin/SettingsController.php',
            'routes/web.stub'=>'src/Routes/web.php',
            'routes/api.stub'=>'src/Routes/api.php',
            'seeders/MarketPlaceSeederTableSeeder.stub'=>'src/Database/Seeders/MarketPlaceSeederTableSeeder.php',
            'seeders/PermissionTableSeeder.stub'=>'src/Database/Seeders/PermissionTableSeeder.php',
            'views/company/settings/index.stub'=>'src/Resources/views/company/settings/index.blade.php',
            'views/super-admin/settings/index.stub'=>'src/Resources/views/super-admin/settings/index.blade.php',
            'views/marketplace/index.stub'=>'src/Resources/views/marketplace/index.blade.php',
            'views/index.stub'=>'src/Resources/views/index.blade.php',
            'providers/eventserviceprovider.stub' => 'src/Providers/EventServiceProvider.php',
            'providers/routeserviceprovider.stub' => 'src/Providers/RouteServiceProvider.php'
        ];

        foreach ($files as $stubFile => $phpFile) {
            $stubPath = base_path('stubs/marouanbahtit-stubs/'.$stubFile);
            $stub = File::get($stubPath);

            $stub = str_replace('$STUDLY_NAME$', $this->UpperName, $stub);
            $stub = str_replace('$LOWER_NAME$', $this->LowerName, $stub);
            $stub = str_replace('$PACKAGE_NAME$', $this->packageName, $stub);

            $filePath = base_path("packages/marouanbahtit/{$this->UpperName}/".$phpFile);

            if (!File::exists(dirname($filePath))) {
                File::makeDirectory(dirname($filePath), 0755, true);
            }
            $this->files->put($filePath, $stub);
        }
    }

    protected function getServiceProviderStub()
    {
        return <<<EOT
        <?php

        namespace MarouanBahtit\\{$this->UpperName}\\Providers;

        use Illuminate\Support\ServiceProvider;
        use MarouanBahtit\\{$this->UpperName}\\Providers\EventServiceProvider;
        use MarouanBahtit\\{$this->UpperName}\\Providers\RouteServiceProvider;

        class {$this->UpperName}ServiceProvider extends ServiceProvider
        {

            protected \$moduleName = '{$this->UpperName}';
            protected \$moduleNameLower = '{$this->LowerName}';

            public function register()
            {
                \$this->app->register(RouteServiceProvider::class);
                \$this->app->register(EventServiceProvider::class);
            }

            public function boot()
            {
                \$this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
                \$this->loadViewsFrom(__DIR__ . '/../Resources/views', '{$this->packageName}');
                \$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
                \$this->registerTranslations();
            }

            /**
             * Register translations.
             *
             * @return void
             */
            public function registerTranslations()
            {
                \$langPath = resource_path('lang/modules/' . \$this->moduleNameLower);

                if (is_dir(\$langPath)) {
                    \$this->loadTranslationsFrom(\$langPath, \$this->moduleNameLower);
                    \$this->loadJsonTranslationsFrom(\$langPath);
                } else {
                    \$this->loadTranslationsFrom(__DIR__.'/../Resources/lang', \$this->moduleNameLower);
                    \$this->loadJsonTranslationsFrom(__DIR__.'/../Resources/lang');
                }
            }
        }
        EOT;
    }

    protected function getSeederStub()
    {
        return <<<EOT
        <?php

        namespace MarouanBahtit\\{$this->UpperName}\\Database\Seeders;

        use Illuminate\Database\Seeder;
        use Illuminate\Database\Eloquent\Model;

        class {$this->UpperName}DatabaseSeeder extends Seeder
        {
            /**
             * Run the database seeds.
             *
             * @return void
             */
            public function run()
            {
                Model::unguard();

                \$this->call(PermissionTableSeeder::class);
                if(module_is_active('LandingPage'))
                {
                    \$this->call(MarketPlaceSeederTableSeeder::class);
                };
            }
        }
        EOT;
    }
}
