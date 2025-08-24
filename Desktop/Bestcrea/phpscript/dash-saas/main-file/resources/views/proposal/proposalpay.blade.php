@extends('layouts.invoicepayheader')
@section('page-title')
    {{ __('Proposal Detail') }}
@endsection
@push('script-page')
    <script>
        $(document).on('change', '.status_change', function() {
            var status = this.value;
            var url = $(this).data('url');
            $.ajax({
                url: url + '?status=' + status,
                type: 'GET',
                cache: false,
                success: function(data) {},
            });
        });
    </script>
@endpush
@section('action-btn')
    @if (\Auth::check() && isset(\Auth::user()->type) && \Auth::user()->type == 'company')
        @if ($proposal->status != 0)
            <div class="row justify-content-between align-items-center ">
                <div class="col-10 offset-1 d-flex align-items-center justify-content-between justify-content-md-end">
                    <div class="all-button-box">
                        <a href="{{ route('proposal.pdf', Crypt::encrypt($proposal->id)) }}" class="btn btn-sm btn-primary"
                            target="_blank"><i class="ti ti-printer"></i>{{ __('Print') }}</a>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="row justify-content-between align-items-center ">
            <div class="col-10  offset-1 d-flex align-items-center justify-content-between justify-content-md-end">
                <div class="all-button-box">
                    <a href="{{ route('proposal.pdf', Crypt::encrypt($proposal->id)) }}" class="btn btn-sm btn-primary"
                        target="_blank"><i class="ti ti-printer"></i>{{ __('Print') }}</a>
                </div>
            </div>
        </div>
    @endif
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
                                    <h2 class="h3 mb-0">{{ __('Proposal') }}</h2>
                                </div>
                                <div class="col-sm-8  col-12">
                                    <div
                                        class="d-flex invoice-wrp flex-wrap align-items-center gap-md-2 gap-1 justify-content-end">
                                        <div
                                            class="d-flex invoice-date flex-wrap align-items-center justify-content-end gap-md-3 gap-1">
                                            <p class="mb-0"><strong>{{ __('Issue Date') }} :</strong>
                                                {{ company_date_formate($proposal->issue_date, $proposal->created_by, $proposal->workspace) }}
                                            </p>
                                        </div>
                                        <h3 class="invoice-number mb-0">
                                            {{ \App\Models\Proposal::proposalNumberFormat($proposal->proposal_id, $proposal->created_by, $proposal->workspace) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>

                            <div class="p-sm-4 p-3 invoice-billed">
                                <div class="row row-gap">
                                    <div class="col-lg-4 col-sm-6">
                                        <p class="mb-3">
                                            <strong class="h5 mb-1">{{ __('Name ') }} :
                                            </strong>{{ !empty($customer->name) ? $customer->name : '' }}
                                        </p>
                                        @if (!empty($customer->billing_name) && !empty($customer->billing_address) && !empty($customer->billing_zip))
                                            <p class="mb-2"><strong class="h5 mb-1 d-block">{{ __('Billed To') }}
                                                    :</strong>
                                                <span class="text-muted d-block" style="max-width:80%">
                                                    {{ !empty($customer->billing_name) ? $customer->billing_name : '' }}
                                                    {{ !empty($customer->billing_address) ? $customer->billing_address : '' }}
                                                    {{ !empty($customer->billing_city) ? $customer->billing_city . ' ,' : '' }}
                                                    {{ !empty($customer->billing_state) ? $customer->billing_state . ' ,' : '' }}
                                                    {{ !empty($customer->billing_zip) ? $customer->billing_zip : '' }}
                                                    {{ !empty($customer->billing_country) ? $customer->billing_country : '' }}
                                                </span>
                                            </p>
                                            <p class="mb-1 text-dark">
                                                {{ !empty($customer->billing_phone) ? $customer->billing_phone : '' }}
                                            </p>
                                            <p class="mb-0">
                                                <strong>{{ __('Tax Number ') }} :
                                                </strong>{{ !empty($customer->tax_number) ? $customer->tax_number : '' }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="col-lg-4 col-sm-6">
                                        <p class="mb-3">
                                            <strong class="h5 mb-1">{{ __('Email ') }} :
                                            </strong>{{ !empty($customer->email) ? $customer->email : '' }}
                                        </p>
                                        @if (!empty($company_settings['proposal_shipping_display']) && $company_settings['proposal_shipping_display'] == 'on')
                                            @if (!empty($customer->shipping_name) && !empty($customer->shipping_address) && !empty($customer->shipping_zip))
                                                <p class="mb-2">
                                                    <strong class="h5 mb-1 d-block">{{ __('Shipped To') }} :</strong>
                                                    <span class="text-muted d-block" style="max-width:80%">
                                                        {{ !empty($customer->shipping_name) ? $customer->shipping_name : '' }}
                                                        {{ !empty($customer->shipping_address) ? $customer->shipping_address : '' }}
                                                        {{ !empty($customer->shipping_city) ? $customer->shipping_city . ' ,' : '' }}
                                                        {{ !empty($customer->shipping_state) ? $customer->shipping_state . ' ,' : '' }}
                                                        {{ !empty($customer->shipping_zip) ? $customer->shipping_zip : '' }}
                                                        {{ !empty($customer->shipping_country) ? $customer->shipping_country : '' }}
                                                    </span>
                                                </p>
                                                <p class="mb-1 text-dark">
                                                    {{ !empty($customer->shipping_phone) ? $customer->shipping_phone : '' }}
                                                </p>
                                                <p class="mb-0">
                                                    <strong>{{ __('Tax Number ') }} :
                                                    </strong>{{ !empty($customer->tax_number) ? $customer->tax_number : '' }}
                                                </p>
                                            @endif
                                        @endif
                                    </div>

                                    <div class="col-lg-2 col-sm-6">
                                        <strong class="h5 d-block mb-2">{{ __('Status') }} :</strong>
                                        @if ($proposal->status == 0)
                                            <span
                                                class="badge bg-primary p-2 px-3">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                        @elseif($proposal->status == 1)
                                            <span
                                                class="badge bg-info p-2 px-3">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                        @elseif($proposal->status == 2)
                                            <span
                                                class="badge bg-success p-2 px-3">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                        @elseif($proposal->status == 3)
                                            <span
                                                class="badge bg-warning p-2 px-3">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                        @elseif($proposal->status == 4)
                                            <span
                                                class="badge bg-danger p-2 px-3">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                        @endif
                                    </div>

                                    <div class="col-lg-2 col-sm-6">
                                        <div class="float-sm-end qr-code">
                                            @if (!empty($company_settings['proposal_qr_display']) && $company_settings['proposal_qr_display'] == 'on')
                                                <div class="col">
                                                    <div class="float-end">
                                                        {!! DNS2D::getBarcodeHTML(
                                                            route('pay.proposalpay', \Illuminate\Support\Facades\Crypt::encrypt($proposal->id)),
                                                            'QRCODE',
                                                            2,
                                                            2,
                                                        ) !!}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>


                            @if (!empty($customFields) && count($proposal->customField) > 0)
                                <div class="px-4 mt-3">
                                    <div class="row row-gap">
                                        @foreach ($customFields as $field)
                                            <div class="col-xxl-3 col-sm-6">
                                                <strong class="d-block mb-1">{{ $field->name }} </strong>

                                                @if ($field->type == 'attachment')
                                                    <a href="{{ get_file($proposal->customField[$field->id]) }}"
                                                        target="_blank">
                                                        <img src=" {{ get_file($proposal->customField[$field->id]) }} "
                                                            class="wid-120 rounded">
                                                    </a>
                                                @else
                                                    <p class="mb-0">
                                                        {{ !empty($proposal->customField[$field->id]) ? $proposal->customField[$field->id] : '-' }}
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
                                    <small>{{ __('All items here cannot be deleted.') }}</small>
                                </div>

                                <div class="table-responsive mt-2">
                                    <table class="table mb-0 table-striped">
                                        <tr>
                                            <th class="text-white bg-primary text-uppercase" data-width="40">#</th>
                                            @if ($proposal->proposal_module == 'account' || $proposal->proposal_module == 'cmms')
                                                <th class="text-white bg-primary text-uppercase">{{ __('Item Type') }}
                                                </th>
                                                <th class="text-white bg-primary text-uppercase">{{ __('Item') }}
                                                </th>
                                            @elseif($proposal->proposal_module == 'taskly')
                                                <th class="text-white bg-primary text-uppercase">{{ __('Project') }}
                                                </th>
                                            @endif
                                            @if ($proposal->proposal_module == 'account' || $proposal->proposal_module == 'cmms')
                                                <th class="text-white bg-primary text-uppercase">{{ __('Quantity') }}
                                                </th>
                                            @endif
                                            <th class="text-white bg-primary text-uppercase">{{ __('Rate') }}</th>
                                            <th class="text-white bg-primary text-uppercase"> {{ __('Discount') }}</th>
                                            <th class="text-white bg-primary text-uppercase">{{ __('Tax') }}</th>
                                            <th class="text-white bg-primary text-uppercase">{{ __('Description') }}
                                            </th>

                                            <th class="text-right text-white bg-primary text-uppercase" width="12%">
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

                                        @foreach ($item as $key => $iteam)
                                            @if (!empty($iteam->tax))
                                                @php
                                                    $taxes = \Workdo\ProductService\Entities\Tax::tax($iteam->tax);
                                                    $totalQuantity += $iteam->quantity;
                                                    $totalRate += $iteam->price;
                                                    $totalDiscount += $iteam->discount;
                                                    foreach ($taxes as $taxe) {
                                                        $taxDataPrice = \App\Models\Proposal::taxRate(
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
                                                @if ($proposal->proposal_module == 'account')
                                                    <td>{{ !empty($iteam->product_type) ? Str::ucfirst($iteam->product_type) : '--' }}
                                                    </td>
                                                    <td>{{ !empty($iteam->product()) ? $iteam->product()->name : '' }}
                                                    </td>
                                                @elseif($proposal->proposal_module == 'taskly')
                                                    {{-- <td>{{ !empty($iteam->product_type) ? Str::ucfirst($iteam->product_type) : '-' }}
                                                    </td> --}}
                                                    <td>{{ !empty($iteam->product()) ? $iteam->product()->title : '--' }}
                                                    </td>
                                                @elseif($proposal->proposal_module == 'cmms')
                                                    <td>{{ !empty($iteam->product_type) ? Str::ucfirst($iteam->product_type) : '--' }}
                                                    </td>
                                                    <td>{{ !empty($iteam->product()) ? $iteam->product()->name : '' }}
                                                    </td>
                                                @endif
                                                @if ($proposal->proposal_module == 'account' || $proposal->proposal_module == 'cmms')
                                                    <td>{{ $iteam->quantity }}</td>
                                                @endif
                                                <td>{{ currency_format_with_sym($iteam->price, $proposal->created_by, $proposal->workspace) }}
                                                </td>
                                                <td>
                                                    {{ currency_format_with_sym($iteam->discount, $proposal->created_by, $proposal->workspace) }}
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
                                                                    $taxPrice = App\Models\Proposal::taxRate(
                                                                        $tax->rate,
                                                                        $iteam->price,
                                                                        $iteam->quantity,
                                                                        $iteam->discount,
                                                                    );

                                                                    $totalTaxPrice += $taxPrice;
                                                                    $data += $taxPrice;
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $tax->name . ' (' . $tax->rate . '%)' }}
                                                                    </td>
                                                                    <td>{{ currency_format_with_sym($taxPrice, $proposal->created_by, $proposal->workspace) }}
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
                                                @php
                                                    $tr_tex =
                                                        array_key_exists($key, $TaxPrice_array) == true
                                                            ? $TaxPrice_array[$key]
                                                            : 0;
                                                @endphp
                                                <td>{{ !empty($iteam->description) ? $iteam->description : '-' }}</td>
                                                <td class="text-center">
                                                    {{ currency_format_with_sym($iteam->price * $iteam->quantity - $iteam->discount + $tr_tex, $proposal->created_by, $proposal->workspace) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                @if ($proposal->proposal_module == 'account' || $proposal->proposal_module == 'cmms')
                                                    <td></td>
                                                @endif
                                                <td class="bg-color"><b>{{ __('Total') }}</b></td>
                                                @if ($proposal->proposal_module == 'account' || $proposal->proposal_module == 'cmms')
                                                    <td class="bg-color"><b>{{ $totalQuantity }}</b></td>
                                                @endif
                                                <td class="bg-color">
                                                    <b>{{ currency_format_with_sym($totalRate, $proposal->created_by, $proposal->workspace) }}</b>
                                                </td>
                                                <td class="bg-color">
                                                    <b>{{ currency_format_with_sym($totalDiscount, $proposal->created_by, $proposal->workspace) }}</b>
                                                </td>
                                                <td class="bg-color">
                                                    <b>{{ currency_format_with_sym($totalTaxPrice, $proposal->created_by, $proposal->workspace) }}</b>
                                                </td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            @php
                                                $colspan = 7;
                                                $customerInvoices = ['taskly', 'account', 'cmms', 'cardealership', 'RestaurantMenu', 'rent' , 'Fleet'];

                                                if (in_array($proposal->invoice_module, $customerInvoices)) {
                                                    $colspan = 7;
                                                }

                                                if ($proposal->proposal_module == 'taskly') {
                                                    $colspan = 5;
                                                }
                                            @endphp
                                            <tr>
                                                <td colspan="{{ $colspan }}"></td>
                                                <td class="text-right"><b>{{ __('Sub Total') }}</b></td>
                                                <td class="text-right">
                                                    {{ currency_format_with_sym($proposal->getSubTotal(), $proposal->created_by, $proposal->workspace) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="{{ $colspan }}"></td>
                                                <td class="text-right"><b>{{ __('Discount') }}</b></td>
                                                <td class="text-right">
                                                    {{ currency_format_with_sym($proposal->getTotalDiscount(), $proposal->created_by, $proposal->workspace) }}
                                                </td>
                                            </tr>
                                            @if (!empty($taxesData))
                                                @foreach ($taxesData as $taxName => $taxPrice)
                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right"><b>{{ $taxName }}</b></td>
                                                        <td class="text-right">
                                                            {{ currency_format_with_sym($taxPrice, $proposal->created_by, $proposal->workspace) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            <tr>
                                                <td colspan="{{ $colspan }}"></td>
                                                <td class="blue-text text-right"><b>{{ __('Total') }}</b></td>
                                                <td class="blue-text text-right">
                                                    {{ currency_format_with_sym($proposal->getTotal(), $proposal->created_by, $proposal->workspace) }}
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

@endsection
