<?php

namespace App\Classes;

use App\Models\AddOn;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class Module
{
    protected $addon;
    public $name;
    public $alias;
    public $monthly_price;
    public $yearly_price;
    public $image;
    public $description;
    public $priority;
    public $child_module;
    public $parent_module;
    public $version;
    public $package_name;
    public $display;
    protected $allEnabled = [];

    public function json($name)
    {
        $path = base_path('packages/workdo/' . $name . '/module.json');
        if (!File::exists($path)) {
            return false;
        }

        $contents = File::get($path);
        return json_decode($contents, true);
    }

    public function find($name)
    {

        return Cache::rememberForever(
            $name,
            function () use ($name) {
                if ($name === 'general') {
                    $this->name =  $name;
                    $this->alias =  $name;
                } else {
                    $this->addon = AddOn::where('module', $name)->orWhere('package_name', $name)->first();

                    $addonJson = $this->json($name);
                    if ($addonJson) {
                        $this->name = $addonJson['name'] ?? $name;
                        $this->alias = $addonJson['alias'] ?? $name;
                        $this->monthly_price = $addonJson['monthly_price'] ?? 0;
                        $this->yearly_price = $addonJson['yearly_price'] ?? 0;
                        $this->image = url('/packages/workdo/' . $addonJson['name'] . '/favicon.png');
                        $this->description = $addonJson['description'] ?? "";
                        $this->priority = $addonJson['priority'] ?? 10;
                        $this->child_module = $addonJson['child_module'] ?? [];
                        $this->parent_module = $addonJson['parent_module'] ?? [];
                        $this->version = $addonJson['version'] ?? 1.0;
                        $this->package_name = $addonJson['package_name'] ?? null;
                        $this->display = $addonJson['display'] ?? true;
                    }

                    if ($this->addon) {
                        $this->name = $this->addon->module ?? $name;
                        $this->alias = $this->addon->name ?? $name;
                        $this->monthly_price = $this->addon->monthly_price ?? 0;
                        $this->yearly_price = $this->addon->yearly_price ?? 0;
                        $this->image = !empty($this->addon->image) ? get_file($this->addon->image) : url('/packages/workdo/' . $this->addon->module . '/favicon.png');
                        $this->package_name = $this->addon->package_name ?? null;
                    }
                }

                return $this;
            }
        );
    }

    public function all()
    {
        $modules = $this->allEnabled();
        return $this->moduleArr($modules);
    }

    public function moduleArr($modules)
    {
        $allModulesArr = [];
        foreach ($modules as $module) {
            $moduleInstance = new self();
            $allModulesArr[] = $moduleInstance->find($module);
        }
        return $allModulesArr;
    }

    public function allEnabled(): array
    {

        return AddOn::where('is_enable', 1)->pluck('module')->toArray() ?? [];

    }

    public function getOrdered()
    {
        $modules = $this->all();

        usort($modules, function ($a, $b) {
            return $a->priority - $b->priority;
        });

        return $modules;
    }

    public function has($name)
    {
        return in_array($name, array_column($this->allModules(), 'name'));
    }

    public function isEnabled($module = null)
    {
        static $cache = [];  

        if ($module) {
            if (!isset($cache[$module])) {

                $cache[$module] = Addon::where('module', $module)
                    ->where('is_enable', 1)
                    ->exists();
            }

            return $cache[$module];
        }

        return $this->addon && $this->addon->is_enable;
    }

    public function enable()
    {
        if ($this->addon) {
            $this->addon->is_enable = 1;
            $this->addon->save();

            $this->moduleCacheForget();

        }
    }

    public function disable()
    {
        if ($this->addon) {
            $this->addon->is_enable = 0;
            $this->addon->save();

            $this->moduleCacheForget();
        }
    }

    public function getDirectories()
    {
        $path = base_path('packages/workdo');
        return File::directories($path);
    }

    public function getPath()
    {
        if (is_null($this->addon)) {
            return $this->getDirectories();
        }
        return base_path('packages/workdo/' . $this->name);
    }

    public function getDevPackagePath()
    {
        if (is_null($this->addon)) {
            $path = base_path('packages/workdo');
            return File::directories($path);
        }
        return base_path('packages/workdo/' . $this->name);
    }

    public function allModules()
    {
         $directories = array_map(function ($dir) {
            return basename($dir);
        }, $this->getDirectories());

        return $this->moduleArr($directories);
    }

    public function moduleCacheForget($module = null)
    {
        try {
            if(is_null($module)){
                Cache::forget($this->addon->module);
                Cache::forget($this->addon->package_name);
            }
            else{
                Cache::forget($module);
            }
        } catch (\Exception $e) {
            \Log::error($module . $e->getMessage());
        }
    }
}
