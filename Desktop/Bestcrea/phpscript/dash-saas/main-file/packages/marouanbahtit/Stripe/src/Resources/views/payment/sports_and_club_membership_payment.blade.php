<li class="radio-btn">
    <input name="membership-payment" id="stripe-plan-payment" type="radio" class="plan_payment_method"
        data-payment-action="{{ route('sports.club.plan.pay.with.stripe', [$slug]) }}">

    <label for="stripe-plan-payment" class="radio-btn-label d-flex align-items-center gap-3 p-3 justify-content-center">
        <span class="fs-5 f-w-600">{{ Module_Alias_Name('Stripe') }}</span>
        <div class="radio-img">
            <img src="{{ get_module_img('Stripe') }}" alt="" class="img-user" style="max-width: 100%">
        </div>
    </label>
</li>
