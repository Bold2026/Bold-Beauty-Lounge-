@extends('layouts.invoicepayheader')
@section('page-title')
    {{ __('Invoice Detail') }}
@endsection
@push('css')
    <style>
        #card-element {
            border: 1px solid #a3afbb !important;
            border-radius: 10px !important;
            padding: 10px !important;
        }
    </style>
@endpush
@php
    $company_settings = getCompanyAllSetting($invoice->created_by, $invoice->workspace);
@endphp
@section('action-btn')
    <div class="row justify-content-center align-items-center ">
        <div class="col-12 d-flex align-items-center justify-content-between justify-content-md-end">
            <div class="all-button-box mr-3 d-flex">
                <a href="{{ route('invoice.pdf', \Crypt::encrypt($invoice->id)) }}" target="_blank"
                    class="btn btn-sm btn-primary btn-icon me-2" data-bs-toggle="tooltip" title="{{ __('Print') }}">
                    <span class="btn-inner--icon text-white"><i class="ti ti-printer"></i>{{ __(' Print') }}</span>
                </a>

                @if ($invoice->status != 0 && $invoice->getDue() > 0)
                    <a id="paymentModals" class="btn btn-sm btn-primary">
                        <span class="btn-inner--icon text-white"><i class="ti ti-credit-card"></i></span>
                        <span class="btn-inner--text text-white">{{ __(' Pay Now') }}</span>
                    </a>
                @endif
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
                                    <h2 class="h3 mb-0">{{ __('Invoice') }}</h2>
                                </div>
                                <div class="col-sm-8  col-12">
                                    <div class="d-flex invoice-wrp flex-wrap align-items-center gap-md-2 gap-1 justify-content-end">
                                        <div class="d-flex invoice-date flex-wrap align-items-center justify-content-end gap-md-3 gap-1">
                                            <p class="mb-0"><strong>{{ __('Issue Date') }} :</strong> {{ company_date_formate($invoice->issue_date,$invoice->created_by, $invoice->workspace) }}</p>
                                            <p class="mb-0"><strong>{{ __('Due Date') }} :</strong> {{ company_date_formate($invoice->due_date,$invoice->created_by, $invoice->workspace) }}</p>
                                        </div>
                                        <h3 class="invoice-number mb-0">
                                            {{ \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id,$invoice->created_by, $invoice->workspace) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>

                            <div class="p-sm-4 p-3 invoice-billed">
                                <div class="row row-gap">
                                        @if (
                                            $invoice->invoice_module == 'taskly' ||
                                            $invoice->invoice_module == 'account' ||
                                            $invoice->invoice_module == 'cmms' ||
                                            $invoice->invoice_module == 'cardealership' ||
                                            $invoice->invoice_module == 'musicinstitute' ||
                                            $invoice->invoice_module == 'rent')
                                            <div class="col-lg-4 col-sm-6">
                                                <p class="mb-3">
                                                    <strong class="h5 mb-1">{{ __('Name ') }}:
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
                                                    <strong class="h5 mb-1">{{ __('Email ') }}:
                                                    </strong>{{ !empty($customer->email) ? $customer->email : '' }}
                                                </p>
                                                @if (!empty($company_settings['invoice_shipping_display']) && $company_settings['invoice_shipping_display'] == 'on')
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
                                                            {{ !empty($customer->billing_phone) ? $customer->billing_phone : '' }}
                                                        </p>
                                                        <p class="mb-0">
                                                            <strong>{{ __('Tax Number ') }} :
                                                            </strong>{{ !empty($customer->tax_number) ? $customer->tax_number : '' }}
                                                        </p>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif

                                    @if ($invoice->invoice_module == 'mobileservice' && !empty($mobileCustomer))
                                        <div class="col">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="customer_name"
                                                        class="form-label">{{ __('Customer Name : ') }}</label><br>
                                                </div>
                                                <div class="col-md-6">
                                                    {{ $mobileCustomer->customer_name }}
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="sender_mobileno"
                                                        class="form-label">{{ __('Customer Mobile No : ') }}</label><br>
                                                </div>
                                                <div class="col-md-6">
                                                    {{ $mobileCustomer->mobile_no }}

                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="sender_email"
                                                        class="form-label">{{ __('Customer Email Address : ') }}</label><br>
                                                </div>
                                                <div class="col-md-6">
                                                    {{ $mobileCustomer->email }}
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="sender_email"
                                                        class="form-label">{{ __('Created By : ') }}</label><br>
                                                </div>
                                                <div class="col-md-6">
                                                    {{ $mobileCustomer->getServiceCreatedName->name }}
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="sender_email"
                                                        class="form-label">{{ __('Request Status : ') }}</label><br>
                                                </div>
                                                <div class="col-md-6">
                                                    <span
                                                        class="badge fix_badge @if ($mobileCustomer->is_approve == 1) bg-success @else bg-danger @endif  p-2 px-3">
                                                        @if ($mobileCustomer->is_approve == 1)
                                                            {{ __('Accepted') }}
                                                        @else
                                                            {{ __('Rejected') }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if (($invoice->invoice_module == 'legalcase' ||
                                          $invoice->invoice_module == 'lms' ||
                                          $invoice->invoice_module == 'sales' ||
                                          $invoice->invoice_module == 'newspaper' ||
                                          $invoice->invoice_module == 'RestaurantMenu' ||
                                          $invoice->invoice_module == 'Fleet') &&
                                          !empty($commonCustomer))
                                        <div class="col">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="customer_name"
                                                        class="form-label">{{ __('Name : ') }}</label><br>
                                                </div>
                                                <div class="col-md-6">
                                                    {{ $commonCustomer['name'] }}
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="customer_name"
                                                        class="form-label">{{ __('Email : ') }}</label><br>
                                                </div>
                                                <div class="col-md-6">
                                                    {{ $commonCustomer['email'] }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($invoice->invoice_module == 'childcare' && !empty($childCustomer))
                                        <div class="col">
                                            <div class="row">
                                                <div class="col-md-5 col-12">
                                                    <h6>{{ __('Child Detail') }}</h6>
                                                    <p>
                                                        <span><b>{{ __('Name :') }} </b>
                                                            {{ $childCustomer['child']->first_name . ' ' . $childCustomer['child']->last_name }}
                                                        </span><br>
                                                        <span><b>{{ __('Date Of Birth :') }}
                                                            </b>{{ $childCustomer['child']->dob }}</span><br>
                                                        <span><b>{{ __('Gender :') }}
                                                            </b>{{ $childCustomer['child']->gender }}</span><br>
                                                        <span><b>{{ __('Age :') }}
                                                            </b>{{ $childCustomer['child']->age }}</span><br>
                                                        <span><b>{{ __('Class :') }} </b>
                                                            {{ !empty($childCustomer['child']->class) ? $childCustomer['child']->class->class_level : '' }}</span><br>
                                                    </p>
                                                </div>
                                                <div class="col-md-5 col-12">
                                                    <h6>{{ __('Parent Detail') }}</h6>
                                                    <p>
                                                        <span><b>{{ __('Name :') }}
                                                            </b>{{ $childCustomer['parent']->name }}</span><br>
                                                        <span><b>{{ __('Email : ') }}</b>
                                                            {{ $childCustomer['parent']->email }}</span><br>
                                                        <span><b>{{ __('Contact Number :') }} </b>
                                                            {{ $childCustomer['parent']->contact_number }}</span><br>
                                                        <span><b>{{ __('Address :') }} </b>
                                                            {{ $childCustomer['parent']->address }}</span><br>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($invoice->invoice_module == 'vehicleinspection')
                                        @php
                                            $inspectionRequest = Workdo\VehicleInspectionManagement\Entities\InspectionRequest::find(
                                                $invoice->customer_id,
                                            );
                                            $vehicle_details = Workdo\VehicleInspectionManagement\Entities\InspectionVehicle::find(
                                                $inspectionRequest->vehicle_id,
                                            );
                                        @endphp
                                        @if (!empty($inspectionRequest->inspector_name) && !empty($inspectionRequest->inspector_email))
                                            <div class="col">
                                                <p class="font-style">
                                                    <strong class="h5 mb-1 d-block">{{ __('Request Number') }} :</strong>
                                                    {{ !empty($invoice->customer_id) ? \Workdo\VehicleInspectionManagement\Entities\InspectionRequest::inspectionRequestIdFormat($invoice->customer_id, $invoice->created_by, $invoice->workspace) : '' }}
                                                </p>
                                                <p class="font-style">
                                                    <strong class="h5 mb-1 d-block">{{ __('Billed To') }} :</strong>
                                                    {{ !empty($inspectionRequest->inspector_name) ? $inspectionRequest->inspector_name : '' }}<br>
                                                    {{ !empty($inspectionRequest->inspector_email) ? $inspectionRequest->inspector_email : '' }}
                                                </p>
                                            </div>
                                            <div class="col">
                                                <p class="font-style">
                                                    <strong class="h5 mb-1 d-block">{{ __('Vehicle Details') }} :</strong>
                                                    <dl class="row align-items-center">
                                                        <dt class="col-sm-6">
                                                            {{ __('Model') }}</dt>
                                                        <dd class="col-sm-6  ms-0"> :
                                                            {{ !empty($vehicle_details->model) ? $vehicle_details->model : '' }}
                                                        </dd>
                                                        <dt class="col-sm-6">
                                                            {{ __('ID Number') }}
                                                        </dt>
                                                        <dd class="col-sm-6  ms-0"> :
                                                            {{ !empty($vehicle_details->vehicle_id_number) ? $vehicle_details->vehicle_id_number : '' }}
                                                        </dd>
                                                        <dt class="col-sm-6">
                                                            {{ __('Current Mileage') }}</dt>
                                                        <dd class="col-sm-6  ms-0"> :
                                                            {{ !empty($vehicle_details->mileage) ? $vehicle_details->mileage : '' }}
                                                        </dd>
                                                        <dt class="col-sm-6">
                                                            {{ __('Manufacture Year') }}</dt>
                                                        <dd class="col-sm-6  ms-0"> :
                                                            {{ !empty($vehicle_details->manufacture_year) ? $vehicle_details->manufacture_year : '' }}
                                                        </dd>
                                                    </dl>
                                                </p>
                                            </div>
                                        @endif
                                    @endif

                                    @if ($invoice->invoice_module == 'machinerepair' && !empty($invoice->customer_id))
                                        @php
                                            $repair_request = \Workdo\MachineRepairManagement\Entities\MachineRepairRequest::find(
                                                $invoice->customer_id,
                                            );
                                            $machine_details = \Workdo\MachineRepairManagement\Entities\Machine::find(
                                                $repair_request->machine_id,
                                            );
                                        @endphp
                                        <div class="col">
                                            <p class="font-style">
                                                <strong>{{ __('Request Number') }} :</strong><br>
                                                {{ !empty($invoice->customer_id) ? \Workdo\MachineRepairManagement\Entities\MachineRepairRequest::machineRepairNumberFormat($invoice->customer_id, $invoice->created_by, $invoice->workspace) : '' }}<br>
                                            </p>
                                            <p class="font-style">
                                                <strong>{{ __('Billed To') }} :</strong><br>
                                                {{ !empty($repair_request->customer_name) ? $repair_request->customer_name : '' }}<br>
                                                {{ !empty($repair_request->customer_email) ? $repair_request->customer_email : '' }}<br>
                                            </p>
                                        </div>

                                        <div class="col">
                                            <p class="font-style">
                                                <strong>{{ __('Machine Details') }} :</strong><br>
                                                <dl class="row align-items-center">
                                                    <dt class="col-sm-4" style="font-weight: 600;">
                                                        {{ __('Name') }}</dt>
                                                    <dd class="col-sm-8  ms-0" style="margin-bottom: 0px;"> :
                                                        {{ !empty($machine_details->name) ? $machine_details->name : '' }}
                                                    </dd>
                                                    <dt class="col-sm-4" style="font-weight: 600;">
                                                        {{ __('Model') }}</dt>
                                                    <dd class="col-sm-8  ms-0" style="margin-bottom: 0px;"> :
                                                        {{ !empty($machine_details->model) ? $machine_details->model : '' }}
                                                    </dd>
                                                    <dt class="col-sm-4" style="font-weight: 600;">
                                                        {{ __('Manufacturer') }}</dt>
                                                    <dd class="col-sm-8  ms-0" style="margin-bottom: 0px;"> :
                                                        {{ !empty($machine_details->manufacturer) ? $machine_details->manufacturer : '' }}
                                                    </dd>
                                                </dl>
                                            </p>
                                        </div>
                                    @endif

                                    <div class="col-lg-2 col-sm-6">
                                        <strong class="h5 d-block mb-2">{{ __('Status')}} :</strong>
                                        @if ($invoice->status == 0)
                                            <span class="badge fix_badge f-12 p-2 d-inline-block bg-info">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 1)
                                            <span class="badge fix_badge f-12 p-2 d-inline-block bg-primary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 2)
                                            <span class="badge fix_badge f-12 p-2 d-inline-block bg-secondary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 3)
                                            <span class="badge fix_badge f-12 p-2 d-inline-block bg-warning">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 4)
                                            <span class="badge fix_badge f-12 p-2 d-inline-block bg-success">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @endif
                                    </div>

                                    @if (!empty($company_settings['invoice_qr_display']) && $company_settings['invoice_qr_display'] == 'on')
                                        <div class="col-lg-2 col-sm-6">
                                            <div class="float-sm-end qr-code">
                                                @if (module_is_active('Zatca', $invoice->created_by))
                                                    <div class="col">
                                                        <div class="float-sm-end">
                                                            @include('zatca::zatca_qr_code', [
                                                                'invoice_id' => $invoice->id,
                                                            ])
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="col">
                                                        <div class="float-sm-end">
                                                            {!! DNS2D::getBarcodeHTML(
                                                                route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)),
                                                                'QRCODE',
                                                                2,
                                                                2,
                                                            ) !!}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if (!empty($customFields) && count($invoice->customField) > 0)
                                <div class="px-4 mt-3">
                                    <div class="row row-gap">
                                        @foreach ($customFields as $field)
                                            <div class="col-xxl-3 col-sm-6">
                                                <strong class="d-block mb-1">{{ $field->name }} </strong>
                                                    @if ($field->type == 'attachment')
                                                        <a href="{{ get_file($invoice->customField[$field->id]) }}"
                                                            target="_blank">
                                                            <img src=" {{ get_file($invoice->customField[$field->id]) }} "
                                                                class="wid-120 rounded">
                                                        </a>
                                                    @else
                                                        <p  class="mb-0">
                                                            {{ !empty($invoice->customField[$field->id]) ? $invoice->customField[$field->id] : '-' }}
                                                        </p>
                                                    @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="row mt-4">
                                <div class="col-md-12 invoice-summary mt-3">
                                    <div class="invoice-title border-1 border-bottom mb-3 pb-2">
                                        <h3 class="h4 mb-0">{{ __('Product Summary') }}</h3>
                                        <small>{{ __('All items here cannot be deleted.') }}</small>
                                    </div>
                                    <div class="table-responsive mt-2">
                                        <table class="table mb-0 table-striped">
                                            <tr>
                                                <th data-width="40" class="text-white bg-primary text-uppercase">#</th>
                                                @if ($invoice->invoice_module == 'account' || $invoice->invoice_module == 'cmms' || $invoice->invoice_module == 'rent' || $invoice->invoice_module == 'machinerepair' || $invoice->invoice_module == 'musicinstitute' || $invoice->invoice_module == 'vehicleinspection' )
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Item Type') }}</th>
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Item') }}</th>
                                                @elseif($invoice->invoice_module == 'taskly')
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Project') }}</th>
                                                @elseif($invoice->invoice_module == 'lms')
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Course') }}</th>
                                                @elseif($invoice->invoice_module == 'childcare')
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Name') }}</th>
                                                @elseif($invoice->invoice_module == 'cardealership' ||  $invoice->invoice_module == 'sales' || $invoice->invoice_module == 'newspaper'|| $invoice->invoice_module == 'mobileservice')
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Items') }}</th>
                                                @elseif($invoice->invoice_module == 'legalcase' )
                                                    <th class="text-white bg-primary text-uppercase">{{ __('PARTICULARS') }}</th>
                                                @elseif($invoice->invoice_module == 'Fleet' )
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Distance') }}</th>
                                                @elseif($invoice->invoice_module == 'RestaurantMenu')
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Item Name') }}</th>
                                                @endif
                                                @if($invoice->invoice_module !== 'Fleet' && $invoice->invoice_module !== 'taskly' && $invoice->invoice_module !== 'childcare')
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Quantity') }}</th>
                                                @endif
                                                <th class="text-white bg-primary text-uppercase">{{ __('Rate') }}</th>
                                                @if($invoice->invoice_module != 'Fleet' && $invoice->invoice_module != 'childcare' )
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Discount') }}</th>
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Tax') }}</th>
                                                @endif
                                                <th class="text-white bg-primary text-uppercase">{{ __('Description') }}</th>
                                                <th class="text-right text-white bg-primary text-uppercase" width="12%">{{ __('Price') }}<br></th>
                                            </tr>
                                            @php
                                                $totalQuantity = 0;
                                                $totalRate = 0;
                                                $totalTaxPrice = 0;
                                                $totalDiscount = 0;
                                                $taxesData = [];
                                                $TaxPrice_array = [];
                                                $commonSubtotal = 0;

                                            @endphp
                                            @foreach ($iteams as $key => $iteam)
                                                @php
                                                    $commonSubtotal += $iteam->price;
                                                @endphp
                                                @if (!empty($iteam->tax))
                                                    @php
                                                        if ($invoice->invoice_module == 'newspaper'){
                                                            $taxes = \Workdo\Newspaper\Entities\NewspaperTax::tax($iteam->tax);
                                                            $totalQuantity += $iteam->quantity;
                                                            $totalRate += $iteam->price;
                                                            foreach ($taxes as $taxe) {
                                                                $taxDataPrice = \Workdo\Newspaper\Entities\NewspaperTax::taxRate(
                                                                    $taxe->percentage,
                                                                    $iteam->price,
                                                                    $iteam->quantity
                                                                );
                                                                if (array_key_exists($taxe->name, $taxesData)) {
                                                                    $taxesData[$taxe->name] = $taxesData[$taxe->name] + $taxDataPrice;
                                                                } else {
                                                                    $taxesData[$taxe->name] = $taxDataPrice;
                                                                }
                                                            }
                                                        }
                                                        else
                                                        {
                                                            $taxes = App\Models\Invoice::tax($iteam->tax);
                                                            $totalQuantity += $iteam->quantity;
                                                            $totalRate += $iteam->price;
                                                            if ($invoice->invoice_module == 'account') {
                                                                $totalDiscount += $iteam->discount;
                                                            } elseif ($invoice->invoice_module == 'taskly') {
                                                                $totalDiscount = $invoice->discount;
                                                            }

                                                            foreach ($taxes as $taxe) {
                                                                $taxDataPrice = App\Models\Invoice::taxRate(
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
                                                        }
                                                    @endphp
                                                @elseif ($invoice->invoice_module == 'Fleet')
                                                    @php
                                                        $totalRate += $iteam->price;
                                                    @endphp
                                                @endif
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    @if ($invoice->invoice_module == 'account' || $invoice->invoice_module == 'machinerepair' || $invoice->invoice_module == 'musicinstitute' || $invoice->invoice_module == 'vehicleinspection')
                                                        <td>{{ !empty($iteam->product_type) ? Str::ucfirst($iteam->product_type) : '--' }}</td>
                                                        <td>{{ !empty($iteam->product()) ? $iteam->product()->name : '' }}</td>
                                                    @elseif ($invoice->invoice_module == 'taskly')
                                                        <td>{{ !empty($iteam->product()) ? $iteam->product()->title : '' }}</td>
                                                    @elseif ($invoice->invoice_module == 'cmms' || $invoice->invoice_module == 'rent')
                                                        <td>{{ !empty($iteam->product_type) ? Str::ucfirst($iteam->product_type) : '--' }}</td>
                                                        <td>{{ !empty($iteam->product()) ? $iteam->product()->name : '' }}</td>
                                                    @elseif ($invoice->invoice_module == 'lms')
                                                        <td>{{ !empty($iteam->product()) ? $iteam->product()->title : '' }}</td>
                                                    @elseif ($invoice->invoice_module == 'childcare' || $invoice->invoice_module == 'legalcase')
                                                        <td>{{ !empty($iteam->product_name) ? $iteam->product_name : '' }}</td>
                                                    @elseif ($invoice->invoice_module == 'cardealership' || $invoice->invoice_module == 'sales' || $invoice->invoice_module == 'newspaper' || $invoice->invoice_module == 'mobileservice')
                                                        <td>{{ !empty($iteam->product()) ? $iteam->product()->name : '' }}</td>
                                                    @elseif ($invoice->invoice_module == 'RestaurantMenu')
                                                        <td>{{ !empty($iteam->product_name) ? $iteam->product_name : '' }}</td>
                                                    @endif
                                                    @if ($invoice->invoice_module == 'Fleet')
                                                        <td>{{ !empty($iteam->product()) ? $iteam->product()->distance : 0 }}</td>
                                                    @elseif($invoice->invoice_module == 'taskly' || $invoice->invoice_module == 'childcare')
                                                    @else
                                                        <td>{{ $iteam->quantity }}</td>
                                                    @endif
                                                    <td>{{ currency_format_with_sym($iteam->price, $invoice->created_by, $invoice->workspace) }}</td>
                                                    @if($invoice->invoice_module != 'Fleet' && $invoice->invoice_module != 'childcare')
                                                        <td>{{ currency_format_with_sym($iteam->discount, $invoice->created_by, $invoice->workspace) }}</td>
                                                        <td>
                                                            @if (!empty($iteam->tax))
                                                                <table>
                                                                    @php
                                                                        $totalTaxRate = 0;
                                                                        $data = 0;
                                                                    @endphp
                                                                    @foreach ($taxes as $tax)
                                                                        @php
                                                                            if ($invoice->invoice_module == 'newspaper'){
                                                                                $taxPrice = \Workdo\Newspaper\Entities\NewspaperTax::taxRate(
                                                                                    $tax->percentage,
                                                                                    $iteam->price,
                                                                                    $iteam->quantity
                                                                                );
                                                                            }
                                                                            else {
                                                                                $taxPrice = \App\Models\Invoice::taxRate(
                                                                                    $tax->rate,
                                                                                    $iteam->price,
                                                                                    $iteam->quantity,
                                                                                    $iteam->discount,
                                                                                );
                                                                            }
                                                                            $totalTaxPrice += $taxPrice;
                                                                            $data += $taxPrice;
                                                                        @endphp
                                                                        <tr>
                                                                            <td>{{ $tax->name . ' (' . $tax->rate . '%)' }}</td>
                                                                            <td>{{ currency_format_with_sym($taxPrice, $invoice->created_by, $invoice->workspace) }}</td>
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
                                                    @endif
                                                    <td>{{ !empty($iteam->description) ? $iteam->description : '-' }}</td>
                                                    @php
                                                        $tr_tex =
                                                            array_key_exists($key, $TaxPrice_array) == true
                                                                ? $TaxPrice_array[$key]
                                                                : 0;
                                                    @endphp
                                                    <td>
                                                        @if ($invoice->invoice_module == 'childcare')
                                                            {{ currency_format_with_sym($iteam->price, $invoice->created_by, $invoice->workspace) }}
                                                        @elseif ($invoice->invoice_module == 'Fleet')
                                                            @php
                                                                $distance = !empty($iteam->product()) ? $iteam->product()->distance : 0;
                                                                $price = $iteam->price * $iteam->product()->distance;
                                                            @endphp
                                                            {{ currency_format_with_sym($price,$invoice->created_by, $invoice->workspace) }}
                                                        @else
                                                            {{ currency_format_with_sym($iteam->price * $iteam->quantity - $iteam->discount + $tr_tex, $invoice->created_by, $invoice->workspace) }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    @if ($invoice->invoice_module == 'account')
                                                        <td></td>
                                                    @endif

                                                    @if ($invoice->invoice_module == 'cmms' || $invoice->invoice_module == 'rent' || $invoice->invoice_module == 'vehicleinspection' || $invoice->invoice_module == 'musicinstitute' || $invoice->invoice_module == 'machinerepair')
                                                        <td></td>
                                                        <td class="bg-color "><b>{{ __('Total') }}</b></td>
                                                    @else
                                                        <td class="bg-color "><b>{{ __('Total') }}</b></td>
                                                    @endif

                                                    @if ($invoice->invoice_module == 'Fleet')
                                                        <td class="bg-color"><b>{{ currency_format_with_sym($totalRate,$invoice->created_by, $invoice->workspace) }}</b></td>
                                                    @elseif($invoice->invoice_module == 'taskly')
                                                        <td class="bg-color"><b>{{ currency_format_with_sym($totalRate,$invoice->created_by, $invoice->workspace) }}</b></td>
                                                        <td class="bg-color"><b>{{ currency_format_with_sym($totalDiscount,$invoice->created_by, $invoice->workspace) }}</b></td>
                                                        <td class="bg-color"><b>{{ currency_format_with_sym($totalTaxPrice,$invoice->created_by, $invoice->workspace)}}</b></td>
                                                    @elseif($invoice->invoice_module == 'cmms')
                                                        <td class="bg-color"><b>{{ $totalQuantity,$invoice->created_by, $invoice->workspace }}</b></td>
                                                        <td class="bg-color"><b>{{ currency_format_with_sym($totalRate,$invoice->created_by, $invoice->workspace) }}</b></td>
                                                        <td class="bg-color"><b>{{ currency_format_with_sym($totalDiscount,$invoice->created_by, $invoice->workspace) }}</b></td>
                                                        <td class="bg-color"><b>{{ currency_format_with_sym($totalTaxPrice,$invoice->created_by, $invoice->workspace) }}</b></td>
                                                    @elseif($invoice->invoice_module == 'childcare')
                                                        <td class="bg-color"><b>{{ currency_format_with_sym($commonSubtotal,$invoice->created_by, $invoice->workspace) }}</b></td>
                                                    @else
                                                        <td class="bg-color"><b>{{ $totalQuantity,$invoice->created_by, $invoice->workspace }}</b></td>
                                                        <td class="bg-color"><b>{{ currency_format_with_sym($totalRate,$invoice->created_by, $invoice->workspace) }}</b></td>
                                                        <td class="bg-color"><b>{{ currency_format_with_sym($totalDiscount,$invoice->created_by, $invoice->workspace) }}</b></td>
                                                        <td class="bg-color"><b>{{ currency_format_with_sym($totalTaxPrice,$invoice->created_by, $invoice->workspace) }}</b></td>
                                                    @endif
                                                </tr>
                                                @php
                                                    $colspan = 6;
                                                    $customerInvoices = ['taskly', 'account', 'cmms', 'cardealership', 'RestaurantMenu', 'rent' , 'Fleet'];
                                                    if (in_array($invoice->invoice_module, $customerInvoices)) {
                                                        $colspan = 7;
                                                    }

                                                    if ($invoice->invoice_module == 'taskly') {
                                                        $colspan = 5;
                                                    }

                                                    if ($invoice->invoice_module == 'Fleet' || $invoice->invoice_module == 'childcare') {
                                                        $colspan = 3;
                                                    }
                                                @endphp

                                                @if ($invoice->invoice_module != 'Fleet')
                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Sub Total') }}</td>
                                                        <td class="text-right">
                                                            @if ($invoice->invoice_module == 'childcare')
                                                                <b>{{ currency_format_with_sym($commonSubtotal,$invoice->created_by, $invoice->workspace) }}</b>
                                                            @else
                                                                <b>{{ currency_format_with_sym($invoice->getSubTotal(),$invoice->created_by, $invoice->workspace) }}</b>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Discount') }}</td>
                                                        <td class="text-right"><b> {{ currency_format_with_sym($invoice->getTotalDiscount(),$invoice->created_by, $invoice->workspace) }}</b></td>
                                                    </tr>
                                                @endif

                                                @if (!empty($taxesData))
                                                    @foreach ($taxesData as $taxName => $taxPrice)
                                                        <tr>
                                                            <td colspan="{{ $colspan }}"></td>
                                                            <td class="text-right">{{ $taxName }}</td>
                                                            <td class="text-right"><b>{{ currency_format_with_sym($taxPrice,$invoice->created_by, $invoice->workspace) }}</b></td>
                                                        </tr>
                                                    @endforeach
                                                @endif

                                                <tr>
                                                    <td colspan="{{ $colspan }}"></td>
                                                    <td class="blue-text text-right">{{ __('Total') }}</td>
                                                    <td class="blue-text text-right">
                                                        @if ($invoice->invoice_module == 'childcare')
                                                            <b>{{ currency_format_with_sym($commonSubtotal,$invoice->created_by, $invoice->workspace) }}</b>
                                                        @elseif ($invoice->invoice_module == 'Fleet')
                                                            <b>{{ currency_format_with_sym($invoice->getFleetSubTotal(),$invoice->created_by, $invoice->workspace) }}</b>
                                                        @else
                                                           <b> {{ currency_format_with_sym($invoice->getTotal(),$invoice->created_by, $invoice->workspace) }}</b>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="{{ $colspan }}"></td>
                                                    <td class="text-right">{{ __('Paid') }}</td>
                                                    <td class="text-right"><b>{{ currency_format_with_sym($invoice->getTotal() - $invoice->getDue() - $invoice->invoiceTotalCreditNote(),$invoice->created_by, $invoice->workspace) }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="{{ $colspan }}"></td>
                                                    <td class="text-right">{{ __('Credit Note Applied') }}</td>
                                                    <td class="text-right"><b>{{ currency_format_with_sym($invoice->invoiceTotalCreditNote(),$invoice->created_by, $invoice->workspace) }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="{{ $colspan }}"></td>
                                                    <td class="text-right">{{ __('Debit note issued') }}</td>
                                                    <td class="text-right"><b>{{ currency_format_with_sym($invoice->invoiceTotalCustomerCreditNote(),$invoice->created_by, $invoice->workspace) }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="{{ $colspan }}"></td>
                                                    <td class="text-right">{{ __('Due') }}</td>
                                                    <td class="text-right"><b>{{ currency_format_with_sym($invoice->getDue(),$invoice->created_by, $invoice->workspace) }}</b></td>
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
        <div class="col-12">
            <h5 class="h4 d-inline-block font-weight-400 mb-4">{{ __('Receipt Summary') }}</h5>
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th class="text-dark">{{ __('Date') }}</th>
                                <th class="text-dark">{{ __('Amount') }}</th>
                                <th class="text-dark">{{ __('Payment Type') }}</th>
                                <th class="text-dark">{{ __('Account') }}</th>
                                <th class="text-dark">{{ __('Reference') }}</th>
                                <th class="text-dark">{{ __('Receipt') }}</th>
                                <th class="text-dark">{{ __('Description') }}</th>
                                <th class="text-dark">{{ __('OrderId') }}</th>
                            </tr>
                            @forelse($invoice->payments as $key =>$payment)
                                <tr>
                                    <td>{{ company_date_formate($payment->date, $invoice->created_by, $invoice->workspace) }}</td>
                                    <td>{{ currency_format_with_sym($payment->amount, $invoice->created_by, $invoice->workspace) }}</td>
                                    <td>{{ $payment->payment_type }}</td>
                                    @if (module_is_active('Account'))
                                        <td>{{ !empty($payment->bankAccount) ? $payment->bankAccount->bank_name . ' ' . $payment->bankAccount->holder_name : '--' }}
                                        @else
                                        <td>--</td>
                                    @endif
                                    <td>{{ !empty($payment->reference) ? $payment->reference : '--' }}</td>
                                    <td>
                                        @if (!empty($payment->add_receipt) && empty($payment->receipt) && check_file($payment->add_receipt))
                                            <div class="action-btn me-2">
                                                <a href="{{ check_file($payment->add_receipt) ? get_file($payment->add_receipt) : '-' }}"
                                                    download=""
                                                    class="mx-3 btn btn-sm align-items-center bg-primary"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Download') }}" target="_blank">
                                                    <i class="ti ti-download text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn">
                                                <a href="{{ check_file($payment->add_receipt) ? get_file($payment->add_receipt) : '-' }}"
                                                    class="mx-3 btn btn-sm align-items-center bg-secondary"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Show') }}" target="_blank">
                                                    <i class="ti ti-crosshair text-white"></i>
                                                </a>
                                            </div>

                                        @elseif (!empty($payment->receipt) && empty($payment->add_receipt) && $payment->payment_type == 'Stripe')
                                            <a href="{{ $payment->receipt }}" target="_blank"> <i class="ti ti-file"></i></a>
                                        @elseif (!empty($payment->receipt) && empty($payment->add_receipt) && $payment->payment_type == 'Coin')
                                            <a href="{{ $payment->receipt }}" target="_blank"> <i class="ti ti-file"></i></a>
                                        @elseif($payment->payment_type == 'Bank Transfer')
                                            <a href="{{ !empty($payment->receipt) ? (check_file($payment->receipt) ? get_file($payment->receipt) : '#!') : '#!' }}" target="_blank"><i class="ti ti-file"></i></a>
                                        @else
                                            --
                                        @endif
                                    </td>
                                    <td>
                                        {{ !empty($payment->description) ? $payment->description : '--' }}</td>
                                    <td>{{ !empty($payment->order_id) ? $payment->order_id : '--' }}</td>
                                </tr>
                            @empty
                                @include('layouts.nodatafound')
                            @endforelse
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if (module_is_active('Account'))
            <div class="col-12">
                <h5 class="h4 d-inline-block font-weight-400 mb-4">{{ __('Credit Note Summary') }}</h5>
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table ">
                                <tr>
                                    <th class="text-dark">{{ __('Date') }}</th>
                                    <th class="text-dark" class="">{{ __('Amount') }}</th>
                                    <th class="text-dark" class="">{{ __('Description') }}</th>
                                    @if (Laratrust::hasPermission('edit credit note') || Laratrust::hasPermission('delete credit note'))
                                        <th class="text-dark">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                                @forelse($invoice->creditNote as $key =>$creditNote)
                                    <tr>
                                        <td>{{ company_date_formate($creditNote->date, $invoice->created_by, $invoice->workspace) }}</td>
                                        <td class="">{{ currency_format_with_sym($creditNote->amount, $invoice->created_by, $invoice->workspace) }}</td>
                                        <td class="">{{ $creditNote->description }}</td>
                                        <td>
                                            @permission('edit credit note')
                                                <a data-url="{{ route('invoice.edit.credit.note', [$creditNote->invoice, $creditNote->id]) }}"
                                                    data-ajax-popup="true" data-title="{{ __('Add Credit Note') }}"
                                                    data-toggle="tooltip" data-original-title="{{ __('Credit Note') }}"
                                                    href="#" class="mx-3 btn btn-sm align-items-center"
                                                    data-toggle="tooltip" data-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            @endpermission
                                            @permission('delete credit note')
                                                <a href="#" class="mx-3 btn btn-sm align-items-center "
                                                    data-toggle="tooltip" data-original-title="{{ __('Delete') }}"
                                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                    data-confirm-yes="document.getElementById('delete-form-{{ $creditNote->id }}').submit();">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                                {!! Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['invoice.delete.credit.note', $creditNote->invoice, $creditNote->id],
                                                    'id' => 'delete-form-' . $creditNote->id,
                                                ]) !!}
                                                {!! Form::close() !!}
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
        @endif
    </div>

    @if ($invoice->getDue() > 0)
        <div id="paymentModal" class="modal" tabindex="-1" aria-labelledby="exampleModalLongTitle" aria-modal="true"
            role="dialog" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">{{ __('Add Payment') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row pb-3 px-2">
                            <section class="">
                                <ul class="nav nav-pills  mb-3" id="pills-tab" role="tablist">
                                    @if ((isset($company_settings['bank_transfer_payment_is_on'])
                                            ? $company_settings['bank_transfer_payment_is_on']
                                            : 'off') == 'on' && !empty($company_settings['bank_number']))
                                        <li class="nav-item">
                                            <a class="nav-link" id="pills-home-tab" data-bs-toggle="pill"
                                                data-bs-target="#bank-payment" type="button" role="tab"
                                                aria-controls="pills-home"
                                                aria-selected="true">{{ __('Bank trasfer') }}</a>
                                        </li>
                                    @endif
                                    @stack('invoice_payment_tab')
                                </ul>
                                <div class="tab-content" id="pills-tabContent">
                                    @if ((isset($company_settings['bank_transfer_payment_is_on'])
                                            ? $company_settings['bank_transfer_payment_is_on']
                                            : 'off') == 'on' && !empty($company_settings['bank_number']))
                                        <div class="tab-pane fade " id="bank-payment" role="tabpanel" aria-labelledby="bank-payment">
                                            <form method="post" action="{{ route('invoice.pay.with.bank') }}"
                                                class="require-validation" id="payment-form"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="type" value="invoice">
                                                <div class="row mt-2">
                                                    <div class="col-sm-8">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('Bank Details :') }}</label>
                                                            <p class="">{!! $company_settings['bank_number'] !!}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('Payment Receipt') }}</label>
                                                            <div class="choose-files">
                                                                <label for="payment_receipt">
                                                                    <div class=" bg-primary "> <i class="ti ti-upload px-1"></i></div>
                                                                    <input type="file" class="form-control"
                                                                        required=""
                                                                        accept="image/png, image/jpeg, image/jpg, .pdf"
                                                                        name="payment_receipt" id="payment_receipt"
                                                                        data-filename="payment_receipt"
                                                                        onchange="document.getElementById('blah3').src = window.URL.createObjectURL(this.files[0])">
                                                                </label>
                                                                <p class="text-danger error_msg d-none">{{ __('This field is required') }}</p>
                                                                <img class="mt-2" width="70px" id="blah3">
                                                            </div>
                                                            <div class="invalid-feedback">{{ __('invalid form file') }}</div>
                                                        </div>
                                                    </div>
                                                    <small class="text-danger">{{ __('first, make a payment and take a screenshot or download the receipt and upload it.') }}</small>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount">{{ __('Amount') }}</label>
                                                        <div class="input-group">
                                                            <span class="input-group-prepend"><span class="input-group-text">{{ !empty($company_settings['defult_currancy']) ? $company_settings['defult_currancy'] : '$' }}</span></span>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}" name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="error" style="display: none;">
                                                            <div class='alert-danger alert'>{{ __('Please correct the errors and try again.') }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <button type="button" class="btn  btn-secondary me-1" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                    <button class="btn btn-primary" type="submit" id="submit_transfer">{{ __('Make Payment') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                    @stack('invoice_payment_div')
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('scripts')
    <script>
        $("#paymentModals").click(function() {
            $("#paymentModal").modal('show');
            $("ul li a").removeClass("active");
            $(".tab-pane").removeClass("active show");
            $("ul li:first a:first").addClass("active");
            $(".tab-pane:first").addClass("active show");
        });

        $("#submit_transfer").click(function() {
            var skill = $('#payment_receipt').val();
            if (skill == '') {
                $('.error_msg').removeClass('d-none')
                return false;
            } else {
                $('.error_msg').addClass('d-none')
            }
        })
    </script>
@endpush
