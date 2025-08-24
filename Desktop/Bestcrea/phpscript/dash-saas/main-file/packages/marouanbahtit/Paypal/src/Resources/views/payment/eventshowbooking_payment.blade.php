
<div class="single-option">
    <div class="option-input-box">
        <div class="option-inner d-flex">
            <div class="option-icon">
                <img src="{{ get_module_img('Paypal') }}" alt="Payment Logo" class="img-user">
            </div>
            <div>
                <label for="paypal-payment">
                    <p class="mb-0 text-capitalize pointer">{{ Module_Alias_Name('Paypal') }}</p>
                </label>
            </div>
        </div>
        <div class="form-check">
            <input class="form-check-input payment_method" name="payment_method" id="paypal-payment"
                type="radio" data-payment-action="{{ route('event.show.booking.pay.with.paypal',[$slug]) }}">
        </div>
    </div>
</div>
