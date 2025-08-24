
@php
$logo_dark = isset($settings['logo_dark'])
    ? (check_file($settings['logo_dark'])
        ? $settings['logo_dark']
        : 'uploads/logo/logo_dark.png')
    : 'uploads/logo/logo_dark.png';
@endphp
<div class="card" id="account-sidenav">
    {{ Form::open(['route' => 'accounts.setting.save', 'method' => 'post']) }}
    <div class="card-header p-3">
        <div class="row">
            <div class="col-lg-10 col-md-10 col-sm-10">
                <h5 class="">{{ __('Account Settings') }}</h5>
            </div>
        </div>
    </div>
    <div class="card-body p-3 pb-0">
        <div class="row mt-2">
            <div class="col-sm-6">
                <div class="form-group">
                    {{ Form::label('customer_prefix', __('Customer Prefix'), ['class' => 'form-label']) }}
                    {{ Form::text('customer_prefix', !empty($settings['customer_prefix']) ? $settings['customer_prefix'] : '#CUST00000', ['class' => 'form-control', 'placeholder' => 'Enter Customer Prefix']) }}
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    {{ Form::label('vendor_prefix', __('Vendor Prefix'), ['class' => 'form-label']) }}
                    {{ Form::text('vendor_prefix', !empty($settings['vendor_prefix']) ? $settings['vendor_prefix'] : '#VEND', ['class' => 'form-control', 'placeholder' => 'Enter Vendor Prefix']) }}
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end p-3">
        <input class="btn btn-print-invoice  btn-primary" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{ Form::close() }}
</div>
<!--Bill Setting-->

<div id="bill-print-sidenav" class="card">
    <div class="card-header p-3">
        <h5>{{ __('Bill Print Settings') }}</h5>
        <small class="text-muted">{{ __('Edit your Company Bill details') }}</small>
    </div>

    <div class="company-setting">
        <form id="setting-form" method="post" action="{{ route('bill.template.setting') }}"
            enctype ="multipart/form-data">
            @csrf
            <div class="card-body border-bottom border-1 p-3 pb-0">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            {{ Form::label('bill_prefix', __('Prefix'), ['class' => 'form-label']) }}
                            {{ Form::text('bill_prefix', !empty($settings['bill_prefix']) ? $settings['bill_prefix'] : '#BILL', ['class' => 'form-control', 'placeholder' => 'Enter Bill Prefix']) }}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {{ Form::label('bill_starting_number', __('Starting Number'), ['class' => 'form-label']) }}
                            {{ Form::number('bill_starting_number', !empty($settings['bill_starting_number']) ? $settings['bill_starting_number'] : 1, ['class' => 'form-control', 'placeholder' => 'Enter Bill Starting Number']) }}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {{ Form::label('bill_footer_title', __('Footer Title'), ['class' => 'form-label']) }}
                            {{ Form::text('bill_footer_title', !empty($settings['bill_footer_title']) ? $settings['bill_footer_title'] : '', ['class' => 'form-control', 'placeholder' => 'Enter Footer Title']) }}
                        </div>
                    </div>
                    <div class="col-xxl-8">
                        <div class="form-group">
                            {{ Form::label('bill_footer_notes', __('Footer Notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('bill_footer_notes', !empty($settings['bill_footer_notes']) ? $settings['bill_footer_notes'] : '', ['class' => 'form-control', 'rows' => '2', 'placeholder' => 'Enter Bill Footer Notes']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="row row-gap">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body p-2">
                                <div class="form-group d-flex align-items-center justify-content-between mb-0">
                                    {{ Form::label('bill_shipping_display', __('Shipping Display?'), ['class' => 'form-label mb-0']) }}
                                    <div class="text-end form-check form-switch d-inline-block">
                                        <input type="checkbox" class="form-check-input"
                                        name="bill_shipping_display" id="bill_shipping_display"
                                        {{ (isset($settings['bill_shipping_display']) ? $settings['bill_shipping_display'] : 'off') == 'on' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body p-2">
                                <div class="form-group d-flex align-items-center justify-content-between mb-0">
                                    {{ Form::label('bill_qr_display', __('QR Display?'), ['class' => 'form-label mb-0']) }}
                                    <div class="text-end form-check form-switch d-inline-block">
                                        <input type="checkbox" class="form-check-input"
                                        name="bill_qr_display" id="bill_qr_display"
                                        {{ (isset($settings['bill_qr_display']) ? $settings['bill_qr_display'] : 'off') == 'on' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body p-2">
                                <div class="form-group">
                                    {{ Form::label('bill_template', __('Bill Template'), ['class' => 'form-label']) }}
                                    {{ Form::select('bill_template', Workdo\Account\Entities\AccountUtility::templateData()['templates'], !empty($settings['bill_template']) ? $settings['bill_template'] : null, ['class' => 'form-control', 'required' => 'required']) }}
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header p-2">
                                <h6 class="form-label mb-0">{{ __('Color Input') }}</h6>
                            </div>
                            <div class="card-body p-2">
                                @foreach (Workdo\Account\Entities\AccountUtility::templateData()['colors'] as $key => $color)
                                    <label class="colorinput">
                                        <input name="bill_color" type="radio" value="{{ $color }}"
                                            class="colorinput-input"
                                            {{ !empty($settings['bill_color']) && $settings['bill_color'] == $color ? 'checked' : '' }}>
                                        <span class="colorinput-color rounded-circle"
                                            style="background: #{{ $color }}"></span>
                                    </label>
                                    @endforeach
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header p-2">
                                <h3 class="h6 mb-0">{{ __('Bill Logo')}}</h3>
                            </div>
                            <div class="card-body setting-card setting-logo-box p-3">
                                <div class="logo-content img-fluid logo-set-bg  text-center">
                                    <img alt="image" src="{{ isset($settings['bill_logo']) ? get_file($settings['bill_logo']) : get_file($logo_dark) }}" id="pre_default_logo4">
                                </div>
                                <div class="choose-files text-center  mt-3">
                                    <label for="bill_logo">
                                        <div class="bg-primary"> <i class="ti ti-upload px-1"></i>{{ __('Choose file here')}}</div>
                                        <input type="file" class="form-control file" name="bill_logo" id="bill_logo" data-filename="bill_logo" onchange="document.getElementById('pre_default_logo4').src = window.URL.createObjectURL(this.files[0])">
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group pt-4 mb-0 text-left">
                            <input type="submit" value="{{ __('Save Changes') }}"
                                class="btn btn-print-invoice  btn-primary ">
                        </div>
                    </div>
                    <div class="col-md-8">
                        @if (!empty($settings['bill_template']) && !empty($settings['bill_color']))
                            <iframe id="bill_frame" class="w-100 h-100 rounded-1" frameborder="0"
                                src="{{ route('bill.preview', [$settings['bill_template'], $settings['bill_color']]) }}"></iframe>
                        @else
                            <iframe id="bill_frame" class="w-100 h-100 rounded-1" frameborder="0"
                                src="{{ route('bill.preview', ['template1', 'fffff']) }}"></iframe>
                        @endif
                    </div>
                </div>
            </div>

        </form>
    </div>

</div>



<script>
    $(document).on("change", "select[name='bill_template'], input[name='bill_color']", function() {
        var template = $("select[name='bill_template']").val();
        var color = $("input[name='bill_color']:checked").val();
        $('#bill_frame').attr('src', '{{ url('/bill/preview') }}/' + template + '/' + color);
    });

</script>

