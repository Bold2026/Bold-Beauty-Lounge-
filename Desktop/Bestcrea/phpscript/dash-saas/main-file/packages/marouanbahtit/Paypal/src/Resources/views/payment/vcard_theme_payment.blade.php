<div class="col-xxl-4 col-lg-6 col-md-4 col-sm-6 col-12">
    <div class="card">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="theme-avtar">
                        <img src="{{ get_module_img('Paypal') }}" alt="" class="img-user" style="max-width: 100%">
                    </div>
                    <div class="ms-3">
                        <label for="paypal-payment">
                            <h5 class="mb-0 text-capitalize pointer">{{ Module_Alias_Name('Paypal') }}</h5>
                        </label>
                    </div>
                </div>
                <div class="form-check form-switch d-inline-block text-end">
                    <input type="checkbox" name="paymentoption[]" id="paypalLabel" data-target="paypal"
                            class="paymentButton form-check-input input-primary" value="paypal" data-payment-action="{{ route('vcard.enable.paypal',$id)}}"
                            {{ isset($cardPayment_content->paypal) && is_object($cardPayment_content->paypal) && $cardPayment_content->paypal->status === 'on' ? 'checked' : '' }}>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).on('change', '#paypalLabel', function() {
    var isChecked = $(this).prop('checked');
    var paymentOptions = isChecked ? ['paypal'] : [];
    var route = $(this).data('payment-action');

    $.ajax({
        url: route,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'), // Add CSRF token for security
            paymentoption: paymentOptions
        },
        success: function(response) {
            if (response.success) {
                $('#paypalContainer').toggleClass('active', isChecked);
                toastrs("success!", "The PayPal settings have been updated successfully.", "success");
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr.responseText);
        }
    });
});

</script>

