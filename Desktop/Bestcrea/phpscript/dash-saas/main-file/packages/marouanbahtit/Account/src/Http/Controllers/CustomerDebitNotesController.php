<?php

namespace Workdo\Account\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDebitNote;
use App\Models\PurchaseProduct;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Account\DataTables\DebitNoteDataTable;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\Bill;
use Workdo\Account\Entities\CustomerDebitNotes;
use Workdo\Account\Entities\Vender;
use App\Traits\CreditDebitNoteBalance;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\BillProduct;
use Workdo\Account\Entities\DebitNote;
use Workdo\Account\Events\CreateCustomerDebitNote;
use Workdo\Account\Events\DestroyCustomerDebitNote;
use Workdo\Account\Events\UpdateCustomerDebitNote;

class CustomerDebitNotesController extends Controller
{
    use CreditDebitNoteBalance;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(DebitNoteDataTable $dataTable)
    {
        if(Auth::user()->isAbleTo('debitnote manage'))
        {
            $debit_type = CustomerDebitNotes::$debit_type;
            return $dataTable->render('account::customerDebitNote.index',compact('debit_type'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if(Auth::user()->isAbleTo('debitnote create'))
        {
            if(Auth::user()->type == 'company') {
                $bills = Bill::where('status', '!=' , 0)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('bill_id', 'id');
                $purchases = Purchase::where('status', '!=' , 0)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('purchase_id', 'id');
            }
            else {
                $bills = Bill::where('status', '!=' , 0)->where('user_id',Auth::user()->id)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('bill_id', 'id');
                $purchases = Purchase::where('status', '!=' , 0)->where('user_id',Auth::user()->id)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('purchase_id', 'id');
            }
            

            $statues = CustomerDebitNotes :: $statues;
            return view('account::customerDebitNote.create', compact('bills','statues','purchases'));

        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if(\Auth::user()->isAbleTo('debitnote create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'amount' => 'required|numeric|gt:0',
                                   'date' => 'required|date_format:Y-m-d',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if($request->type == 'bill'){
                $bill_id = $request->bill;
                $billDue = Bill::where('id', $bill_id)->first();
            }
            else {
                $bill_id = $request->purchase;
                $billDue = Purchase::where('id', $bill_id)->first();
            }
            $debitAmount = floatval($request->amount);
            if($billDue){

                if($request->type == 'bill'){

                    $billPaid = $billDue->getTotal() - $billDue->getDue() - $billDue->billTotalDebitNote();
                    $customerDebitNotes = CustomerDebitNotes::where('bill',$bill_id)->where('type','bill')->get()->sum('amount');
                }
                else {

                    $billPaid = $billDue->getTotal() - $billDue->getDue() - $billDue->purchaseTotalDebitNote();
                    $customerDebitNotes = CustomerDebitNotes::where('bill',$bill_id)->where('type','purchase')->get()->sum('amount');
                }

                if($debitAmount > $billPaid || ($customerDebitNotes + $debitAmount)  > $billPaid)
                {
                    return redirect()->back()->with('error', 'Maximum ' . currency_format_with_sym($billPaid - $customerDebitNotes) . ' debit limit of this bill.');
                }
                    $debit               = new CustomerDebitNotes();
                    $debit->debit_id     = $this->debitNoteNumber();
                    $debit->bill         = $bill_id;
                    $debit->bill_product = $request->bill_product;
                    $debit->type         = $request->type;
                    $debit->vendor       = 0;
                    $debit->date         = $request->date;
                    $debit->amount       = $debitAmount;
                    $debit->status       = 0;
                    $debit->description  = $request->description;
                    $debit->save();

                    event(new CreateCustomerDebitNote($request , $debit));

                    return redirect()->back()->with('success', __('The debit note has been created successfully.'));
            }else{
                return redirect()->back()->with('error', __('The bill field is required.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('account::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($bill_id, $debitNote_id)
    {
        if(Auth::user()->isAbleTo('debitnote edit'))
        {
            if(Auth::user()->type == 'company') {
                $bills = Bill::where('status', '!=' , 0)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('bill_id', 'id');
                $purchases = Purchase::where('status', '!=' , 0)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('purchase_id', 'id');
            }
            else {
                $bills = Bill::where('status', '!=' , 0)->where('user_id',Auth::user()->id)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('bill_id', 'id');
                $purchases = Purchase::where('status', '!=' , 0)->where('user_id',Auth::user()->id)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('purchase_id', 'id');
            }
            $debitNote = CustomerDebitNotes::find($debitNote_id);
            $statues   = CustomerDebitNotes :: $statues;

            return view('account::customerDebitNote.edit', compact('debitNote','statues','bills','purchases'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $bill_id, $debitNote_id)
    {
        if(Auth::user()->isAbleTo('debitnote edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'amount' => 'required|numeric|gt:0',
                                   'date' => 'required|date_format:Y-m-d',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $debit = CustomerDebitNotes::find($debitNote_id);
            if($debit->type == 'bill'){

                $billDue        = Bill::where('id', $bill_id)->first();
                $debitAmount   = floatval($request->amount);
                $billPaid      = $billDue->getTotal() - $billDue->getDue() - $billDue->billTotalDebitNote();
            }else {
                $billDue      = Purchase::where('id', $bill_id)->first();
                $debitAmount  = floatval($request->amount);
                $billPaid     = $billDue->getTotal() - $billDue->getDue() - $billDue->purchaseTotalDebitNote();
            }

            $existingDebits = CustomerDebitNotes::where('bill', $bill_id)->where('id', '!=', $debitNote_id)->get()->sum('amount');

            if (($existingDebits + $debitAmount) > $billPaid) {
                return redirect()->back()->with('error', 'Maximum ' . currency_format_with_sym($billPaid - $existingDebits) . ' debit to this bill.');
            }

            $debit->bill_product = $request->bill_product;
            $debit->date         = $request->date;
            $debit->amount       = $request->amount;
            $debit->status       = $request->status;
            $debit->description  = $request->description;
            $debit->save();
            event(new UpdateCustomerDebitNote($request , $debit));

            return redirect()->back()->with('success', __('The debit note details are updated successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */

    public function destroy($bill_id, $debitNote_id)
    {
        if(Auth::user()->isAbleTo('debitnote delete'))
        {
            $debitNote = CustomerDebitNotes::find($debitNote_id);
            if($debitNote->status == 0)
            {
                event(new DestroyCustomerDebitNote($debitNote));

                $debitNote->delete();

                return redirect()->back()->with('success', __('The debit note has been deleted.'));
            }
            else
            {
                if($debitNote->type == 'bill')
                {
                    $usedDebitNote = DebitNote::where('debit_note', $debitNote->id)
                    ->pluck('bill')
                    ->unique();
                    $bill = Bill::whereIn('id' , $usedDebitNote)->get()->pluck('bill_id')->toarray();
                    $formattedBills = array_map(function ($billId) {
                        return Bill::billNumberFormat($billId);
                    }, $bill);
                    $billId = implode(' , ' ,($formattedBills));
                    return redirect()->back()->with('error', __('This debit note is already used in bill '). $billId.__(', so it can not deleted.'));
                }
                else {
                    $usedDebitNote = PurchaseDebitNote::where('debit_note', $debitNote->id)
                    ->pluck('purchase')
                    ->unique();
                    $purchase = Purchase::whereIn('id' , $usedDebitNote)->get()->pluck('purchase_id')->toarray();
                    $formattedPurchases = array_map(function ($purchaseId) {
                        return Purchase::purchaseNumberFormat($purchaseId);
                    }, $purchase);
                    $purchaseId = implode(' , ' ,($formattedPurchases));
                    return redirect()->back()->with('error', __('This debit note is already used in purchase '). $purchaseId.__(', so it can not deleted.'));
                }
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getItems(Request $request)
    {
        if($request->type == 'bill')
        {
            $bill = Bill::find($request->bill_id);
            if(($bill->bill_module == 'account')){
                $items = BillProduct::select('bill_products.*' , 'product_services.name as product_name')->join('product_services' , 'product_services.id' , 'bill_products.product_id')->where('bill_id' , $request->bill_id)->get();
                $getDue = $bill->getTotal() - $bill->getDue();
                return response()->json(['type' => 'withproduct' ,'items' => $items , 'getDue' => $getDue]);
            }
            else {
                $amount = $bill->getTotal();
                $getDue = $bill->getTotal() - $bill->getDue();
                return response()->json(['type' => 'witoutproduct' ,'amount' => $amount , 'getDue' => $getDue]);
            }
        }
        else
        {
            $purchase = Purchase::find($request->bill_id);
            $items = PurchaseProduct::select('purchase_products.*' , 'product_services.name as product_name')->join('product_services' , 'product_services.id' , 'purchase_products.product_id')->where('purchase_id' , $request->bill_id)->get();
            $getDue = $purchase->getTotal() - $purchase->getDue();

            return response()->json(['type' => 'withproduct' ,'items' => $items , 'getDue' => $getDue]);
            
        }
    }

    public function getItemPrice(Request $request)
    {
        if($request->type == 'bill') {
            $billProduct = BillProduct::find($request->item_id);
        }
        else {
            $billProduct = PurchaseProduct::find($request->item_id);
        }
        $totalPrice     = 0;
        if($billProduct != null)
        {
            $product        = \Workdo\ProductService\Entities\ProductService::find($billProduct->product_id);
            $taxRate        = !empty($product) ? (!empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0) : 0;
            $totalTax       = ($taxRate / 100) * (($billProduct->price * $billProduct->quantity) - $billProduct->discount);
            $totalPrice     = (($billProduct->price * $billProduct->quantity) + $totalTax) - $billProduct->discount;
        }
        return response()->json($totalPrice);
    }

    function debitNoteNumber()
    {
        $latest = CustomerDebitNotes::with('custom_vendor')->select('customer_debit_notes.*')
                ->leftJoin('bills', 'customer_debit_notes.bill', '=', 'bills.id')
                ->leftJoin('purchases', 'customer_debit_notes.bill', '=', 'purchases.id')
                ->where('bills.workspace', getActiveWorkSpace())->orWhere('purchases.workspace', getActiveWorkSpace())->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->debit_id + 1;
        }
    }
}
