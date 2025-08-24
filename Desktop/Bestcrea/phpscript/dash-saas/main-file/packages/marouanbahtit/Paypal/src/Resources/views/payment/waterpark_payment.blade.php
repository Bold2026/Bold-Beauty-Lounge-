<div class="col-lg-4 col-sm-6 col-12">
    <div class="card mb-0">
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

                <div class="form-check">
                    <input class="form-check-input payment_method" name="payment_method" id="paypal-payment"
                        type="radio" data-payment-action="{{ route('water.park.pay.with.paypal', [$slug]) }}">
                </div>
            </div>
        </div>
    </div>
</div>
