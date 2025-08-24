<div class="payment-method">
    <input type="radio" id="payment-paypal" name="payment-method" value="paypal"
           class="peer hidden payment-option"
           data-payment-action="{{ route('ngo.donation.pay.with.paypal', [$slug]) }}"
           checked>
    <label for="payment-paypal"
           class="border-2 border-gray-200 peer-checked:border-secondary peer-checked:bg-secondary/5 hover:border-secondary hover:bg-secondary/5 rounded-lg p-3 cursor-pointer transition-all duration-300 block">
        <div class="flex flex-col items-center text-center">
            <img src="{{ get_module_img('Paypal') }}" alt="PayPal" class="w-8 h-8 mb-1">
            <span class="text-xs font-medium">{{ Module_Alias_Name('Paypal') }}</span>
        </div>
    </label>
</div>