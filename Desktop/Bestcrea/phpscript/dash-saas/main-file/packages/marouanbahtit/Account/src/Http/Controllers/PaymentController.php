<?php

namespace Workdo\Account\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\BankAccount;
use Workdo\Account\Entities\BillPayment;
use Workdo\Account\Entities\Payment;
use Workdo\Account\Entities\Transaction;
use Workdo\Account\Entities\Transfer;
use Workdo\Account\Entities\Vender;
use App\Models\EmailTemplate;
use Workdo\Account\DataTables\PaymentDataTable;
use Workdo\Account\Events\CreatePayment;
use Workdo\Account\Events\DestroyPayment;
use Workdo\Account\Events\UpdatePayment;

// use Workdo\ProductService\Entities\Category;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(PaymentDataTable $dataTable)
    {

        if(Auth::user()->isAbleTo('expense payment manage'))
        {
            $vendor = Vender::where('workspace', '=',getActiveWorkSpace())->get()->pluck('name', 'id');

            $account = BankAccount::where('workspace',getActiveWorkSpace())->get()->pluck('holder_name', 'id');

            $category=[];
            if(module_is_active('ProductService'))
            {
                $category = \Workdo\ProductService\Entities\Category::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->where('type', 2)->get()->pluck('name', 'id');

            }

            return $dataTable->render('account::payment.index',compact('account', 'category', 'vendor'));


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
        if(\Auth::user()->isAbleTo('expense payment create'))
        {
            $vendors = Vender::where('workspace', '=',getActiveWorkSpace())->get()->pluck('name', 'id');
            $categories=[];
            if(module_is_active('ProductService'))
            {
                $categories = \Workdo\ProductService\Entities\Category::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->where('type','2')->get()->pluck('name', 'id');
            }
            $accounts   = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            return view('account::payment.create', compact('vendors', 'categories', 'accounts'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('expense payment create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required|date_format:Y-m-d',
                                   'amount' => 'required|gte:0',
                                   'account_id' => 'required',
                                   'vendor_id' => 'required',
                                   'category_id' => 'required',
                                   'reference' => 'required',
                                   'description' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $payment                 = new Payment();
            $payment->date           = $request->date;
            $payment->amount         = $request->amount;
            $payment->account_id     = $request->account_id;
            $payment->vendor_id      = $request->vendor_id;
            $payment->category_id    = $request->category_id;
            $payment->payment_method = 0;
            $payment->reference      = $request->reference;
            $payment->description    = $request->description;
            if(!empty($request->add_receipt))
            {
                $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                $uplaod = upload_file($request,'add_receipt',$fileName,'payment');
                if($uplaod['flag'] == 1)
                {
                    $url = $uplaod['url'];
                }
                else{
                    return redirect()->back()->with('error',$uplaod['msg']);
                }

                $payment->add_receipt = $url;
            }
            $payment->workspace      = getActiveWorkSpace();
            $payment->created_by     = creatorId();
            $payment->save();

            $category            = \Workdo\ProductService\Entities\Category::where('id', $request->category_id)->first();
            $payment->payment_id = $payment->id;
            $payment->type       = 'Payment';
            $payment->category   = $category->name;
            $payment->user_id    = $payment->vendor_id;
            $payment->user_type  = 'Vendor';
            $payment->account    = $request->account_id;

            Transaction::addTransaction($payment);

            $vendor          = Vender::where('id', $request->vendor_id)->first();
            $bill_payment    = new BillPayment();
            $bill_payment->name   = !empty($vendor) ? $vendor['name'] : '';
            $bill_payment->method = '-';
            $bill_payment->date   = company_date_formate($request->date);
            $bill_payment->amount = currency_format_with_sym($request->amount);
            $bill_payment->bill   = '';

            Transfer::bankAccountBalance($request->account_id, $request->amount, 'debit');

            event(new CreatePayment($request,$bill_payment,$payment));

            if(!empty($vendor))
            {
                AccountUtility::updateUserBalance('vendor', $vendor->id, $request->amount, 'credit');
                if(!empty(company_setting('Bill Payment Create')) && company_setting('Bill Payment Create')  == true)
                {
                    $uArr = [
                        'payment_name' => $bill_payment->name,
                        'payment_bill' => $bill_payment->bill,
                        'payment_amount' => $bill_payment->amount,
                        'payment_date' => $bill_payment->date,
                        'payment_method'=> $bill_payment->method
                    ];
                    try
                    {
                        $resp = EmailTemplate::sendEmailTemplate('Bill Payment Create', [$vendor->id => $vendor->email], $uArr);
                    }
                    catch (\Exception $e) {
                        $resp['error'] = $e->getMessage();
                    }
                    return redirect()->route('payment.index')->with('success', __('The payment has been created successfully.') . ((isset($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                }
            }

            return redirect()->route('payment.index')->with('success', __('The payment has been created successfully.'));
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
        return redirect()->back();
        return view('account::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Payment $payment)
    {
        if(Auth::user()->isAbleTo('expense payment edit'))
        {
            $vendors = Vender::where('workspace', '=',getActiveWorkSpace())->get()->pluck('name', 'id');

            $categories=[];
            if(module_is_active('ProductService'))
            {
                $categories = \Workdo\ProductService\Entities\Category::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            }
            $accounts   = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            return view('account::payment.edit', compact('vendors', 'categories', 'accounts', 'payment'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Payment $payment)
    {
        if(Auth::user()->isAbleTo('expense payment edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                    'date' => 'required|date_format:Y-m-d',
                                    'amount' => 'required|gte:0',
                                    'account_id' => 'required',
                                    'vendor_id' => 'required',
                                    'category_id' => 'required',
                                    'reference' => 'required',
                                    'description' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $vendor = Vender::where('id', $request->vendor_id)->first();
            if(!empty($vendor))
            {
                AccountUtility::updateUserBalance('vendor', $vendor->id, $payment->amount, 'debit');
            }
            Transfer::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

            $payment->date           = $request->date;
            $payment->amount         = $request->amount;
            $payment->account_id     = $request->account_id;
            $payment->vendor_id      = $request->vendor_id;
            $payment->category_id    = $request->category_id;
            $payment->payment_method = 0;
            $payment->reference      = $request->reference;
            $payment->description    = $request->description;
            if(!empty($request->add_receipt))
            {
                if(!empty($payment->add_receipt))
                {
                    try
                    {
                        delete_file($payment->add_receipt);
                    }
                catch (Exception $e)
                    {

                    }
                }

                $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                $uplaod = upload_file($request,'add_receipt',$fileName,'payment');
                if($uplaod['flag'] == 1)
                {
                    $url = $uplaod['url'];
                }
                else{
                    return redirect()->back()->with('error',$uplaod['msg']);
                }

                $payment->add_receipt = $url;
            }
            $payment->save();

            $category            = \Workdo\ProductService\Entities\Category::where('id', $request->category_id)->first();
            $payment->category   = $category->name;
            $payment->payment_id = $payment->id;
            $payment->type       = 'Payment';
            Transaction::editTransaction($payment);
            if(!empty($vendor))
            {
                AccountUtility::updateUserBalance('vendor', $vendor->id, $request->amount, 'credit');
            }

            Transfer::bankAccountBalance($request->account_id, $request->amount, 'debit');

            if(module_is_active('DoubleEntry'))
            {
                $request->merge(['id'=>$payment->id]);
            }

            event(new UpdatePayment($request,$payment));
            return redirect()->back()->with('success', __('The payment details are updated successfully.'));
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
    public function destroy(Payment $payment)
    {
        if(Auth::user()->isAbleTo('expense payment delete'))
        {
            if($payment->workspace == getActiveWorkSpace())
            {
                if(!empty($payment->add_receipt))
                {
                    try
                    {
                        delete_file($payment->add_receipt);
                    }
                    catch (Exception $e)
                    {
                        //
                    }
                }

                Transaction::destroyTransaction($payment->id, 'Vendor');

                if($payment->vendor_id != 0)
                {
                    AccountUtility::updateUserBalance('vendor', $payment->vendor_id, $payment->amount, 'debit');
                }
                Transfer::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

                event(new DestroyPayment($payment));
                $payment->delete();

                return redirect()->route('payment.index')->with('success', __('The payment has been deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function description($id)
    {
        $payment = Payment::find($id);
        return view('account::payment.description', compact('payment'));
    }
}
