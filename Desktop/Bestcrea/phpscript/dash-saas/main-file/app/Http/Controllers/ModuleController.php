<?php

namespace App\Http\Controllers;

use App\Events\CancelSubscription;
use App\Models\AddOn;
use App\Models\Sidebar;
use App\Models\User;
use App\Models\userActiveModule;
use App\Facades\ModuleFacade as Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Permission;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use ZipArchive;

class ModuleController extends Controller
{
    public function index()
    {
        if (Auth::user()->isAbleTo('module manage')) {
            try {
                $modules = Module::allModules();
                $category_wise_add_ons = json_decode(file_get_contents("https://dash-demo.workdo.io/cronjob/dash-addon.json"), true);

                $path = base_path('packages/workdo');
                $devPackagePath = \Illuminate\Support\Facades\File::directories($path);

                $devPackageDirectories = array_map(function ($dir) {
                    return basename($dir);
                }, $devPackagePath);

                $moduleNames = array_column($modules, 'name');

                $devPackages = array_filter($devPackageDirectories, function ($item) use ($moduleNames) {
                    return !in_array($item, $moduleNames);
                });

                $devModules = [];
                $index = 0;
                foreach($devPackages as $devPackage){
                    $moduleFilePath = "{$path}/{$devPackage}/module.json";

                    $devPackageFileContent = file_get_contents($moduleFilePath);
                    $devPackageArr = json_decode($devPackageFileContent);

                    $devModules[$index]['name'] = $devPackageArr->name;
                    $devModules[$index]['alias'] = $devPackageArr->alias;
                    $devModules[$index]['monthly_price'] = $devPackageArr->monthly_price ?? 0;
                    $devModules[$index]['yearly_price'] = $devPackageArr->yearly_price ?? 0;
                    $devModules[$index]['image'] = url('/packages/workdo/' . $devPackage . '/favicon.png');
                    $devModules[$index]['description'] = $devPackageArr->description ?? "";
                    $devModules[$index]['priority'] = $devPackageArr->priority ?? 10;
                    $devModules[$index]['child_module'] = $devPackageArr->child_module ?? [];
                    $devModules[$index]['parent_module'] = $devPackageArr->parent_module ?? [];
                    $devModules[$index]['version'] = $devPackageArr->version ?? 1.0;
                    $devModules[$index]['package_name'] = $devPackageArr->package_name ?? null;
                    $devModules[$index]['display'] = $devPackageArr->display ?? true;

                    $index++;
                }

            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('oops something went wrong!'));
            }

            return view('module.index', compact('modules', 'category_wise_add_ons','devModules'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function add()
    {
        if (Auth::user()->isAbleTo('module add')) {
            return view('module.add');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function enable(Request $request)
    {
        $module = Module::find($request->name);
        if (!empty($module)) {
            // Sidebar Performance Changes
            sideMenuCacheForget('all');

            \App::setLocale('en');

            if ($module->isEnabled()) {
                $check_child_module = $this->Check_Child_Module($module);
                if ($check_child_module == true) {
                    $module = Module::find($request->name);
                    $module->disable();
                    return redirect()->back()->with('success', __('Module Disable Successfully!'));
                } else {
                    return redirect()->back()->with('error', __($check_child_module['msg']));
                }
            } else {
                $addon = AddOn::where('module', $request->name)->first();
                if (empty($addon)) {
                    Artisan::call('migrate --path=/packages/workdo/' . $request->name . '/src/Database/Migrations');
                    Artisan::call('package:seed ' . $request->name);

                    $filePath = base_path('packages/workdo/' . $request->name . '/module.json');
                    $jsonContent = file_get_contents($filePath);
                    $data = json_decode($jsonContent, true);


                    $addon = new AddOn;
                    $addon->module = $data['name'];
                    $addon->name = $data['alias'];
                    $addon->monthly_price = $data['monthly_price'] ?? 0;
                    $addon->yearly_price = $data['yearly_price'] ?? 0;
                    $addon->package_name = $data['package_name'];
                    $addon->save();
                    Module::moduleCacheForget($request->name);
                }
                $module = Module::find($request->name);

                $check_parent_module = $this->Check_Parent_Module($module);
                if ($check_parent_module['status'] == true) {
                    Artisan::call('migrate --path=/packages/workdo/' . $request->name . '/src/Database/Migrations');
                    Artisan::call('package:seed ' . $request->name);
                    $module = Module::find($request->name);
                    $module->enable();
                    return redirect()->back()->with('success', __('Module Enable Successfully!'));
                } else {
                    return redirect()->back()->with('error', __($check_parent_module['msg']));
                }
            }
        } else {
            return redirect()->back()->with('error', __('oops something wren wrong!'));
        }
    }

    public function install(Request $request)
    {
        $zip = new ZipArchive;
        $fileName = $request->file('file')->getClientOriginalName();
        $fileName = str_replace('.zip', '', $fileName); // Remove .zip from the file name

        // Try to open the zip file
        try {
            $res = $zip->open($request->file);
            if ($res !== TRUE) {
                return error_res('Unable to open the ZIP file.');
            }
        } catch (\Exception $e) {
            return error_res($e->getMessage());
        }

        // Prepare the extraction path
        $extractPath = 'packages/workdo/' . $fileName;
        $this->createDirectory($extractPath);


         // After extracting to the temporary directory
         $tempPath = 'packages/workdo/tmp_' . uniqid();
         $zip->extractTo($tempPath);
         $zip->close();

         // Determine the root folder name in the zip (if needed)
         $rootFolder = array_diff(scandir($tempPath), ['.', '..']);
         if (empty($rootFolder) || !file_exists($tempPath.'/'.$fileName.'/module.json')) {
            // Remove the temporary directory
            $this->deleteDirectory($tempPath);
            return error_res(__('You have uploaded an invalid file. Please upload a valid file.'));
         }

        $rootFolderName = array_values($rootFolder)[0]; // Get the first folder name in the zip

        // Move files to the target directory
        $this->moveExtractedFiles($tempPath, $extractPath, $rootFolderName);

        // Remove the temporary directory
        $this->deleteDirectory($tempPath);


        $this->setPermissions($extractPath);

        // Process the `module.json` file
        $filePath = base_path('packages/workdo/' . $fileName . '/module.json');
        $data = $this->parseJsonFile($filePath);

        // Handle AddOn logic
        $addon = AddOn::where('module', $fileName)->first();
        if (empty($addon)) {
            $addon = new AddOn;
            $addon->module = $data['name'];
            $addon->name = $data['alias'];
            $addon->monthly_price = 0;
            $addon->yearly_price = 0;
            $addon->is_enable = 0;
            $addon->package_name = $data['package_name'];
            $addon->save();
        } else {
            Artisan::call('db:seed', ['--class' => 'PackagesName']);
        }

        // Forget the cache for the module
        Module::moduleCacheForget($addon->module);

        return success_res($data['name'].' '. __('Installed successfully.'));

    }

    public function Check_Parent_Module($module)
    {
        $path = $module->getPath() . '/module.json';
        $json = json_decode(file_get_contents($path), true);
        $data['status'] = true;
        $data['msg'] = '';

        if (isset($json['parent_module']) && !empty($json['parent_module'])) {
            foreach ($json['parent_module'] as $key => $value) {
                $modules = implode(',', $json['parent_module']);
                $parent_module = module_is_active($value);
                if ($parent_module == true) {
                    $module = Module::find($value);
                    if ($module) {
                        $this->Check_Parent_Module($module);
                    }
                } else {
                    $data['status'] = false;
                    $data['msg'] = 'please activate this module ' . $modules;
                    return $data;
                }
            }
            return $data;
        } else {
            return $data;
        }
    }
    public function Check_Child_Module($module)
    {
        $path = $module->getPath() . '/module.json';
        $json = json_decode(file_get_contents($path), true);
        $status = true;
        if (isset($json['child_module']) && !empty($json['child_module'])) {
            foreach ($json['child_module'] as $key => $value) {
                $child_module = module_is_active($value);
                if ($child_module == true) {
                    $module = Module::find($value);
                    $module->disable();
                    if ($module) {
                        $this->Check_Child_Module($module);
                    }
                }
            }
            return true;
        } else {
            return true;
        }
    }
    public function GuestModuleSelection(Request $request)
    {
        try {
            $post = $request->all();
            unset($post['_token']);
            Session::put('user-module-selection', $post);
            Session::put('Subscription', 'custom_subscription');
        } catch (\Throwable $th) {
        }
        return true;
    }
    public function ModuleReset(Request $request)
    {
        $value = Session::get('user-module-selection');
        if (!empty($value)) {
            Session::forget('user-module-selection');
        }
        return redirect()->route('plans.index');
    }
    public function CancelAddOn($name = null,$user_id=null)
    {
        if (!empty($name)) {
            $name         = \Illuminate\Support\Facades\Crypt::decrypt($name);
            if(!empty($user_id))
            {
                $user = User::find($user_id);
                $user_module = explode(',', $user->active_module);
            }
            else
            {
                $user = User::find(Auth::user()->id);
                $user_module = explode(',', Auth::user()->active_module);
            }
            $user_module = array_values(array_diff($user_module, array($name)));
            $user->active_module = implode(',', $user_module);
            $user->save();

            event(new CancelSubscription(creatorId(), getActiveWorkSpace(), $name));

            userActiveModule::where('user_id', $user->id)->where('module', $name)->delete();

            // Settings Cache forget
            comapnySettingCacheForget();
            sideMenuCacheForget();
            return redirect()->back()->with('success', __('Successfully cancel subscription.'));
        } else {
            return redirect()->back()->with('error', __('Something went wrong please try again .'));
        }
    }

    private function createDirectory($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            $this->setPermissions($path);
        } else {
            $this->setPermissions($path);
        }
    }

    // Set directory permissions
    private function setPermissions($path)
    {
        if (function_exists('chmod')) {
            @chmod($path, 0777); // Set permissions if possible
        }
    }

    // Parse the module.json file and return its content
    private function parseJsonFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('module.json file is missing.');
        }
        $jsonContent = file_get_contents($filePath);
        return json_decode($jsonContent, true);
    }

    /**
     * Move files from one directory to another.
     *
     * @param string $source
     * @param string $destination
     */
    private function moveExtractedFiles($source, $destination, $filename = null)
    {
        // Adjust the source directory if a root folder (e.g., $filename) exists in the zip
        if ($filename) {
            $source = $source . DIRECTORY_SEPARATOR . $filename;
        }

        $files = array_diff(scandir($source), ['.', '..']);
        foreach ($files as $file) {
            $srcPath = $source . DIRECTORY_SEPARATOR . $file;
            $destPath = $destination . DIRECTORY_SEPARATOR . $file;

            if (is_dir($srcPath)) {
                // Recursively move subdirectories
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0777, true);
                }
                // Check if chmod exists
                if (function_exists('chmod')) {
                    @chmod($destPath, 0777); // Set permissions if possible
                }
                $this->moveExtractedFiles($srcPath, $destPath);
            } else {
                // Move file
                rename($srcPath, $destPath);
                // Check if chmod exists
                if (function_exists('chmod')) {
                    @chmod($destPath, 0777); // Set permissions if possible
                }
            }
        }
    }

    /**
     * Delete a directory and its contents.
     *
     * @param string $dirPath
     * @return bool
     */
    private function deleteDirectory($dirPath)
    {
        if (!is_dir($dirPath)) {
            return false;
        }

        $items = array_diff(scandir($dirPath), ['.', '..']);
        foreach ($items as $item) {
            $path = $dirPath . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dirPath);
    }

}
