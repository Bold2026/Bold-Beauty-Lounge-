<?php

namespace Workdo\Hrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CustomFieldListTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $sub_module = [
            'Employee',
        ];
        if(module_is_active('CustomField'))
        {
            foreach($sub_module as $sm){
                $check = \Workdo\CustomField\Entities\CustomFieldsModuleList::where('module','Hrm')->where('sub_module',$sm)->first();
                if(!$check){
                    $new = new \Workdo\CustomField\Entities\CustomFieldsModuleList();
                    $new->module = 'Hrm';
                    $new->sub_module = $sm;
                    $new->save();
                }
            }
        }
    }
}
