<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class MakePackageComponent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:package {type} {name} {package} {--m}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new component in the specified package';

    /**
     * Execute the console command.
     */
    public $packageName;

    public function handle()
    {
        $type = $this->argument('type');
        $name = $this->argument('name');
        $package = $this->argument('package');
        $createMigration = $this->option('m');

        $this->packageName = $this->camelToKebab($package);

        $baseDir = base_path("packages/marouanbahtit/$package/src");
        $namespace = "MarouanBahtit\\$package\\";

        switch ($type) {
            case 'controller':
                $this->createController($name, $baseDir, $namespace);
                break;
            case 'model':
                $this->createModel($name, $baseDir, $namespace, $createMigration,$package);
                break;
            case 'migration':
                $this->createMigration($name, $package);
                break;
            case 'middleware':
                $this->createMiddleware($name, $baseDir, $namespace);
                break;
            case 'event':
                $this->createEvent($name, $baseDir, $namespace);
                break;
            case 'listener':
                $this->createListener($name, $baseDir, $namespace);
                break;
            case 'provider':
                $this->createProvider($name, $baseDir, $namespace);
                break;
            case 'seeder':
                $this->createSeeder($name, $baseDir, $namespace);
                break;
            case 'datatable':
                $this->createDatatable($name, $baseDir, $namespace);
                break;
            default:
                $this->error("Invalid type provided.");
                break;
        }
    }

    function camelToKebab($name)
    {
        $packageName = preg_replace('/([a-z])([A-Z])/', '$1-$2', $name);
        return strtolower($packageName);
    }
    function camelToSnake($name)
    {
        $packageName = preg_replace('/([a-z])([A-Z])/', '$1_$2', $name);
        return strtolower($packageName);
    }

    function pluralize($word)
    {
        $plural = [
            '/(quiz)$/i' => '\1zes',
            '/^(ox)$/i' => '\1en',
            '/([m|l])ouse$/i' => '\1ice',
            '/(matr|vert|ind)ix|ex$/i' => '\1ices',
            '/(x|ch|ss|sh)$/i' => '\1es',
            '/([^aeiouy]|qu)y$/i' => '\1ies',
            '/(hive)$/i' => '\1s',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/sis$/i' => 'ses',
            '/([ti])um$/i' => '\1a',
            '/(buffal|tomat)o$/i' => '\1oes',
            '/(bu)s$/i' => '\1ses',
            '/(alias|status)$/i' => '\1es',
            '/(octop|vir)us$/i' => '\1i',
            '/(ax|test)is$/i' => '\1es',
            '/s$/i' => 's',
            '/$/' => 's',
        ];

        foreach ($plural as $pattern => $replacement) {
            if (preg_match($pattern, $word)) {
                return preg_replace($pattern, $replacement, $word);
            }
        }
        return $word;
    }


    protected function createController($name, $baseDir, $namespace)
    {
        $path = "$baseDir/Http/Controllers/{$name}.php";
        $namespace .= "Http\\Controllers";

        if (File::exists($path)) {
            $this->error("Controller already exists!");
            return;
        }

        $stubPath = base_path('stubs/marouanbahtit-stubs/controller.stub');
        if (!File::exists($stubPath)) {
            $this->error("Stub file does not exist!");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace('$CLASS_NAMESPACE$', $namespace, $stub);
        $stub = str_replace('$CLASS$', $name, $stub);
        $stub = str_replace('$PACKAGE_NAME$', $this->packageName, $stub);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);

        $this->info("Controller $name created successfully.");
    }

    protected function createModel($name, $baseDir, $namespace, $createMigration,$package)
    {
        $path = "$baseDir/Entities/{$name}.php";
        $namespace .= "Entities";

        if (File::exists($path)) {
            $this->error("Model already exists!");
            return;
        }

        $stubPath = base_path('stubs/marouanbahtit-stubs/model.stub');
        if (!File::exists($stubPath)) {
            $this->error("Stub file does not exist!");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace('$NAMESPACE$', $namespace, $stub);
        $stub = str_replace('$CLASS$', $name, $stub);

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $stub);
        $this->info("Model $name Created Successfully!");

        if ($createMigration) {
            $this->createMigration("create_{$this->camelToSnake($this->pluralize($name))}_table", $package);
        }
    }

    protected function createMigration($name, $package)
    {
        $command = 'make:migration ' . $name . ' --path=/packages/marouanbahtit/' . $package . '/src/Database/Migrations';
        Artisan::call($command);
        $this->info("Migration created successfully.");
    }

    protected function createMiddleware($name, $baseDir, $namespace)
    {
        $path = "$baseDir/Http/Middleware/{$name}.php";
        $namespace .= "Http\\Middleware";

        if (File::exists($path)) {
            $this->error("Middleware already exists!");
            return;
        }

        $stubPath = base_path('stubs/workdo-stubs/middleware.stub');
        if (!File::exists($stubPath)) {
            $this->error("Stub file does not exist!");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace('$NAMESPACE$', $namespace, $stub);
        $stub = str_replace('$CLASS$', $name, $stub);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);

        $this->info("Middleware $name Created Successfully!");
    }

    protected function createEvent($name, $baseDir, $namespace)
    {
        $path = "$baseDir/Events/{$name}.php";
        $namespace .= "Events";

        if (File::exists($path)) {
            $this->error("Event already exists!");
            return;
        }

        $stubPath = base_path('stubs/workdo-stubs/event.stub');
        if (!File::exists($stubPath)) {
            $this->error("Stub file does not exist!");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace('$NAMESPACE$', $namespace, $stub);
        $stub = str_replace('$CLASS$', $name, $stub);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);

        $this->info("Event $name Created Successfully!");
    }

    protected function createListener($name, $baseDir, $namespace)
    {
        $path = "$baseDir/Listeners/{$name}.php";
        $namespace .= "Listeners";

        if (File::exists($path)) {
            $this->error("Listener already exists!");
            return;
        }

        $stubPath = base_path('stubs/workdo-stubs/listener.stub');
        if (!File::exists($stubPath)) {
            $this->error("Stub file does not exist!");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace('$NAMESPACE$', $namespace, $stub);
        $stub = str_replace('$CLASS$', $name, $stub);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);

        $this->info("Listener $name Created Successfully!");
    }

    protected function createProvider($name, $baseDir, $namespace)
    {
        $path = "$baseDir/Providers/{$name}.php";
        $namespace .= "Providers";

        if (File::exists($path)) {
            $this->error("Provider already exists!");
            return;
        }

        $stubPath = base_path('stubs/workdo-stubs/provider.stub');
        if (!File::exists($stubPath)) {
            $this->error("Stub file does not exist!");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace('$NAMESPACE$', $namespace, $stub);
        $stub = str_replace('$CLASS$', $name, $stub);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);

        $this->info("Provider $name Created Successfully!");
    }

    protected function createSeeder($name, $baseDir, $namespace)
    {
        $path = "$baseDir/Database/Seeders/{$name}DatabaseSeeder.php";
        $namespace .= "Database\\Seeders";

        if (File::exists($path)) {
            $this->error("Seeder already exists!");
            return;
        }

        $stubPath = base_path('stubs/workdo-stubs/seeder.stub');
        if (!File::exists($stubPath)) {
            $this->error("Stub file does not exist!");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace('$NAMESPACE$', $namespace, $stub);
        $stub = str_replace('$CLASS$', $name, $stub);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);

        $this->info("Seeder $name Created Successfully!");
    }

    protected function createDatatable($name, $baseDir, $namespace)
    {
        $path = "$baseDir/DataTables/{$name}.php";
        $namespace .= "DataTables";

        if (File::exists($path)) {
            $this->error("Datatable already exists!");
            return;
        }

        $stubPath = base_path('stubs/workdo-stubs/datatable/datatable.stub');
        if (!File::exists($stubPath)) {
            $this->error("Datatable file does not exist!");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace('$NAMESPACE$', $namespace, $stub);
        $stub = str_replace('$CLASS$', $name, $stub);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);

        $this->info("Datatable $name Created Successfully!");
    }
}
