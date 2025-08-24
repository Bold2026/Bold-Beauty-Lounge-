
<div class="payment-item border-b">
    <input type="radio" value="paypal" name="payment" id="paypal-payment" class="form-check-input payment-option hidden"  data-payment-action="{{ route('brand.deposit.pay.payment.with.paypal',[$slug]) }}">
    <label for="paypal-payment" class="flex items-center cursor-pointer">
        <div class="p-4 flex justify-between items-center flex-grow gap-2">
            <span class="font-medium">{{ Module_Alias_Name('Paypal') }}</span>
            <img src="{{ get_module_img('Paypal') }}" alt="" class="max-w-12">
        </div>
    </label>
</div>


