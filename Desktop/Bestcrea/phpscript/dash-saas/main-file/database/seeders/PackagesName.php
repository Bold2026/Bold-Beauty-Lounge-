<?php

namespace Database\Seeders;

use App\Models\AddOn;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PackagesName extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addons = AddOn::where('package_name',null)->get();
        foreach($addons as $addon)
        {
            if($addon->package_name == null)
            {
                $path = base_path('packages/workdo/' . $addon->module . '/module.json');
                $contents = File::get($path);
                $contents = json_decode($contents, true);

                $module = AddOn::find($addon->id);
                $module->package_name = $contents['package_name'];
                $module->save();
            }
        }
    }
}
