<?php

namespace Workdo\Account\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\CustomerCreditNotes;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\User;
use Workdo\Account\DataTables\CreditNoteDataTable;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\Customer;
use App\Traits\CreditDebitNoteBalance;
use Workdo\Account\Entities\CreditNote;
use Workdo\Account\Events\CreateCustomerCreditNote;
use Workdo\Account\Events\DestroyCustomerCreditNote;
use Workdo\Account\Events\UpdateCustomerCreditNote;

class CustomerCreditNotesController extends Controller
{
    use CreditDebitNoteBalance;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(CreditNoteDataTable $dataTable)
    {

        if(Auth::user()->isAbleTo('creditnote manage'))
        {
            return $dataTable->render('account::customerCreditNote.index');
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
        if(Auth::user()->isAbleTo('creditnote create'))
        {
            if(Auth::user()->type == 'company') {
                $invoices = Invoice::where('status', '!=' , 0)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('invoice_id', 'id');
            }
            else {
                $invoices = Invoice::where('status', '!=' , 0)->where('user_id',Auth::user()->id)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('invoice_id', 'id');
            }
            $statues = CustomerCreditNotes :: $statues;
            return view('account::customerCreditNote.create', compact('invoices','statues'));
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
        if(Auth::user()->isAbleTo('creditnote create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'invoice' => 'required|numeric',
                                   'amount' => 'required|numeric|gt:0',
                                   'date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $invoice_id = $request->invoice;
            $invoiceDue = Invoice::where('id', $invoice_id)->first();
            $creditAmount = floatval($request->amount);

            if($invoiceDue){

                $invoicePaid = $invoiceDue->getTotal() - $invoiceDue->getDue() - $invoiceDue->invoiceTotalCreditNote();

                $customerCreditNotes = CustomerCreditNotes::where('invoice',$invoice_id)->get()->sum('amount');

                if($creditAmount > $invoicePaid || ($customerCreditNotes + $creditAmount)  > $invoicePaid)
                {
                    return redirect()->back()->with('error', 'Maximum ' . currency_format_with_sym($invoicePaid-$customerCreditNotes) . ' credit limit of this invoice.');
                }
                    $credit                  = new CustomerCreditNotes();
                    $credit->credit_id       = $this->creditNoteNumber();
                    $credit->invoice         = $invoice_id;
                    $credit->invoice_product = $request->invoice_product;
                    $credit->customer        = 0;
                    $credit->date            = $request->date;
                    $credit->amount          = $creditAmount;
                    $credit->status          = 0;
                    $credit->description     = $request->description;
                    $credit->save();

                    event(new CreateCustomerCreditNote($request , $credit));
                    return redirect()->route('custom-credit.note')->with('success', __('Credit Note successfully created.'));
            }else{
                return redirect()->back()->with('error', __('The invoice field is required.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($invoice_id, $creditNote_id)
    {
        if(Auth::user()->isAbleTo('creditnote edit'))
        {
            if(Auth::user()->type == 'company') {
                $invoices = Invoice::where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('invoice_id', 'id');
            }
            else {
                $invoices = Invoice::where('user_id',Auth::user()->id)->where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->get()->pluck('invoice_id', 'id');
            }
            $creditNote = CustomerCreditNotes::find($creditNote_id);
            $statues = CustomerCreditNotes :: $statues;
            return view('account::customerCreditNote.edit', compact('creditNote','statues' , 'invoices'));
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
    public function update(Request $request, $invoice_id, $creditNote_id)
    {
        if(Auth::user()->isAbleTo('creditnote edit'))
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

            $invoiceDue    = Invoice::where('id', $invoice_id)->first();

            $credit        = CustomerCreditNotes::find($creditNote_id);

            $creditAmount  = floatval($request->amount);

            $invoicePaid   = $invoiceDue->getTotal() - $invoiceDue->getDue() - $invoiceDue->invoiceTotalCreditNote();

            $existingCredits = CustomerCreditNotes::where('invoice', $invoice_id)->where('id', '!=', $creditNote_id)->get()->sum('amount');

            if (($existingCredits + $creditAmount) > $invoicePaid) {
                return redirect()->back()->with('error', 'Maximum ' . currency_format_with_sym($invoicePaid - $existingCredits) . ' credit to this invoice.');
            }

            $credit->invoice_product = $request->invoice_product;
            $credit->date            = $request->date;
            $credit->amount          = $creditAmount;
            $credit->description     = $request->description;
            $credit->save();
            event(new UpdateCustomerCreditNote($request , $credit));

            return redirect()->back()->with('success', __('The credit note details are updated successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($invoice_id, $creditNote_id)
    {
        if(Auth::user()->isAbleTo('creditnote delete'))
        {
            $creditNote = CustomerCreditNotes::find($creditNote_id);

            if($creditNote->status == 0)
            {
                event(new DestroyCustomerCreditNote($creditNote));

                $creditNote->delete();

                return redirect()->back()->with('success', __('The credit note has been deleted.'));
            }
            else
            {
                $usedCreditNote = CreditNote::where('credit_note', $creditNote->id)
                ->pluck('invoice')
                ->unique();
                $invoice = Invoice::whereIn('id' , $usedCreditNote)->get()->pluck('invoice_id')->toarray();
                $formattedInvoices = array_map(function ($invoiceId) {
                    return Invoice::invoiceNumberFormat($invoiceId);
                }, $invoice);
                $invoiceId = implode(' , ' ,($formattedInvoices));
                
                return redirect()->back()->with('error', __('This credit note is already used in invoice ') .$invoiceId. __(', so it can not deleted.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getItems(Request $request)
    {
        $invoice = Invoice::find($request->invoice_id);
        $invoice_module = ['account','cmms' , 'rent' , 'sales' , 'mobileservice' , 'vehicleinspection' , 'machinerepair' , 'musicinstitute' , 'restaurantmenu'];
        if(in_array($invoice->invoice_module , $invoice_module)){
            $items = InvoiceProduct::select('invoice_products.*' , 'product_services.name as product_name')->join('product_services' ,  'product_services.id' , 'invoice_products.product_id')->where('invoice_id' , $request->invoice_id)->where('product_services.created_by',creatorId())->where('product_services.workspace_id' , getActiveWorkSpace())->get();        
            $getDue = $invoice->getTotal() - $invoice->getDue();
            return response()->json(['type' => 'withproduct' ,'items' => $items , 'getDue' => $getDue]);
        }
        else {
            $getDue = $invoice->getTotal() - $invoice->getDue();
            $amount = $invoice->getTotal();
            return response()->json(['type' => 'witoutproduct' ,'amount' => $amount , 'getDue' => $getDue]);
        }
    }

    public function getItemPrice(Request $request)
    {
        $invoiceProduct = InvoiceProduct::find($request->item_id);
        $totalPrice     = 0;
        if($invoiceProduct != null)
        {
            $product        = \Workdo\ProductService\Entities\ProductService::find($invoiceProduct->product_id);
            $taxRate        = !empty($product) ? (!empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0) : 0;
            $totalTax       = ($taxRate / 100) * (($invoiceProduct->price * $invoiceProduct->quantity) - $invoiceProduct->discount);
            $totalPrice     = (($invoiceProduct->price * $invoiceProduct->quantity) + $totalTax) - $invoiceProduct->discount;
        }
        
        return response()->json($totalPrice);
    }

    function creditNoteNumber()
    {
        $latest = CustomerCreditNotes::whereHas('invoices', function ($query) {
                    $query->where('workspace', getActiveWorkSpace());
                     })->with(['custom_customer','invoices'])->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->credit_id + 1;
        }
    }
}
