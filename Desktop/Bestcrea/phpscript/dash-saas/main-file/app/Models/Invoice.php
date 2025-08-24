<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\StockReport;
use Workdo\LMS\Entities\Student;
use Workdo\Fleet\Entities\VehicleInvoice;
use Workdo\Newspaper\Entities\NewspaperTax;

class Invoice extends Model
{
    use HasFactory;
    private $subTotal;
    protected $fillable = [
        'invoice_id',
        'user_id',
        'customer_id',
        'issue_date',
        'due_date',
        'send_date',
        'category_id',
        'ref_number',
        'status',
        'invoice_module',
        'shipping_display',
        'day_type',
        'start_date',
        'end_date',
        'discount_apply',
        'workspace',
        'created_by'
    ];
    public static $statues = [
        'Draft',
        'Sent',
        'Unpaid',
        'Partialy Paid',
        'Paid',
    ];
    public static function countInvoices()
    {
        return Invoice::where('workspace', '=', getActiveWorkSpace())->count();
    }
    public function customers()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'id', 'user_id');
    }

    // public function customerDetail()
    // {
    //     return $this->hasOne(\Workdo\Account\Entities\Customer::class, 'user_id', 'user_id');
    // }

    public function customer()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function category()
    {
        return $this->hasOne(\Workdo\ProductService\Entities\Category::class, 'id', 'category_id');
    }
    public static function invoiceNumberFormat($number, $company_id = null, $workspace = null)
    {
        if (!empty($company_id) && empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id);
        } elseif (!empty($company_id) && !empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id, $workspace);
        } else {
            $company_settings = getCompanyAllSetting();
        }
        $data = !empty($company_settings['invoice_prefix']) ? $company_settings['invoice_prefix'] : '#INVO0';

        return $data . sprintf("%05d", $number);
    }
    public function items()
    {
        return $this->hasMany(InvoiceProduct::class, 'invoice_id', 'id');
    }
    public function payments()
    {
        return $this->hasMany(InvoicePayment::class, 'invoice_id', 'id');
    }
    public function fleet()
    {
        return $this->hasMany(VehicleInvoice::class, 'invoice_id', 'id');
    }
    public function creditNote()
    {
        if (module_is_active('Account')) {
            return $this->hasMany(\Workdo\Account\Entities\CreditNote::class, 'invoice', 'id');
        } else {
            return [];
        }
    }

    public function customcreditNote()
    {
        if (module_is_active('Account')) {

            return $this->hasMany(\Workdo\Account\Entities\CustomerCreditNotes::class, 'invoice', 'id');
        } else {
            return [];
        }
    }

    public function invoiceTotalCreditNote()
    {
        if (module_is_active('Account')) {
            return $this->hasMany(\Workdo\Account\Entities\CreditNote::class, 'invoice', 'id')->sum('amount');
        } else {
            return 0;
        }
    }

    public function invoiceTotalCustomerCreditNote()
    {
        if (module_is_active('Account')) {
            return $this->hasMany(\Workdo\Account\Entities\CustomerCreditNotes::class, 'invoice', 'id')->sum('amount');
        } else {
            return 0;
        }
    }
    public function getSubTotal()
    {
        $subTotal = 0;
        foreach ($this->items as $product) {
            if($product->quantity == 0){
                $quantity = 1;
            }
            else{
                $quantity = $product->quantity;
            }
            $subTotal += ($product->price * $quantity);
        }
        return $subTotal;
    }


    public function getFleetSubTotal()
    {
        $subTotal = 0;
        foreach ($this->fleet as $product) {
            $subTotal += ($product->rate * $product->distance);
        }
        return $subTotal;
    }
    public function getTotalDiscount()
    {
        $totalDiscount = 0;
        foreach ($this->items as $product) {
            $totalDiscount += $product->discount;
        }
        return $totalDiscount;
    }
    public static function taxRate($taxRate, $price, $quantity, $discount = 0)
    {
        return ($taxRate / 100) * (($price * $quantity) - $discount);
    }
    public static function tax($taxes)
    {
        if (module_is_active('ProductService')) {
            $taxArr = explode(',', $taxes);
            $taxes  = [];
            foreach ($taxArr as $tax) {
                $taxes[] = \Workdo\ProductService\Entities\Tax::find($tax);
            }

            return $taxes;
        } else {
            return [];
        }
    }
    // public static function totalTaxRate($taxes)
    // {
    //     if(module_is_active('ProductService'))
    //     {
    //         $TaxData = Invoice::getTaxData();
    //         $taxArr  = explode(',', $taxes);
    //         $taxRate = 0;
    //         foreach($taxArr as $tax)
    //         {
    //             $taxRate += (isset($TaxData[$tax])) ? (int)$TaxData[$tax]['rate'] : 0;
    //         }
    //         return $taxRate;
    //     }
    //     else
    //     {
    //         return 0;
    //     }
    // }

    // public static function totalTaxRate($taxes)
    // {
    //     if(module_is_active('ProductService'))
    //     {
    //         $taxArr  = explode(',', $taxes);

    //         $taxRate = 0;
    //         foreach($taxArr as $tax)
    //         {
    //             $tax     = \Workdo\ProductService\Entities\Tax::find($tax);
    //             $taxRate += !empty($tax->rate) ? $tax->rate : 0;
    //         }
    //         return $taxRate;
    //     }
    //     else
    //     {
    //         return 0;
    //     }
    // }

    public static function getTaxes($taxes)
    {
        static $taxCache = [];
        if (module_is_active('ProductService')) {

            if (!isset($taxCache[$taxes])) {
                $taxIds = explode(',', $taxes);
                $allTaxes = \Workdo\ProductService\Entities\Tax::whereIn('id', $taxIds)->get();
                $taxCache[$taxes] = $allTaxes;
            } else {
                $allTaxes = $taxCache[$taxes];
            }

            return $allTaxes;
        }

        return collect();
    }

    public static function totalTaxRate($taxes)
    {
        static $taxCache = [];
        if (module_is_active('ProductService')) {

            if (!isset($taxCache[$taxes])) {
                $allTaxes = self::getTaxes($taxes);
                $taxCache[$taxes] = $allTaxes->keyBy('id');
            } else {
                $allTaxes = $taxCache[$taxes];
            }

            $taxRate = 0;
            foreach ($allTaxes as $tax) {
                if ($tax) {
                    $taxRate += !empty($tax->rate) ? $tax->rate : 0;
                }
            }
            return $taxRate;
        } else {
            return 0;
        }
    }
    public function getTotalTax()
    {
        $totalTax = 0;
        foreach ($this->items as $product) {
            if (module_is_active('ProductService')) {
                $taxes = $this->totalTaxRate($product->tax);
            } else {
                $taxes = 0;
            }
            $totalTax += ($taxes / 100) * (($product->price * $product->quantity) - $product->discount);
        }

        return $totalTax;
    }

    public function getNewsPaperTax()
    {
        $totalTax = 0;
        foreach ($this->items as $product)
        {
            $tax = NewspaperTax::where('id',$product->tax)->first();
            $totalTax +=  ($tax->percentage / 100) * ($product->price * $product->quantity);
        }
        return $totalTax;
    }

    public function getTotal()
    {
        if ($this->invoice_module == 'Fleet') {
            return ($this->getFleetSubTotal() - $this->getTotalDiscount() + $this->getTotalTax());
        }
        else if($this->invoice_module == 'machinerepair' || $this->invoice_module == 'mobileservice' || $this->invoice_module == 'vehicleinspection') {
            return ($this->getSubTotal() - $this->getTotalDiscount() + $this->getTotalTax()) + $this->category_id;
        }
        else if($this->invoice_module == 'newspaper') {
            return ($this->getSubTotal() + $this->getNewsPaperTax());
        }
        else{
            return ($this->getSubTotal() - $this->getTotalDiscount() + $this->getTotalTax());
        }
    }

    public function getDue()
    {
        $due = 0;

        foreach ($this->payments as $payment) {
            $due += $payment->amount;
        }

        return ($this->getTotal() - $due) - $this->invoiceTotalCreditNote();
    }

    public static function starting_number($id, $type)
    {
        if ($type == 'invoice') {
            $key = 'invoice_starting_number';
        } elseif ($type == 'proposal') {
            $key = 'proposal_starting_number';
        } elseif ($type == 'retainer') {
            $key = 'retainer_starting_number';
        } elseif ($type == 'bill') {
            $key = 'bill_starting_number';
        }
        if (!empty($key) && $id) {

            $data = [
                'key' => $key,
                'workspace' => getActiveWorkSpace(),
                'created_by' => creatorId(),
            ];
            Setting::updateOrInsert($data, ['value' => $id]);
            // Settings Cache forget
            comapnySettingCacheForget();
            return true;
        }
        return false;
    }
    public static function total_quantity($type, $quantity, $product_id, $user_id = null)
    {
        if (module_is_active('ProductService', $user_id)) {
            $product      = \Workdo\ProductService\Entities\ProductService::find($product_id);
            if (!empty($product)) {
                if (($product->type == 'product' || $product->type == 'consignment' || $product->type == 'parts' || $product->type == 'rent'  || $product->type == 'music institute' || $product->type == 'cmms')) {
                    $pro_quantity = $product->quantity;
                    if ($type == 'minus') {
                        $product->quantity = $pro_quantity - $quantity;
                    } else {
                        $product->quantity = $pro_quantity + $quantity;
                    }
                    $product->save();
                }
            }
        }
    }

    public static function weeklyInvoice()
    {
        $staticstart  = date('Y-m-d', strtotime('last Week'));
        $currentDate  = date('Y-m-d');
        $invoices     = Invoice::select('*')->with('items')->where('workspace', getActiveWorkSpace())->where('issue_date', '>=', $staticstart)->where('invoice_module', 'account')->where('issue_date', '<=', $currentDate)->get();
        $invoiceTotal = 0;
        $invoicePaid  = 0;
        $invoiceDue   = 0;
        foreach ($invoices as $invoice) {
            $invoiceTotal += $invoice->getTotal();
            $invoicePaid  += ($invoice->getTotal() - $invoice->getDue());
            $invoiceDue   += $invoice->getDue();
        }

        $invoiceDetail['invoiceTotal'] = $invoiceTotal;
        $invoiceDetail['invoicePaid']  = $invoicePaid;
        $invoiceDetail['invoiceDue']   = $invoiceDue;

        return $invoiceDetail;
    }

    public static function monthlyInvoice()
    {
        $staticstart  = date('Y-m-d', strtotime('last Month'));
        $currentDate  = date('Y-m-d');
        $invoices     = Invoice::select('*')->with('items')->where('workspace', getActiveWorkSpace())->where('issue_date', '>=', $staticstart)->where('invoice_module', 'account')->where('issue_date', '<=', $currentDate)->get();
        $invoiceTotal = 0;
        $invoicePaid  = 0;
        $invoiceDue   = 0;
        foreach ($invoices as $invoice) {
            $invoiceTotal += $invoice->getTotal();
            $invoicePaid  += ($invoice->getTotal() - $invoice->getDue());
            $invoiceDue   += $invoice->getDue();
        }

        $invoiceDetail['invoiceTotal'] = $invoiceTotal;
        $invoiceDetail['invoicePaid']  = $invoicePaid;
        $invoiceDetail['invoiceDue']   = $invoiceDue;

        return $invoiceDetail;
    }

    public static function addProductStock($product_id, $quantity, $type, $description, $type_id)
    {
        $stocks                = new \Workdo\Account\Entities\StockReport();
        $stocks->product_id    = $product_id;
        $stocks->quantity       = $quantity;
        $stocks->type          = $type;
        $stocks->type_id       = $type_id;
        $stocks->description   = $description;
        $stocks->workspace     = getActiveWorkSpace();
        $stocks->created_by    = \Auth::user()->id;
        $stocks->save();
    }

    public static $rates;
    public static $data;

    public static function getTaxData()
    {
        $data = [];
        if (self::$rates == null) {
            $rates          =  \Workdo\ProductService\Entities\Tax::where('workspace_id', getActiveWorkSpace())->get();
            self::$rates    =  $rates;
            foreach (self::$rates as $rate) {
                $data[$rate->id]['name']        = $rate->name;
                $data[$rate->id]['rate']        = $rate->rate;
                $data[$rate->id]['created_by']  = $rate->created_by;
            }
            self::$data    =  $data;
        }
        return self::$data;
    }

    //quantity update in warehouse details
    public static function warehouse_quantity($type, $quantity, $product_id, $warehouse_id)
    {
        $product      = WarehouseProduct::where('warehouse_id', $warehouse_id)->where('product_id', $product_id)->first();
        if (isset($product->quantity) && !empty($product->quantity)) {
            $pro_quantity = (!empty($product) && !empty($product->quantity)) ? $product->quantity : 0;

            if ($type == 'minus') {
                $product->quantity = $pro_quantity != 0 ? $pro_quantity - $quantity : $quantity;
            } else {
                $product->quantity = $pro_quantity + $quantity;
            }
            $product->save();
        }
    }
}
