<div class="payment-method">
    <input type="radio" id="payment-stripe" name="payment-method" value="stripe"
           class="peer hidden payment-option"
           data-payment-action="{{ route('ngo.donation.pay.with.stripe', [$slug]) }}">
    <label for="payment-stripe"
           class="border-2 border-gray-200 peer-checked:border-secondary peer-checked:bg-secondary/5 hover:border-secondary hover:bg-secondary/5 rounded-lg p-3 cursor-pointer transition-all duration-300 block">
        <div class="flex flex-col items-center text-center">
            <img src="{{ get_module_img('Stripe') }}" alt="Stripe" class="w-8 h-8 mb-1">
            <span class="text-xs font-medium">{{ Module_Alias_Name('Stripe') }}</span>
        </div>
    </label>
</div>