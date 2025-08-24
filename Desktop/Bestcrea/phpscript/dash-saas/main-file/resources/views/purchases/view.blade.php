@extends('layouts.main')
@section('page-title')
    {{ __('Purchase Detail') }}
@endsection
@push('scripts')
    <script>
        $(document).on('click', '#shipping', function() {
            var url = $(this).data('url');
            var is_display = $("#shipping").is(":checked");
            $.ajax({
                url: url,
                type: 'get',
                data: {
                    'is_display': is_display,
                },
                success: function(data) {}
            });
        })
    </script>
    <script type="text/javascript">
        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('success', '{{ __("Link Copy on Clipboard") }}', 'success')
        });
    </script>
@endpush
@section('page-breadcrumb')
    {{ __('Purchase') }},
    {{ App\Models\Purchase::purchaseNumberFormat($purchase->purchase_id) }}
@endsection

@push('css')
@if (module_is_active('Signature'))
<link rel="stylesheet" href="{{ asset('packages/workdo/Signature/src/Resources/assets/css/custom.css') }}">
@endif

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dropzone.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dropzone.css') }}">
    <style>
        .border-primary {
            border-color: #0CAF60 !important;
        }
    </style>
@endpush

@section('page-action')
<div>
    <div class="d-flex">
        @if (\Auth::user()->type != 'company')
            <a href="{{ route('purchases.pdf', Crypt::encrypt($purchase->id)) }}" target="_blank"
                class="btn btn-sm btn-primary me-2">
                <span class="btn-inner--icon text-white"><i class="ti ti-download"></i></span>
            </a>
        @endif
        <a href="#" class="btn btn-sm btn-primary cp_link"
            data-link="{{ route('purchases.link.copy', \Illuminate\Support\Facades\Crypt::encrypt($purchase->id)) }}"
            data-bs-toggle="tooltip" title="{{ __('Copy') }}"
            data-original-title="{{ __('Click to copy purchase link') }}">
            <span class="btn-inner--icon text-white"><i class="ti ti-file"></i></span>
        </a>
    </div>
</div>
@endsection

