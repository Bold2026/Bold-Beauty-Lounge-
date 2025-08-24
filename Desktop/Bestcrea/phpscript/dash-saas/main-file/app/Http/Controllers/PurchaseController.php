<?php

namespace App\Http\Controllers;

use App\DataTables\PurchaseDataTable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Purchase;
use App\Models\Warehouse;
use App\Models\PurchaseProduct;
use App\Models\PurchasePayment;
use Illuminate\Support\Facades\Crypt;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Setting;
use App\Models\PosUtility;
use Illuminate\Support\Facades\Auth;
use App\Models\WarehouseProduct;
use App\Events\CreatePaymentPurchase;
use App\Events\CreatePurchase;
use App\Events\DestroyPurchase;
use App\Events\PaymentDestroyPurchase;
use App\Events\ResentPurchase;
use App\Events\SentPurchase;
use App\Events\UpdatePurchase;
use Illuminate\Support\Facades\File;
use App\Models\PurchaseAttachment;
use App\Models\PurchaseDebitNote;
use Illuminate\Support\Facades\DB;
use Workdo\ProductService\Entities\ProductService;
use Illuminate\Support\Facades\Validator;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\BankAccount;
use Workdo\Account\Entities\CustomerDebitNotes;
use Workdo\Account\Entities\Transaction;
use Workdo\Account\Entities\Transfer;
use Workdo\Account\Events\DestroyPurchaseProduct;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(PurchaseDataTable $dataTable)
    {
        if (\Auth::user()->isAbleTo('purchase manage')) {
            $vender = [];
            if (module_is_active('Account')) {
                $vender = \Workdo\Account\Entities\Vender::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
                $vender->prepend('Select Vendor', '');
            }
            $status = Purchase::$statues;

            return $dataTable->render('purchases.index', compact('status', 'vender'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create($vendorId)
    {
        if (\Auth::user()->isAbleTo('purchase create')) {
            if (module_is_active('ProductService')) {
                $category = [];
                $projects = [];
                if (module_is_active('ProductService')) {
                    $category = \Workdo\ProductService\Entities\Category::where('created_by', creatorId())->where('workspace_id', getActiveWorkSpace())->where('type', 2)->get()->pluck('name', 'id');
                    $category->prepend('Select Category', '');
                }

                if (module_is_active('Taskly')) {
                    if (module_is_active('ProductService')) {
                        $taxs = \Workdo\ProductService\Entities\Tax::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                    }
                    $projects = \Workdo\Taskly\Entities\Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', Auth::user()->id)->where('workspace', getActiveWorkSpace())->projectonly()->get()->pluck('name', 'id');
                }
                if (module_is_active('CustomField')) {
                    $customFields = \Workdo\CustomField\Entities\CustomField::where('workspace_id', getActiveWorkSpace())->where('module', '=', 'pos')->where('sub_module', 'purchase')->get();
                } else {
                    $customFields = null;
                }

                $purchase_number = Purchase::purchaseNumberFormat($this->purchaseNumber());

                $venders = [];
                if (module_is_active('Account')) {

                    $venders = User::where('type', 'vendor')->where('created_by', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                    $venders->prepend('Select Vendor', '');
                }

                $warehouse = Warehouse::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
                $warehouse->prepend('Select Warehouse', '');

                $product_services = [];
                $product_type = [];
                if (module_is_active('ProductService')) {
                    $product_services = \Workdo\ProductService\Entities\ProductService::where('created_by', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                    $product_services->prepend('Select Items', '');
                    $product_type = \Workdo\ProductService\Entities\ProductService::$product_type;
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            return view('purchases.create', compact('venders', 'purchase_number', 'product_services', 'category', 'vendorId', 'warehouse', 'customFields', 'product_type', 'projects'));
        } else {
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
        if (\Auth::user()->isAbleTo('purchase create')) {
            if ($request->purchase_type == "product") {
                if (module_is_active('Account')) {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'vender_id' => 'required',
                            'warehouse_id' => 'required',
                            'purchase_date' => 'required',
                            'category_id' => 'required',
                            'items' => 'required',
                        ]
                    );
                } elseif (!empty($request->vender_name)) {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'vender_name' => 'required',
                            'warehouse_id' => 'required',
                            'purchase_date' => 'required',
                            'category_id' => 'required',
                            'items' => 'required',
                        ]
                    );
                }
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $vender = \Workdo\Account\Entities\Vender::where('user_id', $request->vender_id)->first();
                if (empty($vender)) {
                    $user = User::find($request->vender_id);
                }

                $purchase = new Purchase();
                $purchase->purchase_id      = $this->purchaseNumber();
                $purchase->vender_id        = !empty($vender) ?  $vender->id : null;
                $purchase->user_id          = $vender->user_id ?? $user->id;
                $purchase->vender_name      = !empty($request->vender_name) ? $request->vender_name : '';
                $purchase->account_type     = $request->account_type;
                $purchase->warehouse_id     = $request->warehouse_id;
                $purchase->purchase_date    = $request->purchase_date;
                $purchase->purchase_number  = !empty($request->purchase_number) ? $request->purchase_number : 0;
                $purchase->status           = 0;
                $purchase->category_id      = $request->category_id;
                $purchase->purchase_module  = 'account';
                $purchase->workspace        = getActiveWorkSpace();
                $purchase->created_by       = creatorId();
                $purchase->save();

                if (module_is_active('CustomField')) {
                    \Workdo\CustomField\Entities\CustomField::saveData($purchase, $request->customField);
                }

                event(new CreatePurchase($request, $purchase));
                $products = $request->items;
                for ($i = 0; $i < count($products); $i++) {
                    $purchaseProduct = new PurchaseProduct();
                    $purchaseProduct->purchase_id = $purchase->id;
                    $purchaseProduct->product_type = $products[$i]['product_type'];
                    $purchaseProduct->product_id = $products[$i]['item'];
                    $purchaseProduct->quantity = $products[$i]['quantity'];
                    $purchaseProduct->tax = $products[$i]['tax'];
                    $purchaseProduct->discount = $products[$i]['discount'];
                    $purchaseProduct->price = $products[$i]['price'];
                    $purchaseProduct->description = str_replace(array('\'', '"', '`', '{', "\n"), ' ', $products[$i]['description']);
                    $purchaseProduct->workspace = getActiveWorkSpace();

                    $purchaseProduct->save();
                    //inventory management (Quantity)
                    Purchase::total_quantity('plus', $purchaseProduct->quantity, $purchaseProduct->product_id);

                    //Product Stock Report
                    if (module_is_active('Account')) {
                        $type = 'Purchase';
                        $type_id = $purchase->id;
                        $description = $products[$i]['quantity'] . '  ' . __(' quantity add in purchase') . ' ' . Purchase::purchaseNumberFormat($purchase->purchase_id);
                        Purchase::addProductStock($products[$i]['item'], $products[$i]['quantity'], $type, $description, $type_id);
                    }

                    //Warehouse Stock Report
                    if (isset($products[$i]['item'])) {
                        Purchase::addWarehouseStock($products[$i]['item'], $products[$i]['quantity'], $request->warehouse_id);
                    }
                }


                return redirect()->route('purchases.index', $purchase->id)->with('success', __('The purchase has been created successfully'));
            } else if ($request->purchase_type == "project") {
                if (module_is_active('Account')) {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'vender_id' => 'required',
                            'warehouse_id' => 'required',
                            'purchase_date' => 'required',
                            'category_id' => 'required',
                            'items' => 'required',
                        ]
                    );
                } elseif (!empty($request->vender_name)) {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'vender_name' => 'required',
                            'warehouse_id' => 'required',
                            'purchase_date' => 'required',
                            'category_id' => 'required',
                            'items' => 'required',
                        ]
                    );
                }
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                if (!empty($request->vender_id)) {
                    $vender = \Workdo\Account\Entities\Vender::where('user_id', $request->vender_id)->first();
                }
                if (empty($vender)) {
                    $user = User::find($request->vender_id);
                }
                $purchase = new Purchase();
                $purchase->purchase_id      = $this->purchaseNumber();
                $purchase->vender_id        = !empty($vender) ?  $vender->id : null;
                $purchase->user_id          = $vender->user_id ?? $user->id;
                $purchase->vender_name      = !empty($request->vender_name) ? $request->vender_name : '';
                $purchase->account_type     = $request->account_type;
                $purchase->warehouse_id     = $request->warehouse_id;
                $purchase->purchase_date    = $request->purchase_date;
                $purchase->purchase_number  = !empty($request->purchase_number) ? $request->purchase_number : 0;
                $purchase->status           = 0;
                $purchase->category_id      = $request->category_id;
                $purchase->purchase_module  = 'taskly';
                $purchase->workspace        = getActiveWorkSpace();
                $purchase->created_by       = creatorId();
                $purchase->save();

                if (module_is_active('CustomField')) {
                    \Workdo\CustomField\Entities\CustomField::saveData($purchase, $request->customField);
                }

                event(new CreatePurchase($request, $purchase));
                $products = $request->items;
                for ($i = 0; $i < count($products); $i++) {
                    $purchaseProduct = new PurchaseProduct();
                    $purchaseProduct->purchase_id = $purchase->id;
                    $purchaseProduct->product_type = $products[$i]['product_type'];
                    $purchaseProduct->product_id = $products[$i]['item'];
                    $purchaseProduct->quantity = $products[$i]['quantity'];
                    $purchaseProduct->tax = $products[$i]['tax'];
                    $purchaseProduct->discount = $products[$i]['discount'];
                    $purchaseProduct->price = $products[$i]['price'];
                    $purchaseProduct->description = str_replace(array('\'', '"', '`', '{', "\n"), ' ', $products[$i]['description']);
                    $purchaseProduct->workspace = getActiveWorkSpace();

                    $purchaseProduct->save();
                    //inventory management (Quantity)
                    Purchase::total_quantity('plus', $purchaseProduct->quantity, $purchaseProduct->product_id);

                    //Product Stock Report
                    if (module_is_active('Account')) {
                        $type = 'Purchase';
                        $type_id = $purchase->id;
                        $description = $products[$i]['quantity'] . '  ' . __(' quantity add in purchase') . ' ' . Purchase::purchaseNumberFormat($purchase->purchase_id);
                        Purchase::addProductStock($products[$i]['item'], $products[$i]['quantity'], $type, $description, $type_id);
                    }

                    //Warehouse Stock Report
                    if (isset($products[$i]['item'])) {
                        Purchase::addWarehouseStock($products[$i]['item'], $products[$i]['quantity'], $request->warehouse_id);
                    }
                }
                return redirect()->route('purchases.index', $purchase->id)->with('success', __('The purchase has been created successfully'));
            } else if ($request->purchase_type == "parts") {
                if (module_is_active('CMMS')) {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'vender_id' => 'required',
                            'warehouse_id' => 'required',
                            'purchase_date' => 'required',
                            'category_id' => 'required',
                            'items' => 'required',
                        ]
                    );
                } elseif (!empty($request->vender_name)) {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'vender_name' => 'required',
                            'warehouse_id' => 'required',
                            'purchase_date' => 'required',
                            'category_id' => 'required',
                            'items' => 'required',
                        ]
                    );
                }
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                if (!empty($request->vender_id)) {
                    $vender = \Workdo\Account\Entities\Vender::where('user_id', $request->vender_id)->first();
                }
                if (empty($vender)) {
                    $user = User::find($request->vender_id);
                }
                $purchase = new Purchase();
                $purchase->purchase_id      = $this->purchaseNumber();
                $purchase->vender_id        = !empty($vender) ?  $vender->id : null;
                $purchase->user_id          = $vender->user_id ?? $user->id;
                $purchase->vender_name      = !empty($request->vender_name) ? $request->vender_name : '';
                $purchase->account_type     = $request->account_type;
                $purchase->warehouse_id     = $request->warehouse_id;
                $purchase->purchase_date    = $request->purchase_date;
                $purchase->purchase_number  = !empty($request->purchase_number) ? $request->purchase_number : 0;
                $purchase->status           = 0;
                $purchase->category_id      = $request->category_id;
                $purchase->purchase_module  = 'cmms';
                $purchase->workspace        = getActiveWorkSpace();
                $purchase->created_by       = creatorId();
                $purchase->save();

                if (module_is_active('CustomField')) {
                    \Workdo\CustomField\Entities\CustomField::saveData($purchase, $request->customField);
                }

                event(new CreatePurchase($request, $purchase));
                $products = $request->items;
                for ($i = 0; $i < count($products); $i++) {
                    $purchaseProduct = new PurchaseProduct();
                    $purchaseProduct->purchase_id = $purchase->id;
                    $purchaseProduct->product_type = $products[$i]['product_type'];
                    $purchaseProduct->product_id = $products[$i]['item'];
                    $purchaseProduct->quantity = $products[$i]['quantity'];
                    $purchaseProduct->tax = $products[$i]['tax'];
                    $purchaseProduct->discount = $products[$i]['discount'];
                    $purchaseProduct->price = $products[$i]['price'];
                    $purchaseProduct->description = str_replace(array('\'', '"', '`', '{', "\n"), ' ', $products[$i]['description']);
                    $purchaseProduct->workspace = getActiveWorkSpace();

                    $purchaseProduct->save();
                    //inventory management (Quantity)
                    Purchase::total_quantity('plus', $purchaseProduct->quantity, $purchaseProduct->product_id);

                    //Product Stock Report
                    if (module_is_active('Account')) {
                        $type = 'Purchase';
                        $type_id = $purchase->id;
                        $description = $products[$i]['quantity'] . '  ' . __(' quantity add in purchase') . ' ' . Purchase::purchaseNumberFormat($purchase->purchase_id);
                        Purchase::addProductStock($products[$i]['item'], $products[$i]['quantity'], $type, $description, $type_id);
                    }

                    //Warehouse Stock Report
                    if (isset($products[$i]['item'])) {
                        Purchase::addWarehouseStock($products[$i]['item'], $products[$i]['quantity'], $request->warehouse_id);
                    }
                }
                return redirect()->route('purchases.index', $purchase->id)->with('success', __('The purchase has been created successfully'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($ids)
    {
        if (\Auth::user()->isAbleTo('purchase show'))
        {
            try {
                $id = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Purchase Not Found.'));
            }

            $purchase = Purchase::find($id);

            if (!empty($purchase) && $purchase->created_by == creatorId() && $purchase->workspace == getActiveWorkSpace()) {
                $company_settings = getCompanyAllSetting();
                $purchasePayment = PurchasePayment::where('purchase_id', $purchase->id)->first();
                $vendor = [];
                if (module_is_active('Account')) {
                    $vendor = \Workdo\Account\Entities\Vender::where('user_id', $purchase->user_id)->where('workspace', getActiveWorkSpace())->first();
                }
                if (!empty($vendor)) {
                    $vendor->model = 'Customer';
                } else {
                    $vendor = $purchase->user;
                    if (!empty($vendor)) {
                        $vendor->model = 'User';
                    }
                }
                $iteams = $purchase->itemswithproduct;
                if (module_is_active('CustomField')) {
                    $purchase->customField = \Workdo\CustomField\Entities\CustomField::getData($purchase, 'pos', 'purchase');
                    $customFields = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'pos')->where('sub_module', 'purchase')->get();
                } else {
                    $customFields = null;
                }

                $purchase_attachment = PurchaseAttachment::where('purchase_id', $purchase->id)->get();

                return view('purchases.view', compact('purchase', 'vendor', 'iteams', 'purchasePayment', 'customFields', 'purchase_attachment', 'company_settings'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($idsd)
    {
        if (module_is_active('ProductService')) {
            if (\Auth::user()->isAbleTo('purchase edit')) {
                try {
                    $idwww = Crypt::decrypt($idsd);
                } catch (\Throwable $th) {
                    return redirect()->back()->with('error', __('Purchase Not Found.'));
                }
                $purchase = Purchase::find($idwww);
                $category = \Workdo\ProductService\Entities\Category::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->where('type', 2)->get()->pluck('name', 'id');
                $category->prepend('Select Category', '');
                $warehouse = Warehouse::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

                $purchase_number = Purchase::purchaseNumberFormat($purchase->purchase_id);
                $venders = [];
                if (module_is_active('Account')) {
                    $venders = User::where('type', 'vendor')->where('created_by', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                }
                $product_services = \Workdo\ProductService\Entities\ProductService::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');

                if (module_is_active('CustomField')) {
                    $purchase->customField = \Workdo\CustomField\Entities\CustomField::getData($purchase, 'pos', 'purchase');
                    $customFields = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'pos')->where('sub_module', 'purchase')->get();
                } else {
                    $customFields = null;
                }
                $product_type = \Workdo\ProductService\Entities\ProductService::$product_type;

                return view('purchases.edit', compact('venders', 'product_services', 'purchase', 'warehouse', 'purchase_number', 'category', 'customFields', 'product_type'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Please Enable Product & Service Module'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */

    public function update(Request $request, Purchase $purchase)
    {
        if (\Auth::user()->isAbleTo('purchase edit')) {
            if ($purchase->created_by == creatorId() && $purchase->workspace == getActiveWorkSpace()) {
                if (!empty($request->vender_name)) {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'vender_name' => 'required',
                            'purchase_date' => 'required',
                            'items' => 'required',
                        ]
                    );
                } elseif (!empty($request->vender_id)) {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'vender_id' => 'required',
                            'purchase_date' => 'required',
                            'items' => 'required',
                        ]
                    );
                }
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('purchases.index')->with('error', $messages->first());
                }
                if (module_is_active('Signature')) {
                    if ($purchase->vender_id != $request->vender_id) {
                        $purchase->vendor_signature = NULL;
                    }
                }

                $vender = \Workdo\Account\Entities\Vender::where('user_id', $request->vender_id)->first();

                if (!empty($vender)) {
                    $purchase->vender_id = !empty($vender) ?  $vender->id : null;
                    $purchase->vender_name = NULL;
                } else {
                    $purchase->vender_name = $request->vender_name;
                    $purchase->vender_id = 0;
                }

                if (empty($vender)) {
                    $user = User::find($request->vender_id);
                }
                $purchase->user_id        = $vender->user_id ?? $user->id;
                $purchase->purchase_date  = $request->purchase_date;
                $purchase->category_id    = $request->category_id;
                $purchase->save();

                $products = $request->items;

                if (module_is_active('CustomField')) {
                    \Workdo\CustomField\Entities\CustomField::saveData($purchase, $request->customField);
                }

                for ($i = 0; $i < count($products); $i++) {
                    $purchaseProduct = PurchaseProduct::find($products[$i]['id']);
                    if ($purchaseProduct == null) {
                        $purchaseProduct = new PurchaseProduct();
                        $purchaseProduct->purchase_id = $purchase->id;
                        Purchase::total_quantity('plus', $products[$i]['quantity'], $products[$i]['item']);

                        //Product Stock Report
                        if (module_is_active('Account')) {
                            $type = 'Purchase';
                            $type_id = $purchase->id;
                            $description = $products[$i]['quantity'] . '  ' . __(' quantity add in purchase') . ' ' . Purchase::purchaseNumberFormat($purchase->purchase_id);
                            if (empty($products[$i]['id'])) {
                                Purchase::addProductStock($products[$i]['item'], $products[$i]['quantity'], $type, $description, $type_id);
                            }
                        }

                        $old_qty = 0;
                    } else {
                        $old_qty = $purchaseProduct->quantity;
                        Purchase::total_quantity('minus', $purchaseProduct->quantity, $purchaseProduct->product_id);
                        //Product Stock Report
                        if (module_is_active('Account')) {
                            $type = 'Purchase';
                            $type_id = $purchase->id;
                            \Workdo\Account\Entities\StockReport::where('type', '=', 'purchase')->where('type_id', '=', $purchase->id)->where('product_id', $products[$i]['item'])->delete();
                            $description = $products[$i]['quantity'] . '  ' . __(' quantity add in purchase') . ' ' . Purchase::purchaseNumberFormat($purchase->purchase_id);
                            if (!empty($products[$i]['id'])) {
                                Purchase::addProductStock($products[$i]['item'], $products[$i]['quantity'], $type, $description, $type_id);
                            }
                        }
                    }
                    //inventory management (Quantity)
                    if (isset($products[$i]['item'])) {
                        $purchaseProduct->product_id = $products[$i]['item'];
                    }
                    $purchaseProduct->product_type = $products[$i]['product_type'];
                    $purchaseProduct->quantity = $products[$i]['quantity'];
                    $purchaseProduct->tax = $products[$i]['tax'];
                    $purchaseProduct->discount = $products[$i]['discount'];
                    $purchaseProduct->price = $products[$i]['price'];
                    $purchaseProduct->description = str_replace(array('\'', '"', '`', '{', "\n"), ' ', $products[$i]['description']);
                    $purchaseProduct->save();
                    //inventory management (Quantity)
                    if ($products[$i]['id'] > 0) {
                        Purchase::total_quantity('plus', $products[$i]['quantity'], $purchaseProduct->product_id);
                    }

                    //Warehouse Stock Report
                    $new_qty = $purchaseProduct->quantity;
                    $total_qty = $new_qty - $old_qty;
                    if (isset($products[$i]['item'])) {

                        Purchase::addWarehouseStock($products[$i]['item'], $total_qty, $request->warehouse_id);
                    }
                }
                event(new UpdatePurchase($request, $purchase));

                return redirect()->route('purchases.index')->with('success', __('The purchase details are updated successfully'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Purchase $purchase)
    {
        if (\Auth::user()->isAbleTo('purchase delete')) {

            $purchaseDebitNote = CustomerDebitNotes::where('bill',$purchase->id)->where('type' , 'purchase')->first();

            if(!$purchaseDebitNote) {
                if ($purchase->created_by == creatorId() && $purchase->workspace == getActiveWorkSpace()) {
                    $purchase_products = PurchaseProduct::where('purchase_id', $purchase->id)->get();
                    $purchase_payments = PurchasePayment::where('purchase_id', '=', $purchase->id)->get();
                    $getDue = $purchase->getTotal() - $purchase->getDue();
                    AccountUtility::updateUserBalance('vendor', $purchase->vendor_id, $getDue, 'debit');
                    foreach ($purchase_payments as $purchase_payment) {
                        $account = BankAccount::where(['created_by' => $purchase->created_by, 'workspace' => $purchase->workspace])->select('id')->first();
                        $account_id = $purchase_payment->account_id == 0 ? $account->id : $purchase_payment->account_id;
                        Transfer::bankAccountBalance($account_id, $purchase_payment->amount, 'credit');

                        delete_file($purchase_payment->add_receipt);
                        $purchase_payment->delete();
                    }

                    foreach ($purchase_products as $purchase_product) {
                        $warehouse_qty = WarehouseProduct::where('warehouse_id', $purchase->warehouse_id)->where('product_id', $purchase_product->product_id)->first();
                        if (!empty($warehouse_qty)) {
                            $warehouse_qty->quantity = $warehouse_qty->quantity - $purchase_product->quantity;

                            if ($warehouse_qty->quantity > 0) {
                                $warehouse_qty->save();
                            } else {
                                $warehouse_qty->delete();
                            }
                        }
                        $product_qty = \Workdo\ProductService\Entities\ProductService::where('id', $purchase_product->product_id)->first();
                        if (!empty($product_qty)) {
                            $product_qty->quantity = $product_qty->quantity - $purchase_product->quantity;
                            $product_qty->save();
                        }

                        $purchase_product->delete();
                    }
                    if (module_is_active('CustomField')) {
                        $customFields = \Workdo\CustomField\Entities\CustomField::where('module', 'pos')->where('sub_module', 'warehouse')->get();
                        foreach ($customFields as $customField) {
                            $value = \Workdo\CustomField\Entities\CustomFieldValue::where('record_id', '=', $purchase->id)->where('field_id', $customField->id)->first();
                            if (!empty($value)) {

                                $value->delete();
                            }
                        }
                    }
                    PurchaseAttachment::where('purchase_id', $purchase->id)->delete();

                    PurchaseProduct::where('purchase_id', '=', $purchase->id)->delete();

                    event(new DestroyPurchase($purchase));
                    $purchase->delete();

                    return redirect()->back()->with('success', __('The purchase has been deleted'));
                } else {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
            }else{
                return redirect()->back()->with('error', __('A debit note has been created for this invoice, so it cannot be deleted.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    function purchaseNumber()
    {
        $latest = Purchase::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->purchase_id + 1;
    }
    public function product(Request $request)
    {
        $data['product'] = $product = \Workdo\ProductService\Entities\ProductService::find($request->product_id);
        $data['unit'] = !empty($product) ? ((!empty($product->unit())) ? $product->unit()->name : '') : '';
        $data['taxRate'] = $taxRate = !empty($product) ? (!empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0) : 0;
        $data['taxes'] = !empty($product) ? (!empty($product->tax_id) ? $product->tax($product->tax_id) : 0) : 0;
        $salePrice = !empty($product) ? $product->purchase_price : 0;
        $quantity = 1;
        $taxPrice = !empty($product) ? (($taxRate / 100) * ($salePrice * $quantity)) : 0;
        $data['totalAmount'] = !empty($product) ? ($salePrice * $quantity) : 0;
        return json_encode($data);
    }

    public function productDestroy(Request $request)
    {
        if (\Auth::user()->isAbleTo('purchase delete')) {

            $purchaseProduct = PurchaseProduct::where('id', '=', $request->id)->first();
            if (module_is_active('ProductService')) {
                Purchase::total_quantity('minus', $purchaseProduct->quantity, $purchaseProduct->product_id);
            }

            if (module_is_active('Account')) {
                //Product Stock Report
                $type = 'invoice';
                $type_id = $purchaseProduct->purchase_id;
                $purchase = Purchase::find($purchaseProduct->purchase_id);
                $description = $purchaseProduct->quantity . '  ' . __('quantity delete in purchase') . ' ' . Purchase::purchaseNumberFormat($purchase['purchase_id']);
                \Workdo\Account\Entities\AccountUtility::addProductStock($purchaseProduct->product_id, $purchaseProduct->quantity, $type, $description, $type_id);
            }

            //Warehouse Stock Report
            $product = ProductService::find($purchaseProduct->product_id);
            if (!empty($product) && !empty($product->warehouse_id)) {
                Purchase::warehouse_quantity('minus', $purchaseProduct->quantity, $purchaseProduct->product_id, $product->warehouse_id);
            }

            event(new DestroyPurchaseProduct($request, $purchaseProduct));

            $purchaseProduct->delete();

            return redirect()->back()->with('success', __('The purchase product has been deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function sent($id)
    {
        if (\Auth::user()->isAbleTo('purchase send')) {
            $purchase = Purchase::where('id', $id)->first();
            $purchase->send_date = date('Y-m-d');
            $purchase->status = 1;
            $purchase->save();

            event(new SentPurchase($purchase));
            if (!empty($purchase->user_id != 0)) {
                $vender = \Workdo\Account\Entities\Vender::where('user_id', $purchase->vender_id)->first();
                if (empty($vender)) {
                    $vender = User::where('id', $purchase->user_id)->first();
                }

                $purchase->name = !empty($vender) ? $vender->name : '';
                $purchase->purchase = Purchase::purchaseNumberFormat($purchase->purchase_id);

                $purchaseId = Crypt::encrypt($purchase->id);
                $purchase->url = route('purchases.pdf', $purchaseId);
                if (!empty(company_setting('Purchase Send')) && company_setting('Purchase Send') == true) {
                    $uArr = [
                        'purchase_name' => $purchase->name,
                        'purchase_number' => $purchase->purchase,
                        'purchase_url' => $purchase->url,
                    ];
                    try {
                        $resp = EmailTemplate::sendEmailTemplate('Purchase Send', [$vender->id => $vender->email], $uArr);
                    } catch (\Exception $e) {
                        $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                    }
                    return redirect()->back()->with('success', __('Purchase successfully sent.') . ((isset($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                } else {

                    return redirect()->back()->with('error', __('Purchase sent notification is off'));
                }
            } else {
                return redirect()->back()->with('success', __('Purchase successfully sent.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function resent($id)
    {
        if (\Auth::user()->isAbleTo('purchase send')) {
            $purchase = Purchase::where('id', $id)->first();

            if (!empty($purchase->vender_id != 0)) {
                $vender = \Workdo\Account\Entities\Vender::where('user_id', $purchase->vender_id)->first();
                if (empty($vender)) {
                    $vender = User::where('id', $purchase->vender_id)->first();
                }

                $purchase->name = !empty($vender) ? $vender->name : '';
                $purchase->purchase = Purchase::purchaseNumberFormat($purchase->purchase_id);

                $purchaseId = Crypt::encrypt($purchase->id);
                $purchase->url = route('purchases.pdf', $purchaseId);

                if (!empty(company_setting('Purchase Send')) && company_setting('Purchase Send') == true) {
                    $uArr = [
                        'purchase_name' => $purchase->name,
                        'purchase_number' => $purchase->purchase,
                        'purchase_url' => $purchase->url,
                    ];
                    try {
                        $resp = EmailTemplate::sendEmailTemplate('Purchase Send', [$vender->id => $vender->email], $uArr);
                    } catch (\Exception $e) {
                        $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                    }
                }
                event(new ResentPurchase($purchase));
                return redirect()->back()->with('success', __('Purchase successfully sent.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
            } else {
                return redirect()->back()->with('success', __('Purchase successfully sent.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function purchase($purchase_id)
    {
        $purchaseId = Crypt::decrypt($purchase_id);

        $purchase = Purchase::where('id', $purchaseId)->first();

        $vendor = [];
        if (module_is_active('Account')) {
            $vendor = \Workdo\Account\Entities\Vender::where('user_id', $purchase->user_id)->where('workspace', $purchase->workspace)->first();
        }
        if (!empty($vendor)) {
            $vendor->model = 'Customer';
        } else {
            $vendor = $purchase->user;
            if (!empty($vendor)) {
                $vendor->model = 'User';
            }
        }

        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate = 0;
        $totalDiscount = 0;
        $taxesData = [];
        $items = [];

        foreach ($purchase->itemswithproduct as $product)    //->items
        {

            $item = new \stdClass();
            $item->product_type = !empty($product->product_type) ? $product->product_type : '';
            $item->name = !empty($product->product) ? $product->product->name : '';
            $item->quantity = $product->quantity;
            $item->tax = $product->tax;
            $item->discount = $product->discount;
            $item->price = $product->price;
            $item->description = $product->description;

            $totalQuantity += $item->quantity;
            $totalRate += $item->price;
            $totalDiscount += $item->discount;

            $taxes = Purchase::taxs($product->tax);
            $itemTaxes = [];
            if (!empty($item->tax)) {
                foreach ($taxes as $tax) {
                    $taxPrice = Purchase::taxRate($tax->rate, $item->price, $item->quantity, $item->discount);
                    $totalTaxPrice += $taxPrice;

                    $itemTax['name'] = $tax->name;
                    $itemTax['rate'] = $tax->rate . '%';
                    $itemTax['price'] = currency_format_with_sym($taxPrice, $purchase->created_by, $purchase->workspace);
                    $itemTaxes[] = $itemTax;


                    if (array_key_exists($tax->name, $taxesData)) {
                        $taxesData[$tax->name] = $taxesData[$tax->name] + $taxPrice;
                    } else {
                        $taxesData[$tax->name] = $taxPrice;
                    }
                }

                $item->itemTax = $itemTaxes;
            } else {
                $item->itemTax = [];
            }
            $items[] = $item;
        }

        $purchase->itemData = $items;
        $purchase->totalTaxPrice = $totalTaxPrice;
        $purchase->totalQuantity = $totalQuantity;
        $purchase->totalRate = $totalRate;
        $purchase->totalDiscount = $totalDiscount;
        $purchase->taxesData = $taxesData;
        if (module_is_active('CustomField')) {
            $purchase->customField = \Workdo\CustomField\Entities\CustomField::getData($purchase, 'pos', 'purchase');
            $customFields = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace($purchase->created_by, $purchase->workspace))->where('module', '=', 'pos')->where('sub_module', 'purchase')->get();
        } else {
            $customFields = null;
        }
        if ($purchase) {
            $company_settings = getCompanyAllSetting($purchase->created_by, $purchase->workspace);

            $color = isset($company_settings['purchase_color']) ? $company_settings['purchase_color'] : '';
            if ($color) {
                $color = $color;
            } else {
                $color = 'ffffff';
            }
            $color = '#' . $color;
            $font_color = User::getFontColor($color);

            $company_logo = get_file(sidebar_logo());

            $purchase_logo = isset($company_settings['purchase_logo']) ? $company_settings['purchase_logo'] : '';

            if (isset($purchase_logo) && !empty($purchase_logo)) {
                $img = get_file($purchase_logo);
            } else {
                $img = $company_logo;
            }
            $settings['site_rtl'] = isset($company_settings['site_rtl']) ? $company_settings['site_rtl'] : '';
            $settings['company_email'] = isset($company_settings['company_email']) ? $company_settings['company_email'] : '';
            $settings['company_telephone'] = isset($company_settings['company_telephone']) ? $company_settings['company_telephone'] : '';
            $settings['company_name'] = isset($company_settings['company_name']) ? $company_settings['company_name'] : '';
            $settings['company_address'] = isset($company_settings['company_address']) ? $company_settings['company_address'] : '';
            $settings['company_city'] = isset($company_settings['company_city']) ? $company_settings['company_city'] : '';
            $settings['company_state'] = isset($company_settings['company_state']) ? $company_settings['company_state'] : '';
            $settings['company_zipcode'] = isset($company_settings['company_zipcode']) ? $company_settings['company_zipcode'] : '';
            $settings['company_country'] = isset($company_settings['company_country']) ? $company_settings['company_country'] : '';
            $settings['registration_number'] = isset($company_settings['registration_number']) ? $company_settings['registration_number'] : '';
            $settings['tax_type'] = isset($company_settings['tax_type']) ? $company_settings['tax_type'] : '';
            $settings['vat_number'] = isset($company_settings['vat_number']) ? $company_settings['vat_number'] : '';
            $settings['purchase_footer_title'] = isset($company_settings['purchase_footer_title']) ? $company_settings['purchase_footer_title'] : '';
            $settings['purchase_footer_notes'] = isset($company_settings['purchase_footer_notes']) ? $company_settings['purchase_footer_notes'] : '';
            $settings['purchase_shipping_display'] = isset($company_settings['purchase_shipping_display']) ? $company_settings['purchase_shipping_display'] : '';
            $settings['purchase_template'] = isset($company_settings['purchase_template']) ? $company_settings['purchase_template'] : '';
            $settings['purchase_qr_display'] = isset($company_settings['purchase_qr_display']) ? $company_settings['purchase_qr_display'] : '';

            return view('purchases.templates.' . $settings['purchase_template'], compact('purchase', 'color', 'settings', 'vendor', 'img', 'font_color', 'customFields'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function payment($purchase_id)
    {
        if (\Auth::user()->isAbleTo('purchase payment create')) {
            $purchase = Purchase::where('id', $purchase_id)->first();
            $venders = \Workdo\Account\Entities\Vender::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            $categories = \Workdo\ProductService\Entities\Category::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            $accounts = BankAccount::select(
                '*',
                DB::raw("CONCAT(COALESCE(bank_name, ''), ' ', COALESCE(holder_name, '')) AS name")
            )
            ->where('workspace', getActiveWorkSpace())
            ->get()
            ->pluck('name', 'id');

            return view('purchases.payment', compact('venders', 'categories', 'accounts', 'purchase'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
    public function createPayment(Request $request, $purchase_id)
    {
        if (\Auth::user()->isAbleTo('purchase payment create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $purchasePayment = new PurchasePayment();

            if (module_is_active('Account')) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'account_id' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $purchasePayment->account_id = $request->account_id;
            }

            $purchasePayment->purchase_id    = $purchase_id;
            $purchasePayment->date           = $request->date;
            $purchasePayment->amount         = $request->amount;
            $purchasePayment->account_id     = $request->account_id;
            $purchasePayment->payment_method = 0;
            $purchasePayment->reference      = $request->reference;
            $purchasePayment->description    = $request->description;
            if (!empty($request->add_receipt)) {
                $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                $uplaod = upload_file($request, 'add_receipt', $fileName, 'payment');
                if ($uplaod['flag'] == 1) {
                    $url = $uplaod['url'];
                } else {
                    return redirect()->back()->with('error', $uplaod['msg']);
                }
                $purchasePayment->add_receipt = $url;
            }
            $purchasePayment->save();

            $purchase = Purchase::where('id', $purchase_id)->first();
            $due = $purchase->getDue();
            $total = $purchase->getTotal();

            if ($purchase->status == 0) {
                $purchase->send_date = date('Y-m-d');
                $purchase->save();
            }

            if ($due <= 0) {
                $purchase->status = 4;
                $purchase->save();
            } else {
                $purchase->status = 3;
                $purchase->save();
            }
            if ($purchase->vender_name) {

                $purchasePayment->vendor_name = $purchase->vender_name;
            } else {
                $purchasePayment->user_id = $purchase->vender_id;
            }
            $purchasePayment->user_type = 'Vendor';
            $purchasePayment->type = 'Partial';
            $purchasePayment->created_by = \Auth::user()->id;
            $purchasePayment->payment_id = $purchasePayment->id;
            $purchasePayment->category = 'Purchase';
            $purchasePayment->account = $request->account_id;

            if (module_is_active('Account')) {
                \Workdo\Account\Entities\Transaction::addTransaction($purchasePayment);

                \Workdo\Account\Entities\AccountUtility::updateUserBalance('vendor', $purchase->vender_id, $request->amount, 'credit');

                \Workdo\Account\Entities\Transfer::bankAccountBalance($request->account_id, $request->amount, 'debit');
            }

            $payment = new PurchasePayment();
            $payment->name = !empty($vender['name']) ? $purchasePayment->vendor_name : '';
            $payment->method = '-';
            $payment->date = company_date_formate($request->date);
            $payment->amount = currency_format_with_sym($request->amount);
            $payment->bill = 'purchase' . Purchase::purchaseNumberFormat($purchasePayment->purchase_id);
            if (!empty(company_setting('Purchase Payment Create')) && company_setting('Purchase Payment Create') == true) {
                $uArr = [
                    'payment_name' => $payment->name,
                    'payment_bill' => $payment->bill,
                    'payment_amount' => $payment->amount,
                    'payment_date' => $payment->date,
                    'payment_method' => $payment->method

                ];
                try {

                    $resp = EmailTemplate::sendEmailTemplate('Purchase Payment Create', [$vender_acc->id => $vender_acc->email], $uArr);
                } catch (\Exception $e) {
                    $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                }
            }
            event(new CreatePaymentPurchase($request, $purchasePayment, $purchase));
            return redirect()->back()->with('success', __('Payment successfully added.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        }
    }
    public function posPrintIndex()
    {
        if (\Auth::user()->isAbleTo('pos manage')) {

            return view('purchases.pos');
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
    public function previewPurchase($template, $color)
    {

        $objUser = \Auth::user();
        $purchase = new Purchase();

        $vendor = new \stdClass();
        $vendor->name = '<Name>';
        $vendor->email = '<Email>';
        $vendor->shipping_name = '<Vendor Name>';
        $vendor->shipping_country = '<Country>';
        $vendor->shipping_state = '<State>';
        $vendor->shipping_city = '<City>';
        $vendor->shipping_phone = '<Vendor Phone Number>';
        $vendor->shipping_zip = '<Zip>';
        $vendor->shipping_address = '<Address>';
        $vendor->billing_name = '<Vendor Name>';
        $vendor->billing_country = '<Country>';
        $vendor->billing_state = '<State>';
        $vendor->billing_city = '<City>';
        $vendor->billing_phone = '<Vendor Phone Number>';
        $vendor->billing_zip = '<Zip>';
        $vendor->billing_address = '<Address>';

        $totalTaxPrice = 0;
        $taxesData = [];
        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $item = new \stdClass();
            $item->name = 'Item ' . $i;
            $item->quantity = 1;
            $item->tax = 5;
            $item->discount = 50;
            $item->price = 100;
            $item->description = 'In publishing and graphic design, Lorem ipsum is a placeholder';

            $taxes = [
                'Tax 1',
                'Tax 2',
            ];

            $itemTaxes = [];
            foreach ($taxes as $k => $tax) {
                $taxPrice = 10;
                $totalTaxPrice += $taxPrice;
                $itemTax['name'] = 'Tax ' . $k;
                $itemTax['rate'] = '10 %';
                $itemTax['price'] = '$10';
                $itemTaxes[] = $itemTax;
                if (array_key_exists('Tax ' . $k, $taxesData)) {
                    $taxesData['Tax ' . $k] = $taxesData['Tax 1'] + $taxPrice;
                } else {
                    $taxesData['Tax ' . $k] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[] = $item;
        }

        $purchase->purchase_id = 1;
        $purchase->issue_date = date('Y-m-d H:i:s');
        $purchase->itemData = $items;

        $purchase->totalTaxPrice = 60;
        $purchase->totalQuantity = 3;
        $purchase->totalRate = 300;
        $purchase->totalDiscount = 10;
        $purchase->taxesData = $taxesData;
        $purchase->customField = [];
        $customFields = [];

        $preview = 1;
        $color = '#' . $color;
        $font_color = User::getFontColor($color);

        $company_logo = get_file(sidebar_logo());

        $company_settings = getCompanyAllSetting();

        $purchase_logo = isset($company_settings['purchase_logo']) ? $company_settings['purchase_logo'] : '';

        if (isset($purchase_logo) && !empty($purchase_logo)) {
            $img = get_file($purchase_logo);
        } else {
            $img = $company_logo;
        }
        $settings['site_rtl'] = isset($company_settings['site_rtl']) ? $company_settings['site_rtl'] : '';
        $settings['company_email'] = isset($company_settings['company_email']) ? $company_settings['company_email'] : '';
        $settings['company_telephone'] = isset($company_settings['company_telephone']) ? $company_settings['company_telephone'] : '';
        $settings['company_name'] = isset($company_settings['company_name']) ? $company_settings['company_name'] : '';
        $settings['company_address'] = isset($company_settings['company_address']) ? $company_settings['company_address'] : '';
        $settings['company_city'] = isset($company_settings['company_city']) ? $company_settings['company_city'] : '';
        $settings['company_state'] = isset($company_settings['company_state']) ? $company_settings['company_state'] : '';
        $settings['company_zipcode'] = isset($company_settings['company_zipcode']) ? $company_settings['company_zipcode'] : '';
        $settings['company_country'] = isset($company_settings['company_country']) ? $company_settings['company_country'] : '';
        $settings['registration_number'] = isset($company_settings['registration_number']) ? $company_settings['registration_number'] : '';
        $settings['tax_type'] = isset($company_settings['tax_type']) ? $company_settings['tax_type'] : '';
        $settings['vat_number'] = isset($company_settings['vat_number']) ? $company_settings['vat_number'] : '';
        $settings['purchase_footer_title'] = isset($company_settings['purchase_footer_title']) ? $company_settings['purchase_footer_title'] : '';
        $settings['purchase_footer_notes'] = isset($company_settings['purchase_footer_notes']) ? $company_settings['purchase_footer_notes'] : '';
        $settings['purchase_shipping_display'] = isset($company_settings['purchase_shipping_display']) ? $company_settings['purchase_shipping_display'] : '';
        $settings['purchase_qr_display'] = isset($company_settings['purchase_qr_display']) ? $company_settings['purchase_qr_display'] : '';

        return view('purchases.templates.' . $template, compact('purchase', 'preview', 'color', 'img', 'settings', 'vendor', 'font_color', 'customFields'));
    }

    public function savePurchaseTemplateSettings(Request $request)
    {

        $user = \Auth::user();
        $validator = \Validator::make(
            $request->all(),
            [
                'purchase_template' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        if ($request->purchase_logo) {
            $request->validate(
                [
                    'purchase_logo' => 'image|mimes:png',
                ]
            );

            $purchase_logo = $user->id . '_purchase_logo.png';
            $uplaod = upload_file($request, 'purchase_logo', $purchase_logo, 'purchase_logo');
            if ($uplaod['flag'] == 1) {
                $url = $uplaod['url'];
            } else {
                return redirect()->back()->with('error', $uplaod['msg']);
            }
        }

        $post = $request->all();
        unset($post['_token']);
        if (isset($post['purchase_footer_notes'])) {
            $validator = Validator::make(
                $request->all(),
                [
                    'purchase_footer_notes' => 'required|string|regex:/^[^\r\n]*$/',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
        }
        if (isset($post['purchase_template']) && (!isset($post['purchase_color']) || empty($post['purchase_color']))) {
            $post['purchase_color'] = "ffffff";
        }
        if (isset($post['purchase_logo'])) {
            $post['purchase_logo'] = $url;
        }
        if (!isset($post['purchase_qr_display'])) {
            $post['purchase_qr_display'] = 'off';
        }
        if (!isset($post['purchase_shipping_display'])) {
            $post['purchase_shipping_display'] = 'off';
        }
        foreach ($post as $key => $value) {
            // Define the data to be updated or inserted
            $data = [
                'key' => $key,
                'workspace' => getActiveWorkSpace(),
                'created_by' => creatorId(),
            ];
            // Check if the record exists, and update or insert accordingly
            Setting::updateOrInsert($data, ['value' => $value]);
        }
        // Settings Cache forget
        comapnySettingCacheForget();

        return redirect()->back()->with('success', __('The purchase Setting details are updated successfully'));
    }

    public function items(Request $request)
    {
        $items = PurchaseProduct::where('purchase_id', $request->purchase_id)->where('product_id', $request->product_id)->first();
        return json_encode($items);
    }

    public function purchaseLink($purchaseId)
    {
        try {
            $id = Crypt::decrypt($purchaseId);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Purchase Not Found.'));
        }


        $purchase = Purchase::find($id);

        if (!empty($purchase)) {
            $user_id = $purchase->created_by;
            $user = User::find($user_id);
            $iteams = $purchase->itemswithproduct;
            $company_id = $purchase->created_by;
            $workspace_id = $purchase->workspace;
            $purchasePayment = PurchasePayment::where('purchase_id', $purchase->id)->first();
            $vendor = [];
            if (module_is_active('Account')) {
                $vendor = \Workdo\Account\Entities\Vender::where('user_id', $purchase->user_id)->where('workspace', $workspace_id)->first();
            }
            if (!empty($vendor)) {
                $vendor->model = 'Customer';
            } else {
                $vendor = $purchase->user;
                if (!empty($vendor)) {
                    $vendor->model = 'User';
                }
            }
            return view('purchases.customer_bill', compact('purchase', 'vendor', 'iteams', 'purchasePayment', 'user', 'company_id', 'workspace_id'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function paymentDestroy(Request $request, $purchase_id, $payment_id)
    {
        if (\Auth::user()->isAbleTo('purchase payment delete')) {
            $payment = PurchasePayment::find($payment_id);

            $purchase = Purchase::where('id', $purchase_id)->first();
            $due = $purchase->getDue();
            $total = $purchase->getTotal();

            if (($due + $payment->amount) > 0 && ($due + $payment->amount) != $total) {
                $purchase->status = 3;
            } elseif($due + $payment->amount == $total) {
                $purchase->status = 2;
            }

            if (module_is_active('Account')){
                AccountUtility::updateUserBalance('vendor', $purchase->vender_id, $payment->amount, 'debit');

                Transfer::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

                Transaction::destroyTransaction($payment_id, 'Vendor');
            }
            event(new PaymentDestroyPurchase($purchase , $payment));

            $payment->delete();

            $purchase->save();

            return redirect()->back()->with('success', __('The payment has been deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function vender(Request $request)
    {

        if (module_is_active('Account')) {
            $vender = \Workdo\Account\Entities\Vender::where('user_id', '=', $request->id)->first();
            if (empty($vender)) {
                $user = User::find($request->id);
                $vender['name'] = !empty($user->name) ? $user->name : '';
                $vender['email'] = !empty($user->email) ? $user->email : '';
            }
        } else {
            $user = User::find($request->id);
            $vender['name'] = !empty($user->name) ? $user->name : '';
            $vender['email'] = !empty($user->email) ? $user->email : '';
        }

        return view('purchases.vender_detail', compact('vender'));
    }


    public function grid()
    {
        if (\Auth::user()->isAbleTo('purchase manage')) {
            $vender = [];
            if (module_is_active('Account')) {

                $vender = \Workdo\Account\Entities\Vender::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
                $vender->prepend('Select Vendor', '');
            }

            $status = Purchase::$statues;
            if (Auth::user()->type == 'company') {
                $purchases = Purchase::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->with('user');
            } else {
                $purchases = Purchase::select('purchases.*')
                    ->join('vendors', 'purchases.vender_id', '=', 'vendors.user_id')
                    ->where('purchases.status', '!=', 0)
                    ->where('vendors.user_id', Auth::user()->id)
                    ->where('purchases.workspace', getActiveWorkSpace())
                    ->with('user');
            }
            $purchases = $purchases->paginate(11);

            return view('purchases.grid', compact('purchases', 'status', 'vender'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function fileUpload($id, Request $request)
    {
        $purchase = Purchase::find($id);
        $file_name = time() . "_" . $request->file->getClientOriginalName();

        $upload = upload_file($request, 'file', $file_name, 'purchase_attachment', []);

        $fileSizeInBytes = File::size($upload['url']);
        $fileSizeInKB = round($fileSizeInBytes / 1024, 2);

        if ($fileSizeInKB < 1024) {
            $fileSizeFormatted = $fileSizeInKB . " KB";
        } else {
            $fileSizeInMB = round($fileSizeInKB / 1024, 2);
            $fileSizeFormatted = $fileSizeInMB . " MB";
        }

        if ($upload['flag'] == 1) {
            $file = PurchaseAttachment::create(
                [
                    'purchase_id' => $purchase->id,
                    'file_name' => $file_name,
                    'file_path' => $upload['url'],
                    'file_size' => $fileSizeFormatted,
                ]
            );
            $return = [];
            $return['is_success'] = true;


            return response()->json($return);
        } else {

            return response()->json(
                [
                    'is_success' => false,
                    'error' => $upload['msg'],
                ],
                401
            );
        }
    }

    public function fileUploadDestroy($id)
    {
        $file = PurchaseAttachment::find($id);

        if (!empty($file->file_path)) {
            delete_file($file->file_path);
        }
        $file->delete();
        return redirect()->back()->with('success', __('The file has been deleted'));
    }
}
