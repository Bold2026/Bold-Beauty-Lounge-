<li class="radio-btn">
    <input name="membership-payment" id="paypal-plan-payment" type="radio" class="plan_payment_method"
        data-payment-action="{{ route('sports.club.plan.pay.with.paypal', [$slug]) }}">

    <label for="paypal-plan-payment" class="radio-btn-label d-flex align-items-center gap-3 p-3 justify-content-center">
        <span class="fs-5 f-w-600">{{ Module_Alias_Name('Paypal') }}</span>
        <div class="radio-img">
            <img src="{{ get_module_img('Paypal') }}" alt="" class="img-user" style="max-width: 100%">
        </div>
    </label>
</li>
