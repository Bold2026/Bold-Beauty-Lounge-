<?php

namespace App\Providers;

use App\Events\BankTransferRequestUpdate;
use App\Listeners\ReferralTransactionLis;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Workdo\AamarPay\Events\AamarPaymentStatus;
use Workdo\AuthorizeNet\Events\AuthorizeNetStatus;
use Workdo\Benefit\Events\BenefitPaymentStatus;
use Workdo\BlueSnap\Events\BlueSnapPaymentStatus;
use Workdo\Braintree\Events\BraintreePaymentStatus;
use Workdo\BTCPay\Events\BTCPayPaymentStatus;
use Workdo\Cashfree\Events\CashfreePaymentStatus;
use Workdo\Checkout\Events\CheckoutPaymentStatus;
use Workdo\CinetPay\Events\CinetPayPaymentStatus;
use Workdo\Coin\Events\CoinPaymentStatus;
use Workdo\Coingate\Events\CoingatePaymentStatus;
use Workdo\CyberSource\Events\CybersourceStatus;
use Workdo\DPOPay\Events\DPOPayPaymentStatus;
use Workdo\Easebuzz\Events\EasebuzzPaymentStatus;
use Workdo\Esewa\Events\EsewaPaymentStatus;
use Workdo\Fatora\Events\FatoraPaymentStatus;
use Workdo\Fedapay\Events\FedapayPaymentStatus;
use Workdo\Flutterwave\Events\FlutterwavePaymentStatus;
use Workdo\Instamojo\Events\InstamojoPaymentStatus;
use Workdo\Iyzipay\Events\IyzipayPaymentStatus;
use Workdo\Khalti\Events\KhaltiPaymentStatus;
use Workdo\Mercado\Events\MercadoPaymentStatus;
use Workdo\Midtrans\Events\MidtransPaymentStatus;
use Workdo\Mollie\Events\MolliePaymentStatus;
use Workdo\Monnify\Events\MonnifyPaymentStatus;
use Workdo\Moyasar\Events\MoyasarPaymentStatus;
use Workdo\MyFatoorah\Events\MyFatoorahStatus;
use Workdo\Nepalste\Events\NepalstePaymentStatus;
use Workdo\NMI\Events\NMIPatmentStats;
use Workdo\Ozow\Events\OzowPaymentStatus;
use Workdo\Paddle\Events\PaddlePaymentStatus;
use Workdo\PaiementPro\Events\PaiementProPaymentStatus;
use Workdo\Payfast\Events\PayfastPaymentStatus;
use Workdo\PayFort\Events\PayfortPaymentStatus;
use Workdo\PayHere\Events\PayHerePaymentStatus;
use Workdo\Paynow\Events\PaynowPaymentStatus;
use Workdo\Paypay\Events\PaypayPaymentStatus;
use Workdo\Paypal\Events\PaypalPaymentStatus;
use Workdo\Paystack\Events\PaystackPaymentStatus;
use Workdo\PayTab\Events\PaytabPaymentStatus;
use Workdo\Paytm\Events\PaytmPaymentStatus;
use Workdo\PayTR\Events\PaytrPaymentStatus;
use Workdo\PayU\Events\PayUPaymentStatus;
use Workdo\PhonePe\Events\PhonePePaymentStatus;
use Workdo\PowerTranz\Events\PowerTranzPaymentStatus;
use Workdo\Razorpay\Events\RazorpayPaymentStatus;
use Workdo\SenangPay\Events\SenangPayPaymentStatus;
use Workdo\Skrill\Events\SkrillPaymentStatus;
use Workdo\Sofort\Events\SofortPaymentStatus;
use Workdo\Square\Events\SquarePaymentStatus;
use Workdo\SSPay\Events\SSpayPaymentStatus;
use Workdo\Stripe\Events\StripePaymentStatus;
use Workdo\Tap\Events\TapPaymentStatus;
use Workdo\Toyyibpay\Events\ToyyibpayPaymentStatus;
use Workdo\TwoCheckout\Events\TwoCheckoutPaymentStatus;
use Workdo\UddoktaPay\Events\UddoktaPayStatus;
use Workdo\Xendit\Events\XenditPaymentStatus;
use Workdo\YooKassa\Events\YooKassaPaymentStatus;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        BankTransferRequestUpdate::class => [
            ReferralTransactionLis::class,
        ],
        PaypalPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        StripePaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        AamarPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        AuthorizeNetStatus::class => [
            ReferralTransactionLis::class,
        ],
        BenefitPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        CashfreePaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        CinetPayPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        CoingatePaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        FedapayPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        FlutterwavePaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        IyzipayPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        KhaltiPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        MercadoPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        MidtransPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        MolliePaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PaiementProPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PayfastPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PayHerePaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PaystackPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PaytmPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PaytrPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PhonePePaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PaytabPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        RazorpayPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        SkrillPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        SSpayPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        TapPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        ToyyibpayPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        XenditPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        YooKassaPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        NepalstePaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PaddlePaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        SenangPayPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        CybersourceStatus::class => [
            ReferralTransactionLis::class,
        ],
        OzowPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        TwoCheckoutPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        EasebuzzPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        SquarePaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        BraintreePaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PayUPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        InstamojoPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        EsewaPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PaynowPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        MyFatoorahStatus::class => [
            ReferralTransactionLis::class,
        ],
        FatoraPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        MoyasarPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        NMIPatmentStats::class => [
            ReferralTransactionLis::class,
        ],
        PowerTranzPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        DPOPayPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        SofortPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        MonnifyPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        UddoktaPayStatus::class => [
            ReferralTransactionLis::class,
        ],
        PaypayPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        CheckoutPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        PayfortPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        CoinPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        BTCPayPaymentStatus::class => [
            ReferralTransactionLis::class,
        ],
        BlueSnapPaymentStatus::class => [
            ReferralTransactionLis::class,
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
