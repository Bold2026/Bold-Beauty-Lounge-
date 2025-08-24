@extends('layouts.main')
@php
    $admin_settings = getAdminAllSetting();

    $company_settings = getCompanyAllSetting(creatorId());

@endphp
@section('page-title')
    {{ __('Bill Detail') }}
@endsection
@section('page-breadcrumb')
    {{ __('Bill Detail') }}
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
        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('success', '{{ __('Link Copy on Clipboard') }}', 'success')
        });
    </script>
    <script src="{{ asset('assets/js/plugins/dropzone-amd-module.min.js') }}"></script>
    <script>
        Dropzone.autoDiscover = false;
        myDropzone = new Dropzone("#dropzonewidget", {
            url: "{{ route('bill.file.upload', [$bill->id]) }}",
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
            formData.append("bill_id", {{ $bill->id }});
        });
    </script>
@endpush
@push('css')
    @if (module_is_active('Signature'))
        <link rel="stylesheet" href="{{ asset('packages/workdo/Signature/src/Resources/assets/css/custom.css') }}">
    @endif
    <style>
        .bill_status {
            min-width: 94px;
        }
    </style>
@endpush
@section('page-action')
    <div class="float-end d-flex">
        @if (\Auth::user()->type != 'company')
            <a href="{{ route('bill.pdf', Crypt::encrypt($bill->id)) }}" target="_blank" class="btn btn-sm btn-primary me-2">
                <span class="btn-inner--icon text-white"><i class="ti ti-download"></i></span>
            </a>
        @endif
        <a href="#" class="btn btn-sm btn-primary cp_link"
            data-link="{{ route('pay.billpay', \Illuminate\Support\Facades\Crypt::encrypt($bill->id)) }}"
            data-bs-toggle="tooltip" title="{{ __('copy') }}"
            data-original-title="{{ __('Click to copy invoice link') }}">
            <span class="text-white btn-inner--icon"><i class="ti ti-file"></i></span>
        </a>
    </div>
