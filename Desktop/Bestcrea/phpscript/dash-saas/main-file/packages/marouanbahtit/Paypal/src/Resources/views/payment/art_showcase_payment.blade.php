<label class="flex items-center p-3 border cursor-pointer hover:bg-gray-50 transition duration-300">
    <div class="relative me-3">
        <input type="radio" name="payment" value="paypal" id="paypal-payment" class="sr-only peer payment_method"
            data-payment-action="{{ route('artwork.pay.with.paypal', [$slug]) }}">
        <div
            class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-secondary peer-checked:bg-secondary flex items-center justify-center">
            <div class="w-2 h-2 bg-white rounded-full">
            </div>
        </div>
    </div>
    <img src="{{ get_module_img('Paypal') }}" alt="" class="w-5 h-5 me-2" style="max-width: 100%">
    {{ Module_Alias_Name('Paypal') }}
</label>