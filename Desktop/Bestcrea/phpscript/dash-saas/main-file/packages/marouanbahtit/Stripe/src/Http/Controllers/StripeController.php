<?php

namespace MarouanBahtit\Stripe\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Setting;
use App\Models\WorkSpace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use MarouanBahtit\Stripe\Events\StripePaymentStatus;
use Illuminate\Support\Facades\Cookie;
use MarouanBahtit\Holidayz\Entities\Hotels;
use MarouanBahtit\Holidayz\Entities\RoomBookingCart;
use MarouanBahtit\Holidayz\Entities\BookingCoupons;
use MarouanBahtit\Holidayz\Entities\HotelCustomer;
use MarouanBahtit\Holidayz\Entities\RoomBooking;
use MarouanBahtit\Holidayz\Entities\RoomBookingOrder;
use MarouanBahtit\Holidayz\Entities\UsedBookingCoupons;
use MarouanBahtit\Holidayz\Events\CreateRoomBooking;
use MarouanBahtit\GymManagement\Entities\AssignMembershipPlan;
use MarouanBahtit\BeautySpaManagement\Entities\BeautyService;
use MarouanBahtit\BeautySpaManagement\Entities\BeautyReceipt;
use MarouanBahtit\BeautySpaManagement\Entities\BeautyBooking;
use MarouanBahtit\Bookings\Entities\BookingsAppointment;
use MarouanBahtit\Bookings\Entities\BookingsCustomer;
use MarouanBahtit\Bookings\Entities\BookingsPackage;
use MarouanBahtit\EventsManagement\Entities\EventBookingOrder;
use MarouanBahtit\EventsManagement\Entities\EventsBookings;
use MarouanBahtit\EventsManagement\Entities\EventsMange;
use MarouanBahtit\Facilities\Entities\FacilitiesBooking;
use MarouanBahtit\Facilities\Entities\FacilitiesReceipt;
use MarouanBahtit\Facilities\Entities\FacilitiesService;
use MarouanBahtit\GymManagement\Entities\GymMember;
use MarouanBahtit\ParkingManagement\Entities\Parking;
use MarouanBahtit\ParkingManagement\Entities\Payment;
use MarouanBahtit\PropertyManagement\Entities\Property;
use MarouanBahtit\PropertyManagement\Entities\PropertyTenantRequest;
use Illuminate\Support\Facades\Crypt;
use MarouanBahtit\Account\Entities\BankAccount;
use MarouanBahtit\MovieShowBookingSystem\Entities\MovieSeatBooking;
use MarouanBahtit\MovieShowBookingSystem\Entities\MovieSeatBookingOrder;
use MarouanBahtit\VehicleBookingManagement\Entities\Booking;
use MarouanBahtit\VehicleBookingManagement\Entities\VehicleBookingPayments;
use MarouanBahtit\CoworkingSpaceManagement\Entities\CoworkingMembership;
use MarouanBahtit\CoworkingSpaceManagement\Entities\CoworkingBooking;
use MarouanBahtit\Stripe\Events\StripeWaterParkBookingsData;
use MarouanBahtit\WaterParkManagement\Entities\WaterParkBookings;
use MarouanBahtit\SportsClubAndAcademyManagement\Entities\SportsClubAndGroundOrders;
use MarouanBahtit\SportsClubAndAcademyManagement\Entities\SportsClubAndGroundPlanSubscriptions;
use MarouanBahtit\SportsClubAndAcademyManagement\Entities\SportsGroundAndClubMembers;
use MarouanBahtit\SportsClubAndAcademyManagement\Entities\SportsGroundAndClubPlans;
use MarouanBahtit\SportsClubAndAcademyManagement\Entities\SportsGroundAndClubs;
use MarouanBahtit\SportsClubAndAcademyManagement\Events\CreateSportsClubMembers;
use MarouanBahtit\BoutiqueAndDesignerStudio\Entities\OutfitReservation;
use MarouanBahtit\BoutiqueAndDesignerStudio\Entities\OutfitCleaningManagement;
use MarouanBahtit\InfluencerMarketing\Entities\Brand;
use MarouanBahtit\InfluencerMarketing\Entities\BrandTransaction;
use MarouanBahtit\InfluencerMarketing\Entities\Deposit;
use MarouanBahtit\NGOManagment\Entities\Donations;
use MarouanBahtit\NGOManagment\Entities\Donors;
use MarouanBahtit\ArtShowcase\Entities\ArtWorks;
use MarouanBahtit\ArtShowcase\Entities\ArtWorkOrder;
class StripeController extends Controller
{
    public $stripe_key;
    public $stripe_secret;
    public $is_stripe_enabled;
    public $currancy;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function setting(Request $request)
    {
        if (Auth::user()->isAbleTo('stripe manage')) {
            if ($request->has('stripe_is_on')) {
                $validator = Validator::make($request->all(), [
                    'stripe_key' => 'required|string',
                    'stripe_secret' => 'required|string'
                ]);
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
            }
            $getActiveWorkSpace = getActiveWorkSpace();
            $creatorId = creatorId();
            if ($request->has('stripe_is_on')) {
                $post = $request->all();
                unset($post['_token']);
                foreach ($post as $key => $value) {
                    // Define the data to be updated or inserted
                    $data = [
                        'key' => $key,
                        'workspace' => $getActiveWorkSpace,
                        'created_by' => $creatorId,
                    ];

                    // Check if the record exists, and update or insert accordingly
                    Setting::updateOrInsert($data, ['value' => $value]);
                }
            } else {
                $data = [
                    'key' => 'stripe_is_on',
                    'workspace' => $getActiveWorkSpace,
                    'created_by' => $creatorId,
                ];
                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => 'off']);
            }
            // Settings Cache forget
            AdminSettingCacheForget();
            comapnySettingCacheForget();
            return redirect()->back()->with('success', 'Stripe setting save sucessfully.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function planPayWithStripe(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $plan = Plan::find($request->plan_id);
        $admin_settings = getAdminAllSetting();
        $admin_currancy = !empty($admin_settings['defult_currancy']) ? $admin_settings['defult_currancy'] : 'INR';
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->route('plans.index')->with('error', __('Currency is not supported.'));
        }
        $authuser = Auth::user();
        $user_counter = !empty($request->user_counter_input) ? $request->user_counter_input : 0;
        $workspace_counter = !empty($request->workspace_counter_input) ? $request->workspace_counter_input : 0;
        $user_module = !empty($request->user_module_input) ? $request->user_module_input : '';
        $duration = !empty($request->time_period) ? $request->time_period : 'Month';
        $user_module_price = 0;
        if (!empty($user_module) && $plan->custom_plan == 1) {
            $user_module_array = explode(',', $user_module);
            foreach ($user_module_array as $key => $value) {
                $temp = ($duration == 'Year') ? ModulePriceByName($value)['yearly_price'] : ModulePriceByName($value)['monthly_price'];
                $user_module_price = $user_module_price + $temp;
            }
        }
        $user_price = 0;
        if ($user_counter > 0) {
            $temp = ($duration == 'Year') ? $plan->price_per_user_yearly : $plan->price_per_user_monthly;
            $user_price = $user_counter * $temp;
        }
        $workspace_price = 0;
        if ($workspace_counter > 0) {
            $temp = ($duration == 'Year') ? $plan->price_per_workspace_yearly : $plan->price_per_workspace_monthly;
            $workspace_price = $workspace_counter * $temp;
        }
        $plan_price = ($duration == 'Year') ? $plan->package_price_yearly : $plan->package_price_monthly;
        $counter = [
            'user_counter' => $user_counter,
            'workspace_counter' => $workspace_counter,
        ];

