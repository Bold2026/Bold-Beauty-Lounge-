
<div class="payment-item border-b">
    <input type="radio" value="stripe" name="payment" id="stripe-payment" class="form-check-input payment-option hidden"  data-payment-action="{{ route('brand.deposit.pay.payment.with.stripe',[$slug]) }}">
    <label for="stripe-payment" class="flex items-center cursor-pointer">
        <div class="p-4 flex justify-between items-center flex-grow gap-2">
            <span class="font-medium">{{ Module_Alias_Name('Stripe') }}</span>
            <img src="{{ get_module_img('Stripe') }}" alt="" class="max-w-12" >
        </div>
    </label>
</div>