@endsection
@section('content')
    @if (\Auth::user()->type == 'company')
        @if ($bill->status != 4)
            <div class="card mt-3">
                <div class="card-body">
                    <div class="row timeline-wrapper">
                        <div class="col-xl-4 col-lg-4 col-sm-6">
                            <div class="progress mb-3">
                                <div class="progress-value"></div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <div class="timeline-icons">
                                    <i class="ti ti-plus text-primary"></i>
                                </div>
                                <div class="invoice-content">
                                    <h2 class="text-primary h5 mb-2">{{ __('Create Bill') }}</h2>
                                    <p class="text-sm mb-3">
                                        {{ __('Created on ') }}{{ company_date_formate($bill->bill_date) }}
                                    </p>
                                    @permission('bill edit')
                                        <a href="{{ route('bill.edit', \Crypt::encrypt($bill->id)) }}"
                                            class="btn btn-sm btn-light" data-bs-toggle="tooltip"
                                            data-original-title="{{ __('Edit') }}"><i
                                                class="mr-2 ti ti-pencil"></i>{{ __('Edit') }}
                                        </a>
                                    @endpermission
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-sm-6">
                            <div class="progress mb-3">
                                <div class="{{ $bill->status !== 0 ? 'progress-value' : '' }}"></div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <div class="timeline-icons">
                                    <i class="ti ti-send text-warning"></i>
                                </div>
                                <div class="invoice-content">
                                    <h6 class="text-warning h5 mb-2">{{ __('Send Bill') }}</h6>
                                    <p class="text-sm mb-2">
                                        @if ($bill->status != 0)
                                            {{ __('Sent on') }}
                                            {{ company_date_formate($bill->send_date) }}
                                        @else
                                            {{ __('Status') }} : {{ __('Not Sent') }}
                                        @endif
                                    </p>
                                    @stack('recurring_type')
                                    @if ($bill->status == 0)
                                        @permission('bill send')
                                            <a href="{{ route('bill.sent', $bill->id) }}" class="btn btn-sm btn-warning"
                                                data-bs-toggle="tooltip" data-original-title="{{ __('Mark Sent') }}"><i
                                                    class="me-1 ti ti-send"></i>{{ __('Send') }}</a>
                                        @endpermission
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-sm-6">
                            <div class="progress mb-3">
                                <div class="{{ $bill->status == 4 ? 'progress-value' : '' }}"></div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <div class="timeline-icons">
                                    <i class="ti ti-report-money text-info"></i>
                                </div>
                                <div class="invoice-content">
                                    <h6 class="text-info h5 mb-2">{{ __('Pay Bill') }}</h6>
                                    <p class="text-sm mb-3">{{ __('Status') }} :
                                        @if ($bill->status == 0)
                                            <span>{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 1)
                                            <span>{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 2)
                                            <span>{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 3)
                                            <span>{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 4)
                                            <span>{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                        @endif
                                    </p>
                                    @if ($bill->status != 0)
                                        @permission('bill payment create')
                                            <a href="#" data-url="{{ route('bill.payment', $bill->id) }}"
                                                data-ajax-popup="true" data-title="{{ __('Add Payment') }}"
                                                class="btn btn-sm btn-light" data-original-title="{{ __('Add Payment') }}"><i
                                                    class="mr-2 ti ti-report-money"></i>{{ __(' Add Payment') }}</a> <br>
                                        @endpermission
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if (\Auth::user()->type == 'company')
        <div class="mb-3 row justify-content-between align-items-center">
            <div class="col-md-6">
                <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="bill-tab" data-bs-toggle="pill" data-bs-target="#bill"
                            type="button">{{ __('Bill') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payment-summary-tab" data-bs-toggle="pill"
                            data-bs-target="#payment-summary" type="button">{{ __('Payment Summary') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="debit-summary-tab" data-bs-toggle="pill"
                            data-bs-target="#debit-summary" type="button">{{ __('Debit Note Summary') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bill-attechment-tab" data-bs-toggle="pill"
                            data-bs-target="#bill-attechment" type="button">{{ __('Attachment') }}</button>
                    </li>
                    @stack('add_recurring_tab')
                </ul>
            </div>
    @endif
    @if (\Auth::user()->type == 'company')
        @if ($bill->status != 0)
            <div class="col-md-6 d-flex align-items-center justify-content-between justify-content-md-end">
                @if ($bill->status != 4)
                    <div class="me-2 all-button-box">
                        <a href="#" data-url="{{ route('bill.debit.note', $bill->id) }}" data-ajax-popup="true"
                            data-title="{{ __('Apply Debit Note') }}" class="btn btn-sm btn-primary">
                            {{ __('Apply Debit Note') }}
                        </a>
                    </div>
                @endif
                <div class="me-2 all-button-box">
                    <a href="{{ route('bill.resent', $bill->id) }}" class="btn btn-sm btn-primary">
                        {{ __('Resend Bill') }}
                    </a>
                </div>
                <div class="all-button-box">
                    <a href="{{ route('bill.pdf', Crypt::encrypt($bill->id)) }}" target="_blank"
                        class="btn btn-sm btn-primary">
                        {{ __('Download') }}
                    </a>
                </div>
            </div>
        @endif
    @endif

    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade active show" id="bill" role="tabpanel"
                    aria-labelledby="pills-user-tab-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="invoice">
                                <div class="invoice-print">
                                    <div
                                        class="d-flex flex-wrap align-items-center justify-content-between row-gap invoice-title border-1 border-bottom  pb-3 mb-3">
                                        <div>
                                            <h2 class="h3 mb-0">{{ __('Bill') }}</h2>
                                        </div>
                                        <div>
                                            <div class="d-flex invoice-wrp flex-wrap align-items-center gap-md-2 gap-1">
                                                <div
                                                    class="d-flex invoice-date flex-wrap align-items-center gap-md-3 gap-1">
                                                    <p class="mb-0"><strong>{{ __('Bill Date') }} :</strong>
                                                        {{ company_date_formate($bill->bill_date) }}</p>
                                                    <p class="mb-0"><strong>{{ __('Due Date') }} :</strong>
                                                        {{ company_date_formate($bill->due_date) }}</p>
                                                </div>
                                                <h3 class="invoice-number mb-0">
                                                    {{ Workdo\Account\Entities\Bill::billNumberFormat($bill->bill_id) }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-sm-4 p-3 invoice-billed">
                                        <div class="row row-gap">
                                            <div class="col-lg-4 col-sm-6">
                                                <div class="invoice-billed-inner">
                                                    <p class="mb-3">
                                                        <strong class="h5 mb-1">{{ __('Name ') }} :
                                                        </strong>{{ !empty($vendor->name) ? $vendor->name : '' }}
                                                    </p>
                                                    <div class="billed-content-top">

                                                        <div class="invoice-billed-content">
                                                            @if (!empty($vendor->billing_name))
                                                                <p class="mb-2"><strong
                                                                        class="h5 mb-1 d-block">{{ __('Billed To') }}
                                                                        :</strong>
                                                                    <span class="text-muted d-block"
                                                                        style="max-width:80%">
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
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="billed-content-bottom">
                                                        @if (module_is_active('Signature'))
                                                            <p>
                                                                @if ($bill->company_signature != '')
                                                                    <div class="mb-2">
                                                                        <img width="100px"
                                                                            src="{{ $bill->company_signature }}">
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
                                                    <p class="mb-3">
                                                        <strong class="h5 mb-1">{{ __('Email ') }} :
                                                        </strong>{{ !empty($vendor->email) ? $vendor->email : '' }}
                                                    </p>
                                                    <div class="billed-content-top">

                                                        <div class="invoice-billed-content">
                                                            @if (company_setting('bill_shipping_display') == 'on')
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
                                                            @endif
                                                        </div>

                                                    </div>
                                                    <div class="billed-content-bottom">
                                                        @if (module_is_active('Signature'))
                                                            <div class="vendor-signature-content">
                                                                <p>
                                                                    @if ($bill->vendor_signature != '')
                                                                        <div class="mb-2">
                                                                            <img width="100px"
                                                                                src="{{ $bill->vendor_signature }}">
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
                                                <strong class="h5 d-block mb-2">{{ __('Status') }} :</strong>
                                                @if ($bill->status == 0)
                                                    <span
                                                        class="badge fix_badge f-12 p-2 d-inline-block bg-info">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                @elseif($bill->status == 1)
                                                    <span
                                                        class="badge fix_badge f-12 p-2 d-inline-block bg-primary">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                @elseif($bill->status == 2)
                                                    <span
                                                        class="badge fix_badge f-12 p-2 d-inline-block bg-secondary">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                @elseif($bill->status == 3)
                                                    <span
                                                        class="badge fix_badge f-12 p-2 d-inline-block bg-warning">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                @elseif($bill->status == 4)
                                                    <span
                                                        class="badge fix_badge f-12 p-2 d-inline-block bg-success">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                @endif
                                            </div>
                                            @if (!empty($company_settings['bill_qr_display']) && $company_settings['bill_qr_display'] == 'on')
                                                <div class="col-lg-2 col-sm-6">
                                                    <div class="float-sm-end qr-code">
                                                        <div class="col">
                                                            <div class="float-sm-end">
                                                                {!! DNS2D::getBarcodeHTML(
                                                                    route('pay.billpay', \Illuminate\Support\Facades\Crypt::encrypt($bill->id)),
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

                                    @if (!empty($customFields) && count($bill->customField) > 0)
                                        <div class="px-4 mt-3">
                                            <div class="row row-gap">
                                                @foreach ($customFields as $field)
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <strong class="d-block mb-1">{{ $field->name }} </strong>
                                                        @if ($field->type == 'attachment')
                                                            <a href="{{ get_file($bill->customField[$field->id]) }}"
                                                                target="_blank">
                                                                <img src=" {{ get_file($bill->customField[$field->id]) }} "
                                                                    class="wid-120 rounded">
                                                            </a>
                                                        @else
                                                            <p class="mb-0">
                                                                {{ !empty($bill->customField[$field->id]) ? $bill->customField[$field->id] : '-' }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="invoice-summary mt-3">
                                        <div class="invoice-title border-1 border-bottom mb-3 pb-2">
                                            <h3 class="h4 mb-0">{{ __('Item Summary') }}</h3>
                                            <small class="mb-2">{{ __('All items here cannot be deleted.') }}</small>
                                        </div>
                                        <div class="mt-2 table-responsive">
                                            <table class="table mb-0 table-striped">
                                                <tr>
                                                    <th class="text-white bg-primary text-uppercase" data-width="40">#
                                                    </th>
                                                    @if ($bill->bill_module == 'account' || $bill->bill_module == '')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Item Type') }}</th>
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Item') }}</th>
                                                    @elseif($bill->bill_module == 'taskly')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Project') }}</th>
                                                    @endif
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Quantity') }}
                                                    </th>
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Rate') }}
                                                    </th>
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Discount') }}
                                                    </th>
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Tax') }}
                                                    </th>
                                                    @if ($bill->bill_module == 'account' || $bill->bill_module == '')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Chart Of Account') }}</th>
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Account Amount') }}</th>
                                                    @endif
                                                    <th class="text-white bg-primary text-uppercase">
                                                        {{ __('Description') }}</th>
                                                    <th class="text-right text-white bg-primary text-uppercase"
                                                        width="12%">{{ __('Price') }}<br>
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
                                                            $taxes = Workdo\Account\Entities\AccountUtility::tax(
                                                                $iteam->tax,
                                                            );
                                                            $totalQuantity += $iteam->quantity;
                                                            $totalRate += $iteam->price;
                                                            $totalDiscount += $iteam->discount;
                                                            foreach ($taxes as $taxe) {
                                                                $taxDataPrice = Workdo\Account\Entities\AccountUtility::taxRate(
                                                                    $taxe['rate'],
                                                                    $iteam->price,
                                                                    $iteam->quantity,
                                                                    $iteam->discount,
                                                                );
                                                                if (array_key_exists($taxe['name'], $taxesData)) {
                                                                    $taxesData[$taxe['name']] =
                                                                        $taxesData[$taxe['name']] + $taxDataPrice;
                                                                } else {
                                                                    $taxesData[$taxe['name']] = $taxDataPrice;
                                                                }
                                                            }
                                                        @endphp
                                                    @endif
                                                    <tr>
                                                        <td>{{ $key + 1 }}</td>
                                                        @if ($bill->bill_module == 'account' || $bill->bill_module == '')
                                                            <td>{{ !empty($iteam->product_type) ? Str::ucfirst($iteam->product_type) : '--' }}
                                                            </td>
                                                            <td>{{ !empty($iteam->product()) ? $iteam->product()->name : '' }}
                                                            </td>
                                                        @elseif($bill->bill_module == 'taskly')
                                                            <td>{{ !empty($iteam->product()) ? $iteam->product()->title : '' }}
                                                            </td>
                                                        @endif
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
                                                                            $taxPrice = Workdo\Account\Entities\AccountUtility::taxRate(
                                                                                $tax['rate'],
                                                                                $iteam->price,
                                                                                $iteam->quantity,
                                                                                $iteam->discount,
                                                                            );
                                                                            $totalTaxPrice += $taxPrice;
                                                                            $data += $taxPrice;
                                                                        @endphp
                                                                        <tr>
                                                                            <td>{{ $tax['name'] . ' (' . $tax['rate'] . '%)' }}
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

                                                        @if ($bill->bill_module == 'account' || $bill->bill_module == '')
                                                            @php
                                                                $chartAccount = \Workdo\Account\Entities\ChartOfAccount::find(
                                                                    $iteam->chart_account_id,
                                                                );
                                                            @endphp
                                                            <td>{{ !empty($chartAccount) ? $chartAccount->name : '-' }}
                                                            </td>
                                                            <td>{{ currency_format_with_sym($iteam->amount) }}</td>
                                                        @endif
                                                        <td style="white-space: break-spaces;">
                                                            {{ !empty($iteam->description) ? $iteam->description : '-' }}</td>
                                                        @php
                                                            $tr_tex =
                                                                array_key_exists($key, $TaxPrice_array) == true
                                                                    ? $TaxPrice_array[$key]
                                                                    : 0;
                                                        @endphp
                                                        <td class="">
                                                            {{ currency_format_with_sym($iteam->price * $iteam->quantity - $iteam->discount + $tr_tex) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tfoot>
                                                    <tr>
                                                        <td></td>
                                                        @if ($bill->bill_module == 'account' || $bill->bill_module == '')
                                                            <td></td>
                                                        @endif
                                                        <td class="bg-color"><b>{{ __('Total') }}</b></td>
                                                        <td class="bg-color"><b>{{ $totalQuantity }}</b></td>
                                                        <td class="bg-color">
                                                            <b>{{ currency_format_with_sym($totalRate) }}</b></td>
                                                        <td class="bg-color">
                                                            <b>{{ currency_format_with_sym($totalDiscount) }}</b></td>
                                                        <td class="bg-color">
                                                            <b>{{ currency_format_with_sym($totalTaxPrice) }}</b></td>
                                                        @if ($bill->bill_module == 'account' || $bill->bill_module == '')
                                                            <td class="bg-color"></td>
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($bill->getAccountTotal()) }}</b>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                    @php
                                                        $colspan = 6;
                                                        if (
                                                            $bill->bill_module == 'account' ||
                                                            $bill->bill_module == ''
                                                        ) {
                                                            $colspan = 9;
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Sub Total') }}</td>
                                                        <td class="text-right">
                                                            <b>{{ currency_format_with_sym($bill->getSubTotal()) }}</b>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Discount') }}</td>
                                                        <td class="text-right">
                                                            <b>{{ currency_format_with_sym($bill->getTotalDiscount()) }}</b>
                                                        </td>
                                                    </tr>
                                                    @if (!empty($taxesData))
                                                        @foreach ($taxesData as $taxName => $taxPrice)
                                                            <tr>
                                                                <td colspan="{{ $colspan }}"></td>
                                                                <td class="text-right">{{ $taxName }}</td>
                                                                <td class="text-right">
                                                                    <b>{{ currency_format_with_sym($taxPrice) }}</b>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right blue-text">{{ __('Total') }}</td>
                                                        <td class="text-right blue-text">
                                                            <b>{{ currency_format_with_sym($bill->getTotal()) }}</b>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Paid') }}</td>
                                                        <td class="text-right">
                                                            <b>{{ currency_format_with_sym($bill->getTotal() - $bill->getDue() - $bill->billTotalDebitNote()) }}</b>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Debit note Applied') }}</td>
                                                        <td class="text-right">
                                                            <b>{{ currency_format_with_sym($bill->billTotalDebitNote()) }}</b>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Debit note issued') }}</td>
                                                        <td class="text-right">
                                                            <b>{{ currency_format_with_sym($bill->billTotalCustomerDebitNote()) }}</b>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Due') }}</td>
                                                        <td class="text-right">
                                                            <b>{{ currency_format_with_sym($bill->getDue()) }}</b>
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
                <div class="tab-pane fade" id="payment-summary" role="tabpanel" aria-labelledby="pills-user-tab-2">
                    <div class="card">
                        <div class="card-body table-border-style">
                            <h5 class="mb-5 d-inline-block">{{ __('Payment Summary') }}</h5>
                            <div class="table-responsive">
                                <table class="table mb-0 pc-dt-simple" id="invoice-payment">
                                    <thead>
                                        <tr>
                                            <th class="text-dark">{{ __('Payment Receipt') }}</th>
                                            <th class="text-dark">{{ __('Date') }}</th>
                                            <th class="text-dark">{{ __('Amount') }}</th>
                                            <th class="text-dark">{{ __('Account') }}</th>
                                            <th class="text-dark">{{ __('Reference') }}</th>
                                            <th class="text-dark">{{ __('Description') }}</th>
                                            @permission('bill payment delete')
                                                <th class="text-dark">{{ __('Action') }}</th>
                                            @endpermission
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bill->payments as $key =>$payment)
                                            <tr>
                                                <td>
                                                    @if (!empty($payment->add_receipt))
                                                        <div class="action-btn  me-2">
                                                            <a href="{{ get_file($payment->add_receipt) }}"
                                                                download=""
                                                                class=" bg-primary mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" title="{{ __('Download') }}"
                                                                target="_blank">
                                                                <i class="text-white ti ti-download"></i>
                                                            </a>
                                                        </div>
                                                        <div class="action-btn">
                                                            <a href="{{ get_file($payment->add_receipt) }}"
                                                                class="bg-secondary mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" title="{{ __('Show') }}"
                                                                target="_blank">
                                                                <i class="text-white ti ti-crosshair"></i>
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
                                                <td>{{ isset($payment->description) ? $payment->description : '-' }}</td>
                                                <td class="text-dark">
                                                    @permission('bill payment delete')
                                                        <div class="action-btn">
                                                            {{ Form::open(['route' => ['bill.payment.destroy', $bill->id, $payment->id], 'class' => 'm-0']) }}
                                                            <a href="#"
                                                                class="bg-danger mx-3 btn btn-sm align-items-center bs-pass-para show_confirm"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $payment->id }}">
                                                                <i class="text-white ti ti-trash"></i>
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
                <div class="tab-pane fade" id="debit-summary" role="tabpanel" aria-labelledby="pills-user-tab-3">
                    <div class="card">
                        <div class="card-body table-border-style">
                            <h5 class="mb-5 d-inline-block">{{ __('Debit Note Summary') }}</h5>
                            <div class="table-responsive">
                                <table class="table mb-0 pc-dt-simple" id="debit-note">
                                    <thead>
                                        <tr>
                                            <th class="text-dark">{{ __('Debit Note') }}</th>
                                            <th class="text-dark">{{ __('Date') }}</th>
                                            <th class="text-dark">{{ __('Amount') }}</th>
                                            <th class="text-dark">{{ __('Description') }}</th>
                                            @if (Laratrust::hasPermission('debitnote edit') || Laratrust::hasPermission('debitnote delete'))
                                                <th class="text-dark">{{ __('Action') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    @forelse($bill->debitNote as $key =>$debitNote)
                                        <tr>
                                            <td><a href="#" class="btn btn-outline-primary">{{ !empty($debitNote->debitNote) ? \Workdo\Account\Entities\CustomerDebitNotes::debitNumberFormat($debitNote->debitNote->debit_id) : '-' }}</a></td>
                                            <td>{{ company_date_formate($debitNote->date) }}</td>
                                            <td>{{ currency_format_with_sym($debitNote->amount) }}</td>
                                            <td>{{ isset($debitNote->description) ? $debitNote->description : '-' }}</td>
                                            <td>
                                                @permission('debitnote edit')
                                                    <div class="action-btn me-2">
                                                        <a data-url="{{ route('bill.edit.debit.note', [$debitNote->bill, $debitNote->id]) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Add Debit Note') }}"
                                                            href="#" class="bg-info mx-3 btn btn-sm align-items-center"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="text-white ti ti-pencil"></i>
                                                        </a>
                                                    </div>
                                                @endpermission
                                                @permission('debitnote delete')
                                                    <div class="action-btn">
                                                        {{ Form::open(['route' => ['bill.delete.debit.note', $debitNote->bill, $debitNote->id], 'class' => 'm-0']) }}
                                                        @method('DELETE')
                                                        <a href="#"
                                                            class="bg-danger mx-3 btn btn-sm align-items-center bs-pass-para show_confirm"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="Delete" aria-label="Delete"
                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="delete-form-{{ $debitNote->id }}">
                                                            <i class="text-white ti ti-trash"></i>
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
                <div class="tab-pane fade" id="bill-attechment" role="tabpanel" aria-labelledby="pills-user-tab-4">
                    <div class="row">
                        <h5 class="my-3 d-inline-block">{{ __('Attachments') }}</h5>
                        <div class="col-3">
                            <div class="border card border-primary">
                                <div class="card-body table-border-style">
                                    <div class="col-md-12 dropzone browse-file" id="dropzonewidget">
                                        <div class="my-5 dz-message" data-dz-message>
                                            <span>{{ __('Drop files here to upload') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="border card border-primary">
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
                                            @forelse($bill_attachment as $key =>$attachment)
                                                <td>{{ ++$key }}</td>
                                                <td>{{ $attachment->file_name }}</td>
                                                <td>{{ $attachment->file_size }}</td>
                                                <td>{{ company_date_formate($attachment->created_at) }}</td>
                                                <td>
                                                    <div class="action-btn  me-2">
                                                        <a href="{{ url($attachment->file_path) }}"
                                                            class="mx-3 btn btn-sm align-items-center bg-primary"
                                                            data-bs-toggle="tooltip" title="{{ __('Download') }}"
                                                            target="_blank" download>
                                                            <i class="text-white ti ti-download"></i>
                                                        </a>
                                                    </div>
                                                    <div class="action-btn">
                                                        {{ Form::open(['route' => ['bill.attachment.destroy', $attachment->id], 'class' => 'm-0']) }}
                                                        @method('DELETE')
                                                        <a href="#"
                                                            class="bg-danger mx-3 btn btn-sm align-items-center bs-pass-para show_confirm"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="Delete" aria-label="Delete"
                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="delete-form-{{ $attachment->id }}">
                                                            <i class="text-white ti ti-trash"></i>
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
                @stack('add_recurring_pills')
            </div>
        </div>
    </div>

    @if (\Auth::user()->type != 'company')
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
                            @forelse($bill->payments as $key =>$payment)
                                <tr>
                                    <td>
                                        @if (!empty($payment->add_receipt))
                                            <div class="action-btn  me-2">
                                                <a href="{{ get_file($payment->add_receipt) }}" download=""
                                                    class=" bg-primary mx-3 btn btn-sm align-items-center"
                                                    data-bs-toggle="tooltip" title="{{ __('Download') }}"
                                                    target="_blank">
                                                    <i class="text-white ti ti-download"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn">
                                                <a href="{{ get_file($payment->add_receipt) }}"
                                                    class="bg-secondary mx-3 btn btn-sm align-items-center"
                                                    data-bs-toggle="tooltip" title="{{ __('Show') }}"
                                                    target="_blank">
                                                    <i class="text-white ti ti-crosshair"></i>
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
