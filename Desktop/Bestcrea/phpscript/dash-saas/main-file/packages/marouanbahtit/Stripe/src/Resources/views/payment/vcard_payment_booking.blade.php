@if (!empty($cardPayment_content) && isset($cardPayment_content->stripe) && $cardPayment_content->stripe->status === 'on')
<div class="payment-div">
    <a href="{{ route('vcard.pay.with.stripe', $business->id) }}">
        <img src="{{ asset('packages/workdo/VCard/src/Resources/assets/custom/img/payments/stripe.png') }}"
            alt="social" class="img-fluid">
        {{ __('Stripe') }}
    </a>
</div>
@endif
