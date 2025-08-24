@extends('layouts.invoicepayheader')
@section('page-title')
    {{ __('Purchase Detail') }}
@endsection
@push('css')
    @if (module_is_active('Signature'))
        <link rel="stylesheet" href="{{ asset('packages/workdo/Signature/src/Resources/assets/css/custom.css') }}">
    @endif
@endpush
@php
    $company_settings = getCompanyAllSetting($purchase->created_by, $purchase->workspace);
@endphp
@section('action-btn')
    <div class="row justify-content-center align-items-center">
        <div class="col-12 d-flex align-items-center justify-content-between justify-content-md-end">
            <div class="all-button-box mr-3 d-flex">
                <a href="{{ route('purchases.pdf', \Crypt::encrypt($purchase->id)) }}" target="_blank"
                    class="btn btn-sm btn-primary btn-icon me-2" data-bs-toggle="tooltip" title="{{ __('Print') }}">
                    <span class="btn-inner--icon text-white"><i class="ti ti-printer"></i>{{ __(' Print') }}</span>
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
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
                                            <p class="mb-0"><strong>{{ __('Purchase Date') }}
                                                    :</strong>{{ company_date_formate($purchase->purchase_date, $purchase->created_by, $purchase->workspace) }}
                                            </p>
                                        </div>
                                        <h3 class="invoice-number mb-0">
                                            {{ \App\Models\Purchase::purchaseNumberFormat($purchase->purchase_id, $purchase->created_by, $purchase->workspace) }}
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
                                                <div class="invoice-billed-content">
                                                    @if (!empty($vendor->billing_name) && !empty($vendor->billing_address) && !empty($vendor->billing_zip))
                                                        <p class="mb-2"><strong
                                                                class="h5 mb-1 d-block">{{ __('Billed To') }}
                                                                :</strong>
                                                            {{ !empty($vendor->billing_name) ? $vendor->billing_name : '' }}
                                                            {{ !empty($vendor->billing_address) ? $vendor->billing_address : '' }}
                                                            {{ !empty($vendor->billing_city) ? $vendor->billing_city . ' ,' : '' }}
                                                            {{ !empty($vendor->billing_state) ? $vendor->billing_state . ' ,' : '' }}
                                                            {{ !empty($vendor->billing_zip) ? $vendor->billing_zip : '' }}
                                                            {{ !empty($vendor->billing_country) ? $vendor->billing_country : '' }}
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
                                                <div class="invoice-billed-content">
                                                    @if (!empty($company_settings['purchase_shipping_display']) && $company_settings['purchase_shipping_display'] == 'on')
                                                        @if (!empty($vendor->shipping_name) && !empty($vendor->shipping_address) && !empty($vendor->shipping_zip))
                                                            <p class="mb-2">
                                                                <strong class="h5 mb-1 d-block">{{ __('Shipped To') }}
                                                                    :</strong>
                                                                <span class="text-muted d-block" style="max-width:80%">
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
                                                    @endif
                                                </div>
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
                                        <strong>{{ __('Status') }} :</strong><br>
                                        @if ($purchase->status == 0)
                                            <span
                                                class="badge fix_badge f-12 p-2 d-inline-block bg-info">{{ __(Workdo\Account\Entities\Bill::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 1)
                                            <span
                                                class="badge fix_badge f-12 p-2 d-inline-block bg-primary">{{ __(Workdo\Account\Entities\Bill::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 2)
                                            <span
                                                class="badge fix_badge f-12 p-2 d-inline-block bg-secondary">{{ __(Workdo\Account\Entities\Bill::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 3)
                                            <span
                                                class="badge fix_badge f-12 p-2 d-inline-block bg-warning">{{ __(Workdo\Account\Entities\Bill::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 4)
                                            <span
                                                class="badge fix_badge f-12 p-2 d-inline-block bg-success">{{ __(Workdo\Account\Entities\Bill::$statues[$purchase->status]) }}</span>
                                        @endif
                                    </div>
                                    @if (!empty($company_settings['purchase_qr_display']) && $company_settings['purchase_qr_display'] == 'on')
                                        <div class="col-lg-2 col-sm-6">
                                            <div class="float-sm-end qr-code">
                                                <div class="col">
                                                    <div class="float-sm-end">
                                                        <p> {!! DNS2D::getBarcodeHTML(
                                                            route('purchases.link.copy', \Illuminate\Support\Facades\Crypt::encrypt($purchase->id)),
                                                            'QRCODE',
                                                            2,
                                                            2,
                                                        ) !!}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12 invoice-summary mt-3">
                                    <div class="invoice-title border-1 border-bottom mb-3 pb-2">
                                        <h3 class="h4 mb-0">{{ __('Item Summary') }}</h3>
                                        <small>{{ __('All items here cannot be deleted.') }}</small>
                                    </div>
                                    <div class="table-responsive mt-2">
                                        <table class="table mb-0 table-striped">
                                            <tr>
                                                <th class="text-white bg-primary text-uppercase" data-width="40">#</th>
                                                <th class="text-white bg-primary text-uppercase">{{ __('Item Type') }}</th>
                                                <th class="text-white bg-primary text-uppercase">{{ __('Item') }}</th>
                                                <th class="text-white bg-primary text-uppercase">{{ __('Quantity') }}</th>
                                                <th class="text-white bg-primary text-uppercase">{{ __('Rate') }}</th>
                                                <th class="text-white bg-primary text-uppercase">{{ __('Tax') }}</th>
                                                <th class="text-white bg-primary text-uppercase">{{ __('Discount') }} </th>
                                                <th class="text-white bg-primary text-uppercase">{{ __('Description') }}
                                                </th>
                                                <th class="text-white bg-primary text-uppercase" width="12%">
                                                    {{ __('Price') }}<br>
                                                    <small class="text-danger ">{{ __('after discount & tax') }}</small>
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
                                                    <td>{{ !empty($iteam->product) ? $iteam->product->name : '' }}</td>
                                                    <td>{{ $iteam->quantity }}</td>
                                                    <td>{{ currency_format_with_sym($iteam->price, $purchase->created_by, $purchase->workspace) }}
                                                    </td>
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
                                                                        );
                                                                        $totalTaxPrice += $taxPrice;
                                                                        $data += $taxPrice;
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ $tax->name . ' (' . $tax->rate . '%)' }}</td>
                                                                        <td>{{ currency_format_with_sym($taxPrice, $purchase->created_by, $purchase->workspace) }}
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
                                                    <td>
                                                        {{ currency_format_with_sym($iteam->discount, $purchase->created_by, $purchase->workspace) }}

                                                    </td>
                                                    <td>{{ !empty($iteam->description) ? $iteam->description : '-' }}</td>
                                                    @php
                                                        $tr_tex =
                                                            array_key_exists($key, $TaxPrice_array) == true
                                                                ? $TaxPrice_array[$key]
                                                                : 0;
                                                    @endphp
                                                    <td class="text-end">
                                                        {{ currency_format_with_sym($iteam->price * $iteam->quantity - $iteam->discount + $tr_tex, $purchase->created_by, $purchase->workspace) }}
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
                                                        <b>{{ currency_format_with_sym($totalRate, $purchase->created_by, $purchase->workspace) }}</b>
                                                    </td>
                                                    <td class="bg-color">
                                                        <b>{{ currency_format_with_sym($totalTaxPrice, $purchase->created_by, $purchase->workspace) }}</b>
                                                    </td>
                                                    <td class="bg-color">
                                                        <b>{{ currency_format_with_sym($totalDiscount, $purchase->created_by, $purchase->workspace) }}</b>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="text-end"><b>{{ __('Sub Total') }}</b></td>
                                                    <td class="text-end">
                                                        {{ currency_format_with_sym($purchase->getSubTotal(), $purchase->created_by, $purchase->workspace) }}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="text-end"><b>{{ __('Discount') }}</b></td>
                                                    <td class="text-end">
                                                        {{ currency_format_with_sym($purchase->getTotalDiscount(), $purchase->created_by, $purchase->workspace) }}
                                                    </td>
                                                </tr>

                                                @if (!empty($taxesData))
                                                    @foreach ($taxesData as $taxName => $taxPrice)
                                                        <tr>
                                                            <td colspan="7"></td>
                                                            <td class="text-end"><b>{{ $taxName }}</b></td>
                                                            <td class="text-end">
                                                                {{ currency_format_with_sym($taxPrice, $purchase->created_by, $purchase->workspace) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="blue-text text-end"><b>{{ __('Total') }}</b></td>
                                                    <td class="blue-text text-end">
                                                        {{ currency_format_with_sym($purchase->getTotal(), $purchase->created_by, $purchase->workspace) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="text-end"><b>{{ __('Paid') }}</b></td>
                                                    <td class="text-end">
                                                        {{ currency_format_with_sym($purchase->getTotal() - $purchase->getDue(), $purchase->created_by, $purchase->workspace) }}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="text-end"><b>{{ __('Due') }}</b></td>
                                                    <td class="text-end">
                                                        {{ currency_format_with_sym($purchase->getDue(), $purchase->created_by, $purchase->workspace) }}
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
    </div>

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
                                            <a href="{{ get_file($payment->add_receipt) }}" download=""
                                                class="mx-3 btn btn-sm align-items-center bg-primary"
                                                data-bs-toggle="tooltip" title="{{ __('Download') }}"
                                                target="_blank">
                                                <i class="ti ti-download text-white"></i>
                                            </a>
                                        </div>
                                        <div class="action-btn">
                                            <a href="{{ get_file($payment->add_receipt) }}"
                                                class="mx-3 btn btn-sm align-items-center bg-secondary"
                                                data-bs-toggle="tooltip" title="{{ __('Show') }}"
                                                target="_blank">
                                                <i class="ti ti-crosshair text-white"></i>
                                            </a>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ company_date_formate($payment->date, $purchase->created_by, $purchase->workspace) }}</td>
                                <td>{{ currency_format_with_sym($payment->amount, $purchase->created_by, $purchase->workspace) }}</td>
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

@endsection