        $stripe_session = '';
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        if ($plan) {
            /* Check for code usage */
            $plan->discounted_price = false;
            $payment_frequency = $plan->duration;
            if ($request->coupon_code) {
                $plan_price = CheckCoupon($request->coupon_code, $plan_price, $plan->id);
            }
            $price = $plan_price + $user_module_price + $user_price + $workspace_price;
            if ($price <= 0) {
                $assignPlan = DirectAssignPlan($plan->id, $duration, $user_module, $counter, 'STRIPE', $request->coupon_code);
                if ($assignPlan['is_success']) {
                    return redirect()->route('plans.index')->with('success', __('Plan activated Successfully!'));
                } else {
                    return redirect()->route('plans.index')->with('error', __('Something went wrong, Please try again,'));
                }
            }

            try {

                $payment_plan = $duration;
                $payment_type = 'onetime';
                /* Payment details */
                $code = '';

                $product = 'Basic Package';

                /* Final price */
                $stripe_formatted_price = in_array(
                    $admin_currancy,
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                        'BRL'
                    ]
                ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
                $return_url_parameters = function ($return_type) use ($payment_frequency, $payment_type) {
                    return '&return_type=' . $return_type . '&payment_processor=stripe&payment_frequency=' . $payment_frequency . '&payment_type=' . $payment_type;
                };
                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey(isset($admin_settings['stripe_secret']) ? $admin_settings['stripe_secret'] : '');
                $stripe_session = \Stripe\Checkout\Session::create(
                    [
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' => $admin_currancy,
                                    'unit_amount' => (int) $stripe_formatted_price,
                                    'product_data' => [
                                        'name' => !empty($plan->name) ? $plan->name : 'Basic Package',
                                        'description' => $payment_plan,
                                    ],
                                ],
                                'quantity' => 1,
                            ],
                        ],
                        'mode' => 'payment',
                        'metadata' => [
                            'user_id' => $authuser->id,
                            'package_id' => $plan->id,
                            'payment_frequency' => $payment_frequency,
                            'code' => $code,
                        ],
                        'success_url' => route(
                            'plan.get.payment.status',
                            [
                                'order_id' => $orderID,
                                'plan_id' => $plan->id,
                                'user_module' => $user_module,
                                'duration' => $duration,
                                'counter' => $counter,
                                'coupon_code' => $request->coupon_code,
                                $return_url_parameters('success'),
                            ]
                        ),
                        'cancel_url' => route(
                            'plan.get.payment.status',
                            [
                                'plan_id' => $orderID,
                                'order_id' => $plan->id,
                                $return_url_parameters('cancel'),
                            ]
                        ),
                    ]
                );
                Order::create(
                    [
                        'order_id' => $orderID,
                        'name' => null,
                        'email' => null,
                        'card_number' => null,
                        'card_exp_month' => null,
                        'card_exp_year' => null,
                        'plan_name' => !empty($plan->name) ? $plan->name : 'Basic Package',
                        'plan_id' => $plan->id,
                        'price' => !empty($price) ? $price : 0,
                        'price_currency' => $admin_currancy,
                        'txn_id' => '',
                        'payment_type' => 'Stripe',
                        'payment_status' => 'pending',
                        'receipt' => null,
                        'user_id' => $authuser->id,
                    ]
                );
                $request->session()->put('stripe_session', $stripe_session);
                $stripe_session = $stripe_session ?? false;
            } catch (\Exception $e) {
                \Log::debug($e->getMessage());
                return redirect()->route('plans.index')->with('error', $e->getMessage());
            }
            return view('stripe::plan.request', compact('stripe_session'));
        } else {
            return redirect()->route('plans.index')->with('error', __('Plan is deleted.'));
        }
    }

    public function planGetStripeStatus(Request $request)
    {
        \Log::debug((array) $request->all());
        $admin_settings = getAdminAllSetting();
        try {
            $stripe = new \Stripe\StripeClient(!empty($admin_settings['stripe_secret']) ? $admin_settings['stripe_secret'] : '');
            $paymentIntents = $stripe->paymentIntents->retrieve(
                $request->session()->get('stripe_session')->payment_intent,
                []
            );
            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
        } catch (\Exception $exception) {
            $receipt_url = "";
        }
        \Session::forget('stripe_session');
        $request->session()->forget('stripe_session');

        try {
            if ($request->return_type == 'success') {
                $Order = Order::where('order_id', $request->order_id)->first();
                $Order->payment_status = 'succeeded';
                $Order->receipt = $receipt_url;
                $Order->save();

                $user = User::find(\Auth::user()->id);
                $plan = Plan::find($request->plan_id);
                $assignPlan = $user->assignPlan($plan->id, $request->duration, $request->user_module, $request->counter);
                if ($request->coupon_code) {
                    UserCoupon($request->coupon_code, $request->order_id);
                }
                $type = 'Subscription';
                event(new StripePaymentStatus($plan, $type, $Order));
                $value = Session::get('user-module-selection');
                if (!empty($value)) {
                    Session::forget('user-module-selection');
                }
                if ($assignPlan['is_success']) {
                    return redirect()->route('plans.index')->with('success', __('Plan activated Successfully!'));
                } else {
                    return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
                }
            } else {
                return redirect()->route('plans.index')->with('error', __('Your Payment has failed!'));
            }
        } catch (\Exception $exception) {
            return redirect()->route('plans.index')->with('error', $exception->getMessage());
        }
    }

    public function payment_setting($id = null, $wokspace = Null)
    {
        if (!empty($id) && empty($wokspace)) {
            $company_settings = getCompanyAllSetting($id);
        } elseif (!empty($id) && !empty($wokspace)) {
            $company_settings = getCompanyAllSetting($id, $wokspace);
        } else {
            $company_settings = getCompanyAllSetting();
        }
        $this->currancy = !empty($company_settings['defult_currancy']) ? $company_settings['defult_currancy'] : 'INR';
        $this->is_stripe_enabled = ($company_settings['stripe_is_on']) ? $company_settings['stripe_is_on'] : 'off';
        $this->stripe_key = ($company_settings['stripe_key']) ? $company_settings['stripe_key'] : '';
        $this->stripe_secret = ($company_settings['stripe_secret']) ? $company_settings['stripe_secret'] : '';
    }

    public function invoicePayWithStripe(Request $request)
    {
        if ($request->type == "invoice") {
            $invoice = \App\Models\Invoice::find($request->invoice_id);
            $user_id = $invoice->created_by;
            $wokspace = $invoice->workspace;

            $invoice_payID = $invoice->invoice_id;
            $invoiceID = $invoice->id;
            $printID = Invoice::invoiceNumberFormat($invoice_payID, $user_id, $wokspace);
        } elseif ($request->type == "retainer") {
            $invoice = \Workdo\Retainer\Entities\Retainer::find($request->invoice_id);
            $user_id = $invoice->created_by;
            $wokspace = $invoice->workspace;
            $invoice_payID = $invoice->retainer_id;
            $invoiceID = $invoice->id;
            $printID = \Workdo\Retainer\Entities\Retainer::retainerNumberFormat($invoice_payID, $user_id, $wokspace);
        }

        self::payment_setting($user_id, $wokspace);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {

            $user = Auth::user();
            $validator = Validator::make(
                $request->all(),
                [
                    'amount' => 'required|numeric',
                    'invoice_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            $comapany_stripe_data = '';

            if ($invoice) {

                $account = BankAccount::where(['created_by'=>$invoice->created_by,'workspace'=>$invoice->workspace])->where('payment_name','Stripe')->first();
                if(!$account)
                {
                    if ($request->type == 'invoice') {
                        return redirect()->route('pay.invoice', encrypt($invoiceID))->with('error', __('Bank account not connected with Stripe.'));
                    } elseif ($request->type == 'retainer') {
                        return redirect()->route('pay.retainer', encrypt($invoiceID))->with('error', __('Bank account not connected with Stripe.'));
                    }
                }

                /* Check for code usage */
                $price = $request->amount;

                try {

                    $stripe_formatted_price = in_array(
                        company_setting('defult_currancy', $user_id, $wokspace),
                        [
                            'MGA',
                            'BIF',
                            'CLP',
                            'PYG',
                            'DJF',
                            'RWF',
                            'GNF',
                            'UGX',
                            'JPY',
                            'VND',
                            'VUV',
                            'XAF',
                            'KMF',
                            'KRW',
                            'XOF',
                            'XPF',
                            'BRL'
                        ]
                    ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;

                    $return_url_parameters = function ($return_type) {
                        return '&return_type=' . $return_type;
                    };
                    /* Initiate Stripe */
                    \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $user_id, $wokspace));
                    $code = '';

                    $comapany_stripe_data = \Stripe\Checkout\Session::create(
                        [
                            'payment_method_types' => ['card'],
                            'line_items' => [
                                [
                                    'price_data' => [
                                        'currency' => $this->currancy,
                                        'unit_amount' => (int) $stripe_formatted_price,
                                        'product_data' => [
                                            'name' => $printID,
                                        ],
                                    ],
                                    'quantity' => 1,
                                ],
                            ],
                            'mode' => 'payment',
                            'metadata' => [
                                'user_id' => isset($user->name) ? $user->name : 0,
                                'package_id' => $invoiceID,
                                'code' => $code,
                            ],
                            'success_url' => route(
                                'invoice.stripe',
                                [
                                    'invoice_id' => encrypt($invoiceID),
                                    $request->type,
                                    $return_url_parameters('success'),
                                ]
                            ),
                            'cancel_url' => route(
                                'invoice.stripe',
                                [
                                    'invoice_id' => encrypt($invoiceID),
                                    $return_url_parameters('cancel'),
                                ]
                            ),
                        ]

                    );

                    $data = [
                        'amount' => $price,
                        'currency' => $this->currancy,
                        'stripe' => $comapany_stripe_data

                    ];
                    $request->session()->put('comapany_stripe_data', $data);

                    $comapany_stripe_data = $comapany_stripe_data ?? false;
                    return new RedirectResponse($comapany_stripe_data->url);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', $e);
                }
            } else {
                if ($request->type == 'invoice') {
                    return redirect()->route('pay.invoice', encrypt($invoiceID))->with('error', __('Invoice is deleted.'));
                } elseif ($request->type == 'retainer') {
                    return redirect()->route('pay.retainer', encrypt($invoiceID))->with('error', __('Retainer is deleted.'));
                }
            }
        } else {
            return redirect()->back()->with('error', __('Please Enter Stripe Details.'));
        }
    }

    public function getInvoicePaymentStatus($invoice_id, Request $request, $type)
    {
        try {
            if ($request->return_type == 'success') {
                if ($type == 'invoice') {
                    if (!empty($invoice_id)) {
                        $invoice_id = decrypt($invoice_id);
                        $invoice = Invoice::find($invoice_id);
                        \Log::debug((array) $request->all());
                        $session_data = $request->session()->get('comapany_stripe_data');
                        try {
                            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $invoice->created_by, $invoice->workspace)) ? company_setting('stripe_secret', $invoice->created_by, $invoice->workspace) : '');
                            $paymentIntents = $stripe->paymentIntents->retrieve(
                                $session_data['stripe']->payment_intent,
                                []
                            );
                            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
                        } catch (\Exception $exception) {
                            $receipt_url = "";
                        }
                        Session::forget('comapany_stripe_data');
                        $request->session()->forget('comapany_stripe_data');
                        $get_data = $session_data;
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                        if ($invoice) {

                            try {
                                if ($request->return_type == 'success') {
                                    $invoice_payment = new InvoicePayment();
                                    $invoice_payment->invoice_id = $invoice_id;
                                    $invoice_payment->date = date('Y-m-d');
                                    $invoice_payment->amount = isset($get_data['amount']) ? $get_data['amount'] : 0;
                                    $invoice_payment->account_id = 0;
                                    $invoice_payment->payment_method = 0;
                                    $invoice_payment->order_id = $orderID;
                                    $invoice_payment->currency = isset($get_data['currency']) ? $get_data['currency'] : 'INR';
                                    $invoice_payment->payment_type = 'Stripe';
                                    $invoice_payment->receipt = $receipt_url;
                                    $invoice_payment->save();

                                    $due = $invoice->getDue();
                                    if ($due <= 0) {
                                        $invoice->status = 4;
                                        $invoice->save();
                                    } else {
                                        $invoice->status = 3;
                                        $invoice->save();
                                    }

                                    event(new StripePaymentStatus($invoice, $type, $invoice_payment));


                                    return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __('Payment added Successfully'));
                                } else {
                                    return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Transaction has been failed!'));
                                }
                            } catch (\Exception $e) {

                                return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', $e->getMessage());
                            }
                        } else {


                            return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Invoice not found.'));
                        }
                    } else {

                        return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
                    }
                } elseif ($type == 'retainer') {

                    if (!empty($invoice_id)) {
                        $invoice_id = decrypt($invoice_id);
                        $invoice = \Workdo\Retainer\Entities\Retainer::find($invoice_id);

                        \Log::debug((array) $request->all());
                        $session_data = $request->session()->get('comapany_stripe_data');
                        try {
                            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $invoice->created_by, $invoice->workspace)) ? company_setting('stripe_secret', $invoice->created_by, $invoice->workspace) : '');

                            $paymentIntents = $stripe->paymentIntents->retrieve(
                                $session_data['stripe']->payment_intent,
                                []
                            );

                            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
                        } catch (\Exception $exception) {


                            $receipt_url = "";
                        }
                        Session::forget('comapany_stripe_data');
                        $request->session()->forget('comapany_stripe_data');
                        $get_data = $session_data;
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                        if ($invoice) {
                            try {
                                if ($request->return_type == 'success') {
                                    $retainer_payment = new \Workdo\Retainer\Entities\RetainerPayment();
                                    $retainer_payment->retainer_id = $invoice_id;
                                    $retainer_payment->date = date('Y-m-d');
                                    $retainer_payment->amount = isset($get_data['amount']) ? $get_data['amount'] : 0;
                                    $retainer_payment->account_id = 0;
                                    $retainer_payment->payment_method = 0;
                                    $retainer_payment->order_id = $orderID;
                                    $retainer_payment->currency = isset($get_data['currency']) ? $get_data['currency'] : 'INR';
                                    $retainer_payment->payment_type = 'Stripe';
                                    $retainer_payment->receipt = $receipt_url;
                                    $retainer_payment->save();

                                    $due = $invoice->getDue();
                                    if ($due <= 0) {
                                        $invoice->status = 5;

                                        $invoice->save();
                                    } else {
                                        $invoice->status = 4;
                                        $invoice->save();
                                    }

                                    event(new StripePaymentStatus($invoice, $type, $retainer_payment));
                                    return redirect()->route('pay.retainer', Crypt::encrypt($invoice_id))->with('success', __('Payment added Successfully'));
                                } else {

                                    return redirect()->route('pay.retainer', Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed!'));
                                }
                            } catch (\Exception $e) {

                                return redirect()->route('pay.retainer', Crypt::encrypt($invoice_id))->with('error', $e->getMessage());
                            }
                        } else {
                            return redirect()->route('pay.retainer', Crypt::encrypt($invoice_id))->with('error', __('Retainer not found.'));
                        }
                    } else {
                        return redirect()->route('pay.retainer', Crypt::encrypt($invoice_id))->with('error', __('Retainer not found.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('Oops something went wrong.'));
                }
            } else {

                return redirect()->back()->with('error', __('Transaction has been failed.'));
            }
        } catch (\Exception $exception) {

            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    public function coursePayWithStripe(Request $request, $slug)
    {
        $cart = session()->get($slug);
        $products = $cart['products'];

        $store = \Workdo\LMS\Entities\Store::where('slug', $slug)->first();
        $student = Auth::guard('students')->user();

        self::payment_setting($store->created_by, $store->wokspace_id);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        $products = $cart['products'];
        $sub_totalprice = 0;
        $totalprice = 0;
        $product_name = [];
        $product_id = [];

        foreach ($products as $key => $product) {
            $product_name[] = $product['product_name'];
            $product_id[] = $product['id'];
            $sub_totalprice += $product['price'];
            $totalprice += $product['price'];
        }
        if (isset($cart['coupon'])) {
            $coupon = $cart['coupon']['coupon'];
        }
        if (!empty($coupon)) {
            if ($coupon['enable_flat'] == 'off') {
                $discount_value = ($sub_totalprice / 100) * $coupon['discount'];
                $totalprice = $sub_totalprice - $discount_value;
            } else {
                $discount_value = $coupon['flat_discount'];
                $totalprice = $sub_totalprice - $discount_value;
            }
        }
        if ($totalprice <= 0) {
            $assignCourse = \Workdo\LMS\Entities\LmsUtility::DirectAssignCourse($store, 'Stripe');
            if ($assignCourse['is_success']) {
                return redirect()->route(
                    'store-complete.complete',
                    [
                        $store->slug,
                        \Illuminate\Support\Facades\Crypt::encrypt($assignCourse['courseorder_id']),
                    ]
                )->with('success', __('Transaction has been success'));
            } else {
                return redirect()->route('store.cart', $store->slug)->with('error', __('Something went wrong, Please try again,'));
            }
        }
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        if ($products) {
            try {

                $stripe_formatted_price = in_array(
                    company_setting('defult_currancy', $store->created_by, $store->wokspace_id),
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                        'BRL'
                    ]
                ) ? number_format($totalprice, 2, '.', '') : number_format($totalprice, 2, '.', '') * 100;

                $return_url_parameters = function ($return_type) {
                    return '&return_type=' . $return_type;
                };
                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $store->created_by, $store->wokspace_id));
                $code = '';

                $comapany_stripe_data = \Stripe\Checkout\Session::create(
                    [
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' => $this->currancy,
                                    'unit_amount' => (int) $stripe_formatted_price,
                                    'product_data' => [
                                        'name' => $orderID,
                                    ],
                                ],
                                'quantity' => 1,
                            ],
                        ],
                        'mode' => 'payment',
                        'metadata' => [
                            'user_id' => isset($student->name) ? $student->name : 0,
                            'package_id' => '',
                            'code' => $code,
                        ],
                        'success_url' => route(
                            'course.stripe',
                            [
                                $store->slug,
                                $return_url_parameters('success'),
                            ]
                        ),
                        'cancel_url' => route(
                            'course.stripe',
                            [
                                $return_url_parameters('cancel'),
                            ]
                        ),
                    ]

                );

                $data = [
                    'amount' => $totalprice,
                    'currency' => $this->currancy,
                    'stripe' => $comapany_stripe_data

                ];

                $comapany_stripe_data = $comapany_stripe_data ?? false;
                return new RedirectResponse($comapany_stripe_data->url);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e);
                \Log::debug($e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', __('Plan is deleted.'));
        }
    }

    public function getCoursePaymentStatus($slug, Request $request)
    {
        try {
            if ($request->return_type == 'success') {
                $store = \Workdo\LMS\Entities\Store::where('slug', $slug)->first();

                $cart = session()->get($slug);
                if (isset($cart['coupon'])) {
                    $coupon = $cart['coupon']['coupon'];
                }
                $products = $cart['products'];
                $sub_totalprice = 0;
                $totalprice = 0;
                $product_name = [];
                $product_id = [];

                foreach ($products as $key => $product) {
                    $product_name[] = $product['product_name'];
                    $product_id[] = $product['id'];
                    $sub_totalprice += $product['price'];
                    $totalprice += $product['price'];
                }
                if (!empty($coupon)) {
                    if ($coupon['enable_flat'] == 'off') {
                        $discount_value = ($sub_totalprice / 100) * $coupon['discount'];
                        $totalprice = $sub_totalprice - $discount_value;
                    } else {
                        $discount_value = $coupon['flat_discount'];
                        $totalprice = $sub_totalprice - $discount_value;
                    }
                }
                if ($products) {
                    \Log::debug((array) $request->all());
                    $session_data = $request->session()->get('comapany_stripe_data');
                    try {
                        $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $store->created_by, $store->workspace)) ? company_setting('stripe_secret', $store->created_by, $store->workspace) : '');
                        $paymentIntents = $stripe->paymentIntents->retrieve(
                            $session_data['stripe']->payment_intent,
                            []
                        );
                        $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
                    } catch (\Exception $exception) {
                        $receipt_url = "";
                    }
                    try {

                        $student = Auth::guard('students')->user();
                        $course_order = new \Workdo\LMS\Entities\CourseOrder();
                        $course_order->order_id = '#' . time();
                        $course_order->name = $student->name;
                        $course_order->card_number = '';
                        $course_order->card_exp_month = '';
                        $course_order->card_exp_year = '';
                        $course_order->student_id = $student->id;
                        $course_order->course = json_encode($products);
                        $course_order->price = $totalprice;
                        $course_order->coupon = !empty($cart['coupon']['coupon']['id']) ? $cart['coupon']['coupon']['id'] : '';
                        $course_order->coupon_json = json_encode(!empty($coupon) ? $coupon : '');
                        $course_order->discount_price = !empty($cart['coupon']['discount_price']) ? $cart['coupon']['discount_price'] : '';
                        $course_order->price_currency = !empty(company_setting('defult_currancy', $store->created_by, $store->workspace_id)) ? company_setting('defult_currancy', $store->created_by, $store->workspace_id) : 'USD';
                        $course_order->txn_id = '';
                        $course_order->payment_type = 'Stripe';
                        $course_order->payment_status = 'success';
                        $course_order->receipt = $receipt_url;
                        $course_order->store_id = $store['id'];
                        $course_order->save();

                        foreach ($products as $course_id) {
                            $purchased_course = new \Workdo\LMS\Entities\PurchasedCourse();
                            $purchased_course->course_id = $course_id['product_id'];
                            $purchased_course->student_id = $student->id;
                            $purchased_course->order_id = $course_order->id;
                            $purchased_course->save();

                            $student = \Workdo\LMS\Entities\Student::where('id', $purchased_course->student_id)->first();
                            $student->courses_id = $purchased_course->course_id;
                            $student->save();
                        }
                        if (!empty(company_setting('New Course Order', $store->created_by, $store->workspace_id)) && company_setting('New Course Order', $store->created_by, $store->workspace_id) == true) {
                            $course = \Workdo\LMS\Entities\Course::whereIn('id', $product_id)->get()->pluck('title');
                            $course_name = implode(', ', $course->toArray());
                            $user = User::where('id', $store->created_by)->where('workspace_id', $store->workspace_id)->first();
                            $uArr = [
                                'student_name' => $student->name,
                                'course_name' => $course_name,
                                'store_name' => $store->name,
                                'order_url' => route('user.order', [$store->slug, \Illuminate\Support\Facades\Crypt::encrypt($course_order->id),]),
                            ];
                            try {
                                // Send Email
                                $resp = EmailTemplate::sendEmailTemplate('New Course Order', [$user->id => $user->email], $uArr, $store->created_by);
                            } catch (\Exception $e) {
                                $resp['error'] = $e->getMessage();
                            }
                        }
                        $type = 'coursepayment';
                        event(new StripePaymentStatus($store, $type, $course_order));

                        session()->forget($slug);

                        return redirect()->route(
                            'store-complete.complete',
                            [
                                $store->slug,
                                \Illuminate\Support\Facades\Crypt::encrypt($course_order->id),
                            ]
                        )->with('success', __('Transaction has been success'));
                    } catch (\Exception $e) {
                        return redirect()->back()->with('error', __('Transaction has been failed.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('is deleted.'));
                }
            } else {

                return redirect()->back()->with('error', __('Transaction has been failed.'));
            }
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    public function contentPayWithStripe(Request $request, $slug)
    {
        $cart = session()->get($slug);
        $products = $cart['products'];

        $store = \Workdo\TVStudio\Entities\TVStudioStore::where('slug', $slug)->first();
        $customer = Auth::guard('customers')->user();

        self::payment_setting($store->created_by, $store->wokspace_id);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        $products = $cart['products'];
        $sub_totalprice = 0;
        $totalprice = 0;
        $product_name = [];
        $product_id = [];

        foreach ($products as $key => $product) {
            $product_name[] = $product['product_name'];
            $product_id[] = $product['id'];
            $sub_totalprice += $product['price'];
            $totalprice += $product['price'];
        }
        if (isset($cart['coupon'])) {
            $coupon = $cart['coupon']['coupon'];
        }
        if (!empty($coupon)) {
            if ($coupon['enable_flat'] == 'off') {
                $discount_value = ($sub_totalprice / 100) * $coupon['discount'];
                $totalprice = $sub_totalprice - $discount_value;
            } else {
                $discount_value = $coupon['flat_discount'];
                $totalprice = $sub_totalprice - $discount_value;
            }
        }
        if ($totalprice <= 0) {
            $assignCourse = \Workdo\TVStudio\Entities\TVStudioUtility::DirectAssignCourse($store, 'Stripe');
            if ($assignCourse['is_success']) {
                return redirect()->route(
                    'tv.store-complete.complete',
                    [
                        $store->slug,
                        \Illuminate\Support\Facades\Crypt::encrypt($assignCourse['courseorder_id']),
                    ]
                )->with('success', __('Transaction has been success'));
            } else {
                return redirect()->route('store.cart', $store->slug)->with('error', __('Something went wrong, Please try again,'));
            }
        }
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        if ($products) {
            try {

                $stripe_formatted_price = in_array(
                    company_setting('defult_currancy', $store->created_by, $store->wokspace_id),
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                        'BRL'
                    ]
                ) ? number_format($totalprice, 2, '.', '') : number_format($totalprice, 2, '.', '') * 100;

                $return_url_parameters = function ($return_type) {
                    return '&return_type=' . $return_type;
                };
                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $store->created_by, $store->wokspace_id));
                $code = '';

                $comapany_stripe_data = \Stripe\Checkout\Session::create(
                    [
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' => $this->currancy,
                                    'unit_amount' => (int) $stripe_formatted_price,
                                    'product_data' => [
                                        'name' => $orderID,
                                    ],
                                ],
                                'quantity' => 1,
                            ],
                        ],
                        'mode' => 'payment',
                        'metadata' => [
                            'user_id' => isset($customer->name) ? $customer->name : 0,
                            'package_id' => '',
                            'code' => $code,
                        ],
                        'success_url' => route(
                            'content.stripe',
                            [
                                $store->slug,
                                $return_url_parameters('success'),
                            ]
                        ),
                        'cancel_url' => route(
                            'content.stripe',
                            [
                                $return_url_parameters('cancel'),
                            ]
                        ),
                    ]

                );

                $data = [
                    'amount' => $totalprice,
                    'currency' => $this->currancy,
                    'stripe' => $comapany_stripe_data

                ];

                $comapany_stripe_data = $comapany_stripe_data ?? false;
                return new RedirectResponse($comapany_stripe_data->url);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e);
                \Log::debug($e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', __('Plan is deleted.'));
        }
    }

    public function getContentPaymentStatus($slug, Request $request)
    {
        try {
            if ($request->return_type == 'success') {
                $store = \Workdo\TVStudio\Entities\TVStudioStore::where('slug', $slug)->first();

                $cart = session()->get($slug);
                if (isset($cart['coupon'])) {
                    $coupon = $cart['coupon']['coupon'];
                }
                $products = $cart['products'];
                $sub_totalprice = 0;
                $totalprice = 0;
                $product_name = [];
                $product_id = [];

                foreach ($products as $key => $product) {
                    $product_name[] = $product['product_name'];
                    $product_id[] = $product['id'];
                    $sub_totalprice += $product['price'];
                    $totalprice += $product['price'];
                }
                if (!empty($coupon)) {
                    if ($coupon['enable_flat'] == 'off') {
                        $discount_value = ($sub_totalprice / 100) * $coupon['discount'];
                        $totalprice = $sub_totalprice - $discount_value;
                    } else {
                        $discount_value = $coupon['flat_discount'];
                        $totalprice = $sub_totalprice - $discount_value;
                    }
                }
                if ($products) {
                    \Log::debug((array) $request->all());
                    $session_data = $request->session()->get('comapany_stripe_data');
                    try {
                        $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $store->created_by, $store->workspace)) ? company_setting('stripe_secret', $store->created_by, $store->workspace) : '');
                        $paymentIntents = $stripe->paymentIntents->retrieve(
                            $session_data['stripe']->payment_intent,
                            []
                        );
                        $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
                    } catch (\Exception $exception) {
                        $receipt_url = "";
                    }
                    try {

                        $customer = Auth::guard('customers')->user();
                        $content_order = new \Workdo\TVStudio\Entities\TVStudioOrder();
                        $content_order->order_id = '#' . time();
                        $content_order->name = $customer->name;
                        $content_order->card_number = '';
                        $content_order->card_exp_month = '';
                        $content_order->card_exp_year = '';
                        $content_order->customer_id = $customer->id;
                        $content_order->content = json_encode($products);
                        $content_order->price = $totalprice;
                        $content_order->coupon = !empty($cart['coupon']['coupon']['id']) ? $cart['coupon']['coupon']['id'] : '';
                        $content_order->coupon_json = json_encode(!empty($coupon) ? $coupon : '');
                        $content_order->discount_price = !empty($cart['coupon']['discount_price']) ? $cart['coupon']['discount_price'] : '';
                        $content_order->price_currency = !empty(company_setting('defult_currancy', $store->created_by, $store->workspace_id)) ? company_setting('defult_currancy', $store->created_by, $store->workspace_id) : 'USD';
                        $content_order->txn_id = '';
                        $content_order->payment_type = 'Stripe';
                        $content_order->payment_status = 'success';
                        $content_order->receipt = $receipt_url;
                        $content_order->store_id = $store['id'];
                        $content_order->save();

                        foreach ($products as $course_id) {
                            $purchased_content = new \Workdo\TVStudio\Entities\TVStudioPurchasedContent();
                            $purchased_content->content_id = $course_id['product_id'];
                            $purchased_content->customer_id = $customer->id;
                            $purchased_content->order_id = $content_order->id;
                            $purchased_content->save();

                            $student = \Workdo\TVStudio\Entities\TVStudioCustomer::where('id', $purchased_content->customer_id)->first();
                            $student->contents_id = $purchased_content->content_id;
                            $student->save();
                        }
                        session()->forget($slug);
                        $type = 'contentpayment';
                        event(new StripePaymentStatus($store, $type, $content_order));

                        return redirect()->route(
                            'tv.store-complete.complete',
                            [
                                $store->slug,
                                \Illuminate\Support\Facades\Crypt::encrypt($content_order->id),
                            ]
                        )->with('success', __('Transaction has been success'));
                    } catch (\Exception $e) {
                        return redirect()->back()->with('error', __('Transaction has been failed.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('is deleted.'));
                }
            } else {

                return redirect()->back()->with('error', __('Transaction has been failed.'));
            }
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    // holidayz
    public function BookingPayWithStripe(Request $request, $slug)
    {
        $hotel = Hotels::where('slug', $slug)->first();

        if ($hotel) {
            $grandTotal = $couponsId = 0;
            if (!auth()->guard('holiday')->user()) {
                $Carts = Cookie::get('cart');
                $Carts = json_decode($Carts, true);
                foreach ($Carts as $key => $value) {
                    //
                    $toDate = \Carbon\Carbon::parse($value['check_in']);
                    $fromDate = \Carbon\Carbon::parse($value['check_out']);

                    $days = $toDate->diffInDays($fromDate);
                    //
                    $grandTotal += $value['price'] * $value['room'] * $days;
                    $grandTotal += ($value['serviceCharge']) ? $value['serviceCharge'] : 0;
                }
            } else {
                $Carts = RoomBookingCart::where(['customer_id' => auth()->guard('holiday')->user()->id])->get();
                foreach ($Carts as $key => $value) {
                    $grandTotal += $value->price;   //  * $value->room
                    $grandTotal += ($value->service_charge) ? $value->service_charge : 0;
                }
            }

            $price = $grandTotal;
            if (!empty($request->coupon)) {
                $coupons = BookingCoupons::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($price / 100) * $coupons->discount;
                    $price = $price - $discount_value;
                    $couponsId = $coupons->id;
                    if ($coupons->limit == $usedCoupun) {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }

            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            if ($price > 0.0) {
                $stripe_formatted_price = in_array(
                    company_setting('defult_currancy', $hotel->created_by, $hotel->wokspace),
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                        'BRL'
                    ]
                ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;

                \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $hotel->created_by, $hotel->wokspace));
                $data = \Stripe\Charge::create([
                    // "amount" => 100 * $price,
                    "amount" => (int) $stripe_formatted_price,
                    "currency" => !empty(company_setting('defult_currancy', $hotel->created_by, $hotel->wokspace)) ? company_setting('defult_currancy', $hotel->created_by, $hotel->wokspace) : 'INR',
                    "source" => $request->stripeToken,
                    "description" => " Booking ",
                    "metadata" => ["order_id" => $orderID]
                ]);
            } else {
                $data['amount_refunded'] = 0;
                $data['failure_code'] = '';
                $data['paid'] = 1;
                $data['captured'] = 1;
                $data['status'] = 'succeeded';
            }

            if ($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1) {
                if (!auth()->guard('holiday')->user()) {
                    $booking_number = \Workdo\Holidayz\Entities\Utility::getLastId('room_booking', 'booking_number');
                    $booking = RoomBooking::create([
                        'booking_number' => $booking_number,
                        'user_id' => isset(auth()->guard('holiday')->user()->id) ? auth()->guard('holiday')->user()->id : 0,
                        'payment_method' => 'STRIPE',
                        'payment_status' => 1,
                        'invoice' => isset($data['receipt_url']) ? $data['receipt_url'] : '',
                        'workspace' => $hotel->workspace,
                        'created_by' => $hotel->created_by,
                        'total' => $price,
                        'coupon_id' => $couponsId,
                        'first_name' => $request->firstname,
                        'last_name' => $request->lastname,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'address' => $request->address,
                        'city' => $request->city,
                        'country' => ($request->country) ? $request->country : 'india',
                        'zipcode' => $request->zipcode,
                    ]);
                    foreach ($Carts as $key => $value) {
                        //
                        $toDate = \Carbon\Carbon::parse($value['check_in']);
                        $fromDate = \Carbon\Carbon::parse($value['check_out']);

                        $days = $toDate->diffInDays($fromDate);
                        //
                        $bookingOrder = RoomBookingOrder::create([
                            'booking_id' => $booking->id,
                            'customer_id' => isset(auth()->guard('holiday')->user()->id) ? auth()->guard('holiday')->user()->id : 0,
                            'room_id' => $value['room_id'],
                            'workspace' => $value['workspace'],
                            'check_in' => $value['check_in'],
                            'check_out' => $value['check_out'],
                            'price' => $value['price'] * $value['room'] * $days, // * $value['room']
                            'room' => $value['room'],
                            'service_charge' => $value['serviceCharge'],
                            'services' => $value['serviceIds'],
                        ]);
                        unset($Carts[$key]);
                    }
                    $cart_json = json_encode($Carts);
                    Cookie::queue('cart', $cart_json, 1440);
                } else {
                    $booking_number = \Workdo\Holidayz\Entities\Utility::getLastId('room_booking', 'booking_number');
                    $booking = RoomBooking::create([
                        'booking_number' => $booking_number,
                        'user_id' => auth()->guard('holiday')->user()->id,
                        'payment_method' => 'STRIPE',
                        'payment_status' => 1,
                        'total' => $price,
                        'coupon_id' => $couponsId,
                        'invoice' => isset($data['receipt_url']) ? $data['receipt_url'] : 'free coupon',
                        'workspace' => $hotel->workspace,
                        'created_by' => $hotel->created_by,
                    ]);
                    foreach ($Carts as $key => $value) {
                        $bookingOrder = RoomBookingOrder::create([
                            'booking_id' => $booking->id,
                            'customer_id' => auth()->guard('holiday')->user()->id,
                            'room_id' => $value->room_id,
                            'workspace' => $value->workspace,
                            'check_in' => $value->check_in,
                            'check_out' => $value->check_out,
                            'price' => $value->price,   // * $value->room
                            'room' => $value->room,
                            'service_charge' => $value->service_charge,
                            'services' => $value->services,
                        ]);
                    }
                    RoomBookingCart::where(['customer_id' => auth()->guard('holiday')->user()->id])->delete();
                }
                if (!empty($request->coupon) && $couponsId != 0) {
                    $coupons = BookingCoupons::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                    $userCoupon = new UsedBookingCoupons();
                    $userCoupon->customer_id = isset(auth()->guard('holiday')->user()->id) ? auth()->guard('holiday')->user()->id : 0;
                    $userCoupon->coupon_id = $coupons->id;
                    $userCoupon->save();
                }

                if ($data['status'] == 'succeeded') {
                    event(new CreateRoomBooking($request, $booking));
                    $type = 'roombookinginvoice';
                    event(new StripePaymentStatus($hotel, $type, $booking));

                    //Email notification
                    if (!empty(company_setting('New Room Booking By Hotel Customer', $hotel->created_by, $hotel->workspace)) && company_setting('New Room Booking By Hotel Customer', $hotel->created_by, $hotel->workspace) == true) {
                        $user = User::where('id', $hotel->created_by)->first();
                        $customer = HotelCustomer::find($booking->user_id);
                        $room = \Workdo\Holidayz\Entities\Rooms::find($bookingOrder->room_id);
                        $uArr = [
                            'hotel_customer_name' => isset($customer->name) ? $customer->name : $booking->first_name,
                            'invoice_number' => $booking->booking_number,
                            'check_in_date' => $bookingOrder->check_in,
                            'check_out_date' => $bookingOrder->check_out,
                            'room_type' => $room->type,
                            'hotel_name' => $hotel->name,
                        ];

                        try {
                            $resp = EmailTemplate::sendEmailTemplate('New Room Booking By Hotel Customer', [$user->email], $uArr);
                        } catch (\Exception $e) {
                            $resp['error'] = $e->getMessage();
                        }

                        return redirect()->route('hotel.home', $slug)->with('success', __('Booking Successfully.') . ((isset($resp['error'])) ? '<br> <span class="text-danger" style="color:red">' . $resp['error'] . '</span>' : ''));
                    }
                    return redirect()->route('hotel.home', $slug)->with('success', 'Booking Successfully. email notification is off.');
                    // return redirect()->route('hotel.home',$slug)->with('success', __('Booking successfully activated.'));
                } else {
                    return redirect()->route('hotel.home', $slug)->with('error', __('Your payment has failed.'));
                }
            } else {
                return redirect()->route('hotel.home', $slug)->with('error', __('Transaction has been failed.'));
            }
        }
    }



    public function BookinginvoicePayWithStripe(Request $request, $slug)
    {
        if ($request->type == "roombookinginvoice") {
            $hotel = Hotels::where('slug', $slug)->first();
            $user_id = !empty($hotel->created_by) ? $hotel->created_by : auth()->guard('web')->user()->id;
            $wokspace = $hotel->workspace;
        }

        self::payment_setting($user_id, $wokspace);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {

            $user = Auth::user();
            $comapany_stripe_data = '';
            $invoice_id = $slug;

            if ($hotel) {

                /* Check for code usage */
                $grandTotal = $couponsId = 0;
                if (!auth()->guard('holiday')->user()) {
                    $Carts = Cookie::get('cart');
                    $Carts = json_decode($Carts, true);
                    foreach ($Carts as $key => $value) {
                        //
                        $toDate = \Carbon\Carbon::parse($value['check_in']);
                        $fromDate = \Carbon\Carbon::parse($value['check_out']);

                        $days = $toDate->diffInDays($fromDate);
                        //
                        $grandTotal += $value['price'] * $value['room'] * $days;
                        $grandTotal += ($value['serviceCharge']) ? $value['serviceCharge'] : 0;
                    }
                } else {
                    $Carts = RoomBookingCart::where(['customer_id' => auth()->guard('holiday')->user()->id])->get();
                    foreach ($Carts as $key => $value) {
                        $grandTotal += $value->price;   // * $value->room
                        $grandTotal += ($value->service_charge) ? $value->service_charge : 0;
                    }
                }

                $price = $grandTotal;

                if (!empty($request->coupon)) {
                    $coupons = BookingCoupons::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                    if (!empty($coupons)) {
                        $usedCoupun = $coupons->used_coupon();
                        $discount_value = ($price / 100) * $coupons->discount;
                        $price = $price - $discount_value;
                        $couponsId = $coupons->id;
                        if ($coupons->limit == $usedCoupun) {
                            return redirect()->back()->with('error', __('This coupon code has expired.'));
                        }
                    } else {
                        return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                    }
                }

                if ($request->type == "roombookinginvoice") {
                    $booking_number = \Workdo\Holidayz\Entities\Utility::getLastId('room_booking', 'booking_number');
                    $printID = \Workdo\Holidayz\Entities\RoomBooking::bookingNumberFormat($booking_number, $user_id, $wokspace);
                }

                try {
                    $stripe_formatted_price = in_array(
                        company_setting('defult_currancy', $user_id, $wokspace),
                        [
                            'MGA',
                            'BIF',
                            'CLP',
                            'PYG',
                            'DJF',
                            'RWF',
                            'GNF',
                            'UGX',
                            'JPY',
                            'VND',
                            'VUV',
                            'XAF',
                            'KMF',
                            'KRW',
                            'XOF',
                            'XPF',
                            'BRL'
                        ]
                    ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;

                    $return_url_parameters = function ($return_type) {
                        return '&return_type=' . $return_type;
                    };
                    /* Initiate Stripe */
                    \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $user_id, $wokspace));
                    $code = '';

                    $comapany_stripe_data = \Stripe\Checkout\Session::create(
                        [
                            'payment_method_types' => ['card'],
                            'line_items' => [
                                [
                                    'name' => $printID,
                                    'amount' => (int) $stripe_formatted_price,
                                    'currency' => $this->currancy,
                                    'quantity' => 1,
                                ],
                            ],
                            'metadata' => [
                                'user_id' => isset($user->name) ? $user->name : 0,
                                'package_id' => $slug,
                                'code' => $code,
                            ],
                            'success_url' => route(
                                'booking.stripe',
                                [
                                    'invoice_id' => $slug,
                                    $request->type,
                                    $return_url_parameters('success'),
                                ]
                            ),
                            'cancel_url' => route(
                                'booking.stripe',
                                [
                                    'invoice_id' => $slug,
                                    $return_url_parameters('cancel'),
                                ]
                            ),
                        ]

                    );

                    $data = [
                        'amount' => $price,
                        'currency' => $this->currancy,
                        'stripe' => $comapany_stripe_data,
                        'cart' => $Carts,
                        'coupon' => $request->coupon,
                        'coupon_id' => $couponsId

                    ];
                    $request->session()->put('comapany_stripe_data', $data);
                    session()->put('guestInfo', $request->only(['firstname', 'email', 'address', 'country', 'lastname', 'phone', 'city', 'zipcode']));
                    $comapany_stripe_data = $comapany_stripe_data ?? false;
                    return new RedirectResponse($comapany_stripe_data->url);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', $e);
                    \Log::debug($e->getMessage());
                }
            } else {
                if ($request->type == 'roombookinginvoice') {

                    return redirect()->route('hotel.home', $slug)->with('error', __('Salesinvoice is deleted.'));
                }
            }
        } else {
            return redirect()->back()->with('error', __('Please Enter Stripe Details.'));
        }
    }

    public function getBookingInvoicePaymentStatus($invoice_id, Request $request, $type)
    {
        try {
            if ($request->return_type == 'success') {
                $slug = $invoice_id;
                if ($type == 'roombookinginvoice') {
                    if (!empty($invoice_id)) {
                        $invoice_id = $invoice_id;
                        $invoice = Hotels::where('slug', $invoice_id)->first();
                        \Log::debug((array) $request->all());
                        $session_data = $request->session()->get('comapany_stripe_data');
                        try {
                            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $invoice->created_by, $invoice->workspace)) ? company_setting('stripe_secret', $invoice->created_by, $invoice->workspace) : '');
                            $paymentIntents = $stripe->paymentIntents->retrieve(
                                $session_data['stripe']->payment_intent,
                                []
                            );
                            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
                        } catch (\Exception $exception) {

                            $receipt_url = "";
                        }
                        Session::forget('comapany_stripe_data');
                        $request->session()->forget('comapany_stripe_data');
                        $get_data = $session_data;
                        $guestDetails = session()->get('guestInfo');
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                        if ($invoice) {
                            try {
                                if ($request->return_type == 'success') {
                                    if (!auth()->guard('holiday')->user()) {
                                        $Carts = Cookie::get('cart');
                                        $Carts = json_decode($Carts, true);
                                        $booking_number = \Workdo\Holidayz\Entities\Utility::getLastId('room_booking', 'booking_number');
                                        $booking = RoomBooking::create([
                                            'booking_number' => $booking_number,
                                            'user_id' => isset(auth()->guard('holiday')->user()->id) ? auth()->guard('holiday')->user()->id : 0,
                                            'payment_method' => 'STRIPE',
                                            'payment_status' => 1,
                                            'invoice' => $receipt_url,
                                            'workspace' => $invoice->workspace,
                                            'created_by' => $invoice->created_by,
                                            'total' => $get_data['amount'],
                                            'coupon_id' => $get_data['coupon_id'],
                                            'first_name' => $guestDetails['firstname'],
                                            'last_name' => $guestDetails['lastname'],
                                            'email' => $guestDetails['email'],
                                            'phone' => $guestDetails['phone'],
                                            'address' => $guestDetails['address'],
                                            'city' => $guestDetails['city'],
                                            'country' => ($guestDetails['country']) ? $guestDetails['country'] : 'india',
                                            'zipcode' => $guestDetails['zipcode'],
                                        ]);
                                        foreach ($Carts as $key => $value) {
                                            //
                                            $toDate = \Carbon\Carbon::parse($value['check_in']);
                                            $fromDate = \Carbon\Carbon::parse($value['check_out']);

                                            $days = $toDate->diffInDays($fromDate);
                                            //
                                            $bookingOrder = RoomBookingOrder::create([
                                                'booking_id' => $booking->id,
                                                'customer_id' => isset(auth()->guard('holiday')->user()->id) ? auth()->guard('holiday')->user()->id : 0,
                                                'room_id' => $value['room_id'],
                                                'workspace' => $value['workspace'],
                                                'check_in' => $value['check_in'],
                                                'check_out' => $value['check_out'],
                                                'price' => $value['price'] * $value['room'] * $days,
                                                'room' => $value['room'],
                                                'service_charge' => $value['serviceCharge'],
                                                'services' => $value['serviceIds'],
                                            ]);
                                            unset($Carts[$key]);
                                        }
                                        $cart_json = json_encode($Carts);
                                        Cookie::queue('cart', $cart_json, 1440);

                                        if (!empty($get_data['coupon']) && $get_data['coupon_id'] != 0) {
                                            $coupons = BookingCoupons::where('code', strtoupper($get_data['coupon']))->where('is_active', '1')->first();
                                            $userCoupon = new UsedBookingCoupons();
                                            $userCoupon->customer_id = isset(auth()->guard('holiday')->user()->id) ? auth()->guard('holiday')->user()->id : 0;
                                            $userCoupon->coupon_id = $coupons->id;
                                            $userCoupon->save();
                                        }
                                    } else {
                                        $Carts = RoomBookingCart::where(['customer_id' => auth()->guard('holiday')->user()->id])->get();
                                        $booking_number = \Workdo\Holidayz\Entities\Utility::getLastId('room_booking', 'booking_number');
                                        $booking = RoomBooking::create([
                                            'booking_number' => $booking_number,
                                            'user_id' => auth()->guard('holiday')->user()->id,
                                            'payment_method' => 'STRIPE',
                                            'payment_status' => 1,
                                            'total' => $get_data['amount'],
                                            'coupon_id' => $get_data['coupon_id'],
                                            'invoice' => $receipt_url,
                                            'workspace' => $invoice->workspace,
                                            'created_by' => $invoice->created_by,
                                        ]);
                                        foreach ($get_data['cart'] as $key => $value) {
                                            $bookingOrder = RoomBookingOrder::create([
                                                'booking_id' => $booking->id,
                                                'customer_id' => auth()->guard('holiday')->user()->id,
                                                'room_id' => $value->room_id,
                                                'workspace' => $value->workspace,
                                                'check_in' => $value->check_in,
                                                'check_out' => $value->check_out,
                                                'price' => $value->price,   //  * $value->room
                                                'room' => $value->room,
                                                'service_charge' => $value->service_charge,
                                                'services' => $value->services,
                                            ]);
                                        }
                                        RoomBookingCart::where(['customer_id' => auth()->guard('holiday')->user()->id])->delete();

                                        if (!empty($get_data['coupon']) && $get_data['coupon_id'] != 0) {
                                            $coupons = BookingCoupons::where('code', strtoupper($get_data['coupon']))->where('is_active', '1')->first();
                                            $userCoupon = new UsedBookingCoupons();
                                            $userCoupon->customer_id = isset(auth()->guard('holiday')->user()->id) ? auth()->guard('holiday')->user()->id : 0;
                                            $userCoupon->coupon_id = $coupons->id;
                                            $userCoupon->save();
                                        }
                                    }

                                    event(new CreateRoomBooking($request, $booking));
                                    $type = 'roombookinginvoice';
                                    event(new StripePaymentStatus($invoice, $type, $booking));

                                    //Email notification
                                    if (!empty(company_setting('New Room Booking By Hotel Customer', $invoice->created_by, $invoice->workspace)) && company_setting('New Room Booking By Hotel Customer', $invoice->created_by, $invoice->workspace) == true) {
                                        $user = User::where('id', $invoice->created_by)->first();
                                        $customer = HotelCustomer::find($booking->user_id);
                                        $room = \Workdo\Holidayz\Entities\Rooms::find($bookingOrder->room_id);
                                        $uArr = [
                                            'hotel_customer_name' => isset($customer->name) ? $customer->name : $booking->first_name,
                                            'invoice_number' => $booking->booking_number,
                                            'check_in_date' => $bookingOrder->check_in,
                                            'check_out_date' => $bookingOrder->check_out,
                                            'room_type' => $room->type,
                                            'hotel_name' => $invoice->name,
                                        ];

                                        try {
                                            $resp = EmailTemplate::sendEmailTemplate('New Room Booking By Hotel Customer', [$user->email], $uArr);
                                        } catch (\Exception $e) {
                                            $resp['error'] = $e->getMessage();
                                        }

                                        return redirect()->route('hotel.home', $slug)->with('success', __('Booking Successfully.') . ((isset($resp['error'])) ? '<br> <span class="text-danger" style="color:red">' . $resp['error'] . '</span>' : ''));
                                    }
                                    return redirect()->route('hotel.home', $slug)->with('success', 'Booking Successfully. email notification is off.');

                                    return redirect()->route('hotel.home', $slug)->with('success', __('Payment added Successfully'));
                                } else {

                                    return redirect()->route('hotel.home', $slug)->with('error', __('Transaction has been failed!'));
                                }
                            } catch (\Exception $e) {
                                return redirect()->route('hotel.home', $slug)->with('error', __('Transaction has been failed!'));
                            }
                        } else {
                            return redirect()->route('hotel.home', $slug)->with('error', __('Invoice not found.'));
                        }
                    } else {
                        return redirect()->route('hotel.home', $slug)->with('error', __('Invoice not found.'));
                    }
                }
            } else {

                return redirect()->back()->with('error', __('Transaction has been failed.'));
            }
        } catch (\Exception $exception) {

            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    public function propertyPayWithStripe(Request $request)
    {
        $invoice = \Workdo\PropertyManagement\Entities\PropertyInvoice::find($request->invoice_id);
        $user_id = $invoice->created_by;
        $wokspace = $invoice->workspace;
        self::payment_setting($user_id, $wokspace);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {

            $tenant = Auth::user();
            $user = User::find($tenant->user_id);

            $comapany_stripe_data = '';
            if ($invoice) {

                $invoiceID = $invoice->id;
                $printID = \Workdo\PropertyManagement\Entities\PropertyInvoice::tenantNumberFormat($invoiceID, $user_id, $wokspace);

                /* Check for code usage */
                $price = $invoice->total_amount;

                try {

                    $stripe_formatted_price = in_array(
                        company_setting('defult_currancy', $user_id, $wokspace),
                        [
                            'MGA',
                            'BIF',
                            'CLP',
                            'PYG',
                            'DJF',
                            'RWF',
                            'GNF',
                            'UGX',
                            'JPY',
                            'VND',
                            'VUV',
                            'XAF',
                            'KMF',
                            'KRW',
                            'XOF',
                            'XPF',
                            'BRL'
                        ]
                    ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;

                    $return_url_parameters = function ($return_type) {
                        return '&return_type=' . $return_type;
                    };
                    /* Initiate Stripe */
                    \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $user_id, $wokspace));
                    $code = '';

                    $comapany_stripe_data = \Stripe\Checkout\Session::create(
                        [
                            'payment_method_types' => ['card'],
                            'line_items' => [
                                [
                                    'price_data' => [
                                        'currency' => $this->currancy,
                                        'unit_amount' => (int) $stripe_formatted_price,
                                        'product_data' => [
                                            'name' => $printID,
                                        ],
                                    ],
                                    'quantity' => 1,
                                ],
                            ],
                            'mode' => 'payment',
                            'metadata' => [
                                'user_id' => isset($user->name) ? $user->name : 0,
                                'package_id' => $invoiceID,
                                'code' => $code,
                            ],
                            'success_url' => route(
                                'property.get.payment.status.stripe',
                                [
                                    'invoice_id' => encrypt($invoiceID),
                                    $return_url_parameters('success'),
                                ]
                            ),
                            'cancel_url' => route(
                                'property.get.payment.status.stripe',
                                [
                                    'invoice_id' => encrypt($invoiceID),
                                    $return_url_parameters('cancel'),
                                ]
                            ),
                        ]

                    );

                    $data = [
                        'amount' => $price,
                        'currency' => $this->currancy,
                        'stripe' => $comapany_stripe_data

                    ];
                    $request->session()->put('comapany_stripe_data', $data);

                    $comapany_stripe_data = $comapany_stripe_data ?? false;
                    return new RedirectResponse($comapany_stripe_data->url);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', $e);
                    \Log::debug($e->getMessage());
                }
            } else {
                return redirect()->route('property-invoice.index')->with('error', __('Invoice is deleted.'));
            }
        } else {
            return redirect()->back()->with('error', __('Please Enter Stripe Details.'));
        }
    }

    public function propertyGetStripeStatus(Request $request)
    {
        \Log::debug((array) $request->all());
        try {
            if ($request->return_type == 'success') {
                $invoice_id = $request->invoice_id;
                if (!empty($invoice_id)) {
                    $invoice_id = decrypt($invoice_id);
                    $invoice = \Workdo\PropertyManagement\Entities\PropertyInvoice::find($invoice_id);
                    $session_data = $request->session()->get('comapany_stripe_data');
                    try {
                        $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $invoice->created_by, $invoice->workspace)) ? company_setting('stripe_secret', $invoice->created_by, $invoice->workspace) : '');
                        $paymentIntents = $stripe->paymentIntents->retrieve(
                            $session_data['stripe']->payment_intent,
                            []
                        );
                        $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
                    } catch (\Exception $exception) {
                        $receipt_url = "";
                    }
                    Session::forget('comapany_stripe_data');
                    $request->session()->forget('comapany_stripe_data');
                    $get_data = $session_data;
                    // $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $tenant = \Workdo\PropertyManagement\Entities\Tenant::where('user_id', Auth::user()->id)->first();
                    if ($invoice) {
                        try {
                            if ($request->return_type == 'success') {
                                $invoice_payment = new \Workdo\PropertyManagement\Entities\PropertyInvoicePayment();
                                $invoice_payment->invoice_id = $invoice_id;
                                $invoice_payment->user_id = $tenant->id;
                                $invoice_payment->date = date('Y-m-d');
                                $invoice_payment->amount = isset($get_data['amount']) ? $get_data['amount'] : 0;
                                $invoice_payment->payment_type = 'Stripe';
                                $invoice_payment->receipt = $receipt_url;
                                $invoice_payment->save();


                                $invoice->status = 'Paid';
                                $invoice->save();

                                $type = 'propertyinvoice';
                                event(new StripePaymentStatus($invoice, $type, $invoice_payment));

                                //Email notification
                                if (!empty(company_setting('Property Invoice Payment Create', $invoice->created_by, $invoice->workspace)) && company_setting('Property Invoice Payment Create', $invoice->created_by, $invoice->workspace) == true) {
                                    // $user = User::where('id',$invoice->created_by)->first();
                                    $user = User::find(Auth::user()->id);
                                    $uArr = [
                                        'payment_name' => isset(Auth::user()->name) ? Auth::user()->name : '',
                                        'invoice_number' => \Workdo\PropertyManagement\Entities\PropertyInvoice::tenantNumberFormat($invoice_id),
                                        'payment_amount' => $invoice_payment->amount,
                                        'payment_date' => $invoice_payment->date,
                                    ];

                                    try {
                                        $resp = EmailTemplate::sendEmailTemplate('Property Invoice Payment Create', [$user->email], $uArr);
                                    } catch (\Exception $e) {
                                        $resp['error'] = $e->getMessage();
                                    }

                                    return redirect()->route('property-invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __('Invoice paid Successfully.') . ((isset($resp['error'])) ? '<br> <span class="text-danger" style="color:red">' . $resp['error'] . '</span>' : ''));
                                }

                                return redirect()->route('property-invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __('Invoice paid Successfully. email notification is off.'));
                            } else {
                                return redirect()->route('property-invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Transaction has been failed!'));
                            }
                        } catch (\Exception $e) {

                            return redirect()->route('property-invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Transaction has been failed!'));
                        }
                    } else {

                        return redirect()->route('property-invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Invoice not found.'));
                    }
                } else {

                    return redirect()->route('property-invoice.show', $invoice_id)->with('error', __('Invoice not found.'));
                }
            } else {

                return redirect()->back()->with('error', __('Transaction has been failed.'));
            }
        } catch (\Exception $exception) {

            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    public function memberplanPayWithStripe(Request $request)
    {
        $membershipplan = AssignMembershipPlan::where('id', $request->membershipplan_id)->first();
        self::payment_setting($membershipplan->created_by, $membershipplan->workspace);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {

            $user      = User::find($membershipplan->user_id);
            $validator = Validator::make(
                $request->all(),
                [
                    'amount' => 'required|numeric',
                    'membershipplan_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            $comapany_stripe_data = '';
            $printID = GymMember::gymmemberNumberFormat($membershipplan->member_id, $membershipplan->created_by, $membershipplan->workspace);
            if ($membershipplan) {

                /* Check for code usage */
                $price = $request->amount;

                try {

                    $stripe_formatted_price = in_array(
                        company_setting('defult_currancy', $membershipplan->created_by, $membershipplan->workspace),
                        [
                            'MGA',
                            'BIF',
                            'CLP',
                            'PYG',
                            'DJF',
                            'RWF',
                            'GNF',
                            'UGX',
                            'JPY',
                            'VND',
                            'VUV',
                            'XAF',
                            'KMF',
                            'KRW',
                            'XOF',
                            'XPF',
                            'BRL'
                        ]
                    ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;

                    $return_url_parameters = function ($return_type) {
                        return '&return_type=' . $return_type;
                    };
                    /* Initiate Stripe */
                    \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $membershipplan->created_by, $membershipplan->workspace));
                    $code = '';

                    $comapany_stripe_data = \Stripe\Checkout\Session::create(
                        [
                            'payment_method_types' => ['card'],
                            'line_items' => [
                                [
                                    'price_data' => [
                                        'currency' => $this->currancy,
                                        'unit_amount' => (int)$stripe_formatted_price,
                                        'product_data' => [
                                            'name' => $printID,
                                        ],
                                    ],
                                    'quantity' => 1,
                                ],
                            ],
                            'mode' => 'payment',
                            'metadata' => [
                                'user_id' => isset($user->name) ? $user->name : 0,
                                'package_id' => $membershipplan->id,
                                'code' => $code,
                            ],
                            'success_url' => route(
                                'memberplan.stripe',
                                [
                                    'membershipplan_id' => encrypt($membershipplan->id),
                                    $return_url_parameters('success'),
                                ]
                            ),
                            'cancel_url' => route(
                                'memberplan.stripe',
                                [
                                    'membershipplan_id' => encrypt($membershipplan->id),
                                    $return_url_parameters('cancel'),
                                ]
                            ),
                        ]

                    );

                    $data           = [
                        'amount' => $price,
                        'currency' => $this->currancy,
                        'stripe' => $comapany_stripe_data

                    ];
                    $request->session()->put('comapany_stripe_data', $data);

                    $comapany_stripe_data = $comapany_stripe_data ?? false;
                    return new RedirectResponse($comapany_stripe_data->url);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', $e);
                    \Log::debug($e->getMessage());
                }
            } else {

                return redirect()->route('pay.membership.plan', encrypt($membershipplan->user_id))->with('error', __('Membership Plan is deleted.'));
            }
        } else {
            return redirect()->back()->with('error', __('Please Enter Stripe Details.'));
        }
    }

    public function getMemberPlanPaymentStatus($membershipplan_id, Request $request)
    {
        try {
            $membershipplan_id = decrypt($membershipplan_id);
            $membershipplan = \Workdo\GymManagement\Entities\AssignMembershipPlan::where('id', $membershipplan_id)->first();
            self::payment_setting($membershipplan->created_by, $membershipplan->workspace);
            if ($request->return_type == 'success') {
                if (!empty($membershipplan_id)) {
                    \Log::debug((array)$request->all());
                    $session_data = $request->session()->get('comapany_stripe_data');
                    try {
                        $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $membershipplan->created_by, $membershipplan->workspace)) ? company_setting('stripe_secret', $membershipplan->created_by, $membershipplan->workspace) : '');
                        $paymentIntents = $stripe->paymentIntents->retrieve(
                            $session_data['stripe']->payment_intent,
                            []
                        );
                        $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
                    } catch (\Exception $exception) {
                        $receipt_url = "";
                    }
                    Session::forget('comapany_stripe_data');
                    $request->session()->forget('comapany_stripe_data');
                    $get_data   = $session_data;
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    if ($membershipplan) {
                        try {
                            if ($request->return_type == 'success') {
                                $membershipplan_payment                  = new \Workdo\GymManagement\Entities\MembershipPlanPayment();
                                $membershipplan_payment->member_id       = !empty($membershipplan->member_id) ? $membershipplan->member_id : null;
                                $membershipplan_payment->user_id         = $membershipplan->user_id;
                                $membershipplan_payment->date            = date('Y-m-d');
                                $membershipplan_payment->amount          = isset($get_data['amount']) ? $get_data['amount'] : 0;
                                $membershipplan_payment->order_id        = $orderID;
                                $membershipplan_payment->currency        = isset($get_data['currency']) ? $get_data['currency'] : 'INR';
                                $membershipplan_payment->payment_type    = 'Stripe';
                                $membershipplan_payment->receipt         = $receipt_url;
                                $membershipplan_payment->save();

                                $type = 'membershipplan';

                                event(new StripePaymentStatus($membershipplan, $type, $membershipplan_payment));

                                return redirect()->route('pay.membership.plan', encrypt($membershipplan->user_id))->with('success', __('Payment added Successfully'));
                            } else {
                                return redirect()->route('pay.membership.plan', encrypt($membershipplan->user_id))->with('error', __('Transaction has been failed!'));
                            }
                        } catch (\Exception $e) {
                            return redirect()->route('pay.membership.plan', encrypt($membershipplan->user_id))->with('error', __('Transaction has been failed!'));
                        }
                    } else {


                        return redirect()->route('pay.membership.plan', encrypt($membershipplan->user_id))->with('error', __('Membership Plan not found.'));
                    }
                } else {

                    return redirect()->route('pay.membership.plan', encrypt($membershipplan->user_id))->with('error', __('Membership Plan not found.'));
                }
            } else {

                return redirect()->back()->with('error', __('Transaction has been failed.'));
            }
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    //Beauty Spa Payment
    public function BeautySpaPayWithStripe(Request $request, $slug, $lang = '')
    {
        $service = BeautyService::find($request->service_id);
        $price = $service->price * $request->person;
        $data = $request->all();
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $admin_currancy = company_setting('defult_currancy', $service->created_by, $service->wokspace);
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        if ($service) {
            try {
                $payment_type = 'onetime';
                /* Payment details */
                $code = '';
                /* Final price */
                $stripe_formatted_price = in_array(
                    company_setting('defult_currancy', $service->created_by, $service->wokspace),
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                        'BRL'
                    ]
                ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
                $return_url_parameters = function ($return_type) {
                    return '&return_type=' . $return_type;
                };
                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $service->created_by, $service->wokspace));

                $stripe_session = \Stripe\Checkout\Session::create(
                    [
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' =>  company_setting('defult_currancy', $service->created_by, $service->wokspace),
                                    'unit_amount' => (int)$stripe_formatted_price,
                                    'product_data' => [
                                        'name' => !empty($service->name) ? $service->name : 'Basic Package',
                                        'description' => 'stripe payment',
                                    ],
                                ],
                                'quantity' => 1,
                            ],
                        ],
                        'mode' => 'payment',
                        'success_url' => route(
                            'beauty.spa.stripe',
                            [
                                'slug' => $slug,
                                'price' => $price,
                                $return_url_parameters('success'),
                            ]
                        ),
                        'cancel_url' => route(
                            'beauty.spa.stripe',
                            [
                                'slug' => $slug,
                                $return_url_parameters('cancel'),
                            ]
                        ),

                    ]
                );
                session()->put('beauty_spa_variable', $data);
                $request->session()->put('stripe_session', $stripe_session);
                $stripe_session = $stripe_session ?? false;
            } catch (\Exception $e) {
                \Log::debug($e->getMessage());
            }
            return view('beauty-spa-management::booking.style', compact('stripe_session', 'service'));
        } else {
            $msg = __('Plan is deleted.');
            return redirect()->back()->with('msg', $msg);
        }
    }

    public function getBeautySpaPaymentStatus(Request $request, $slug)
    {
        $workspace = WorkSpace::where('id', $slug)->first();

        try {
            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
            $paymentIntents = $stripe->paymentIntents->retrieve(
                $request->session()->get('stripe_session')->payment_intent,
                []
            );
            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
        } catch (\Exception $exception) {
            $receipt_url = "";
        }
        try {
            if ($request->return_type == 'success' && !empty(session()->get('beauty_spa_variable'))) {
                $data = session()->get('beauty_spa_variable');

                $beautybooking                  = new BeautyBooking();
                $beautybooking->name            = $data['name'];
                $beautybooking->service         = $data['service_id'];
                $beautybooking->date            = $data['date'];
                $beautybooking->number          = $data['number'];
                $beautybooking->email           = $data['email'];
                $beautybooking->stage_id        = 2;
                $beautybooking->person          = $data['person'];
                $beautybooking->gender          = $data['gender'];
                $beautybooking->start_time      = $data['start_time'];
                $beautybooking->end_time        = $data['end_time'];
                $beautybooking->payment_option  = $data['payment_option'];
                $beautybooking->workspace       = $workspace->id;
                $beautybooking->created_by      = $workspace->created_by;
                $beautybooking->save();

                $beautyreceipt                  = new BeautyReceipt();
                $beautyreceipt->booking_id      = $beautybooking->id;
                $beautyreceipt->name            = $beautybooking->name;
                $beautyreceipt->service         = $beautybooking->service;
                $beautyreceipt->number          = $beautybooking->number;
                $beautyreceipt->gender          = $beautybooking->gender;
                $beautyreceipt->start_time      = $beautybooking->start_time;
                $beautyreceipt->end_time        = $beautybooking->end_time;
                $beautyreceipt->price           = $request->price;
                $beautyreceipt->payment_type    =  'Stripe';
                $beautyreceipt->workspace       = $workspace->id;
                $beautyreceipt->created_by      = $workspace->created_by;
                $beautyreceipt->save();

                $type = 'beautypayment';

                event(new StripePaymentStatus($beautybooking, $type, $beautyreceipt));
                $msg = __('Payment has been success.');
                return redirect()->route('beauty.home', [$slug],)->with('msg', $msg);
            } else {
                $msg = __('Transaction has been failed');
                return redirect()->back()->with('msg', $msg);
            }
        } catch (\Exception $exception) {
            $msg = __('Transaction has been failed');
            return redirect()->back()->with('msg', $msg);
        }
    }

    //Movie Show Booking Payment
    public function MovieShowBookingPayWithStripe(Request $request, $slug, $lang = '')
    {

        $seatsData = json_decode($request->input('seatsData'), true);
        $workspace = WorkSpace::where('slug', $slug)->first();
        $price = $request->totalPrice;
        $data = $request->all();

        self::payment_setting($workspace->created_by, $workspace->id);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {
            try {
                $stripe_formatted_price = in_array(
                    company_setting('defult_currancy', $workspace->created_by, $workspace->id),
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                        'BRL'
                    ]
                ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;

                $return_url_parameters = function ($return_type) {
                    return '&return_type=' . $return_type;
                };
                /* Initiate Stripe */

                \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $workspace->created_by, $workspace->id));
                $code = '';

                $comapany_stripe_data = \Stripe\Checkout\Session::create(
                    [
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' =>  company_setting('defult_currancy', $workspace->created_by, $workspace->id),
                                    'unit_amount' => (int)$stripe_formatted_price,
                                    'product_data' => [
                                        'name' => !empty($workspace->name) ? $workspace->name : 'Basic Package',
                                        'description' => 'stripe payment',
                                    ],
                                ],
                                'quantity' => 1,
                            ],
                        ],
                        'mode' => 'payment',
                        'success_url' => route(
                            'movie.show.booking.stripe',
                            [
                                'slug' => $slug,
                                'price' => $price,
                                $return_url_parameters('success'),
                            ]
                        ),
                        'cancel_url' => route(
                            'movie.show.booking.stripe',
                            [
                                'slug' => $slug,
                                $return_url_parameters('cancel'),
                            ]
                        ),
                    ]
                );

                $data = [
                    'amount' => $price,
                    'slug' => $slug,
                    'currency' => $this->currancy,
                    'request_data' => $request->all(),
                    'stripe' => $comapany_stripe_data,
                ];
                $request->session()->put('movie_show_data', $data);
                session()->put($request->all(), $slug, $workspace->id);
                $comapany_stripe_data = $comapany_stripe_data ?? false;

                return new RedirectResponse($comapany_stripe_data->url);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e);
                \Log::debug($e->getMessage());
            }
        }
    }

    public function getMovieShowBookingPaymentStatus(Request $request, $slug)
    {
        $workspace = WorkSpace::where('slug', $slug)->first();
        try {
            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
            $paymentIntents = $stripe->paymentIntents->retrieve(
                $request->session()->get('stripe_session')->payment_intent,
                []
            );
            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
        } catch (\Exception $exception) {
            $receipt_url = "";
        }

        try {
            if ($request->return_type == 'success' && !empty(session()->get('movie_show_data'))) {
                $data = session()->get('movie_show_data');
                $seatsData = json_decode($data['request_data']['seatsData'], true);
                $orderID = crc32(uniqid('', true));

                foreach ($seatsData as $seatData) {

                    //     // Create a new SeatBooking instance and set its properties
                    $seatBooking = new MovieSeatBooking();
                    $seatBooking->movie_id = $seatData['movieId'];
                    $seatBooking->seating_template_detail_id = $seatData['seatingTemplateDetailId'];
                    $seatBooking->row = $seatData['row'];
                    $seatBooking->column = $seatData['column'];
                    $seatBooking->booking_date = $data['request_data']['date'];
                    $seatBooking->booking_show_time = $data['request_data']['ShowTime'];
                    $seatBooking->workspace = $workspace->id;
                    $seatBooking->created_by = $workspace->created_by;
                    $seatBooking->save();
                }


                $movieseatbooking                  = new MovieSeatBookingOrder();
                $movieseatbooking->movie_id        = $data['request_data']['movieId'];
                $movieseatbooking->order_id        = $orderID;
                $movieseatbooking->seat_data       = $data['request_data']['seatsData'];
                $movieseatbooking->booking_show_time = $data['request_data']['ShowTime'];
                $movieseatbooking->name            = $data['request_data']['name'];
                $movieseatbooking->email           = $data['request_data']['email'];
                $movieseatbooking->mobile_number   = $data['request_data']['mobile_number'];
                $movieseatbooking->price           = $data['amount'];
                $movieseatbooking->payment_type    =  'Stripe';
                $movieseatbooking->payment_status       = 'successful';
                $movieseatbooking->workspace       = $workspace->id;
                $movieseatbooking->created_by      = $workspace->created_by;

                $movieseatbooking->save();

                $type = 'movieshowbookingpayment';

                event(new StripePaymentStatus($seatBooking, $type, $movieseatbooking));

                return redirect()->route('movie.print.ticket', [
                    'slug' => $slug,
                    'seatBooking' => Crypt::encrypt($seatBooking->id),
                    'movieseatbooking' => Crypt::encrypt($movieseatbooking->id),
                ])->with('success', __('Payment has been successful'));
            } else {
                $msg = __('Transaction has been failed');
                return redirect()->back()->with('msg', $msg);
            }
        } catch (\Exception $exception) {
            $msg = __('Transaction has been failed');
            return redirect()->back()->with('msg', $msg);
        }
    }

    //Parking Payment
    public function parkingPayWithStripe(Request $request, $slug, $lang = '')
    {
        if ($request->payment) {

            $parking = Parking::find($request->parking_id);
            if ($lang == '') {
                $lang = !empty(company_setting('defult_currancy', $parking->created_by, $parking->workspace)) ? company_setting('defult_currancy', $parking->created_by, $parking->workspace) : 'en';
            }
            \App::setLocale($lang);
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            self::payment_setting($parking->created_by, $parking->wokspace_id);
            $admin_currancy = $this->currancy;
            $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

            if (!in_array($admin_currancy, $supported_currencies)) {
                return redirect()->back()->with('error', __('Currency is not supported.'));
            }
            $return_url_parameters = function ($return_type) {
                return '&return_type=' . $return_type;
            };
            if ($parking->amount <= 0) {
                $parking_payment                       = new Payment();
                $parking_payment->parking_id           = $parking->id;
                $parking_payment->amount               = isset($parking['amount']) ? $parking['amount'] : 0;
                $parking_payment->name                 = $parking->name;
                $parking_payment->order_id             = '#' . time();
                $parking_payment->amount_currency      = !empty(company_setting('defult_currancy', $parking->created_by, $parking->workspace)) ? company_setting('defult_currancy', $parking->created_by, $parking->workspace) : 'USD';
                $parking_payment->payment_type         = 'Stripe';
                $parking_payment->payment_status       = 'successful';
                $parking_payment->receipt              = '';
                $parking_payment->workspace            = $parking->workspace;
                $parking_payment->created_by           = $parking->created_by;
                $parking_payment->save();
                //parking status update
                $parking->status = 2; //paid
                $parking->save();

                $type = 'parkingpayment';
                event(new StripePaymentStatus($parking, $type, $parking_payment));
                return redirect()->route('parking.home', $slug)->with('successs', __('Payment has been success'));
            }

            if ($parking) {
                try {

                    $stripe_formatted_price = in_array(
                        company_setting('defult_currancy', $parking->created_by, $parking->wokspace_id),
                        [
                            'MGA',
                            'BIF',
                            'CLP',
                            'PYG',
                            'DJF',
                            'RWF',
                            'GNF',
                            'UGX',
                            'JPY',
                            'VND',
                            'VUV',
                            'XAF',
                            'KMF',
                            'KRW',
                            'XOF',
                            'XPF',
                            'BRL'
                        ]
                    ) ? number_format($parking->amount, 2, '.', '') : number_format($parking->amount, 2, '.', '') * 100;


                    /* Initiate Stripe */
                    \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $parking->created_by, $parking->wokspace_id));
                    $code = '';

                    $comapany_stripe_data = \Stripe\Checkout\Session::create(
                        [
                            'payment_method_types' => ['card'],
                            'line_items' => [
                                [
                                    'price_data' => [
                                        'currency' => $this->currancy,
                                        'unit_amount' => (int)$stripe_formatted_price,
                                        'product_data' => [
                                            'name' => '#' . time(),
                                        ],
                                    ],
                                    'quantity' => 1,
                                ],
                            ],
                            'mode' => 'payment',
                            'metadata' => [
                                'user_id' => isset($parking->name) ? $parking->name : 0,
                                'package_id' => '',
                                'code' => $code,
                            ],
                            'success_url' => route(
                                'parking.stripe',
                                [
                                    $slug,
                                    $parking->id,
                                    $parking->amount,
                                    $lang,
                                    $return_url_parameters('success'),
                                ]
                            ),
                            'cancel_url' => route(
                                'parking.stripe',
                                [
                                    $slug,
                                    $parking->id,
                                    $parking->amount,
                                    $lang,
                                    $return_url_parameters('cancel'),
                                ]
                            ),
                        ]
                    );
                    $data           = [
                        'amount' =>   $parking->amount,
                        'currency' => $this->currancy,
                        'stripe' => $comapany_stripe_data

                    ];
                    $request->session()->put('comapany_stripe_data', $data);

                    $comapany_stripe_data = $comapany_stripe_data ?? false;
                    return new RedirectResponse($comapany_stripe_data->url);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', $e);
                    \Log::debug($e->getMessage());
                }
            } else {
                return redirect()->back()->with('error', __('Plan is deleted.'));
            }
        } else {
            return redirect()->back()->with('error', __('Please select payment method'));
        }
    }

    public function getParkingPaymentStatus(Request $request, $slug, $parking_id, $amount = '', $lang = '')
    {
        try {
            if ($request->return_type == 'success') {
                if (!empty($parking_id)) {
                    $parking    = Parking::find($parking_id);
                    if ($lang == '') {
                        $lang = !empty(company_setting('defult_currancy', $parking->created_by, $parking->workspace)) ? company_setting('defult_currancy', $parking->created_by, $parking->workspace) : 'en';
                    }
                    \App::setLocale($lang);
                    \Log::debug((array)$request->all());
                    $session_data = $request->session()->get('comapany_stripe_data');
                    try {
                        $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $parking->created_by, $parking->workspace)) ? company_setting('stripe_secret', $parking->created_by, $parking->workspace) : '');
                        $paymentIntents = $stripe->paymentIntents->retrieve(
                            $session_data['stripe']->payment_intent,
                            []
                        );
                        $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
                    } catch (\Exception $exception) {
                        $receipt_url = "";
                    }
                    Session::forget('comapany_stripe_data');
                    $request->session()->forget('comapany_stripe_data');
                    $get_data   = $session_data;
                    if ($parking) {
                        try {
                            if ($request->return_type == 'success') {
                                $parking_payment                       = new Payment();
                                $parking_payment->parking_id           = $parking_id;
                                $parking_payment->amount               = isset($get_data['amount']) ? $get_data['amount'] : 0;
                                $parking_payment->name                 = $parking->name;
                                $parking_payment->order_id             = '#' . time();
                                $parking_payment->amount_currency             = !empty(company_setting('defult_currancy', $parking->created_by, $parking->workspace)) ? company_setting('defult_currancy', $parking->created_by, $parking->workspace) : 'USD';
                                $parking_payment->payment_type         = 'Stripe';
                                $parking_payment->payment_status       = 'successful';
                                $parking_payment->receipt              = $receipt_url;
                                $parking_payment->txn_id               = '';
                                $parking_payment->workspace            = $parking->workspace;
                                $parking_payment->created_by           = $parking->created_by;
                                $parking_payment->save();
                                //parking status update
                                $parking->status = 2; //paid
                                $parking->save();

                                $type = 'parkingpayment';
                                event(new StripePaymentStatus($parking, $type, $parking_payment));
                                return redirect()->route('parking.home', [$slug, $lang])->with('successs', __('Payment has been success'));
                            } else {
                                return redirect()->back()->with('error', __('Transaction has been failed.'));
                            }
                        } catch (\Exception $e) {
                            return redirect()->back()->with('error', __('Transaction has been failed.'));
                        }
                    } else {
                        return redirect()->back()->with('error', __('Parking not found.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('Parking not found.'));
                }
            } else {
                return redirect()->back()->with('error', __('Transaction has been failed.'));
            }
        } catch (\Exception $exception) {

            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    //Bookings Payment
    public function BookingsPayWithStripe(Request $request, $slug, $lang = '')
    {
        $package = BookingsPackage::find($request->package);
        $price = $package->price;
        $data = $request->all();
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        if ($package) {
            try {
                $payment_type = 'onetime';
                /* Payment details */
                $code = '';
                /* Final price */
                $stripe_formatted_price = in_array(
                    company_setting('defult_currancy', $package->created_by, $package->wokspace),
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                        'BRL'
                    ]
                ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
                $return_url_parameters = function ($return_type) {
                    return '&return_type=' . $return_type;
                };
                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $package->created_by, $package->wokspace));

                $stripe_session = \Stripe\Checkout\Session::create(
                    [
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' =>  company_setting('defult_currancy', $package->created_by, $package->wokspace),
                                    'unit_amount' => (int)$stripe_formatted_price,
                                    'product_data' => [
                                        'name' => !empty($package->name) ? $package->name : 'Basic Package',
                                        'description' => 'stripe payment',
                                    ],
                                ],
                                'quantity' => 1,
                            ],
                        ],
                        'mode' => 'payment',
                        'success_url' => route(
                            'bookings.stripe',
                            [
                                'slug' => $slug,
                                'price' => $price,
                                $return_url_parameters('success'),
                            ]
                        ),
                        'cancel_url' => route(
                            'bookings.stripe',
                            [
                                'slug' => $slug,
                                $return_url_parameters('cancel'),
                            ]
                        ),

                    ]
                );
                session()->put('bookings_variable', $data);
                $request->session()->put('stripe_session', $stripe_session);
                $stripe_session = $stripe_session ?? false;
            } catch (\Exception $e) {
                \Log::debug($e->getMessage());
            }
            return view('bookings::Appointments.style', compact('stripe_session', 'package'));
        } else {
            $msg = __('Plan is deleted.');
            return redirect()->back()->with('msg', $msg);
        }
    }

    public function getBookingsPaymentStatus(Request $request, $slug)
    {
        $workspace = WorkSpace::where('id', $slug)->first();
        try {
            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
            $paymentIntents = $stripe->paymentIntents->retrieve(
                $request->session()->get('stripe_session')->payment_intent,
                []
            );
            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
        } catch (\Exception $exception) {
            $receipt_url = "";
        }
        try {
            if ($request->return_type == 'success' && !empty(session()->get('bookings_variable'))) {
                $data = session()->get('bookings_variable');
                $package = BookingsPackage::find($data['package']);
                $bookingscustomer = BookingsCustomer::where('email', $data['email'])->first();
                if (!empty($bookingscustomer)) {

                    $bookingscustomer->name        = isset($data['name']) ? $data['name'] : $bookingscustomer->name;
                    $bookingscustomer->client_id   = isset($data['client_name']) ? $data['client_name'] : $bookingscustomer['client_id'];
                    $bookingscustomer->number      = isset($data['number']) ? $data['number'] : $bookingscustomer->number;
                    $bookingscustomer->customer    = isset($data['customer']) ? $data['customer'] : $bookingscustomer->customer;
                    $bookingscustomer->workspace   = $workspace->id;
                    $bookingscustomer->created_by  = $workspace->created_by;
                    $bookingscustomer->save();
                } else {

                    $bookingscustomer = new BookingsCustomer();
                    $bookingscustomer->name        = isset($data['name']) ? $data['name'] : '';
                    $bookingscustomer->client_id   = isset($data['client_name']) ? $data['client_name'] : '';
                    $bookingscustomer->number      = isset($data['number']) ? $data['number'] : '';
                    $bookingscustomer->email       = isset($data['email']) ? $data['email'] : '';
                    $bookingscustomer->customer    = isset($data['customer']) ? $data['customer'] : '';
                    $bookingscustomer->workspace   = $workspace->id;
                    $bookingscustomer->created_by  = $workspace->created_by;
                    $bookingscustomer->save();
                }

                $bookingsappointment                  = new BookingsAppointment();
                $bookingsappointment->appointment_id  = $this->AppointmentNumber($slug);

                $bookingsappointment->date            = isset($data['date']) ? $data['date'] : '-';
                $bookingsappointment->service         = isset($data['service']) ? $data['service'] : '-';
                $bookingsappointment->package         = isset($data['package']) ? $data['package'] : '-';
                $bookingsappointment->staff           = isset($data['staff']) ? $data['staff'] : '-';
                $bookingsappointment->client_id       = isset($data['client_id']) ? $data['client_id'] : $bookingscustomer->id;
                $bookingsappointment->start_time      = isset($data['start_time']) ? $data['start_time'] : '-';
                $bookingsappointment->end_time        = isset($data['end_time']) ? $data['end_time'] : '-';
                $bookingsappointment->your_country    = isset($data['your_country']) ? $data['your_country'] : '-';
                $bookingsappointment->your_state      = isset($data['your_state']) ? $data['your_state'] : '-';
                $bookingsappointment->your_city       = isset($data['your_city']) ? $data['your_city'] : '-';
                $bookingsappointment->your_address    = isset($data['your_address']) ? $data['your_address'] : '-';
                $bookingsappointment->your_zip_code   = isset($data['your_zip_code']) ? $data['your_zip_code'] : '-';
                $bookingsappointment->our_country     = isset($data['our_country']) ? $data['our_country'] : '-';
                $bookingsappointment->our_state       = isset($data['our_state']) ? $data['our_state'] : '-';
                $bookingsappointment->our_city        = isset($data['our_city']) ? $data['our_city'] : '-';
                $bookingsappointment->our_zip_code    = isset($data['our_zip_code']) ? $data['our_zip_code'] : '-';
                $bookingsappointment->payment         = 'Stripe';
                $bookingsappointment->stage_id        = 2;
                $bookingsappointment->workspace       = $workspace->id;
                $bookingsappointment->created_by      = $workspace->created_by;
                $bookingsappointment->save();

                $type = 'bookingspayment';

                event(new StripePaymentStatus($package, $type, $bookingsappointment));
                $msg = __('Payment has been success.');
                return redirect()->back()->with('msg', $msg);
            } else {
                $msg = __('Transaction has been failed');
                return redirect()->back()->with('msg', $msg);
            }
        } catch (\Exception $exception) {
            $msg = __('Transaction has been failed');
            return redirect()->back()->with('msg', $msg);
        }
    }

    function AppointmentNumber($slug)
    {
        $workspace                      = WorkSpace::where('id', $slug)->first();
        $workspace = $workspace->id;
        $appointment_id = BookingsAppointment::where('workspace', $workspace)->max('appointment_id');
        if ($appointment_id == null) {
            return 1;
        } else {
            return $appointment_id + 1;
        }
    }

    //EventShow Booking Payment
    public function EventShowBookingPayWithStripe(Request $request, $slug, $lang = '')
    {
        $event = EventsMange::find($request->event);
        $workspace = WorkSpace::where('slug', $slug)->first();
        $price = $request->price * $request->person;
        $data = $request->all();
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        self::payment_setting($workspace->created_by, $workspace->id);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        if ($event) {
            try {
                $payment_type = 'onetime';
                /* Payment details */
                $code = '';
                /* Final price */
                $stripe_formatted_price = in_array(
                    company_setting('defult_currancy', $event->created_by, $event->wokspace),
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                        'BRL'
                    ]
                ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
                $return_url_parameters = function ($return_type) {
                    return '&return_type=' . $return_type;
                };
                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $event->created_by, $event->wokspace));

                $stripe_session = \Stripe\Checkout\Session::create(
                    [
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' =>  company_setting('defult_currancy', $event->created_by, $event->wokspace),
                                    'unit_amount' => (int)$stripe_formatted_price,
                                    'product_data' => [
                                        'name' => !empty($event->name) ? $event->name : 'Basic Package',
                                        'description' => 'stripe payment',
                                    ],
                                ],
                                'quantity' => 1,
                            ],
                        ],
                        'mode' => 'payment',
                        'success_url' => route(
                            'event.show.booking.stripe',
                            [
                                'slug' => $slug,
                                'price' => $price,
                                $return_url_parameters('success'),
                            ]
                        ),
                        'cancel_url' => route(
                            'event.show.booking.stripe',
                            [
                                'slug' => $slug,
                                $return_url_parameters('cancel'),
                            ]
                        ),

                    ]
                );;
                session()->put('event_show_data', $data);
                $request->session()->put('stripe_session', $stripe_session);
                $stripe_session = $stripe_session ?? false;
            } catch (\Exception $e) {
                \Log::debug($e->getMessage());
            }
            return view('events-management::booking.stripe', compact('stripe_session', 'event'));
        } else {
            $msg = __('Event is deleted.');
            return redirect()->back()->with('msg', $msg);
        }
    }

    public function getEventShowBookingPaymentStatus(Request $request, $slug)
    {

        $workspace = WorkSpace::where('slug', $slug)->first();
        try {
            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
            $paymentIntents = $stripe->paymentIntents->retrieve(
                $request->session()->get('stripe_session')->payment_intent,
                []
            );
            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
        } catch (\Exception $exception) {
            $receipt_url = "";
        }


        try {
            if ($request->return_type == 'success' && !empty(session()->get('event_show_data'))) {
                $data = session()->get('event_show_data');

                $orderID = crc32(uniqid('', true));

                $eventbooking                  = new EventsBookings();
                $eventbooking->name            = $data['name'];
                $eventbooking->event           = $data['event'];
                $eventbooking->date            = $data['date'];
                $eventbooking->number          = $data['mobile_number'];
                $eventbooking->email           = $data['email'];
                $eventbooking->person          = $data['person'];
                $eventbooking->start_time      = $data['start_time'];
                $eventbooking->end_time        = $data['end_time'];
                $eventbooking->payment_option  = $data['payment_option'];
                $eventbooking->workspace       = $workspace->id;
                $eventbooking->created_by      = $workspace->created_by;
                $eventbooking->save();

                $eventbookingorder                  = new EventBookingOrder();
                $eventbookingorder->booking_id      = $eventbooking->id;
                $eventbookingorder->order_id        = $orderID;
                $eventbookingorder->name            = $eventbooking->name;
                $eventbookingorder->event_id        = $eventbooking->event;
                $eventbookingorder->number          = $eventbooking->number;
                $eventbookingorder->start_time      = $eventbooking->start_time;
                $eventbookingorder->end_time        = $eventbooking->end_time;
                $eventbookingorder->price           = $request->price;
                $eventbookingorder->payment_type    =  'Stripe';
                $eventbookingorder->workspace       = $workspace->id;
                $eventbookingorder->created_by      = $workspace->created_by;
                $eventbookingorder->save();


                $type = 'eventshowpayment';

                event(new StripePaymentStatus($eventbooking, $type, $eventbookingorder));
                return redirect()->route('event.print.ticket', [
                    'slug' => $slug,
                    'eventBooking' => Crypt::encrypt($eventbooking->id),
                    'eventbookingorder' => Crypt::encrypt($eventbookingorder->id),
                ])->with('success', __('Payment has been successfully'));
            } else {
                $msg = __('Transaction has been failed');
                return redirect()->back()->with('msg', $msg);
            }
        } catch (\Exception $exception) {
            $msg = __('Transaction has been failed');
            return redirect()->back()->with('msg', $msg);
        }
    }

    //Facilities Payment
    public function FacilitiesPayWithStripe(Request $request, $slug, $lang = '')
    {
        $service = FacilitiesService::find($request->service_id);
        $price = $request->price;
        $data = $request->all();
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        if ($service) {
            try {
                $payment_type = 'onetime';
                /* Payment details */
                $code = '';
                /* Final price */
                $stripe_formatted_price = in_array(
                    company_setting('defult_currancy', $service->created_by, $service->wokspace),
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                        'BRL'
                    ]
                ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
                $return_url_parameters = function ($return_type) {
                    return '&return_type=' . $return_type;
                };
                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $service->created_by, $service->wokspace));

                $stripe_session = \Stripe\Checkout\Session::create(
                    [
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' =>  company_setting('defult_currancy', $service->created_by, $service->wokspace),
                                    'unit_amount' => (int)$stripe_formatted_price,
                                    'product_data' => [
                                        'name' => !empty($service->name) ? $service->name : 'Basic Package',
                                        'description' => 'stripe payment',
                                    ],
                                ],
                                'quantity' => 1,
                            ],
                        ],
                        'mode' => 'payment',
                        'success_url' => route(
                            'facilities.stripe',
                            [
                                'slug' => $slug,
                                'price' => $price,
                                $return_url_parameters('success'),
                            ]
                        ),
                        'cancel_url' => route(
                            'facilities.stripe',
                            [
                                'slug' => $slug,
                                $return_url_parameters('cancel'),
                            ]
                        ),

                    ]
                );
                session()->put('facilities_variable', $data);
                $request->session()->put('stripe_session', $stripe_session);
                $stripe_session = $stripe_session ?? false;
            } catch (\Exception $e) {
                \Log::debug($e->getMessage());
            }
            return view('facilities::frontend.style', compact('stripe_session', 'service'));
        } else {
            $msg = __('Plan is deleted.');
            return redirect()->back()->with('msg', $msg);
        }
    }

    public function getFacilitiesPaymentStatus(Request $request, $slug)
    {
        $workspace = WorkSpace::where('id', $slug)->first();

        try {
            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
            $paymentIntents = $stripe->paymentIntents->retrieve(
                $request->session()->get('stripe_session')->payment_intent,
                []
            );
            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
        } catch (\Exception $exception) {
            $receipt_url = "";
        }
        try {
            if ($request->return_type == 'success' && !empty(session()->get('facilities_variable'))) {
                $data = session()->get('facilities_variable');

                $service = FacilitiesService::find($data['service_id']);

                $facilitiesBooking                  = new FacilitiesBooking();
                $facilitiesBooking->name            = $data['name'];
                $facilitiesBooking->client_id       = 0;
                $facilitiesBooking->service         = $service->item_id ?? '';
                $facilitiesBooking->date            = $data['date'];
                $facilitiesBooking->number          = $data['number'];
                $facilitiesBooking->email           = $data['email'];
                $facilitiesBooking->gender          = $data['gender'];
                $facilitiesBooking->start_time      = $data['start_time'];
                $facilitiesBooking->end_time        = $data['end_time'];
                $facilitiesBooking->person          = $data['person'];
                $facilitiesBooking->payment_option  = $data['payment_option'];
                $facilitiesBooking->stage_id        = 2;
                $facilitiesBooking->workspace       = $workspace->id;
                $facilitiesBooking->created_by      = $workspace->created_by;
                $facilitiesBooking->save();


                $facilitiesreceipt                  = new FacilitiesReceipt();
                $facilitiesreceipt->booking_id      = $facilitiesBooking->id;
                $facilitiesreceipt->name            = $facilitiesBooking->name;
                $facilitiesreceipt->client_id       = $facilitiesBooking->client_id;
                $facilitiesreceipt->service         = $facilitiesBooking->service;
                $facilitiesreceipt->number          = $facilitiesBooking->number;
                $facilitiesreceipt->gender          = $facilitiesBooking->gender;
                $facilitiesreceipt->start_time      = $facilitiesBooking->start_time;
                $facilitiesreceipt->end_time        = $facilitiesBooking->end_time;
                $facilitiesreceipt->price           = $request->price;
                $facilitiesreceipt->workspace       = $workspace->id;
                $facilitiesreceipt->created_by      = $workspace->created_by;
                $facilitiesreceipt->save();

                $type = 'facilitiespayment';

                event(new StripePaymentStatus($facilitiesBooking, $type, $facilitiesreceipt));
                $msg = __('Payment has been success.');
                return redirect()->route('facilities.booking', [$workspace->slug],)->with('msg', $msg);
            } else {
                $msg = __('Transaction has been failed');
                return redirect()->back()->with('msg', $msg);
            }
        } catch (\Exception $exception) {
            $msg = __('Transaction has been failed');
            return redirect()->back()->with('msg', $msg);
        }
    }

    public function PropertyBookingPayWithStripe(Request $request, $slug)
    {
        $property = Property::find($request->property);
        $workspace = WorkSpace::where('slug', $slug)->first();
        if (!$workspace) {
            return redirect()->back()->with('error', __('Workspace not found.'));
        }
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:180',
                'email' => [
                    'required',
                    \Illuminate\Validation\Rule::unique('users')->where(function ($query) use ($workspace) {
                        return $query->where('created_by', $workspace->created_by)->where('workspace_id', $workspace->id);
                    })
                ],
                'mobile_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $price = $request->price;
        $data = $request->all();
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        self::payment_setting($workspace->created_by, $workspace->id);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        if ($property) {
            try {
                $payment_type = 'onetime';
                /* Payment details */
                $code = '';
                /* Final price */
                $stripe_formatted_price = in_array(
                    company_setting('defult_currancy', $property->created_by, $property->wokspace),
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                        'BRL'
                    ]
                ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
                $return_url_parameters = function ($return_type) {
                    return '&return_type=' . $return_type;
                };
                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $property->created_by, $property->wokspace));

                $stripe_session = [];
                $stripe_session = \Stripe\Checkout\Session::create(
                    [
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' =>  company_setting('defult_currancy', $property->created_by, $property->wokspace),
                                    'unit_amount' => (int)$stripe_formatted_price,
                                    'product_data' => [
                                        'name' => !empty($property->name) ? $property->name : 'Basic Package',
                                        'description' => 'stripe payment',
                                    ],
                                ],
                                'quantity' => 1,
                            ],
                        ],
                        'mode' => 'payment',
                        'success_url' => route(
                            'property.booking.stripe',
                            [
                                'slug' => $slug,
                                'price' => $price,
                                $return_url_parameters('success'),
                            ]
                        ),
                        'cancel_url' => route(
                            'property.booking.stripe',
                            [
                                'slug' => $slug,
                                $return_url_parameters('cancel'),
                            ]
                        ),

                    ]
                );

                session()->put('property_booking_variable', $data);

                $request->session()->put('stripe_session', $stripe_session);
                $stripe_session = $stripe_session ?? false;
            } catch (\Exception $e) {

                \Log::debug($e->getMessage());
            }

            return view('property-management::frontend.stripe', compact('stripe_session', 'property'));
        } else {
            $msg = __('Event is deleted.');
            return redirect()->back()->with('msg', $msg);
        }
    }
    public function GetPropertyBookingPaymentStatus(Request $request, $slug)
    {

        $workspace = WorkSpace::where('slug', $slug)->first();

        try {
            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
            $paymentIntents = $stripe->paymentIntents->retrieve(
                $request->session()->get('stripe_session')->payment_intent,
                []
            );
            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
        } catch (\Exception $exception) {
            $receipt_url = "";
        }

        try {
            if ($request->return_type == 'success' && !empty(session()->get('property_booking_variable'))) {
                $data = session()->get('property_booking_variable');
                $tenant_request                  = new PropertyTenantRequest();
                $tenant_request->name            = $data['name'];
                $tenant_request->email           = $data['email'];
                $tenant_request->mobile_no       = $data['mobile_number'];
                $tenant_request->property_id     = $data['property'];
                $tenant_request->unit_id         = $data['unit'];
                $tenant_request->total_amount    = $data['price'];
                $tenant_request->workspace       = $workspace->id;
                $tenant_request->created_by      = $workspace->created_by;
                $tenant_request->save();

                // $type = '';

                // event(new StripePaymentStatus($tenant_request, $type, $facilitiesreceipt));
                $msg =  __('Booking Request Send Successfully.');
                return redirect()->back()->with('success', $msg);
            } else {
                $msg = __('Transaction has been failed');
                return redirect()->back()->with('msg', $msg);
            }
        } catch (\Exception $exception) {
            $msg = __('Transaction has been failed');
            return redirect()->back()->with('msg', $msg);
        }
    }

    public function vehicleBookingWithStripe(Request $request, $slug, $id)
    {
        $workspace = WorkSpace::where('slug', $slug)->first();
        $price = $request->total_price;
        self::payment_setting($workspace->created_by, $workspace->id);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        $user = User::find($workspace->created_by);
        if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {
            // try {
            $stripe_formatted_price = in_array(
                company_setting('defult_currancy', $workspace->created_by, $workspace->id),
                [
                    'MGA',
                    'BIF',
                    'CLP',
                    'PYG',
                    'DJF',
                    'RWF',
                    'GNF',
                    'UGX',
                    'JPY',
                    'VND',
                    'VUV',
                    'XAF',
                    'KMF',
                    'KRW',
                    'XOF',
                    'XPF',
                    'BRL'
                ]
            ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;

            $return_url_parameters = function ($return_type) {
                return '&return_type=' . $return_type;
            };
            /* Initiate Stripe */
            \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $workspace->created_by, $workspace->id));
            $code = '';

            $comapany_stripe_data = \Stripe\Checkout\Session::create(
                [
                    'payment_method_types' => ['card'],
                    'line_items' => [
                        [
                            'name' => 'booking',
                            'amount' => (int) $stripe_formatted_price,
                            'currency' => $this->currancy,
                            'quantity' => 1,
                        ],
                    ],
                    'metadata' => [
                        'user_id' => isset($user->name) ? '' : 0,
                        'package_id' => $slug,
                        'code' => $code,
                    ],
                    'success_url' => route(
                        'vehicle.booking.status.stripe',
                        [
                            'slug' => $slug,
                            'id' => $id,
                            'request_data' => $request->all(),
                            'route_id' => $request->route_id,
                            $return_url_parameters('success'),
                        ]
                    ),
                    'cancel_url' => route(
                        'vehicle.booking.create',
                        [
                            'slug' => $slug,
                            'id' => $id,
                            $return_url_parameters('cancel'),
                        ]
                    ),
                ]
            );

            $data = [
                'amount' => $price,
                'slug' => $slug,
                'vehicle_id' => $id,
                'currency' => $this->currancy,
                'request_data' => $request->all(),
                'stripe' => $comapany_stripe_data,
            ];
            $request->session()->put('comapany_stripe_data', $data);
            session()->put($request->all(), $slug, $id);
            $comapany_stripe_data = $comapany_stripe_data ?? false;
            return new RedirectResponse($comapany_stripe_data->url);
        }
    }

    public function vehicleBookingStatus(Request $request, $slug, $id)
    {

        // if ($request->return_type == 'success') {
        $workspace = WorkSpace::where('slug', $slug)->first();
        if (!empty($workspace)) {
            $session_data = $request->session()->get('comapany_stripe_data');

            try {
                $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->workspace)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->workspace) : '');
                $paymentIntents = $stripe->paymentIntents->retrieve(
                    $session_data['stripe']->payment_intent,
                    []
                );
                $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
            } catch (\Exception $exception) {

                $receipt_url = "";
            }

            if ($workspace) {
                try {

                    $request_data = $request->request_data;
                    $vehicle_route  = json_decode($request_data['vehicle_route'], true);
                    $selectedSeats  = explode(',', $request_data['selectedSeats']);
                    $bookingData    = [];
                    foreach ($selectedSeats as $index => $seatNumber) {
                        $userData = $request_data['users'][$index + 1] ?? null;

                        if ($userData) {
                            $booking = Booking::create([
                                'vehicle_id'    => $vehicle_route['vehicle_id'],
                                'route_id'      => $request_data['route_id'],
                                'date'          => $request_data['trip_date'],
                                'seat_number'   => $seatNumber,
                                'user_name'     => $userData['name'],
                                'age'           => $userData['age'],
                                'status'        => 'Paid',
                                'email_id'      => $request_data['email'],
                                'from'          => $vehicle_route['starting_point'],
                                'to'            => $vehicle_route['dropping_point'],
                                'workspace'     => $workspace->id,
                                'created_by'    => $workspace->created_by,
                            ]);
                            $bookingData[] = [
                                'booking_id'    => $booking->id,
                                'seat_number'   => $seatNumber,
                            ];
                        }
                    }
                    $bookingIds     = [];
                    $seatNumbers    = [];
                    foreach ($bookingData as $bookingInfo) {
                        $bookingIds[]   = $bookingInfo['booking_id'];
                        $seatNumbers[]  = $bookingInfo['seat_number'];
                    }

                    // Convert arrays to comma-separated strings
                    $bookingIdsString   = implode(',', $bookingIds);
                    $seatNumbersString  = implode(',', $seatNumbers);

                    $payment = VehicleBookingPayments::create([
                        'booking_id'    => $bookingIdsString,
                        'seat_number'   => $seatNumbersString,
                        'payment_type'  => 'Stripe',
                        'amount'        => $request_data['total_price'],
                        'payment_date'  => now(),
                        'workspace'     => $workspace->id,
                        'created_by'    => $workspace->created_by,
                    ]);

                    $type = 'vehiclebookingpayment';

                    event(new StripePaymentStatus($request_data, $type, $payment));
                    return redirect()->route('vehicle.booking.checkout', [$slug, $payment->booking_id])->with('success', __('Payment added Successfully'));
                } catch (\Exception $e) {
                    return redirect()->route('vehicle.booking.manage', $slug)->with('error', __('Transaction has been failed!'));
                }
            } else {
                return redirect()->route('vehicle.booking.manage', $slug)->with('error', __('Recipt not found.'));
            }
        } else {
            return redirect()->route('vehicle.booking.manage', $slug)->with('error', __('Payment Fail.'));
        }
    }

    //vcard payment
    public function VcardEnableStripe(Request $request, $id)
    {
        if ($id) {
            $cardPayment = \Workdo\VCard\Entities\CardPayment::where('business_id', $id)->first();

            // Decode the content (assuming it's a JSON string)
            $paymentOptions = json_decode($cardPayment->content, true) ?? [];

            // Toggle the stripe status based on the checkbox state
            $status = in_array('stripe', $request->input('paymentoption', [])) ? 'on' : 'off';
            $paymentOptions['stripe'] = ['status' => $status];

            // Re-encode and save
            $jsonResult = json_encode($paymentOptions);
            $cardPayment->content = $jsonResult;
            $cardPayment->save();

            $tab = 9;
            return response()->json(['success' => true, 'status' => $status]);
        } else {
            return response()->json(['success' => false, 'message' => 'Something went wrong']);
        }
    }

    public function VcardPayWithStripe(Request $request, $id)
    {
        $paymentData = \Workdo\VCard\Entities\CardPayment::cardPaymentData($id);
        $business = \Workdo\VCard\Entities\Business::find($id);
        $amount = $paymentData->payment_amount;
        $content = json_decode($paymentData->content);
        self::payment_setting($business->created_by, $business->workspace);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        $user = User::find($business->created_by);
        if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {
            try {
                /* Final price */
                $stripe_formatted_price = in_array(
                    company_setting('defult_currancy', $business->created_by, $business->id),
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                    ]
                ) ? number_format($amount, 2, '.', '') : number_format($amount, 2, '.', '') * 100;

                $return_url_parameters = function ($return_type) {
                    return '&return_type=' . $return_type . '&payment_processor=stripe';
                };

                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $business->created_by, $business->id));
                $code = '';
                $product = $business->title;
                $code = '';

                $comapany_stripe_data = \Stripe\Checkout\Session::create(
                    [
                        'payment_method_types' => ['card'],
                        'line_items' => [[
                            'price_data' => [
                                'currency' => $this->currancy,
                                'product_data' => [
                                    'name' => $product,
                                ],
                                'unit_amount' => $stripe_formatted_price,
                            ],
                            'quantity' => 1,
                        ]],
                        'mode' => 'payment',
                        'success_url' => route(
                            'card.vcard.stripe',
                            [
                                'cardpayment_id' => $paymentData->id,
                                'business_id' => $business->id,
                                $return_url_parameters('success'),
                            ]
                        ),
                        'cancel_url' => route(
                            'card.vcard.stripe',
                            [
                                'cardpayment_id' => $paymentData->id,
                                'business_id' => $business->id,
                                $return_url_parameters('cancel'),
                            ]
                        ),
                    ]
                );
                $data = [
                    'amount' => $amount,
                    'slug' => $business->id,
                    'currency' => $this->currancy,
                    'request_data' => $request->all(),
                    'stripe' => $comapany_stripe_data,
                ];
                $request->session()->put('comapany_stripe_data', $data);
                session()->put($request->all(), $id);
                return new RedirectResponse($comapany_stripe_data->url);
            } catch (\Exception $e) {
                return redirect(url('cards/' . $business->slug))->with('error', __('Something went wrong!.'));
            }
        }
    }


    public function VcardGetStripePaymentStatus(Request $request)
    {
        $cardPayment = \Workdo\VCard\Entities\CardPayment::cardPaymentData($request->business_id);
        $business = \Workdo\VCard\Entities\Business::find($request->business_id);
        $content = json_decode($cardPayment->content);
        if ($request->return_type == 'success') {
            $cardPayment->payment_status = 'Paid';
            $cardPayment->payment_type = 'Stripe';
            $cardPayment->save();
            return redirect(url('cards/' . $business->slug))->with('success', 'Payment added succesfully');
        } else {
            return redirect(url('cards/' . $business->slug))->with('error', 'Something went wrong.');
        }
    }

    //Coworking space payment
    public function CoworkingPayWithStripe(Request $request, $slug, $lang = '')
    {

        $workspace = WorkSpace::where('slug', $slug)->first();
        if ($request->type == 'membership') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'member_name'        => 'required',
                    'email'              => 'required',
                    'phone_no'           => 'required',
                ]
            );
            $price = $request->plan_price;
        } else {
            $price = $request->amount;
            $validator = \Validator::make(
                $request->all(),
                [
                    'customer_name'   => 'required',
                    'email'           => 'required',
                    'phone_no'        => 'required',
                    'start_date_time' => 'required|date|after_or_equal:today',
                    'end_date_time'   => 'required|date|after:start_date_time',
                    'amenities.*'     => 'exists:amenities,id'
                ]
            );
        }

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->route('coworking.space.management', [$slug])->with('error', $messages->first());
        }

        $data = $request->all();

        self::payment_setting($workspace->created_by, $workspace->id);
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->route('coworking.space.management', [$slug])->with('error', __('Currency is not supported.'));
        }
        if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {

            try {
                $stripe_formatted_price = in_array(
                    company_setting('defult_currancy', $workspace->created_by, $workspace->id),
                    [
                        'MGA',
                        'BIF',
                        'CLP',
                        'PYG',
                        'DJF',
                        'RWF',
                        'GNF',
                        'UGX',
                        'JPY',
                        'VND',
                        'VUV',
                        'XAF',
                        'KMF',
                        'KRW',
                        'XOF',
                        'XPF',
                        'BRL'
                    ]
                ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;

                $return_url_parameters = function ($return_type) {
                    return '&return_type=' . $return_type;
                };
                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $workspace->created_by, $workspace->id));
                $code = '';
                $comapany_stripe_data = \Stripe\Checkout\Session::create(
                    [
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' =>  company_setting('defult_currancy', $workspace->created_by, $workspace->id),
                                    'unit_amount' => (int)$stripe_formatted_price,
                                    'product_data' => [
                                        'name' => !empty($workspace->name) ? $workspace->name : 'Basic Package',
                                        'description' => 'stripe payment',
                                    ],
                                ],
                                'quantity' => 1,
                            ],
                        ],
                        'mode' => 'payment',
                        'success_url' => route(
                            'coworking.booking.stripe',
                            [
                                'slug' => $slug,
                                'type' => $request->type,
                                'price' => $price,
                                $return_url_parameters('success'),
                            ]
                        ),
                        'cancel_url' => route(
                            'coworking.booking.stripe',
                            [
                                'slug' => $slug,
                                'type' => $request->type,
                                $return_url_parameters('cancel'),
                            ]
                        ),
                    ]
                );
                $data =  (object) [
                    'amount'       => $price,
                    'slug'         => $slug,
                    'currency'     => $this->currancy,
                    'request_data' => (object) $request->all(),
                    'stripe'       => (object) $comapany_stripe_data,
                ];
                Session::put('coworking_data', $data);
                $comapany_stripe_data = $comapany_stripe_data ?? false;
                return new RedirectResponse($comapany_stripe_data->url);
            } catch (\Exception $e) {

                return redirect()->route('coworking.space.management', [$slug])->with('error', $e);
            }
        }
    }

    public function getCoworkingPaymentStatus(Request $request, $slug)
    {
        $workspace = WorkSpace::where('slug', $slug)->first();
        try {
            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
            $paymentIntents = $stripe->paymentIntents->retrieve(
                $request->session()->get('stripe_session')->payment_intent,
                []
            );
            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
        } catch (\Exception $exception) {
            $receipt_url = "";
        }

        try {
            $data = Session::get('coworking_data');
            if ($request->return_type == 'success' && !empty($data)) {
                if ($request->type == 'membership') {
                    $currentDate = now();
                    switch ($data->request_data->duration) {
                        case 'yearly':
                            $expiryDate = $currentDate->addYear();
                            break;
                        case 'monthly':
                            $expiryDate = $currentDate->addMonth();
                            break;
                        case 'weekly':
                            $expiryDate = $currentDate->addWeek();
                            break;
                        default:
                            return redirect()->route('coworking.space.management', [$slug])->with('error', __('Invalid membership duration.'));
                    }

                    $membership                     =  new CoworkingMembership();
                    $membership->member_name        =  $data->request_data->member_name;
                    $membership->email              =  $data->request_data->email;
                    $membership->phone_no           =  $data->request_data->phone_no;
                    $membership->membership_plan_id =  $data->request_data->membership_plan_id;
                    $membership->duration           =  $data->request_data->duration;
                    $membership->payment_method     =  'Stripe';
                    $membership->plan_expiry_date   =  $expiryDate;
                    $membership->price              =  $data->request_data->plan_price;
                    $membership->plan_status        =  'active';
                    $membership->payment_status     =  'paid';
                    $membership->workspace          =  $workspace->id;
                    $membership->created_by         =  $workspace->created_by;
                    $membership->save();

                    $membership->membership_id = '#MEM0' . sprintf("%05d", $membership->id);
                    $membership->save();

                    $type = 'coworkingmembershippayment';

                    event(new StripePaymentStatus($data->request_data, $type, $membership));
                    Session::forget('coworking_data');
                    if (!empty(company_setting('New Membership Order', $membership->created_by, $membership->workspace)) && company_setting('New Membership Order', $membership->created_by, $membership->workspace) == true) {
                        $membershipData = CoworkingMembership::where('email', $data->request_data->email)
                            ->where('workspace', $workspace->id)
                            ->latest('created_at')
                            ->first();
                        $uArr = [
                            "member_name" =>   $membershipData->member_name,
                            "membership_plan_name" =>  $membershipData->membershipPlan->plan_name,
                            "membership_duration" =>   $membershipData->duration,
                            "membership_expiry_date" =>   company_date_formate($membershipData->plan_expiry_date, $membershipData->created_by, $membershipData->workspace),
                            'order_url' => route('coworking.order.membership', [$slug, \Illuminate\Support\Facades\Crypt::encrypt($membership->id)]),
                            'company_name' => env('APP_NAME'),
                        ];

                        try {
                            EmailTemplate::sendEmailTemplate('New Membership Order', [$membershipData->email], $uArr, $membershipData->created_by, $membershipData->workspace);
                            return redirect()->route('coworking.space.management', [$slug])->with('success', __('Payment Pay Successfully.Please check your mail.'));
                        } catch (\Exception $e) {
                            return redirect()->route('coworking.space.management', [$slug])->with('success', __('Transaction has been failed.'));
                        }
                    }
                    return redirect()->route('coworking.space.management', [$slug])->with('success', __('Payment Pay Successfully.'));
                } else if ($request->type == 'booking') {
                    $booking                   = new CoworkingBooking();
                    $booking->customer_name    = $data->request_data->customer_name;
                    $booking->email            = $data->request_data->email;
                    $booking->phone_no         = $data->request_data->phone_no;
                    $booking->start_date_time  = $data->request_data->start_date_time;
                    $booking->end_date_time    = $data->request_data->end_date_time;
                    $booking->booking_duration = $data->request_data->booking_duration;
                    $booking->amount           = $data->request_data->amount;
                    $booking->payment_status   = 'paid';
                    $booking->payment_method   = 'Stripe';
                    $booking->workspace        = $workspace->id;
                    $booking->created_by       = $workspace->created_by;
                    $booking->save();

                    $booking->booking_ref_id   = '#BOOKO0' . sprintf("%05d", $booking->id);
                    $booking->save();
                    $booking->amenities()->attach($data->request_data->amenities);

                    $type = 'coworkingbookingpayment';

                    event(new StripePaymentStatus($data->request_data, $type, $booking));
                    Session::forget('coworking_data');
                    if (!empty(company_setting('New Booking Order', $booking->created_by, $booking->workspace)) && company_setting('New Booking Order', $booking->created_by, $booking->workspace) == true) {
                        $amenitiesList = collect($booking->amenities)->pluck('name')->implode(', ');
                        $uArr = [
                            "customer_name"       => $booking->customer_name,
                            "amenities"           => $amenitiesList,
                            "booking_duration"    => $booking->booking_duration,
                            "start_end_date_time" => company_date_formate($booking->start_date_time, $workspace->created_by, $workspace->id) . ' ' . company_Time_formate($booking->start_date_time, $workspace->created_by, $workspace->id) . __(' To ') . company_date_formate($booking->end_date_time, $workspace->created_by, $workspace->id) . ' ' . company_Time_formate($booking->end_date_time, $workspace->created_by, $workspace->id),
                            "order_url"           => route('coworking.order.booking', [$slug, \Illuminate\Support\Facades\Crypt::encrypt($booking->id)]),
                            'company_name'        => company_setting('company_name', $booking->created_by, $booking->workspace) ?? 'Workdo',
                            'app_name'            => env('APP_NAME'),
                        ];

                        try {
                            EmailTemplate::sendEmailTemplate('New Booking Order', [$booking->email], $uArr, $booking->created_by, $booking->workspace);
                            return redirect()->route('coworking.space.management', [$slug])->with('success', __('Payment Pay Successfully.Please check your mail.'));
                        } catch (\Exception $e) {
                            return redirect()->route('coworking.space.management', [$slug])->with('success', __('Transaction has been failed.'));
                        }
                    }
                    return redirect()->route('coworking.space.management', [$slug])->with('success', __('Payment Pay Successfully.'));
                }
            } else {
                return redirect()->route('coworking.space.management', [$slug])->with('error', __('Transaction has been failed.'));
            }
        } catch (\Exception $exception) {
            return redirect()->route('coworking.space.management', [$slug])->with('error', __('Transaction has been failed.'));
        }
    }

    //Water Park Payment
    public function WaterParkPayWithStripe(Request $request, $slug, $lang = '')
    {

        $workspace = WorkSpace::where('slug', $slug)->first();
        $price = WaterParkBookings::finalPrice($request, $slug);
        $request['total_price'] = $price;
        $data = $request->all();
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $admin_currancy = company_setting('defult_currancy', $workspace->created_by, $workspace->wokspace);
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        try {
            $payment_type = 'onetime';
            $code = '';
            $stripe_formatted_price = in_array(
                company_setting('defult_currancy', $workspace->created_by, $workspace->id),
                [
                    'MGA',
                    'BIF',
                    'CLP',
                    'PYG',
                    'DJF',
                    'RWF',
                    'GNF',
                    'UGX',
                    'JPY',
                    'VND',
                    'VUV',
                    'XAF',
                    'KMF',
                    'KRW',
                    'XOF',
                    'XPF',
                    'BRL'
                ]
            ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
            $return_url_parameters = function ($return_type) {
                return '&return_type=' . $return_type;
            };
            /* Initiate Stripe */
            \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $workspace->created_by, $workspace->id));

            $stripe_session = \Stripe\Checkout\Session::create(
                [
                    'payment_method_types' => ['card'],
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' =>  company_setting('defult_currancy', $workspace->created_by, $workspace->id),
                                'unit_amount' => (int)$stripe_formatted_price,
                                'product_data' => [
                                    'name' => '#' . time(),
                                    'description' => 'stripe payment',
                                ],
                            ],
                            'quantity' => 1,
                        ],
                    ],
                    'mode' => 'payment',
                    'success_url' => route(
                        'water.park.stripe',
                        [
                            'slug' => $slug,
                            'price' => $price,
                            $return_url_parameters('success'),
                        ]
                    ),
                    'cancel_url' => route(
                        'water.park.stripe',
                        [
                            'slug' => $slug,
                            $return_url_parameters('cancel'),
                        ]
                    ),
                ]
            );
            session()->put('water_park_variable', $data);
            $request->session()->put('stripe_session', $stripe_session);
            $stripe_session = $stripe_session ?? false;
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
        }
        return view('water-park-management::bookings.style', compact('stripe_session', 'workspace'));
    }

    public function getWaterParkPaymentStatus(Request $request, $slug)
    {
        $workspace = WorkSpace::where('slug', $slug)->first();
        try {
            if ($request->return_type == 'success' && !empty(session()->get('water_park_variable'))) {
                $data = session()->get('water_park_variable');
                $bookingdata = event(new StripeWaterParkBookingsData($data, $workspace));
                event(new StripePaymentStatus($bookingdata[0], 'waterparkpayment'));
                $msg = __('Payment has been success.');
                return redirect()->route('waterpark.booking', [$slug])->with('msg', $msg);
            } else {
                $msg = __('Transaction has been failed');
                return redirect()->route('waterpark.booking', [$slug])->with('msg', $msg);
            }
        } catch (\Exception $exception) {
            $msg = __('Transaction has been failed');
            return redirect()->route('waterpark.booking', [$slug])->with('msg', $msg);
        }
    }

    //Sports And Club Payment
    public function SportsAndClubPayWithStripe(Request $request, $slug, $lang = '')
    {
        if ($request->payment) {
            $sportsGroundAndClubs = SportsGroundAndClubs::find($request->sports_club_id);
            $data = $request->all();
            $price = $data['total_amount'];
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            $admin_currancy = company_setting('defult_currancy', $sportsGroundAndClubs->created_by, $sportsGroundAndClubs->wokspace);
            $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

            if (!in_array($admin_currancy, $supported_currencies)) {
                return redirect()->back()->with('error', __('Currency is not supported.'));
            }
            if ($sportsGroundAndClubs) {
                try {
                    $payment_type = 'onetime';
                    /* Payment details */
                    $code = '';
                    /* Final price */
                    $stripe_formatted_price = in_array(
                        company_setting('defult_currancy', $sportsGroundAndClubs->created_by, $sportsGroundAndClubs->wokspace),
                        [
                            'MGA',
                            'BIF',
                            'CLP',
                            'PYG',
                            'DJF',
                            'RWF',
                            'GNF',
                            'UGX',
                            'JPY',
                            'VND',
                            'VUV',
                            'XAF',
                            'KMF',
                            'KRW',
                            'XOF',
                            'XPF',
                            'BRL'
                        ]
                    ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
                    $return_url_parameters = function ($return_type) {
                        return '&return_type=' . $return_type;
                    };
                    /* Initiate Stripe */
                    \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $sportsGroundAndClubs->created_by, $sportsGroundAndClubs->wokspace));

                    $stripe_session = \Stripe\Checkout\Session::create(
                        [
                            'payment_method_types' => ['card'],
                            'line_items' => [
                                [
                                    'price_data' => [
                                        'currency' =>  company_setting('defult_currancy', $sportsGroundAndClubs->created_by, $sportsGroundAndClubs->wokspace),
                                        'unit_amount' => (int)$stripe_formatted_price,
                                        'product_data' => [
                                            'name' => !empty($sportsGroundAndClubs->name) ? $sportsGroundAndClubs->name : 'Basic Package',
                                            'description' => 'stripe payment',
                                        ],
                                    ],
                                    'quantity' => 1,
                                ],
                            ],
                            'mode' => 'payment',
                            'success_url' => route(
                                'sports.club.stripe',
                                [
                                    'slug' => $slug,
                                    'price' => $price,
                                    $return_url_parameters('success'),
                                ]
                            ),
                            'cancel_url' => route(
                                'sports.club.stripe',
                                [
                                    'slug' => $slug,
                                    $return_url_parameters('cancel'),
                                ]
                            ),

                        ]
                    );
                    session()->put('sports_and_club_session', $data);
                    $request->session()->put('stripe_session', $stripe_session);
                    $stripe_session = $stripe_session ?? false;
                    $stripe_session = $stripe_session ?? false;
                    return new RedirectResponse($stripe_session->url);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', $e);
                    \Log::debug($e->getMessage());
                }
            } else {
                $msg = __('Sports Club And Ground Not Found.');
                return redirect()->back()->with('error', $msg);
            }
        } else {
            return redirect()->back()->with('error', __('Please select payment method'));
        }
    }

    public function getSportsAndClubPaymentStatus(Request $request, $slug)
    {
        $workspace = WorkSpace::where('slug', $slug)->first();

        try {
            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
            $paymentIntents = $stripe->paymentIntents->retrieve(
                $request->session()->get('stripe_session')->payment_intent,
                []
            );
            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
        } catch (\Exception $exception) {
            $receipt_url = "";
        }
        try {
            if ($request->return_type == 'success' && !empty(session()->get('sports_and_club_session'))) {
                $data = session()->get('sports_and_club_session');
                $sportsGroundBooking                    = new SportsClubAndGroundOrders();
                $sportsGroundBooking->sports_club_id    = $data['sports_club_id'];
                $sportsGroundBooking->name              = $data['name'];
                $sportsGroundBooking->email             = $data['email'];
                $sportsGroundBooking->mobile_no         = $data['mobile_no'];
                $sportsGroundBooking->booked_by         = $data['booked_by'];
                $sportsGroundBooking->start_time        = $data['start_time'];
                $sportsGroundBooking->end_time          = $data['end_time'];
                $sportsGroundBooking->date              = $data['date'];
                $sportsGroundBooking->start_date        = $data['start_date'];
                $sportsGroundBooking->end_date          = $data['end_date'];
                $sportsGroundBooking->total_amount      = $data['total_amount'];
                $sportsGroundBooking->purpose           = $data['purpose'];
                $sportsGroundBooking->requirements_desc = $data['requirements_desc'];
                $sportsGroundBooking->notes             = $data['notes'];
                $sportsGroundBooking->payment_type      =  'Stripe';
                $sportsGroundBooking->payment_status    = 'paid';
                $sportsGroundBooking->receipt           = $receipt_url;
                $sportsGroundBooking->workspace         = $workspace->id;
                $sportsGroundBooking->created_by        = $workspace->created_by;
                $sportsGroundBooking->save();

                $type = 'sportsclubandgroundpayment';
                event(new StripePaymentStatus($data, $type, $sportsGroundBooking));

                //Email notification
                if (!empty(company_setting('New Sports Ground And Club Booking', $sportsGroundBooking->created_by, $sportsGroundBooking->workspace)) && company_setting('New Sports Ground And Club Booking', $sportsGroundBooking->created_by, $sportsGroundBooking->workspace) == true) {
                    $sportsClub = SportsGroundAndClubs::find($sportsGroundBooking->sports_club_id);
                    $orderID    = Crypt::encrypt($sportsGroundBooking->id);
                    $responsURL = Route('sports.club.booking.email.response', ['slug' => $slug, 'orderID' => $orderID]);
                    $company = User::find($sportsClub->created_by);
                    $uArr = [
                        'sports_club_customer_name'     => $data['name'],
                        'sports_club_name'              => isset($sportsClub->name) ? $sportsClub->name : '-',
                        'sports_club_customer_email'    => $data['email'],
                        'sports_club_customer_amount'   => currency_format_with_sym($data['total_amount'], $sportsClub->created_by, $sportsClub->workspace),
                        'sports_club_booking_date'      => company_date_formate($data['date'] ?? now()->format('Y-m-d'), $sportsClub->created_by, $sportsClub->workspace),
                        'sports_club_start_time'        => isset($data['start_time']) ? $data['start_time'] : '-',
                        'sports_club_end_time'          => isset($data['end_time']) ? $data['end_time'] : '-',
                        'sports_club_start_date'        => isset($data['start_date']) ? company_date_formate($data['start_date'], $sportsClub->created_by, $sportsClub->workspace) : "-",
                        'sports_club_end_date'          => isset($data['end_date']) ? company_date_formate($data['end_date'], $sportsClub->created_by, $sportsClub->workspace) : "-",
                        'sports_club_order_url'         => $responsURL,
                        'company_name'                  => $company->name,
                    ];
                    try {
                        $resp = EmailTemplate::sendEmailTemplate(
                            'New Sports Ground And Club Booking',
                            [$data['email']],
                            $uArr,
                            $sportsClub->created_by,
                            $sportsClub->workspace
                        );
                    } catch (\Exception $e) {
                        $resp['error'] = $e->getMessage();
                    }
                    $msg = __('Payment has been success.') . ((isset($resp['error'])) ? '<br> <span class="text-danger" style="color:red">' . $resp['error'] . '</span>' : '');
                    return redirect()->route('sportsclub.and.ground.show', [$slug])->with('success', $msg);
                }

                $msg = __('Payment has been success.');
                return redirect()->route('sportsclub.and.ground.show', [$slug])->with('success', $msg);
            } else {
                $msg = __('Transaction has been failed');
                return redirect()->route('sportsclub.and.ground.show', [$slug])->with('error', $msg);
            }
        } catch (\Exception $exception) {
            $msg = __('Transaction has been failed');
            return redirect()->route('sportsclub.and.ground.show', [$slug])->with('error', $msg);
        }
    }

    public function SportsAndClubPlanPayWithStripe(Request $request, $slug)
    {
        if ($request->plan_payment) {
            $membershipplan = SportsGroundAndClubPlans::where('id', $request->plan_id)->first();
            self::payment_setting($membershipplan->created_by, $membershipplan->workspace);
            $data = $request->all();
            $workspace = WorkSpace::where('slug', $slug)->first();
            if ($data['user_type'] == 'existing-user') {
                $sportsclubmember = SportsGroundAndClubMembers::where('email', $data['member_email'])->where('workspace', $workspace->id)->where('created_by', $workspace->created_by)->first();
                if (!isset($sportsclubmember)) {
                    return redirect()->back()->with('error', 'The sports club and ground member not found.');
                }
            } else {
                $sportsclubmember = SportsGroundAndClubMembers::where('email', $data['member_email'])->where('workspace', $workspace->id)->where('created_by', $workspace->created_by)->first();
                if (isset($sportsclubmember)) {
                    return redirect()->back()->with('error', 'The sports club and ground member already exist.');
                }
            }
            $price = $data['plan_price'];
            $admin_currancy = $this->currancy;
            $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];
            if (!in_array($admin_currancy, $supported_currencies)) {
                return redirect()->back()->with('error', __('Currency is not supported.'));
            }
            if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {
                if ($membershipplan) {
                    try {
                        $payment_type = 'onetime';
                        /* Payment details */
                        $code = '';
                        /* Final price */
                        $stripe_formatted_price = in_array(
                            company_setting('defult_currancy', $membershipplan->created_by, $membershipplan->wokspace),
                            [
                                'MGA',
                                'BIF',
                                'CLP',
                                'PYG',
                                'DJF',
                                'RWF',
                                'GNF',
                                'UGX',
                                'JPY',
                                'VND',
                                'VUV',
                                'XAF',
                                'KMF',
                                'KRW',
                                'XOF',
                                'XPF',
                                'BRL'
                            ]
                        ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
                        $return_url_parameters = function ($return_type) {
                            return '&return_type=' . $return_type;
                        };
                        /* Initiate Stripe */
                        \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $membershipplan->created_by, $membershipplan->wokspace));

                        $stripe_session = \Stripe\Checkout\Session::create(
                            [
                                'payment_method_types' => ['card'],
                                'line_items' => [
                                    [
                                        'price_data' => [
                                            'currency' =>  company_setting('defult_currancy', $membershipplan->created_by, $membershipplan->wokspace),
                                            'unit_amount' => (int)$stripe_formatted_price,
                                            'product_data' => [
                                                'name' => !empty($membershipplan->name) ? $membershipplan->name : 'Basic Package',
                                                'description' => 'stripe payment',
                                            ],
                                        ],
                                        'quantity' => 1,
                                    ],
                                ],
                                'mode' => 'payment',
                                'success_url' => route(
                                    'get.sports.club.plan.pay.status',
                                    [
                                        'slug' => $slug,
                                        'price' => $price,
                                        $return_url_parameters('success'),
                                    ]
                                ),
                                'cancel_url' => route(
                                    'get.sports.club.plan.pay.status',
                                    [
                                        'slug' => $slug,
                                        $return_url_parameters('cancel'),
                                    ]
                                ),

                            ]
                        );
                        session()->put('sports_and_club_membership_session', $data);
                        $request->session()->put('stripe_session', $stripe_session);
                        $stripe_session = $stripe_session ?? false;
                        $stripe_session = $stripe_session ?? false;
                        return new RedirectResponse($stripe_session->url);
                    } catch (\Exception $e) {
                        return redirect()->back()->with('error', $e);
                        \Log::debug($e->getMessage());
                    }
                } else {
                    $msg = __('Sports Club And Ground Not Found.');
                    return redirect()->back()->with('error', $msg);
                }
            }
        } else {
            return redirect()->back()->with('error', __('Please select payment method'));
        }
    }

    public function getSportsAndClubPlanPaymentStatus(Request $request, $slug)
    {
        $workspace = WorkSpace::where('slug', $slug)->first();
        try {
            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
            $paymentIntents = $stripe->paymentIntents->retrieve(
                $request->session()->get('stripe_session')->payment_intent,
                []
            );
            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
        } catch (\Exception $exception) {
            $receipt_url = "";
        }
        try {
            if ($request->return_type == 'success' && !empty(session()->get('sports_and_club_membership_session'))) {
                $data = session()->get('sports_and_club_membership_session');
                $planID = $data['plan_id'];
                $plan = SportsGroundAndClubPlans::where('id', $planID)->first();
                $expiryDate     = null;
                $currentDate    = now();
                switch ($plan->duration) {
                    case 'yearly':
                        $expiryDate = $currentDate->copy()->addYear();
                        break;
                    case 'monthly':
                        $expiryDate = $currentDate->copy()->addMonth();
                        break;
                    case 'weekly':
                        $expiryDate = $currentDate->copy()->addWeek();
                        break;
                    case 'daily':
                        $expiryDate = $currentDate->copy()->addDay();
                        break;
                    default:
                        return redirect()->back()->with('error', __('Invalid membership duration.'));
                }

                if ($data['user_type'] == 'new-user') {
                    $groundAndClubMember                            = new SportsGroundAndClubMembers();
                    $groundAndClubMember->name                      = $data['member_name'];
                    $groundAndClubMember->email                     = $data['member_email'];
                    $groundAndClubMember->type                      = $data['gender'];
                    $groundAndClubMember->dob                       = $data['dob'];
                    $groundAndClubMember->contact_number            = $data['member_mobile'] ?? null;
                    $groundAndClubMember->address                   = $data['address'] ?? null;
                    $groundAndClubMember->notes                     = $data['notes'] ?? null;
                    $groundAndClubMember->workspace                 = $workspace->id;
                    $groundAndClubMember->created_by                = $workspace->created_by;
                    $groundAndClubMember->save();

                    $membershipOrder                            = new SportsClubAndGroundPlanSubscriptions();
                    $membershipOrder->member_id                 = $groundAndClubMember->id;
                    $membershipOrder->plan_id                   = $plan->id;
                    $membershipOrder->membership_start_date     = date('Y-m-d H:i:s');
                    $membershipOrder->membership_expiry_date    = $expiryDate;
                    $membershipOrder->payment_status            = 'Paid';
                    $membershipOrder->amount                    = $data['plan_price'];
                    $membershipOrder->payment_method            = 'Stripe';
                    $membershipOrder->receipt                   = $receipt_url;
                    $membershipOrder->workspace                 = $workspace->id;
                    $membershipOrder->created_by                = $workspace->created_by;
                    $membershipOrder->save();

                    $groundAndClubMember->subscription_id       = $membershipOrder->id;
                    $groundAndClubMember->save();

                    event(new CreateSportsClubMembers($request, $groundAndClubMember));
                } else if ($data['user_type'] == 'existing-user') {
                    $groundAndClubMember                        = SportsGroundAndClubMembers::where('email', $data['member_email'])->where('workspace', $workspace->id)->where('created_by', $workspace->created_by)->first();
                    $membershipOrder                            = new SportsClubAndGroundPlanSubscriptions();
                    $membershipOrder->member_id                 = $groundAndClubMember->id;
                    $membershipOrder->plan_id                   = $plan->id;
                    $membershipOrder->membership_start_date     = date('Y-m-d H:i:s');
                    $membershipOrder->membership_expiry_date    = $expiryDate;
                    $membershipOrder->payment_status            = 'Paid';
                    $membershipOrder->amount                    = $data['plan_price'];
                    $membershipOrder->payment_method            = 'Stripe';
                    $membershipOrder->receipt                   = $receipt_url;
                    $membershipOrder->workspace                 = $workspace->id;
                    $membershipOrder->created_by                = $workspace->created_by;
                    $membershipOrder->save();

                    $groundAndClubMember->subscription_id       = $membershipOrder->id;
                    $groundAndClubMember->save();
                }
                $type = 'sportsmembershippayment';
                event(new StripePaymentStatus($data, $type, $groundAndClubMember));

                //Email notification
                if (!empty(company_setting('Sports Ground And Club Membership Order', $groundAndClubMember->created_by, $groundAndClubMember->workspace)) && company_setting('Sports Ground And Club Membership Order', $groundAndClubMember->created_by, $groundAndClubMember->workspace) == true) {
                    $orderID        = Crypt::encrypt($membershipOrder->id);
                    $membershipplan = SportsGroundAndClubPlans::find($planID);
                    $responsURL     = Route('sports.club.membership.email.response', ['slug' => $slug, 'orderID' => $orderID]);
                    $company        = User::find($groundAndClubMember->created_by);
                    $uArr = [
                        'sports_member_name'                   => $groundAndClubMember->name,
                        'sports_membership_plan_name'          => isset($membershipplan->name) ? $membershipplan->name : '-',
                        'sports_membership_plan_duration'      => $membershipplan->duration,
                        'sports_membership_expiry_date'        => company_datetime_formate($membershipOrder->membership_expiry_date, $groundAndClubMember->created_by, $groundAndClubMember->workspace),
                        'sports_membership_order_url'          => $responsURL,
                        'company_name'                         => $company->name,
                    ];
                    try {
                        $resp = EmailTemplate::sendEmailTemplate(
                            'Sports Ground And Club Membership Order',
                            [$data['member_email']],
                            $uArr,
                            $groundAndClubMember->created_by,
                            $groundAndClubMember->workspace
                        );
                    } catch (\Exception $e) {
                        $resp['error'] = $e->getMessage();
                    }
                    $msg = __('Payment has been success.') . ((isset($resp['error'])) ? '<br> <span class="text-danger" style="color:red">' . $resp['error'] . '</span>' : '');
                    return redirect()->route('sportsclub.and.ground.show', [$slug])->with('success', $msg);
                }

                $msg = __('Payment has been success.');
                return redirect()->route('sportsclub.and.ground.show', [$slug])->with('success', $msg);
            } else {
                $msg = __('Transaction has been failed');
                return redirect()->route('sportsclub.and.ground.show', [$slug])->with('error', $msg);
            }
        } catch (\Exception $exception) {
            $msg = __('Transaction has been failed');
            return redirect()->route('sportsclub.and.ground.show', [$slug])->with('error', $msg);
        }
    }

   //Boutique & desinger studio
   public function BoutiquePayWithStripe(Request $request, $slug)
   {
       $workspace = WorkSpace::where('slug', $slug)->first();
       $today     = now()->toDateString();
       $validator = \Validator::make(
           $request->all(),
           [
               'reservation_date' => 'required|date|after_or_equal:' . $today,
               'pickup_date'      => 'required|date|before_or_equal:reservation_date',
               'return_date'      => 'required|date|after_or_equal:reservation_date',
               'email'            => 'required|email',
               'phone_no'         => 'required',
               'address'          => 'required',
           ],
           [
               'reservation_date.after_or_equal'   => 'Reservation date must be today or a future date.',
               'pickup_date.before_or_equal'       => 'Pickup date must be on or before the reservation date.',
               'return_date.after_or_equal'        => 'Return date must be on or after the reservation date.',
           ]
       );

       if ($validator->fails()) {
           $messages = $validator->getMessageBag();
           return redirect()->back()->with('error', $messages->first());
       }

       $conflict = OutfitReservation::where('outfit_id', $request->outfit_id)
           ->where(function ($query) use ($request) {
               $query->whereBetween('pickup_date', [$request->pickup_date, $request->return_date])
                   ->orWhereBetween('return_date', [$request->pickup_date, $request->return_date])
                   ->orWhere(function ($q) use ($request) {
                       $q->where('pickup_date', '<=', $request->pickup_date)
                           ->where('return_date', '>=', $request->return_date);
                   });
           })
           ->first();
       if ($conflict) {
           return redirect()->back()->with('error', __('This outfit is already booked from ') . company_date_formate($conflict->pickup_date, $conflict->created_by, $conflict->workspace) . __(' to ') . company_date_formate($conflict->return_date, $conflict->created_by, $conflict->workspace));
       }

       $cleaningConflict = OutfitCleaningManagement::where('outfit_id', $request->outfit_id)
           ->whereBetween('cleaning_date', [$request->pickup_date, $request->return_date])
           ->first();

       if ($cleaningConflict) {
           return redirect()->back()->with(
               'error',
               __('This outfit is not available as it is scheduled for cleaning on ') .
                   company_date_formate($cleaningConflict->cleaning_date, $cleaningConflict->created_by, $cleaningConflict->workspace)
           )->withInput();
       }
       $price = $request->rent_amount;

       $data = $request->all();

       self::payment_setting($workspace->created_by, $workspace->id);
       $admin_currancy = $this->currancy;
       $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

       if (!in_array($admin_currancy, $supported_currencies)) {
           return redirect()->back()->with('error', __('Currency is not supported.'));
       }
       if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {

           try {
               $stripe_formatted_price = in_array(
                   company_setting('defult_currancy', $workspace->created_by, $workspace->id),
                   [
                       'MGA',
                       'BIF',
                       'CLP',
                       'PYG',
                       'DJF',
                       'RWF',
                       'GNF',
                       'UGX',
                       'JPY',
                       'VND',
                       'VUV',
                       'XAF',
                       'KMF',
                       'KRW',
                       'XOF',
                       'XPF',
                       'BRL'
                   ]
               ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;

               $return_url_parameters = function ($return_type) {
                   return '&return_type=' . $return_type;
               };
               /* Initiate Stripe */
               \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $workspace->created_by, $workspace->id));
               $code = '';
               $comapany_stripe_data = \Stripe\Checkout\Session::create(
                   [
                       'payment_method_types' => ['card'],
                       'line_items' => [
                           [
                               'price_data' => [
                                   'currency' =>  company_setting('defult_currancy', $workspace->created_by, $workspace->id),
                                   'unit_amount' => (int)$stripe_formatted_price,
                                   'product_data' => [
                                       'name' => !empty($workspace->name) ? $workspace->name : 'Basic Package',
                                       'description' => 'stripe payment',
                                   ],
                               ],
                               'quantity' => 1,
                           ],
                       ],
                       'mode' => 'payment',
                       'success_url' => route(
                           'boutique.booking.stripe',
                           [
                               'slug' => $slug,
                               'type' => $request->type,
                               'price' => $price,
                               $return_url_parameters('success'),
                           ]
                       ),
                       'cancel_url' => route(
                           'boutique.booking.stripe',
                           [
                               'slug' => $slug,
                               'type' => $request->type,
                               $return_url_parameters('cancel'),
                           ]
                       ),
                   ]
               );
               $data =  (object) [
                   'amount'       => $price,
                   'slug'         => $slug,
                   'currency'     => $this->currancy,
                   'request_data' => (object) $request->all(),
                   'stripe'       => (object) $comapany_stripe_data,
               ];
               Session::put('boutique_data', $data);
               $comapany_stripe_data = $comapany_stripe_data ?? false;
               return new RedirectResponse($comapany_stripe_data->url);
           } catch (\Exception $e) {

               return redirect()->back()->with('error', $e);
           }
       }
   }

   public function getStripeBoutiquePaymentStatus(Request $request, $slug)
   {
       $workspace = WorkSpace::where('slug', $slug)->first();
       try {
           $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
           $paymentIntents = $stripe->paymentIntents->retrieve(
               $request->session()->get('stripe_session')->payment_intent,
               []
           );
           $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
       } catch (\Exception $exception) {
           $receipt_url = "";
       }
       try {
           $data = Session::get('boutique_data');

           if ($request->return_type == 'success' && !empty($data)) {
               $reservation                   = new OutfitReservation();
               $reservation->customer_name    = $data->request_data->customer_name;
               $reservation->email            = $data->request_data->email;
               $reservation->phone_no         = $data->request_data->phone_no;
               $reservation->outfit_id        = $data->request_data->outfit_id;
               $reservation->address          = $data->request_data->address;
               $reservation->outfit_image     = $data->request_data->outfit_image;
               $reservation->outfit_size      = $data->request_data->outfit_size ?? '';
               $reservation->reservation_date = $data->request_data->reservation_date;
               $reservation->pickup_date      = $data->request_data->pickup_date;
               $reservation->return_date      = $data->request_data->return_date;
               $reservation->rent_amount      = $data->request_data->rent_amount;
               $reservation->status           = 'reserved';
               $reservation->payment_method   = 'Stripe';
               $reservation->payment_status   = 'paid';
               $reservation->workspace        = $workspace->id;
               $reservation->created_by       = $workspace->created_by;
               $reservation->save();

               $reservation->reservation_id = '#RES0' . sprintf("%05d", $reservation->id);
               $reservation->save();

               $type = 'boutiquestudiopayment';

               event(new StripePaymentStatus($data->request_data, $type, $reservation));
               if (!empty(company_setting('New Reservation Order', $reservation->created_by, $reservation->workspace)) && company_setting('New Membership Order', $reservation->created_by, $reservation->workspace) == true) {
                   $uArr = [
                       "customer_name"    => $reservation->customer_name,
                       "outfit_name"      => $reservation->outfit->name,
                       "reservation_date" => company_date_formate($reservation->reservation_date, $reservation->created_by, $reservation->workspace),
                       "pick_date"        => company_date_formate($reservation->pickup_date, $reservation->created_by, $reservation->workspace),
                       "return_date"      => company_date_formate($reservation->return_date, $reservation->created_by, $reservation->workspace),
                       "rent_price"       => currency_format_with_sym($reservation->rent_amount, $reservation->created_by, $reservation->workspace),
                       'order_url'        => route('boutique.outfit.reservation.order', [$slug, \Illuminate\Support\Facades\Crypt::encrypt($reservation->id)]),
                       'company_name'     => company_setting('company_name', $reservation->created_by, $reservation->workspace) ?? 'Workdo',
                       'app_name'         => env('APP_NAME'),
                   ];

                   try {
                       EmailTemplate::sendEmailTemplate('New Reservation Order', [$reservation->email], $uArr, $reservation->created_by, $reservation->workspace);
                       return redirect()->route('boutique.designer.studio', $slug)->with('success', __('Payment Pay Successfully.Please check your email.'));
                   } catch (\Exception $e) {
                       return redirect()->route('boutique.designer.studio', $slug)->with('success', __('Something went wrong.'));
                   }
               }
               return redirect()->route('boutique.designer.studio', $slug)->with('success', __('Payment Pay Successfully.'));
           } else {
               return redirect()->route('boutique.designer.studio', $slug)->with('error', __('Transaction has been failed.'));
           }
       } catch (\Exception $exception) {
           return redirect()->route('boutique.designer.studio', $slug)->with('error', __('Transaction has been failed.'));
       }
   }


   // influencer marketing 
   public function BrandDepositPayWithStripe(Request $request, $slug)
   {
        $brand = Brand::find($request->brand_id);
        self::payment_setting($brand->created_by, $brand->workspace);
        $data = $request->all();
        $workspace = WorkSpace::where('slug', $slug)->first();
        $price = $data['amount'];
        $admin_currancy = $this->currancy;
        $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];
        if (!in_array($admin_currancy, $supported_currencies)) {
            return redirect()->back()->with('error', __('Currency is not supported.'));
        }
        if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {
            if ($brand) {
                try {
                    $payment_type = 'onetime';
                    /* Payment details */
                    $code = '';
                    /* Final price */
                    $stripe_formatted_price = in_array(
                        company_setting('defult_currancy', $brand->created_by, $brand->wokspace),
                        [
                            'MGA',
                            'BIF',
                            'CLP',
                            'PYG',
                            'DJF',
                            'RWF',
                            'GNF',
                            'UGX',
                            'JPY',
                            'VND',
                            'VUV',
                            'XAF',
                            'KMF',
                            'KRW',
                            'XOF',
                            'XPF',
                            'BRL'
                        ]
                    ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
                    $return_url_parameters = function ($return_type) {
                        return '&return_type=' . $return_type;
                    };
                    /* Initiate Stripe */
                    \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $brand->created_by, $brand->wokspace));

                    $stripe_session = \Stripe\Checkout\Session::create(
                        [
                            'payment_method_types' => ['card'],
                            'line_items' => [
                                [
                                    'price_data' => [
                                        'currency' =>  company_setting('defult_currancy', $brand->created_by, $brand->wokspace),
                                        'unit_amount' => (int)$stripe_formatted_price,
                                        'product_data' => [
                                            'name' => !empty($brand->first_name) ? $brand->first_name : 'Basic Package',
                                            'description' => 'stripe payment',
                                        ],
                                    ],
                                    'quantity' => 1,
                                ],
                            ],
                            'mode' => 'payment',
                            'success_url' => route(
                                'brand.deposit.stripe.status',
                                [
                                    'slug' => $slug,
                                    'price' => $price,
                                    $return_url_parameters('success'),
                                ]
                            ),
                            'cancel_url' => route(
                                'brand.deposit.stripe.status',
                                [
                                    'slug' => $slug,
                                    $return_url_parameters('cancel'),
                                ]
                            ),

                        ]
                    );
                    session()->put('brand_deposit_session', $data);
                    $request->session()->put('stripe_session', $stripe_session);
                    $stripe_session = $stripe_session ?? false;
                    $stripe_session = $stripe_session ?? false;
                    return new RedirectResponse($stripe_session->url);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', $e);
                    \Log::debug($e->getMessage());
                }
            } else {
                $msg = __('Brand Not Found.');
                return redirect()->back()->with('error', $msg);
            }
        }
   }

   public function getStripeBrandDepositPaymentStatus(Request $request, $slug)
   {
   
        $workspace = WorkSpace::where('slug', $slug)->first();
        try {
            $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
            $paymentIntents = $stripe->paymentIntents->retrieve(
                $request->session()->get('stripe_session')->payment_intent,
                []
            );
            $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
        } catch (\Exception $exception) {
            $receipt_url = "";
        }
        try {
           
            if ($request->return_type == 'success' && !empty(session()->get('brand_deposit_session'))) {
                $data = session()->get('brand_deposit_session');
                $brand = Brand::find($data['brand_id']);
                $deposit               = new Deposit();
                $deposit->brand_id     = $brand->id;
                $deposit->name         = $brand->first_name;
                $deposit->amount       = $data['amount'];
                $deposit->payment_type = __('Stripe');
                $deposit->status       = '1';
                $deposit->date         = date('Y-m-d');
                $deposit->workspace    = $workspace->id;
                $deposit->created_by   = $workspace->created_by;
                $deposit->save();

                //transaction
                $trxID = strtoupper(str_replace('.', '', uniqid('', true)));
                $transaction                  = new BrandTransaction();
                $transaction->brand_id        = $deposit->brand_id;
                $transaction->trx_id          = $trxID;
                $transaction->amount          = $deposit->amount;
                $transaction->type            = 'credit';
                $transaction->detail          = 'Deposit Via - Stripe';
                $transaction->workspace       = $deposit->workspace;
                $transaction->created_by      = $deposit->created_by;
                $transaction->save();
                
                $type = 'brand_deposit';
                event(new StripePaymentStatus($deposit, $type, $transaction));
                return redirect()->route('brand.deposit.history', [$slug])->with('success', __('Deposit Pay Successfully.'));
            } else {
                $msg = __('Transaction has been failed');
                return redirect()->back()->with('error', $msg);
            }
        } catch (\Exception $e) {
            $e = __('Transaction has been failed');
            return redirect()->back()->with('error', $e);
        }
   }

   // NGO Payment
   public function NGODonationPayWithStripe(Request $request, $slug)
   {
       $workspace = WorkSpace::where('slug', $slug)->first();
       $data = $request->all();
       $price = $data['donation_amount'];
       
       self::payment_setting($workspace->created_by, $workspace->workspace);
       $currency = $this->currancy;
       $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

       if (!in_array($currency, $supported_currencies)) {
           return redirect()->back()->with('error', __('Currency is not supported.'));
       }

       if (isset($this->is_stripe_enabled) && $this->is_stripe_enabled == 'on' && !empty($this->stripe_key) && !empty($this->stripe_secret)) {
           try {
               $stripe_formatted_price = in_array(
                   $currency,
                   ['MGA','BIF','CLP','PYG','DJF','RWF','GNF','UGX','JPY','VND','VUV','XAF','KMF','KRW','XOF','XPF','BRL']
               ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;

               \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $workspace->created_by, $workspace->id));

               $stripe_session = \Stripe\Checkout\Session::create([
                   'payment_method_types' => ['card'],
                   'line_items' => [[
                       'price_data' => [
                           'currency' => $currency,
                           'unit_amount' => (int)$stripe_formatted_price,
                           'product_data' => [
                               'name' => 'NGO Donation',
                               'description' => 'Donation via Stripe',
                           ],
                       ],
                       'quantity' => 1,
                   ]],
                   'mode' => 'payment',
                   'success_url' => route('ngo.donation.stripe.status', array_merge(['slug' => $slug, 'return_type' => 'success'], $data)),
                   'cancel_url' => route('ngo.donation.stripe.status', array_merge(['slug' => $slug, 'return_type' => 'cancel'], $data)),
               ]);

               session()->put('ngo_donation_session', $data);
               $request->session()->put('stripe_session', $stripe_session);

               return new \Illuminate\Http\RedirectResponse($stripe_session->url);
           } catch (\Exception $e) {
               \Log::error('Stripe Error: ' . $e->getMessage());
               return redirect()->back()->with('error', __('Stripe error: ') . $e->getMessage());
           }
       }

       return redirect()->back()->with('error', __('Stripe is not properly configured.'));
   }
   public function getNGODonationPaymentStatus(Request $request, $slug)
   {
       $workspace = WorkSpace::where('slug', $slug)->first();

       try {
           $stripe = new \Stripe\StripeClient(company_setting('stripe_secret', $workspace->created_by, $workspace->id));
           $paymentIntentId = $request->session()->get('stripe_session')->payment_intent;
           $paymentIntents = $stripe->paymentIntents->retrieve($paymentIntentId, []);
           $receipt_url = $paymentIntents->charges->data[0]->receipt_url ?? '';

       } catch (\Exception $e) {
           \Log::error('Stripe Retrieval Error: ' . $e->getMessage());
           $receipt_url = '';
       }

       try {
           if ($request->return_type == 'success' && session()->has('ngo_donation_session')) {
               $data = session()->get('ngo_donation_session');

                $donorEmail = $request->email;
               $donor = \Workdo\NGOManagment\Entities\Donors::where('email', $donorEmail)
                   ->where('workspace', $workspace->id)
                   ->first();

               if (!$donor) {
                   $donor = new \Workdo\NGOManagment\Entities\Donors();
                   $donor->name = $request->name ?? 'Anonymous';
                   $donor->email = $donorEmail;
                   $donor->phone = $request->phone ?? null;
                   $donor->workspace = $workspace->id;
                   $donor->donor_type = 'Anonymous';
                   $donor->created_by = $workspace->created_by;
                   $donor->save();
               }
               $donation = new \Workdo\NGOManagment\Entities\Donations();
               $donation->donor_id = $donor->id ?? null;
               $donation->campaign_id = $data['campaign_id'] ?? null;
               $donation->amount = $data['donation_amount'];
               $donation->payment_mode = 'Stripe';
               $donation->transaction_id = $paymentIntentId;
               $donation->donation_date = date('Y-m-d');
               $donation->notes = $data['notes'] ?? '';
               $donation->workspace = $workspace->id;
               $donation->created_by = $workspace->created_by;
               $donation->save();

               $type = 'ngo_donation';
               event(new StripePaymentStatus($donation, $type));

               return redirect()->route('ngo.front.index', $slug)->with('success', __('Donation Pay Successfully.'));
           } else {
               return redirect()->route('ngo.front.index', $slug)->with('error', __('Transaction has been failed'));
           }
       } catch (\Exception $e) {
           \Log::error('Stripe Donation Save Error: ' . $e->getMessage());
           return redirect()->route('ngo.front.index', $slug)->with('error', __('Transaction has been failed'));
       }
   }

   //Art Showcase Payment
   public function artWorkPayWithStripe(Request $request, $slug)
   {
       if ($request->payment == 'stripe') {
           $artWork = ArtWorks::find($request->artwork_id);
           if ($artWork) {
               if ($artWork->status == 1) {
                   $data = $request->all();
                   $price = $artWork->price;
                   self::payment_setting($artWork->created_by, $artWork->workspace);
                   $admin_currancy = $this->currancy;
                   $supported_currencies = ['EUR', 'GBP', 'USD', 'CAD', 'AUD', 'JPY', 'INR', 'CNY', 'SGD', 'HKD', 'BRL'];

                   if (!in_array($admin_currancy, $supported_currencies)) {
                       return redirect()->back()->with('error', __('Currency is not supported.'));
                   }

                   try {
                       $payment_type = 'onetime';
                       /* Payment details */
                       $code = '';
                       /* Final price */
                       $stripe_formatted_price = in_array(
                           company_setting('defult_currancy', $artWork->created_by, $artWork->wokspace),
                           [
                               'MGA',
                               'BIF',
                               'CLP',
                               'PYG',
                               'DJF',
                               'RWF',
                               'GNF',
                               'UGX',
                               'JPY',
                               'VND',
                               'VUV',
                               'XAF',
                               'KMF',
                               'KRW',
                               'XOF',
                               'XPF',
                               'BRL'
                           ]
                       ) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
                       $return_url_parameters = function ($return_type) {
                           return '&return_type=' . $return_type;
                       };
                       /* Initiate Stripe */
                       \Stripe\Stripe::setApiKey(company_setting('stripe_secret', $artWork->created_by, $artWork->wokspace));

                       $stripe_session = \Stripe\Checkout\Session::create(
                           [
                               'payment_method_types' => ['card'],
                               'line_items' => [
                                   [
                                       'price_data' => [
                                           'currency' =>  company_setting('defult_currancy', $artWork->created_by, $artWork->wokspace),
                                           'unit_amount' => (int)$stripe_formatted_price,
                                           'product_data' => [
                                               'name' => !empty($artWork->name) ? $artWork->name : 'Basic Package',
                                               'description' => 'stripe payment',
                                           ],
                                       ],
                                       'quantity' => 1,
                                   ],
                               ],
                               'mode' => 'payment',
                               'success_url' => route(
                                   'get.artwork.pay.status',
                                   [
                                       'slug' => $slug,
                                       'price' => $price,
                                       $return_url_parameters('success'),
                                   ]
                               ),
                               'cancel_url' => route(
                                   'get.artwork.pay.status',
                                   [
                                       'slug' => $slug,
                                       $return_url_parameters('cancel'),
                                   ]
                               ),

                           ]
                       );
                       session()->put('art_work_session', $data);
                       $request->session()->put('stripe_session', $stripe_session);
                       $stripe_session = $stripe_session ?? false;
                       $stripe_session = $stripe_session ?? false;
                       return new RedirectResponse($stripe_session->url);
                   } catch (\Exception $e) {
                       return redirect()->back()->with('error', $e);
                       \Log::debug($e->getMessage());
                   }
               } else {
                   $msg = __('Art Work Already Sold.');
               return redirect()->back()->with('error', $msg);
               }
           } else {
               $msg = __('Art Work Not Found.');
               return redirect()->back()->with('error', $msg);
           }
       } else {
           return redirect()->back()->with('error', __('Please select payment method'));
       }
   }

   public function getArtWorkPaymentStatus(Request $request, $slug)
   {
       $workspace = WorkSpace::where('slug', $slug)->first();
       if ($workspace) {
           try {
               $stripe = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $workspace->created_by, $workspace->id)) ? company_setting('stripe_secret', $workspace->created_by, $workspace->id) : '');
               $paymentIntents = $stripe->paymentIntents->retrieve(
                   $request->session()->get('stripe_session')->payment_intent,
                   []
               );
               $receipt_url = $paymentIntents->charges->data[0]->receipt_url;
           } catch (\Exception $exception) {
               $receipt_url = "";
           }
           try {
               if ($request->return_type == 'success' && !empty(session()->get('art_work_session'))) {
                   $data = session()->get('art_work_session');
                   $artWork = ArtWorks::find($data['artwork_id']);
                   if ($artWork) {
                       $ArtWorkOrder                   = new ArtWorkOrder();
                       $ArtWorkOrder->artwork_id       = $artWork->id;
                       $ArtWorkOrder->full_name        = $data['full_name'];
                       $ArtWorkOrder->email            = $data['email'];
                       $ArtWorkOrder->contact_no       = $data['contact_no'];
                       $ArtWorkOrder->total_amount     = $artWork->price;
                       $ArtWorkOrder->payment_type     = 'Stripe';
                       $ArtWorkOrder->payment_status   = 'paid';
                       $ArtWorkOrder->receipt          = $receipt_url;
                       $ArtWorkOrder->address          = $data['address'];
                       $ArtWorkOrder->workspace        = $workspace->id;
                       $ArtWorkOrder->created_by       = $workspace->created_by;
                       $ArtWorkOrder->save();

                       $artWork->status = 2;
                       $artWork->save();


                       $type = 'artworkpayment';
                       event(new StripePaymentStatus($data, $type, $ArtWorkOrder));

                       $msg = __('Payment has been success.');
                       return redirect()->route('artshowcase.copyLink', [$slug])->with('success', $msg);
                   } else {
                       $msg = __('Art Work Not Found');
                       return redirect()->route('artshowcase.copyLink', [$slug])->with('error', $msg);
                   }
               } else {
                   $msg = __('Transaction has been failed');
                   return redirect()->route('artshowcase.copyLink', [$slug])->with('error', $msg);
               }
           } catch (\Exception $exception) {
               $msg = __('Transaction has been failed');
               return redirect()->route('artshowcase.copyLink', [$slug])->with('error', $msg);
           }
       } else {
           return redirect()->back()->with('error', __('Workspace not found'));
       }
   }
}
