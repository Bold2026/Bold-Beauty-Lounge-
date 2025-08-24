<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Workdo\CarDealership\Entities\DealershipProduct;
use Workdo\Newspaper\Entities\Newspaper;
use Workdo\Fleet\Entities\VehicleInvoice;
    

class InvoiceProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_type',
        'product_id',
        'invoice_id',
        'quantity',
        'tax',
        'discount',
        'description',
        'total',
        'product_name',

    ];
    public function product()
    {
        $invoice =  $this->hasMany(Invoice::class, 'id', 'invoice_id')->first();
        if (!empty($invoice) && $invoice->invoice_module == "account" || !empty($invoice) && $invoice->invoice_module == "machinerepair" || !empty($invoice) && $invoice->invoice_module == "sales" || $invoice->invoice_module == 'musicinstitute'|| $invoice->invoice_module == 'mobileservice' || $invoice->invoice_module == 'vehicleinspection' )  {
            if (module_is_active('ProductService')) {
                return $this->hasOne(\Workdo\ProductService\Entities\ProductService::class, 'id', 'product_id')->first();
            } else {
                return [];
            }
        } elseif (!empty($invoice) && $invoice->invoice_module == "taskly") {
            if (module_is_active('Taskly')) {
                return  $this->hasOne(\Workdo\Taskly\Entities\Task::class, 'id', 'product_id')->first();
            } else {
                return [];
            }
        } elseif (!empty($invoice) && $invoice->invoice_module == "cmms") {
            if (module_is_active('ProductService')) {
                return $this->hasOne(\Workdo\ProductService\Entities\ProductService::class, 'id', 'product_id')->first();
            } else {
                return [];
            }
        } elseif (!empty($invoice) && $invoice->invoice_module == "rent") {
            if (module_is_active('ProductService')) {
                return $this->hasOne(\Workdo\ProductService\Entities\ProductService::class, 'id', 'product_id')->first();
            } else {
                return [];
            }
        } elseif (!empty($invoice) && $invoice->invoice_module == "lms" ) {
            return $this->hasOne(\Workdo\LMS\Entities\Course::class, 'id', 'product_id')->first();
        } elseif (!empty($invoice) && $invoice->invoice_module == "cardealership" ) {

            return $this->hasOne(DealershipProduct::class, 'id', 'product_id')->first();
        } elseif (!empty($invoice) && $invoice->invoice_module == "newspaper" ) {

            return $this->hasOne(Newspaper::class, 'id', 'product_id')->first();

        }elseif (!empty($invoice) && $invoice->invoice_module == "Fleet" ) {
            return VehicleInvoice::where('invoice_id',$this->invoice_id)->where('item',$this->product_id)->first();
        }
    }
}
