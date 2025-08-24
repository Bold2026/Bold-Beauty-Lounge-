<div class="col-xxl-4 col-lg-6 col-md-4 col-sm-6 col-12">
    <div class="card">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="theme-avtar">
                        <img src="{{ get_module_img('Stripe') }}" alt="" class="img-user" style="max-width: 100%">
                    </div>
                    <div class="ms-3">
                        <label for="stripe-payment">
                            <h5 class="mb-0 text-capitalize pointer">{{ Module_Alias_Name('Stripe') }}</h5>
                        </label>
                    </div>
                </div>
                <div class="form-check form-switch custom-switch-v1">
                    <input type="checkbox" name="paymentoption[]" id="stripeLabel" data-target="Stripe"
                            class="paymentButton form-check-input input-primary" value="stripe" data-payment-action="{{ route('vcard.enable.stripe',$id)}}"
                            {{ isset($cardPayment_content->stripe) && is_object($cardPayment_content->stripe) && $cardPayment_content->stripe->status === 'on' ? 'checked' : '' }}>
                </div>
            </div>
        </div>
    </div>
</div>




<script>
    $(document).on('change', '#stripeLabel', function() {
    var isChecked = $(this).prop('checked');
    var paymentOptions = isChecked ? ['stripe'] : [];
    var route = $(this).data('payment-action');

    $.ajax({
        url: route,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            paymentoption: paymentOptions
        },
        success: function(response) {
            if (response.success) {
                $('#stripeContainer').toggleClass('active', isChecked);
                toastrs("success!", "The Stripe settings have been updated successfully.", "success");
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr.responseText);
        }
    });
});

</script>

