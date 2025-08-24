@if (!empty($cardPayment_content) && isset($cardPayment_content->paypal) && $cardPayment_content->paypal->status == 'on')
<div class="payment-div">
    <a href="{{ route('vcard.pay.with.paypal', $business->id) }}">
        <img src="{{ asset('packages/workdo/VCard/src/Resources/assets/custom/img/payments/paypal.png') }}"
            alt="social" class="img-fluid">
        {{ __('payPal') }}
    </a>
</div>
@endif
