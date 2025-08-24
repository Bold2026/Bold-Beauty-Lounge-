<?php

namespace Workdo\Account\Providers;

use App\Events\BankTransferPaymentStatus;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanyMenuEvent;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use App\Events\CreatePaymentInvoice;
use App\Events\CreatePaymentPurchase;
use App\Events\DefaultData;
use App\Events\DestroyInvoice;
use App\Events\DestroyPurchase;
use App\Events\GivePermissionToRole;
use App\Events\PaymentDestroyInvoice;
use App\Events\PaymentDestroyPurchase;
use App\Events\ProductDestroyInvoice;
use App\Events\SentInvoice;
use App\Events\SentPurchase;
use App\Events\UpdateInvoice;
use App\Events\UpdatePurchase;
use Workdo\Account\Listeners\CompanyMenuListener;
use Workdo\Account\Listeners\CompanySettingListener;
use Workdo\Account\Listeners\CompanySettingMenuListener;
use Workdo\Account\Listeners\InvoiceBalanceTransfer;
use Workdo\AamarPay\Events\AamarPaymentStatus;
use Workdo\Account\Events\CreateCustomerCreditNote;
use Workdo\Account\Events\CreateCustomerDebitNote;
use Workdo\Account\Events\CreatePayment;
use Workdo\Account\Events\CreatePaymentBill;
use Workdo\Account\Events\CreateRevenue;
use Workdo\Account\Events\DestroyBill;
use Workdo\Account\Events\DestroyCustomerCreditNote;
use Workdo\Account\Events\DestroyCustomerDebitNote;
use Workdo\Account\Events\DestroyPayment;
use Workdo\Account\Events\DestroyPurchaseProduct;
use Workdo\Account\Events\DestroyRevenue;
use Workdo\Account\Events\PaymentDestroyBill;
use Workdo\Account\Events\ProductDestroyBill;
use Workdo\Account\Events\SentBill;
use Workdo\Account\Events\UpdateBill;
use Workdo\Account\Events\UpdateCustomerCreditNote;
use Workdo\Account\Events\UpdateCustomerDebitNote;
use Workdo\Account\Events\UpdatePayment;
use Workdo\Account\Events\UpdateRevenue;
use Workdo\Account\Listeners\BillDestroy;
use Workdo\Account\Listeners\BillPaymentCreate;
use Workdo\Account\Listeners\BillPaymentDestroy;
use Workdo\Account\Listeners\BillProductDestroy;
use Workdo\Account\Listeners\BillSent;
use Workdo\Account\Listeners\BillUpdate;
use Workdo\Account\Listeners\CreateProductLis;
use Workdo\Account\Listeners\CustomerCreditNoteCreate;
use Workdo\Account\Listeners\CustomerCreditNoteDestroy;
use Workdo\Account\Listeners\CustomerCreditNoteUpdate;
use Workdo\Account\Listeners\CustomerDebitNoteCreate;
use Workdo\Account\Listeners\CustomerDebitNoteDestroy;
use Workdo\Account\Listeners\CustomerDebitNoteUpdate;
use Workdo\Account\Listeners\DataDefault;
use Workdo\Account\Listeners\GiveRoleToPermission;
use Workdo\Account\Listeners\InvoiceDestroy;
use Workdo\Account\Listeners\InvoiceOnlinePayamentCreate;
use Workdo\Account\Listeners\InvoicePaymentCreate;
use Workdo\Account\Listeners\InvoicePaymentDestroy;
use Workdo\Account\Listeners\InvoiceProductDestroy;
use Workdo\Account\Listeners\InvoiceSent;
use Workdo\Account\Listeners\InvoiceUpdate;
use Workdo\Account\Listeners\PaymentCreate;
use Workdo\Account\Listeners\PaymentDestroy;
use Workdo\Account\Listeners\PaymentUpdate;
use Workdo\Account\Listeners\PurchaseDestroy;
use Workdo\Account\Listeners\PurchasePaymentCreate;
use Workdo\Account\Listeners\PurchasePaymentDestroy;
use Workdo\Account\Listeners\PurchaseProductDestroy;
use Workdo\Account\Listeners\PurchaseSent;
use Workdo\Account\Listeners\PurchaseUpdate;
use Workdo\Account\Listeners\RetainerPaymentCreate;
use Workdo\Account\Listeners\RevenueCreate;
use Workdo\Account\Listeners\RevenueDestroy;
use Workdo\Account\Listeners\RevenueUpdate;
use Workdo\Account\Listeners\UpdateProductLis;
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
use Workdo\LinePay\Events\LinePayPaymentStatus;
use Workdo\Mercado\Events\MercadoPaymentStatus;
use Workdo\Midtrans\Events\MidtransPaymentStatus;
use Workdo\Mollie\Events\MolliePaymentStatus;
use Workdo\Moneris\Events\MonerisPaymentStatus;
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
use Workdo\Paypal\Events\PaypalPaymentStatus;
use Workdo\Paystack\Events\PaystackPaymentStatus;
use Workdo\PayTab\Events\PaytabPaymentStatus;
use Workdo\Paytm\Events\PaytmPaymentStatus;
use Workdo\PayTR\Events\PaytrPaymentStatus;
use Workdo\PhonePe\Events\PhonePePaymentStatus;
use Workdo\ProductService\Events\CreateProduct;
use Workdo\ProductService\Events\UpdateProduct;
use Workdo\Razorpay\Events\RazorpayPaymentStatus;
use Workdo\Skrill\Events\SkrillPaymentStatus;
use Workdo\SSPay\Events\SSpayPaymentStatus;
use Workdo\Stripe\Events\StripePaymentStatus;
use Workdo\Tap\Events\TapPaymentStatus;
use Workdo\Toyyibpay\Events\ToyyibpayPaymentStatus;
use Workdo\Xendit\Events\XenditPaymentStatus;
use Workdo\YooKassa\Events\YooKassaPaymentStatus;
use Workdo\Paypay\Events\PaypayPaymentStatus;
use Workdo\PayU\Events\PayUPaymentStatus;
use Workdo\PeachPayment\Events\PeachPaymentStatus;
use Workdo\Pesapal\Events\PesapalPaymentStatus;
use Workdo\PowerTranz\Events\PowerTranzPaymentStatus;
use Workdo\Retainer\Events\RetainerConvertToInvoice;
use Workdo\SenangPay\Events\SenangPayPaymentStatus;
use Workdo\Sofort\Events\SofortPaymentStatus;
use Workdo\Square\Events\SquarePaymentStatus;
use Workdo\SSLCommerz\Events\SSLCommerzPaymentStatus;
use Workdo\TwoCheckout\Events\TwoCheckoutPaymentStatus;
use Workdo\UddoktaPay\Events\UddoktaPayStatus;
use Workdo\Yoco\Events\YocoPaymentStatus;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class,
        ],
        CompanySettingEvent::class => [
            CompanySettingListener::class,
        ],
        CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class,
        ],
        DefaultData :: class => [
            DataDefault::class,
        ],
        GivePermissionToRole::class => [
            GiveRoleToPermission::class,
        ],

        StripePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaypalPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        FlutterwavePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaystackPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        RazorpayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MolliePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PayfastPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        YooKassaPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaytabPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        SSpayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        ToyyibpayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        SkrillPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        IyzipayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaytrPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        AamarPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        BenefitPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        CashfreePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        CoingatePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MercadoPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaytmPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaddlePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MidtransPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        XenditPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        TapPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        KhaltiPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PhonePePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        AuthorizeNetStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PayHerePaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaiementProPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        FedapayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        BankTransferPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaypayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        DPOPayPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        SofortPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        SSLCommerzPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaynowPaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        SquarePaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PowerTranzPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MoyasarPaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        BraintreePaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        InstamojoPaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        SentInvoice::class =>[
            InvoiceSent::class
        ],
        CreatePaymentBill::class =>[
            BillPaymentCreate::class
        ],
        SentBill::class =>[
            BillSent::class
        ],
        UpdateBill::class =>[
            BillUpdate::class
        ],
        CreatePaymentInvoice::class =>[
            InvoicePaymentCreate::class
        ],
        UpdateInvoice::class =>[
            InvoiceUpdate::class
        ],
        CreateProduct::class =>[
            CreateProductLis::class
        ],
        UpdateProduct::class =>[
            UpdateProductLis::class
        ],
        PeachPaymentStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        OzowPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        CybersourceStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        SenangPayPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        NepalstePaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        TwoCheckoutPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        EasebuzzPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PayUPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        FatoraPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        NMIPatmentStats::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MonerisPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        CinetPayPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        CheckoutPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        BlueSnapPaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        BTCPayPaymentStatus::class => [
          InvoiceBalanceTransfer::class,
          InvoiceOnlinePayamentCreate::class
        ],
        PesapalPaymentStatus::class => [
           InvoiceBalanceTransfer::class,
           InvoiceOnlinePayamentCreate::class
        ],
        CoinPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        UddoktaPayStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MyFatoorahStatus::class =>[
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        MonnifyPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        LinePayPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PayfortPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        EsewaPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],  
        YocoPaymentStatus::class => [
            InvoiceBalanceTransfer::class,
            InvoiceOnlinePayamentCreate::class
        ],
        PaymentDestroyInvoice::class => [
            InvoicePaymentDestroy::class
        ],
        DestroyInvoice::class => [
            InvoiceDestroy::class
        ],
        ProductDestroyInvoice::class => [
            InvoiceProductDestroy::class
        ],
        CreateCustomerCreditNote::class => [
            CustomerCreditNoteCreate::class
        ],
        UpdateCustomerCreditNote::class => [
            CustomerCreditNoteUpdate::class
        ],
        DestroyCustomerCreditNote::class => [
            CustomerCreditNoteDestroy::class
        ],
        RetainerConvertToInvoice::class => [
            InvoiceSent::class,
            RetainerPaymentCreate::class
        ],
        ProductDestroyBill::class => [
            BillProductDestroy::class
        ],
        DestroyBill::class => [
            BillDestroy::class
        ],
        PaymentDestroyBill::class => [
            BillPaymentDestroy::class
        ],
        CreateCustomerDebitNote::class => [
            CustomerDebitNoteCreate::class
        ],
        UpdateCustomerDebitNote::class => [
            CustomerDebitNoteUpdate::class
        ],
        DestroyCustomerDebitNote::class => [
            CustomerDebitNoteDestroy::class
        ],
        SentPurchase::class => [
            PurchaseSent::class
        ],
        UpdatePurchase::class => [
            PurchaseUpdate::class
        ],
        DestroyPurchase::class => [
            PurchaseDestroy::class
        ],
        DestroyPurchaseProduct::class => [
            PurchaseProductDestroy::class
        ],
        CreatePaymentPurchase::class => [
            PurchasePaymentCreate::class
        ],
        PaymentDestroyPurchase::class => [
            PurchasePaymentDestroy::class
        ],
        CreateRevenue::class => [
            RevenueCreate::class
        ],
        DestroyRevenue::class => [
            RevenueDestroy::class
        ],
        UpdateRevenue::class => [
            RevenueUpdate::class
        ],
        CreatePayment::class => [
            PaymentCreate::class
        ],
        DestroyPayment::class => [
            PaymentDestroy::class
        ],
        UpdatePayment::class => [
            PaymentUpdate::class
        ]
    ];

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