@section('content')
    @if (\Auth::user()->type == 'company')
        @permission('purchase send')
            @if ($purchase->status != 4)
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="row timeline-wrapper">
                            <div class="col-xl-4 col-lg-4 col-sm-6">
                                <div class="progress mb-3">
                                    <div class="progress-value"></div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <div class="timeline-icons ">
                                        <i class="ti ti-plus text-primary"></i>
                                    </div>
                                    <div class="invoice-content">
                                        <h2 class="text-primary h5 mb-2">{{ __('Create Purchase') }}</h2>
                                        <p class="text-sm mb-3">
                                            {{ __('Created on ') }}{{ company_date_formate($purchase->purchase_date) }}
                                        </p>
                                        @permission('purchase edit')
                                            <a href="{{ route('purchases.edit', \Crypt::encrypt($purchase->id)) }}"
                                                class="btn btn-sm btn-light" data-bs-toggle="tooltip"
                                                data-original-title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil me-1"></i>{{ __('Edit') }}</a>
                                        @endpermission
                                    </div>

                                </div>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-sm-6">
                                <div class="progress mb-3">
                                    <div class="{{ $purchase->status !== 0 ? 'progress-value' : '' }}"></div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <div class="timeline-icons ">
                                        <i class="ti ti-send text-warning"></i>
                                    </div>
                                    <div class="invoice-content">
                                        <h6 class="text-warning h5 mb-2">{{ __('Send Purchase') }}</h6>
                                        <p class="text-sm mb-2">
                                            @if ($purchase->status != 0)
                                                {{ __('Sent on') }}
                                                {{ company_date_formate($purchase->send_date) }}
                                            @else
                                                @permission('purchase send')
                                                    {{ __('Status') }} : {{ __('Not Sent') }}
                                                @endpermission
                                            @endif
                                        </p>
                                        @if ($purchase->status == 0)
                                            @permission('purchase send')
                                                <a href="{{ route('purchases.sent', $purchase->id) }}" class="btn btn-sm btn-warning"
                                                    data-bs-toggle="tooltip" data-original-title="{{ __('Mark Sent') }}"><i
                                                        class="ti ti-send me-1"></i>{{ __('Send') }}</a>
                                            @endpermission
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-sm-6">
                                <div class="progress mb-3">
                                    <div class="{{ $purchase->status == 4 ? 'progress-value' : '' }}"></div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <div class="timeline-icons ">
                                        <i class="ti ti-report-money text-info"></i>
                                    </div>
                                    <div class="invoice-content">
                                        <h6 class="text-info h5 mb-2">{{ __('Get Paid') }}</h6>
                                        <p class="text-sm mb-3">{{ __('Status') }} :
                                            @if ($purchase->status == 0)
                                                <span>{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                            @elseif($purchase->status == 1)
                                                <span>{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                            @elseif($purchase->status == 2)
                                                <span>{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                            @elseif($purchase->status == 3)
                                                <span>{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                            @elseif($purchase->status == 4)
                                                <span>{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                            @endif
                                        </p>
                                        @if ($purchase->status != 0)
                                            @permission('purchase payment create')
                                                <a href="#" data-url="{{ route('purchases.payment', $purchase->id) }}"
                                                    data-ajax-popup="true" data-title="{{ __('Add Payment') }}"
                                                    class="btn btn-sm btn-light" data-original-title="{{ __('Add Payment') }}"><i
                                                        class="ti ti-report-money mr-2"></i>{{ __(' Add Payment') }}</a> <br>
                                            @endpermission
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endpermission
        @endif
        @if (\Auth::user()->type == 'company')
            <div class="row row-gap justify-content-between align-items-center mb-3">
                <div class="col-md-6">
                    <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if (!session('tab') or session('tab') and session('tab') == 1) active @endif" id="purchase-setting-tab"
                                data-bs-toggle="pill" data-bs-target="#purchase-setting"
                                type="button">{{ __('Purchase') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if (session('tab') and session('tab') == 2) active @endif" id="payment-setting-tab"
                                data-bs-toggle="pill" data-bs-target="#payment-setting"
                                type="button">{{ __('Payment') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if (session('tab') and session('tab') == 3) active @endif" id="debit-setting-tab"
                                data-bs-toggle="pill" data-bs-target="#debit-setting"
                                type="button">{{ __('Debit Note') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if (session('tab') and session('tab') == 4) active @endif" id="attachment-setting-tab"
                                data-bs-toggle="pill" data-bs-target="#attachment-setting"
                                type="button">{{ __('Attachments') }}</button>
                        </li>
                    </ul>
                </div>

                <div class="col-md-6 apply-wrp d-flex align-items-center justify-content-between justify-content-md-end">
                    @if ($purchase->status != 0)
                        <div class="row justify-content-between align-items-center">
                            <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                                <div class="all-button-box me-2">
                                    <a href="#" data-url="{{ route('purchases.debit.note', $purchase->id) }}"
                                        data-ajax-popup="true" data-title="{{ __('Apply Debit Note') }}"
                                        class="btn btn-sm btn-primary">
                                        {{ __('Apply Debit Note') }}
                                    </a>
                                </div>
                                <div class="all-button-box me-2">
                                    <a href="{{ route('purchases.resent', $purchase->id) }}" class="btn btn-sm btn-primary">
                                        {{ __('Resend purchase') }}
                                    </a>
                                </div>
                                <div class="all-button-box">
                                    <a href="{{ route('purchases.pdf', Crypt::encrypt($purchase->id)) }}" target="_blank"
                                        class="btn btn-sm btn-primary">
                                        {{ __('Download') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif


        <div class="row">
            <div class="col-12">
                <div class="tab-content" id="pills-tabContent">

                    <div class="tab-pane fade @if (!session('tab') or session('tab') and session('tab') == 1) active show @endif" id="purchase-setting"
                        role="tabpanel" aria-labelledby="pills-user-tab-1">
                        <div class="card">
                            <div class="card-body">
                                <div class="invoice">
                                    <div class="invoice-print">
                                        <div class="row row-gap invoice-title border-1 border-bottom  pb-3 mb-3">
                                            <div class="col-sm-4  col-12">
                                                <h2 class="h3 mb-0">{{ __('Purchase') }}</h2>
                                            </div>
                                            <div class="col-sm-8  col-12">
                                                <div
                                                    class="d-flex invoice-wrp flex-wrap align-items-center gap-md-2 gap-1 justify-content-end">
                                                    <div
                                                        class="d-flex invoice-date flex-wrap align-items-center justify-content-end gap-md-3 gap-1">
                                                        <p class="mb-0"><strong>{{ __('Purchase Date')}} :</strong>
                                                            {{ company_date_formate($purchase->purchase_date) }}</p>
                                                    </div>
                                                    <h3 class="invoice-number mb-0">
                                                        {{ App\Models\Purchase::purchaseNumberFormat($purchase->purchase_id) }}
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="p-sm-4 p-3 invoice-billed">
                                            <div class="row row-gap">
                                                <div class="col-lg-4 col-sm-6">
                                                    <div class="invoice-billed-inner">
                                                        <p class="mb-3">
                                                            @if (empty($purchase->vender_name))
                                                                <strong class="h5 mb-1">{{ __('Name ') }} :
                                                                </strong>{{ !empty($vendor->name) ? $vendor->name : '' }}
                                                            @else
                                                                <strong class="h5 mb-1">{{ __('Vendor Name ') }} :
                                                                </strong>{{ !empty($purchase->vender_name) ? $purchase->vender_name : '' }}
                                                            @endif
                                                        </p>
                                                        <div class="billed-content-top">
                                                           @if (!empty($vendor->billing_name) && !empty($vendor->billing_address) && !empty($vendor->billing_zip))
                                                                <div class="invoice-billed-content">
                                                                    <p class="mb-2"><strong
                                                                            class="h5 mb-1 d-block">{{ __('Billed To') }}
                                                                            :</strong>
                                                                        <span class="text-muted d-block" style="max-width:80%">
                                                                                {{ !empty($vendor->billing_name) ? $vendor->billing_name : '' }}
                                                                                {{ !empty($vendor->billing_address) ? $vendor->billing_address : '' }}
                                                                                {{ !empty($vendor->billing_city) ? $vendor->billing_city . ' ,' : '' }}
                                                                                {{ !empty($vendor->billing_state) ? $vendor->billing_state . ' ,' : '' }}
                                                                                {{ !empty($vendor->billing_zip) ? $vendor->billing_zip : '' }}
                                                                                {{ !empty($vendor->billing_country) ? $vendor->billing_country : '' }}
                                                                        </span>
                                                                    </p>
                                                                    <p class="mb-1 text-dark">
                                                                        {{ !empty($vendor->billing_phone) ? $vendor->billing_phone : '' }}
                                                                    </p>
                                                                    <p class="mb-0">
                                                                        <strong>{{ __('Tax Number ') }} :
                                                                        </strong>{{ !empty($vendor->tax_number) ? $vendor->tax_number : '' }}
                                                                    </p>

                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="billed-content-bottom">

                                                            @if (module_is_active('Signature'))
                                                                <p>
                                                                    @if ($purchase->company_signature)
                                                                        <div class="mb-2">
                                                                            <img width="100px"
                                                                                src="{{ $purchase->company_signature }}">
                                                                        </div>
                                                                    @else
                                                                        <div class="mb-2">
                                                                            <span
                                                                                class="badge bg-secondary p-2">{{ __('Not Signed') }}</span>
                                                                        </div>
                                                                    @endif
                                                                <div>
                                                                    <h5 class="mt-auto">
                                                                        {{ __('Company Signature') }}</h5>
                                                                </div>
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-sm-6">
                                                    <div class="invoice-billed-inner">
                                                        @if(empty($purchase->vender_name))
                                                            <p class="mb-3">
                                                                <strong class="h5 mb-1">{{ __('Email ') }} :
                                                                </strong>{{ !empty($vendor->email) ? $vendor->email : '' }}
                                                            </p>
                                                        @endif
                                                        <div class="billed-content-top">
                                                            @if (!empty($company_settings['purchase_shipping_display']) && $company_settings['purchase_shipping_display'] == 'on')
                                                                @if (!empty($vendor->shipping_name) && !empty($vendor->shipping_address) && !empty($vendor->shipping_zip))
                                                                    <div class="invoice-billed-content">
                                                                        <p class="mb-2">
                                                                            <strong class="h5 mb-1 d-block">{{ __('Shipped To') }}
                                                                                :</strong>
                                                                            <span class="text-muted d-block"
                                                                                style="max-width:80%">
                                                                                {{ !empty($vendor->shipping_name) ? $vendor->shipping_name : '' }}
                                                                                {{ !empty($vendor->shipping_address) ? $vendor->shipping_address : '' }}
                                                                                {{ !empty($vendor->shipping_city) ? $vendor->shipping_city . ' ,' : '' }}
                                                                                {{ !empty($vendor->shipping_state) ? $vendor->shipping_state . ' ,' : '' }}
                                                                                {{ !empty($vendor->shipping_zip) ? $vendor->shipping_zip : '' }}
                                                                                {{ !empty($vendor->shipping_country) ? $vendor->shipping_country : '' }}
                                                                            </span>
                                                                        </p>
                                                                        <p class="mb-1 text-dark">
                                                                            {{ !empty($vendor->shipping_phone) ? $vendor->shipping_phone : '' }}
                                                                        </p>
                                                                        <p class="mb-0">
                                                                            <strong>{{ __('Tax Number ') }} :
                                                                            </strong>{{ !empty($vendor->tax_number) ? $vendor->tax_number : '' }}
                                                                        </p>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                        <div class="billed-content-bottom">
                                                            @if (module_is_active('Signature'))
                                                                <div class="vendor-signature-content">
                                                                    <p>
                                                                        @if ($purchase->vendor_signature != '')
                                                                            <div class="mb-2">
                                                                                <img width="100px"
                                                                                    src="{{ $purchase->vendor_signature }}">
                                                                            </div>
                                                                        @else
                                                                            <div class="mb-2">
                                                                                <span
                                                                                    class="badge bg-secondary p-2">{{ __('Not Signed') }}</span>
                                                                            </div>
                                                                        @endif

                                                                    <div>
                                                                        <h5 class="mt-auto">
                                                                            {{ __('Vendor Signature') }}</h5>
                                                                    </div>
                                                                    </p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-sm-6">
                                                    <strong class="h5 d-block mb-2">Status :</strong>
                                                    @if ($purchase->status == 0)
                                                        <span
                                                            class="badge bg-info p-2 px-3">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                                    @elseif($purchase->status == 1)
                                                        <span
                                                            class="badge bg-primary p-2 px-3">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                                    @elseif($purchase->status == 2)
                                                        <span
                                                            class="badge bg-secondary p-2 px-3">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                                    @elseif($purchase->status == 3)
                                                        <span
                                                            class="badge bg-warning p-2 px-3">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                                    @elseif($purchase->status == 4)
                                                        <span
                                                            class="badge bg-success p-2 px-3">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                                    @endif
                                                </div>

                                                @if (!empty($company_settings['purchase_qr_display']) && $company_settings['purchase_qr_display'] == 'on')
                                                    <div class="col-lg-2 col-sm-6">
                                                        <div class="float-sm-end qr-code">
                                                            <div class="col">
                                                                <div class="float-sm-end">
                                                                    {!! DNS2D::getBarcodeHTML(
                                                                        route('purchases.link.copy', \Illuminate\Support\Facades\Crypt::encrypt($purchase->id)),
                                                                        'QRCODE',
                                                                        2,
                                                                        2,
                                                                    ) !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @if (!empty($customFields) && count($purchase->customField) > 0)
                                            <div class="px-4 mt-3">
                                                <div class="row row-gap">
                                                    @foreach ($customFields as $field)
                                                        <div class="col-xxl-3 col-sm-6">
                                                            <strong class="d-block mb-1">{{ $field->name }} </strong>

                                                            @if ($field->type == 'attachment')
                                                                <a href="{{ get_file($purchase->customField[$field->id]) }}"
                                                                    target="_blank">
                                                                    <img src=" {{ get_file($purchase->customField[$field->id]) }} "
                                                                        class="wid-120 rounded">
                                                                </a>
                                                            @else
                                                                <p class="mb-0">
                                                                    {{ !empty($purchase->customField[$field->id]) ? $purchase->customField[$field->id] : '-' }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div class="invoice-summary mt-3">
                                            <div class="col-md-12">
                                                <div class="invoice-title border-1 border-bottom mb-3 pb-2">
                                                    <h3 class="h4 mb-0">{{ __('Item Summary') }}</h3>
                                                    <small>{{ __('All items here cannot be deleted.') }}</small>
                                                </div>
                                                <div class="table-responsive mt-2">
                                                    <table class="table mb-0 table-striped">
                                                        <tr>
                                                            <th data-width="40" class="text-white bg-primary text-uppercase">#
                                                            </th>
                                                            <th class="text-white bg-primary text-uppercase">
                                                                {{ __('Item Type') }}</th>
                                                            <th class="text-white bg-primary text-uppercase">
                                                                {{ __('Item') }}</th>
                                                            <th class="text-white bg-primary text-uppercase">
                                                                {{ __('Quantity') }}</th>
                                                            <th class="text-white bg-primary text-uppercase">
                                                                {{ __('Rate') }}</th>
                                                            <th class="text-white bg-primary text-uppercase">
                                                                {{ __('Discount') }} </th>
                                                            <th class="text-white bg-primary text-uppercase">
                                                                {{ __('Tax') }}</th>
                                                            <th class="text-white bg-primary text-uppercase">
                                                                {{ __('Description') }}</th>
                                                            <th class="text-end text-white bg-primary text-uppercase"
                                                                width="12%">
                                                                {{ __('Price') }}<br>
                                                                <small
                                                                    class="text-danger font-weight-bold">{{ __('After discount & tax') }}</small>
                                                            </th>

                                                        </tr>
                                                        @php
                                                            $totalQuantity = 0;
                                                            $totalRate = 0;
                                                            $totalTaxPrice = 0;
                                                            $totalDiscount = 0;
                                                            $taxesData = [];
                                                            $TaxPrice_array = [];
                                                        @endphp

                                                        @foreach ($iteams as $key => $iteam)
                                                            @if (!empty($iteam->tax))
                                                                @php
                                                                    $taxes = App\Models\Purchase::taxs($iteam->tax);
                                                                    $totalQuantity += $iteam->quantity;
                                                                    $totalRate += $iteam->price;
                                                                    $totalDiscount += $iteam->discount;
                                                                    foreach ($taxes as $taxe) {
                                                                        $taxDataPrice = App\Models\Purchase::taxRate(
                                                                            $taxe->rate,
                                                                            $iteam->price,
                                                                            $iteam->quantity,
                                                                            $iteam->discount,
                                                                        );
                                                                        if (array_key_exists($taxe->name, $taxesData)) {
                                                                            $taxesData[$taxe->name] =
                                                                                $taxesData[$taxe->name] + $taxDataPrice;
                                                                        } else {
                                                                            $taxesData[$taxe->name] = $taxDataPrice;
                                                                        }
                                                                    }
                                                                @endphp
                                                            @endif
                                                            <tr>
                                                                <td>{{ $key + 1 }}</td>
                                                                <td>{{ !empty($iteam->product_type) ? Str::ucfirst($iteam->product_type) : '--' }}
                                                                </td>
                                                                <td>{{ !empty($iteam->product) ? $iteam->product->name : '' }}
                                                                </td>
                                                                <td>{{ $iteam->quantity }}</td>
                                                                <td>{{ currency_format_with_sym($iteam->price) }}</td>
                                                                <td>{{ currency_format_with_sym($iteam->discount) }}</td>
                                                                <td>
                                                                    @if (!empty($iteam->tax))
                                                                        <table>
                                                                            @php
                                                                                $totalTaxRate = 0;
                                                                                $data = 0;
                                                                            @endphp
                                                                            @foreach ($taxes as $tax)
                                                                                @php
                                                                                    $taxPrice = App\Models\Purchase::taxRate(
                                                                                        $tax->rate,
                                                                                        $iteam->price,
                                                                                        $iteam->quantity,
                                                                                        $iteam->discount,
                                                                                    );
                                                                                    $totalTaxPrice += $taxPrice;
                                                                                    $data += $taxPrice;
                                                                                @endphp
                                                                                <tr>
                                                                                    <td class="">
                                                                                        {{ $tax->name . ' (' . $tax->rate . '%)' }}
                                                                                    </td>
                                                                                    <td>{{ currency_format_with_sym($taxPrice) }}
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                            @php
                                                                                array_push($TaxPrice_array, $data);
                                                                            @endphp
                                                                        </table>
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td>{{ !empty($iteam->description) ? $iteam->description : '-' }}
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ currency_format_with_sym($iteam->price * $iteam->quantity - $iteam->discount + $totalTaxPrice) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        <tfoot>
                                                            <tr>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="bg-color"><b>{{ __('Total') }}</b></td>
                                                                <td class="bg-color"><b>{{ $totalQuantity }}</b></td>
                                                                <td class="bg-color">
                                                                    <b>{{ currency_format_with_sym($totalRate) }}</b></td>
                                                                <td class="bg-color">
                                                                    <b>{{ currency_format_with_sym($totalDiscount) }}</b></td>
                                                                <td class="bg-color">
                                                                    <b>{{ currency_format_with_sym($totalTaxPrice) }}</b></td>

                                                            </tr>
                                                            <tr>
                                                                <td colspan="7"></td>
                                                                <td class="text-end">{{ __('Sub Total') }}</td>
                                                                <td class="text-end">
                                                                    <b>{{ currency_format_with_sym($purchase->getSubTotal()) }}</b>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td colspan="7"></td>
                                                                <td class="text-end">{{ __('Discount') }}</td>
                                                                <td class="text-end">
                                                                    <b>{{ currency_format_with_sym($purchase->getTotalDiscount()) }}</b>
                                                                </td>
                                                            </tr>

                                                            @if (!empty($taxesData))
                                                                @foreach ($taxesData as $taxName => $taxPrice)
                                                                    <tr>
                                                                        <td colspan="7"></td>
                                                                        <td class="text-end">{{ $taxName }}</td>
                                                                        <td class="text-end">
                                                                            <b>{{ currency_format_with_sym($taxPrice) }}</b>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                            <tr>
                                                                <td colspan="7"></td>
                                                                <td class="blue-text text-end">{{ __('Total') }}
                                                                </td>
                                                                <td class="blue-text text-end">
                                                                    <b>{{ currency_format_with_sym($purchase->getTotal()) }}</b>
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $getdue = $purchase->getDue();
                                                            @endphp
                                                            <tr>
                                                                <td colspan="7"></td>
                                                                <td class="text-end">{{ __('Paid') }}</td>
                                                                <td class="text-end">
                                                                    <b>{{ currency_format_with_sym($purchase->getTotal() - $getdue) }}</b>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="7"></td>
                                                                <td class="text-end">{{ __('Debit Note') }}</td>
                                                                <td class="text-end">
                                                                    <b>{{ currency_format_with_sym($purchase->purchaseTotalDebitNote()) }}</b>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="7"></td>
                                                                <td class="text-end">{{ __('Due') }}</td>
                                                                <td class="text-end">
                                                                    <b>{{ currency_format_with_sym($getdue) }}</b>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade @if (session('tab') and session('tab') == 2) active show @endif" id="payment-setting"
                        role="tabpanel" aria-labelledby="pills-user-tab-2">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body table-border-style">
                                        <h5 class=" d-inline-block mb-5">{{ __('Payment Summary') }}</h5>
                                        <div class="table-responsive">
                                            <table class="table mb-0 pc-dt-simple" id="payment_summary">
                                                <thead>
                                                    <tr>
                                                        <th class="text-dark">{{ __('Payment Receipt') }}</th>
                                                        <th class="text-dark">{{ __('Date') }}</th>
                                                        <th class="text-dark">{{ __('Amount') }}</th>
                                                        <th class="text-dark">{{ __('Account') }}</th>
                                                        <th class="text-dark">{{ __('Reference') }}</th>
                                                        <th class="text-dark">{{ __('Description') }}</th>
                                                        @permission('purchase payment delete')
                                                            <th class="text-dark">{{ __('Action') }}</th>
                                                        @endpermission
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($purchase->payments as $key =>$payment)
                                                        <tr>
                                                            <td>
                                                                @if (!empty($payment->add_receipt))
                                                                    <div class="action-btn me-2">
                                                                        <a href="{{ get_file($payment->add_receipt) }}"
                                                                            download=""
                                                                            class="mx-3 btn btn-sm align-items-center bg-primary"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Download') }}" target="_blank">
                                                                            <i class="ti ti-download text-white"></i>
                                                                        </a>
                                                                    </div>
                                                                    <div class="action-btn">
                                                                        <a href="{{ get_file($payment->add_receipt) }}"
                                                                            class="mx-3 btn btn-sm align-items-center bg-secondary"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Show') }}" target="_blank">
                                                                            <i class="ti ti-crosshair text-white"></i>
                                                                        </a>
                                                                    </div>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>{{ company_date_formate($payment->date) }}</td>
                                                            <td>{{ currency_format_with_sym($payment->amount) }}</td>
                                                            <td>{{ !empty($payment->bankAccount) ? $payment->bankAccount->bank_name . ' ' . $payment->bankAccount->holder_name : '' }}
                                                            </td>
                                                            <td>{{ $payment->reference }}</td>
                                                            <td>
                                                                {{ isset($payment->description) ? $payment->description : '-' }}</td>
                                                            <td class="text-dark">
                                                                @permission('purchase payment delete')
                                                                    <div class="action-btn">
                                                                        {{ Form::open(['route' => ['purchases.payment.destroy', $purchase->id, $payment->id], 'class' => 'm-0']) }}
                                                                        <a href="#"
                                                                            class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="Delete" aria-label="Delete"
                                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="delete-form-{{ $payment->id }}">
                                                                            <i class="ti ti-trash text-white text-white"></i>
                                                                        </a>
                                                                        {{ Form::close() }}
                                                                    </div>
                                                                @endpermission
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        @include('layouts.nodatafound')
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade @if (session('tab') and session('tab') == 3) active show @endif" id="debit-setting"
                        role="tabpanel" aria-labelledby="pills-user-tab-3">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body table-border-style">
                                        <h5 class="d-inline-block mb-5">{{ __('Debit Note Summary') }}</h5>
                                        <div class="table-responsive">
                                            <table class="table mb-0 pc-dt-simple" id="debit_summary">
                                                <thead>
                                                    <tr>
                                                        <th class="text-dark">{{ __('Debit Note') }}</th>
                                                        <th class="text-dark">{{ __('Date') }}</th>
                                                        <th class="text-dark">{{ __('Amount') }}</th>
                                                        <th class="text-dark">{{ __('Description') }}</th>
                                                        @if (Laratrust::hasPermission('purchase debitnote edit') || Laratrust::hasPermission('purchase debitnote delete'))
                                                            <th class="text-dark">{{ __('Action') }}</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                @forelse($purchase->debitNote as $key =>$debitNote)
                                                    <tr>
                                                        <td><a href="#" class="btn btn-outline-primary">{{ !empty($debitNote->debitNote) ? \Workdo\Account\Entities\CustomerDebitNotes::debitNumberFormat($debitNote->debitNote->debit_id) : '-' }}</a></td>
                                                        <td>{{ company_date_formate($debitNote->date) }}</td>
                                                        <td>{{ currency_format_with_sym($debitNote->amount) }}</td>
                                                        <td>{{ isset($debitNote->description) ? $debitNote->description :'-' }}</td>
                                                        <td>
                                                            @permission('purchase debitnote edit')
                                                                <div class="action-btn me-2">
                                                                    <a data-url="{{ route('purchases.edit.debit.note', [$debitNote->purchase, $debitNote->id]) }}"
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Edit Debit Note') }}" href="#"
                                                                        class="mx-3 btn btn-sm align-items-center bg-info"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-original-title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endpermission
                                                            @permission('purchase debitnote delete')
                                                                <div class="action-btn">
                                                                    {{ Form::open(['route' => ['purchases.delete.debit.note', $debitNote->purchase, $debitNote->id], 'class' => 'm-0']) }}
                                                                    @method('DELETE')
                                                                    <a href="#"
                                                                        class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-bs-original-title="Delete" aria-label="Delete"
                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="delete-form-{{ $debitNote->id }}">
                                                                        <i class="ti ti-trash text-white text-white"></i>
                                                                    </a>
                                                                    {{ Form::close() }}
                                                                </div>
                                                            @endpermission
                                                        </td>
                                                    </tr>
                                                @empty
                                                    @include('layouts.nodatafound')
                                                @endforelse
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade @if (session('tab') and session('tab') == 4) active show @endif" id="attachment-setting"
                        role="tabpanel" aria-labelledby="pills-user-tab-4">
                        <div class="row">
                            <h5 class="d-inline-block my-3">{{ __('Attachments') }}</h5>
                            <div class="col-3">
                                <div class="card border-primary border">
                                    <div class="card-body table-border-style">
                                        <div class="col-md-12 dropzone browse-file" id="dropzonewidget">
                                            <div class="dz-message my-5" data-dz-message>
                                                <span>{{ __('Drop files here to upload') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-9">
                                <div class="card border-primary border">
                                    <div class="card-body table-border-style">
                                        <div class="table-responsive">
                                            <table class="table mb-0 pc-dt-simple" id="attachment">
                                                <thead>
                                                    <tr>
                                                        <th class="text-dark">{{ __('#') }}</th>
                                                        <th class="text-dark">{{ __('File Name') }}</th>
                                                        <th class="text-dark">{{ __('File Size') }}</th>
                                                        <th class="text-dark">{{ __('Date Created') }}</th>
                                                        <th class="text-dark">{{ __('Action') }}</th>
                                                    </tr>
                                                </thead>
                                                @forelse($purchase_attachment as $key =>$attachment)
                                                    <tr>
                                                        <td>{{ ++$key }}</td>
                                                        <td>{{ $attachment->file_name }}</td>
                                                        <td>{{ $attachment->file_size }}</td>
                                                        <td>{{ company_date_formate($attachment->created_at) }}</td>
                                                        <td>
                                                            <div class="action-btn me-2">
                                                                <a href="{{ url($attachment->file_path) }}" data-bs-toggle="tooltip"
                                                                    class="mx-3 btn btn-sm align-items-center bg-primary"
                                                                    title="{{ __('Download') }}" target="_blank" download>
                                                                    <i class="ti ti-download text-white"></i>
                                                                </a>
                                                            </div>
                                                            <div class="action-btn">
                                                                {{ Form::open(['route' => ['purchases.attachment.destroy', $attachment->id], 'class' => 'm-0']) }}
                                                                @method('DELETE')
                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Delete" aria-label="Delete"
                                                                    data-confirm="{{ __('Are You Sure?') }}"
                                                                    data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="delete-form-{{ $attachment->id }}">
                                                                    <i class="ti ti-trash text-white text-white"></i>
                                                                </a>
                                                                {{ Form::close() }}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    @include('layouts.nodatafound')
                                                @endforelse
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(\Auth::user()->type != 'company')
            <div class="col-12">
                <h5 class="h4 d-inline-block font-weight-400 mb-4">{{ __('Receipt Summary') }}</h5>
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table ">
                                <tr>
                                    <th class="text-dark">{{ __('Payment Receipt') }}</th>
                                    <th class="text-dark">{{ __('Date') }}</th>
                                    <th class="text-dark">{{ __('Amount') }}</th>
                                    <th class="text-dark">{{ __('Account') }}</th>
                                    <th class="text-dark">{{ __('Reference') }}</th>
                                    <th class="text-dark">{{ __('Description') }}</th>
                                </tr>
                                @forelse($purchase->payments as $key =>$payment)
                                    <tr>
                                        <td>
                                            @if (!empty($payment->add_receipt))
                                                <div class="action-btn me-2">
                                                    <a href="{{ get_file($payment->add_receipt) }}"
                                                        download=""
                                                        class="mx-3 btn btn-sm align-items-center bg-primary"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Download') }}" target="_blank">
                                                        <i class="ti ti-download text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn">
                                                    <a href="{{ get_file($payment->add_receipt) }}"
                                                        class="mx-3 btn btn-sm align-items-center bg-secondary"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Show') }}" target="_blank">
                                                        <i class="ti ti-crosshair text-white"></i>
                                                    </a>
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ company_date_formate($payment->date) }}</td>
                                        <td>{{ currency_format_with_sym($payment->amount) }}</td>
                                        <td>{{ !empty($payment->bankAccount) ? $payment->bankAccount->bank_name . ' ' . $payment->bankAccount->holder_name : '' }}</td>
                                        <td>{{ $payment->reference }}</td>
                                        <td>{{ isset($payment->description) ? $payment->description : '-' }}</td>
                                    </tr>
                                @empty
                                    @include('layouts.nodatafound')
                                @endforelse
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @endsection

@push('scripts')
    <script src="{{ asset('assets/js/plugins/dropzone-amd-module.min.js') }}"></script>
    <script>
        Dropzone.autoDiscover = false;
        myDropzone = new Dropzone("#dropzonewidget", {
            maxFiles: 20,
            maxFilesize: 20,
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.pdf,.doc,.txt",
            url: "{{ route('purchases.files.upload', [$purchase->id]) }}",
            success: function(file, response) {
                if (response.is_success) {
                    // dropzoneBtn(file, response);
                    location.reload();
                    myDropzone.removeFile(file);
                    toastrs('{{ __('Success') }}', 'File Successfully Uploaded', 'success');
                } else {
                    location.reload();
                    myDropzone.removeFile(response.error);
                    toastrs('Error', response.error, 'error');
                }
            },
            error: function(file, response) {
                myDropzone.removeFile(file);
                location.reload();
                if (response.error) {
                    toastrs('Error', response.error, 'error');
                } else {
                    toastrs('Error', response, 'error');
                }
            }
        });
        myDropzone.on("sending", function(file, xhr, formData) {
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("purchase_id", {{ $purchase->id }});
        });
    </script>
@endpush
