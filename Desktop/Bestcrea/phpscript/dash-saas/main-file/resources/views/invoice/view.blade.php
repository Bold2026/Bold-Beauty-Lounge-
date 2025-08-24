@extends('layouts.main')
@section('page-title')
    {{ __('Invoice Detail') }}
@endsection
@section('page-breadcrumb')
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
@push('scripts')
    <script type="text/javascript">
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
    <script src="{{ asset('assets/js/plugins/dropzone-amd-module.min.js') }}"></script>
    <script>
        Dropzone.autoDiscover = false;
        myDropzone = new Dropzone("#dropzonewidget", {
            url: "{{ route('invoice.file.upload', [$invoice->id]) }}",
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
            formData.append("invoice_id", {{ $invoice->id }});
        });
    </script>
@endpush
@section('page-action')
    <div>
        <div class="d-flex">
            <a href="#" class="btn btn-sm btn-primary cp_link me-2  "
                data-link="{{ route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}"
                data-bs-toggle="tooltip" title="{{ __('Copy') }}"
                data-original-title="{{ __('Click to copy invoice link') }}">
                <span class="btn-inner--icon text-white"><i class="ti ti-file"></i></span>
            </a>
            <a href="#" class="btn btn-sm btn-info align-items-center"
                data-url="{{ route('delivery-form.pdf', \Crypt::encrypt($invoice->id)) }}" data-ajax-popup="true"
                data-size="lg" data-bs-toggle="tooltip" title="{{ __('Invoice Delivery Form') }}"
                data-title="{{ __('Invoice Delivery Form') }}">
                <i class="ti ti-clipboard-list text-white"></i>
            </a>
        </div>
    </div>
@endsection
@section('content')
    @if (\Auth::user()->type == 'company')
        @if ($invoice->status != 4)
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
                                    <h2 class="text-primary h5 mb-2">{{ __('Create Invoice') }}</h2>
                                    <p class="text-sm mb-3">
                                        {{ __('Created on ') }}{{ company_date_formate($invoice->issue_date, $invoice->created_by, $invoice->workspace) }}
                                    </p>
                                    @permission('invoice edit')
                                        <a href="{{ route('invoice.edit', \Crypt::encrypt($invoice->id)) }}"
                                            class="btn btn-sm btn-light" data-bs-toggle="tooltip"
                                            data-original-title="{{ __('Edit') }}"><i
                                                class="ti ti-pencil me-1"></i>{{ __('Edit') }}</a>
                                    @endpermission
                                </div>

                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-sm-6">
                            <div class="progress mb-3">
                                <div class="{{ $invoice->status !== 0 ? 'progress-value' : '' }}"></div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <div class="timeline-icons ">
                                    <i class="ti ti-send text-warning"></i>
                                </div>
                                <div class="invoice-content">
                                    <h6 class="text-warning h5 mb-2">{{ __('Send Invoice') }}</h6>
                                    <p class="text-sm mb-2">
                                        @if ($invoice->status != 0)
                                            {{ __('Sent on') }}
                                            {{ company_date_formate($invoice->send_date, $invoice->created_by, $invoice->workspace) }}
                                        @else
                                            {{ __('Status') }} : {{ __('Not Sent') }}
                                        @endif
                                    </p>
                                    @stack('recurring_type')
                                    @if ($invoice->status == 0)
                                        @permission('invoice send')
                                            <a href="{{ route('invoice.sent', $invoice->id) }}" class="btn btn-sm btn-warning"
                                                data-bs-toggle="tooltip" data-original-title="{{ __('Mark Sent') }}"><i
                                                    class="ti ti-send me-1"></i>{{ __('Send') }}</a>
                                        @endpermission
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-sm-6">
                            <div class="progress mb-3">
                                <div class="{{ $invoice->status == 4 ? 'progress-value' : '' }}"></div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <div class="timeline-icons ">
                                    <i class="ti ti-report-money text-info"></i>
                                </div>
                                <div class="invoice-content">
                                    <h6 class="text-info h5 mb-2">{{ __('Get Paid') }}</h6>
                                    <p class=" text-sm mb-3">{{ __('Status') }} :
                                        @if ($invoice->status == 0)
                                            <span>{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 1)
                                            <span>{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 2)
                                            <span>{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 3)
                                            <span>{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 4)
                                            <span>{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @endif
                                    </p>
                                    @if ($invoice->status != 0)
                                        @permission('invoice payment create')
                                            <a href="#" data-url="{{ route('invoice.payment', $invoice->id) }}"
                                                data-ajax-popup="true" data-title="{{ __('Add Payment') }}"
                                                class="btn btn-sm btn-light" data-original-title="{{ __('Add Payment') }}"><i
                                                    class="ti ti-report-money me-1"></i>{{ __('Add Payment') }}</a> <br>
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

    @if ($invoice->status != 0 && \Auth::user()->type == 'company')
        <div class="row row-gap justify-content-between align-items-center mb-3">
            <div class="col-md-6">
                <ul class="nav nav-pills nav-fill cust-nav information-tab invoice-tab" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="invoice-tab" data-bs-toggle="pill" data-bs-target="#invoice"
                            type="button">{{ __('Invoice') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="receipt-summary-tab" data-bs-toggle="pill"
                            data-bs-target="#receipt-summary" type="button">{{ __('Receipt Summary') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="credit-summary-tab" data-bs-toggle="pill"
                            data-bs-target="#credit-summary" type="button">{{ __('Credit Note Summary') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="invoice-attechment-tab" data-bs-toggle="pill"
                            data-bs-target="#invoice-attechment" type="button">{{ __('Attachment') }}</button>
                    </li>
                    @stack('add_recurring_tab')
                </ul>
            </div>

            <div
                class="col-md-6 apply-wrp d-flex flex-wrap gap-2 align-items-center justify-content-start justify-content-md-end">
                @permission('creditnote create')
                    @if (!empty($customer) && $invoice->status != 4)
                        <div class="all-button-box">
                            <a href="#" class="btn btn-sm btn-primary"
                                data-url="{{ route('invoice.credit.note', $invoice->id) }}" data-ajax-popup="true"
                                data-title="{{ __('Apply Credit Note') }}">
                                {{ __('Apply Credit Note') }}
                            </a>
                        </div>
                    @endif
                @endpermission

                @if ($invoice->status != 4)
                    <div class="all-button-box">
                        <a href="{{ route('invoice.payment.reminder', $invoice->id) }}"
                            class="btn btn-sm btn-primary">{{ __('Receipt Reminder') }}</a>
                    </div>
                @endif

                <div class="all-button-box">
                    <a href="{{ route('invoice.resent', $invoice->id) }}"
                        class="btn btn-sm btn-primary">{{ __('Resend Invoice') }}</a>
                </div>

                <div class="all-button-box">
                    <a href="{{ route('invoice.pdf', Crypt::encrypt($invoice->id)) }}" target="_blank"
                        class="btn btn-sm btn-primary">{{ __('Download') }}</a>
                </div>
            </div>
        </div>
    @else
        <div class="row justify-content-between align-items-center mb-3">
            <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                <div class="all-button-box">
                    <a href="{{ route('invoice.pdf', Crypt::encrypt($invoice->id)) }}" target="_blank"
                        class="btn btn-sm btn-primary">
                        {{ __('Download') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade active show" id="invoice" role="tabpanel"
                    aria-labelledby="pills-user-tab-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="invoice">
                                <div class="invoice-print">
                                    <div
                                        class="d-flex flex-wrap align-items-center justify-content-between row-gap invoice-title border-1 border-bottom  pb-3 mb-3">
                                        <div>
                                            <h2 class="h3 mb-0">{{ __('Invoice') }}</h2>
                                        </div>
                                        <div>
                                            <div class="d-flex invoice-wrp flex-wrap align-items-center gap-md-2 gap-1">
                                                <div
                                                    class="d-flex invoice-date flex-wrap align-items-center gap-md-3 gap-1">
                                                    <p class="mb-0"><strong>{{ __('Issue Date') }} :</strong>
                                                        {{ company_date_formate($invoice->issue_date) }}
                                                    </p>
                                                    <p class="mb-0"><strong>{{ __('Due Date') }} :</strong>
                                                        {{ company_date_formate($invoice->due_date) }}
                                                    </p>
                                                </div>
                                                <h3 class="invoice-number mb-0">
                                                    {{ \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id) }}
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
                                                        <strong class="h5 mb-1">{{ __('Name ') }} :
                                                        </strong>{{ !empty($customer->name) ? $customer->name : '' }}
                                                    </p>

                                                    @if (!empty($customer->billing_name) && !empty($customer->billing_address) && !empty($customer->billing_zip))
                                                        <p class="mb-2"><strong
                                                                class="h5 mb-1 d-block">{{ __('Billed To') }}
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

                                                    @if (!empty($company_settings['invoice_shipping_display']) && ($company_settings['invoice_shipping_display'] == 'on'))
                                                        @if (!empty($customer->shipping_name) && !empty($customer->shipping_address) && !empty($customer->shipping_zip))
                                                            <p class="mb-2">
                                                                <strong class="h5 mb-1 d-block">{{ __('Shipped To') }}
                                                                    :</strong>
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

                                            @if (
                                                ($invoice->invoice_module == 'legalcase' ||
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
                                                            <strong class="h5 mb-1 d-block">{{ __('Request Number') }}
                                                                :</strong>
                                                            {{ !empty($invoice->customer_id) ? \Workdo\VehicleInspectionManagement\Entities\InspectionRequest::inspectionRequestIdFormat($invoice->customer_id, $invoice->created_by, $invoice->workspace) : '' }}
                                                        </p>
                                                        <p class="font-style">
                                                            <strong class="h5 mb-1 d-block">{{ __('Billed To') }}
                                                                :</strong>
                                                            {{ !empty($inspectionRequest->inspector_name) ? $inspectionRequest->inspector_name : '' }}<br>
                                                            {{ !empty($inspectionRequest->inspector_email) ? $inspectionRequest->inspector_email : '' }}
                                                        </p>
                                                    </div>
                                                    <div class="col">
                                                        <p class="font-style">
                                                            <strong class="h5 mb-1 d-block">{{ __('Vehicle Details') }}
                                                                :</strong>
                                                        <dl class="row align-items-center">
                                                            <dt class="col-sm-6">
                                                                {{ __('Model') }}
                                                            </dt>
                                                            <dd class="col-sm-6  ms-0" style="margin-bottom: 0px;"> :
                                                                {{ !empty($vehicle_details->model) ? $vehicle_details->model : '' }}
                                                            </dd>
                                                            <dt class="col-sm-6">
                                                                {{ __('ID Number') }}
                                                            </dt>
                                                            <dd class="col-sm-6  ms-0" style="margin-bottom: 0px;"> :
                                                                {{ !empty($vehicle_details->vehicle_id_number) ? $vehicle_details->vehicle_id_number : '' }}
                                                            </dd>
                                                            <dt class="col-sm-6">
                                                                {{ __('Current Mileage') }}
                                                            </dt>
                                                            <dd class="col-sm-6  ms-0" style="margin-bottom: 0px;"> :
                                                                {{ !empty($vehicle_details->mileage) ? $vehicle_details->mileage : '' }}
                                                            </dd>
                                                            <dt class="col-sm-6">
                                                                {{ __('Manufacture Year') }}
                                                            </dt>
                                                            <dd class="col-sm-6  ms-0" style="margin-bottom: 0px;"> :
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
                                                            {{ __('Name') }}
                                                        </dt>
                                                        <dd class="col-sm-8  ms-0" style="margin-bottom: 0px;"> :
                                                            {{ !empty($machine_details->name) ? $machine_details->name : '' }}
                                                        </dd>
                                                        <dt class="col-sm-4" style="font-weight: 600;">
                                                            {{ __('Model') }}
                                                        </dt>
                                                        <dd class="col-sm-8  ms-0" style="margin-bottom: 0px;"> :
                                                            {{ !empty($machine_details->model) ? $machine_details->model : '' }}
                                                        </dd>
                                                        <dt class="col-sm-4" style="font-weight: 600;">
                                                            {{ __('Manufacturer') }}
                                                        </dt>
                                                        <dd class="col-sm-8  ms-0" style="margin-bottom: 0px;"> :
                                                            {{ !empty($machine_details->manufacturer) ? $machine_details->manufacturer : '' }}
                                                        </dd>
                                                    </dl>
                                                    </p>
                                                </div>
                                            @endif

                                            <div class="col-lg-4 col-sm-6">
                                                <div class="d-flex flex-wrap justify-content-between gap-3">
                                                    <div>
                                                        <strong class="h5 d-block mb-2">{{ __('Status') }} :</strong>
                                                        @if ($invoice->status == 0)
                                                            <span
                                                                class="badge fix_badge f-12 p-2 d-inline-block bg-info">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                        @elseif($invoice->status == 1)
                                                            <span
                                                                class="badge fix_badge f-12 p-2 d-inline-block bg-primary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                        @elseif($invoice->status == 2)
                                                            <span
                                                                class="badge fix_badge f-12 p-2 d-inline-block bg-secondary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                        @elseif($invoice->status == 3)
                                                            <span
                                                                class="badge fix_badge f-12 p-2 d-inline-block bg-warning">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                        @elseif($invoice->status == 4)
                                                            <span
                                                                class="badge fix_badge f-12 p-2 d-inline-block bg-success">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                        @endif
                                                    </div>

                                                    @if (!empty($company_settings['invoice_qr_display']) && $company_settings['invoice_qr_display'] == 'on')
                                                        <div class="float-sm-end qr-code">
                                                            @if (module_is_active('Zatca'))
                                                                <div class="float-sm-end w-100">
                                                                    @include('zatca::zatca_qr_code', [
                                                                        'invoice_id' => $invoice->id,
                                                                    ])
                                                                </div>
                                                            @else
                                                                <div class="float-sm-end invoice-qr-inner w-100">
                                                                    {!! DNS2D::getBarcodeHTML(
                                                                        route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)),
                                                                        'QRCODE',
                                                                        2,
                                                                        2,
                                                                    ) !!}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
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
                                                            <p class="mb-0">
                                                                {{ !empty($invoice->customField[$field->id]) ? $invoice->customField[$field->id] : '-' }}
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
                                        </div>
                                        <div class="table-responsive mt-2">
                                            <table class="table mb-0 table-striped">
                                                <tr>
                                                    <th data-width="40" class="text-white bg-primary text-uppercase">#
                                                    </th>
                                                    @if (
                                                        $invoice->invoice_module == 'account' ||
                                                            $invoice->invoice_module == 'cmms' ||
                                                            $invoice->invoice_module == 'rent' ||
                                                            $invoice->invoice_module == 'machinerepair' ||
                                                            $invoice->invoice_module == 'musicinstitute' ||
                                                            $invoice->invoice_module == 'vehicleinspection')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Item Type') }}</th>
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Item') }}</th>
                                                    @elseif($invoice->invoice_module == 'taskly')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Project') }}</th>
                                                    @elseif($invoice->invoice_module == 'lms')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Course') }}</th>
                                                    @elseif($invoice->invoice_module == 'childcare')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Name') }}</th>
                                                    @elseif(
                                                        $invoice->invoice_module == 'cardealership' ||
                                                            $invoice->invoice_module == 'sales' ||
                                                            $invoice->invoice_module == 'newspaper' ||
                                                            $invoice->invoice_module == 'mobileservice')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Items') }}</th>
                                                    @elseif($invoice->invoice_module == 'legalcase')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('PARTICULARS') }}</th>
                                                    @elseif($invoice->invoice_module == 'Fleet')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Distance') }}</th>
                                                    @elseif($invoice->invoice_module == 'RestaurantMenu')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Item Name') }}</th>
                                                    @endif
                                                    @if (
                                                        $invoice->invoice_module !== 'Fleet' &&
                                                            $invoice->invoice_module !== 'taskly' &&
                                                            $invoice->invoice_module !== 'childcare')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Quantity') }}</th>
                                                    @endif
                                                    <th class="text-white bg-primary text-uppercase">{{ __('Rate') }}
                                                    </th>
                                                    @if ($invoice->invoice_module != 'Fleet' && $invoice->invoice_module != 'childcare')
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Discount') }}</th>
                                                        <th class="text-white bg-primary text-uppercase">
                                                            {{ __('Tax') }}</th>
                                                    @endif
                                                    <th class="text-white bg-primary text-uppercase">
                                                        {{ __('Description') }}</th>
                                                    <th class="text-right text-white bg-primary text-uppercase"
                                                        width="12%">{{ __('Price') }}</th>
                                                </tr>
                                                @php
                                                    $totalQuantity = 0;
                                                    $totalRate = 0;
                                                    $totalTaxPrice = 0;
                                                    $totalDiscount = 0;
                                                    $commonSubtotal = 0;
                                                    $taxesData = [];
                                                    $TaxPrice_array = [];
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
                                                                    $taxesData[$taxe->name] =
                                                                        $taxesData[$taxe->name] + $taxDataPrice;
                                                                } else {
                                                                    $taxesData[$taxe->name] = $taxDataPrice;
                                                                }
                                                            }
                                                        }
                                                        else{
                                                            $taxes = \App\Models\Invoice::tax($iteam->tax);
                                                            $totalQuantity += $iteam->quantity;
                                                            $totalRate += $iteam->price;
                                                            $totalDiscount += $iteam->discount;

                                                            foreach ($taxes as $taxe) {
                                                                $taxDataPrice = \App\Models\Invoice::taxRate(
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
                                                        @if (
                                                            $invoice->invoice_module == 'account' ||
                                                                $invoice->invoice_module == 'machinerepair' ||
                                                                $invoice->invoice_module == 'musicinstitute' ||
                                                                $invoice->invoice_module == 'vehicleinspection')
                                                            <td>{{ !empty($iteam->product_type) ? Str::ucfirst($iteam->product_type) : '--' }}
                                                            </td>
                                                            <td>{{ !empty($iteam->product()) ? $iteam->product()->name : '' }}
                                                            </td>
                                                        @elseif ($invoice->invoice_module == 'taskly')
                                                            <td>{{ !empty($iteam->product()) ? $iteam->product()->title : '' }}
                                                            </td>
                                                        @elseif ($invoice->invoice_module == 'cmms' || $invoice->invoice_module == 'rent')
                                                            <td>{{ !empty($iteam->product_type) ? Str::ucfirst($iteam->product_type) : '--' }}
                                                            </td>
                                                            <td>{{ !empty($iteam->product()) ? $iteam->product()->name : '' }}
                                                            </td>
                                                        @elseif ($invoice->invoice_module == 'lms')
                                                            <td>{{ !empty($iteam->product()) ? $iteam->product()->title : '' }}
                                                            </td>
                                                        @elseif ($invoice->invoice_module == 'childcare' || $invoice->invoice_module == 'legalcase')
                                                            <td>{{ !empty($iteam->product_name) ? $iteam->product_name : '' }}
                                                            </td>
                                                        @elseif (
                                                            $invoice->invoice_module == 'cardealership' ||
                                                                $invoice->invoice_module == 'sales' ||
                                                                $invoice->invoice_module == 'newspaper' ||
                                                                $invoice->invoice_module == 'mobileservice')
                                                            <td>{{ !empty($iteam->product()) ? $iteam->product()->name : '' }}
                                                            </td>
                                                        @elseif ($invoice->invoice_module == 'RestaurantMenu')
                                                            <td>{{ !empty($iteam->product_name) ? $iteam->product_name : '' }}
                                                            </td>
                                                        @endif

                                                        @if ($invoice->invoice_module == 'Fleet')
                                                            <td>{{ !empty($iteam->product()) ? $iteam->product()->distance : 0 }}
                                                            </td>
                                                        @elseif($invoice->invoice_module == 'taskly' || $invoice->invoice_module == 'childcare')
                                                        @else
                                                            <td>{{ $iteam->quantity }}</td>
                                                        @endif

                                                        <td>{{ currency_format_with_sym($iteam->price) }}</td>
                                                        @if ($invoice->invoice_module != 'Fleet' && $invoice->invoice_module != 'childcare')
                                                            <td>{{ currency_format_with_sym($iteam->discount) }}</td>
                                                            <td>
                                                                @if (!empty($iteam->tax))
                                                                    <table class="w-100">
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
                                                                                <td>{{ $tax->name . ' (' . $tax->rate . '%)' }}
                                                                                    {{ currency_format_with_sym($taxPrice) }}
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
                                                        @endif
                                                        <td>{{ !empty($iteam->description) ? $iteam->description : '-' }}
                                                        </td>
                                                        @php
                                                            $tr_tex =
                                                                array_key_exists($key, $TaxPrice_array) == true
                                                                    ? $TaxPrice_array[$key]
                                                                    : 0;
                                                        @endphp
                                                        <td class="">
                                                            @if ($invoice->invoice_module == 'childcare')
                                                                {{ currency_format_with_sym($iteam->price) }}
                                                            @elseif ($invoice->invoice_module == 'Fleet')
                                                                @php
                                                                    $distance = !empty($iteam->product())
                                                                        ? $iteam->product()->distance
                                                                        : 0;
                                                                    $price =
                                                                        $iteam->price * $iteam->product()->distance;
                                                                @endphp
                                                                {{ currency_format_with_sym($price) }}
                                                            @else
                                                                {{ currency_format_with_sym($iteam->price * $iteam->quantity - $iteam->discount + $tr_tex) }}
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

                                                        @if (
                                                            $invoice->invoice_module == 'cmms' ||
                                                                $invoice->invoice_module == 'rent' ||
                                                                $invoice->invoice_module == 'vehicleinspection' ||
                                                                $invoice->invoice_module == 'musicinstitute' ||
                                                                $invoice->invoice_module == 'machinerepair')
                                                            <td></td>
                                                            <td class="bg-color "><b>{{ __('Total') }}</b></td>
                                                        @else
                                                            <td class="bg-color "><b>{{ __('Total') }}</b></td>
                                                        @endif

                                                        @if ($invoice->invoice_module == 'Fleet')
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($totalRate) }}</b></td>
                                                        @elseif($invoice->invoice_module == 'taskly')
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($totalRate) }}</b></td>
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($totalDiscount) }}</b></td>
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($totalTaxPrice) }}</b></td>
                                                        @elseif($invoice->invoice_module == 'cmms')
                                                            <td class="bg-color"><b>{{ $totalQuantity }}</b></td>
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($totalRate) }}</b></td>
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($totalDiscount) }}</b></td>
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($totalTaxPrice) }}</b></td>
                                                        @elseif($invoice->invoice_module == 'childcare')
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($commonSubtotal) }}</b>
                                                            </td>
                                                        @else
                                                            <td class="bg-color"><b>{{ $totalQuantity }}</b></td>
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($totalRate) }}</b></td>
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($totalDiscount) }}</b></td>
                                                            <td class="bg-color">
                                                                <b>{{ currency_format_with_sym($totalTaxPrice) }}</b></td>
                                                        @endif
                                                    </tr>

                                                    @php
                                                        $colspan = 6;
                                                        $customerInvoices = ['taskly', 'account', 'cmms', 'cardealership', 'RestaurantMenu', 'rent' , 'Fleet','vehicleinspection','machinerepair'];
                                                        if (in_array($invoice->invoice_module, $customerInvoices)) {
                                                            $colspan = 7;
                                                        }

                                                        if ($invoice->invoice_module == 'taskly') {
                                                            $colspan = 5;
                                                        }

                                                        if (
                                                            $invoice->invoice_module == 'Fleet' || $invoice->invoice_module == 'childcare'
                                                        ) {
                                                            $colspan = 3;
                                                        }
                                                    @endphp

                                                    @if ($invoice->invoice_module != 'Fleet')
                                                        <tr>
                                                            <td colspan="{{ $colspan }}"></td>
                                                            <td class="text-right">{{ __('Sub Total') }}</td>
                                                            <td class="text-right">
                                                                @if ($invoice->invoice_module == 'childcare')
                                                                    <b>{{ currency_format_with_sym($commonSubtotal) }}</b>
                                                                @else
                                                                    <b>{{ currency_format_with_sym($invoice->getSubTotal()) }}</b>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @if($invoice->invoice_module != 'childcare')
                                                            <tr>
                                                                <td colspan="{{ $colspan }}"></td>
                                                                <td class="text-right">{{ __('Discount') }}</td>
                                                                <td class="text-right">
                                                                    <b>{{ currency_format_with_sym($invoice->getTotalDiscount()) }}</b>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endif

                                                    @if (!empty($taxesData))
                                                        @foreach ($taxesData as $taxName => $taxPrice)
                                                            <tr>
                                                                <td colspan="{{ $colspan }}"></td>
                                                                <td class="text-right">{{ $taxName }}</td>
                                                                <td class="text-right">
                                                                    <b>{{ currency_format_with_sym($taxPrice) }}</b></td>
                                                            </tr>
                                                        @endforeach
                                                    @endif

                                                    @if($invoice->invoice_module == 'machinerepair' || $invoice->invoice_module == 'mobileservice' || $invoice->invoice_module == 'vehicleinspection')
                                                        <tr>
                                                            <td colspan="{{ $colspan }}"></td>
                                                            <td class="text-right">{{ __('Service Charge') }}</td>
                                                            <td class="text-right">
                                                                <b>{{ currency_format_with_sym($invoice->category_id) }}</b></td>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="blue-text text-right">{{ __('Total') }}</td>
                                                        <td class="blue-text text-right">
                                                            @if ($invoice->invoice_module == 'childcare')
                                                                <b>{{ currency_format_with_sym($commonSubtotal) }}</b>
                                                            @elseif ($invoice->invoice_module == 'Fleet')
                                                                <b>{{ currency_format_with_sym($invoice->getFleetSubTotal()) }}</b>
                                                            @else
                                                                <b>{{ currency_format_with_sym($invoice->getTotal()) }}</b>
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Paid') }}</td>
                                                        <td class="text-right">
                                                            <b>{{ currency_format_with_sym($invoice->getTotal() - $invoice->getDue() - $invoice->invoiceTotalCreditNote()) }}</b>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Credit Note Applied') }}</td>
                                                        <td class="text-right">
                                                            <b>{{ currency_format_with_sym($invoice->invoiceTotalCreditNote()) }}</b>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Credit note issued') }}</td>
                                                        <td class="text-right">
                                                            <b>{{ currency_format_with_sym($invoice->invoiceTotalCustomerCreditNote()) }}</b>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td colspan="{{ $colspan }}"></td>
                                                        <td class="text-right">{{ __('Due') }}</td>
                                                        <td class="text-right">
                                                            <b>{{ currency_format_with_sym($invoice->getDue()) }}</b></td>
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

                <div class="tab-pane fade" id="receipt-summary" role="tabpanel" aria-labelledby="pills-user-tab-2">
                    {{-- <h5 class="h4 d-inline-block font-weight-400 my-2">{{ __('Receipt Summary') }}</h5> --}}
                    <div class="card">
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table mb-0 pc-dt-simple" id="invoice-receipt-summary">
                                    <thead>
                                        <tr>
                                            <th class="text-dark">{{ __('Date') }}</th>
                                            <th class="text-dark">{{ __('Amount') }}</th>
                                            <th class="text-dark">{{ __('Payment Type') }}</th>
                                            <th class="text-dark">{{ __('Account') }}</th>
                                            <th class="text-dark">{{ __('Reference') }}</th>
                                            <th class="text-dark">{{ __('Description') }}</th>
                                            <th class="text-dark">{{ __('Receipt') }}</th>
                                            <th class="text-dark">{{ __('OrderId') }}</th>
                                            @permission('invoice payment delete')
                                                <th class="text-dark">{{ __('Action') }}</th>
                                            @endpermission
                                        </tr>
                                    </thead>
                                    @if (!empty($invoice->payments) || !empty($bank_transfer_payments))
                                        @foreach ($bank_transfer_payments as $bank_transfer_payment)
                                            <tr>
                                                <td>{{ company_datetime_formate($bank_transfer_payment->created_at) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ currency_format_with_sym($bank_transfer_payment->price) }}</td>
                                                <td>{{ $bank_transfer_payment->payment_type }}</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>
                                                    @if (!empty($bank_transfer_payment->attachment))
                                                        <a href="{{ get_file($bank_transfer_payment->attachment) }}"
                                                            target="_blank"> <i class="ti ti-file"></i></a>
                                                    @else
                                                        --
                                                    @endif
                                                </td>
                                                <td>{{ $bank_transfer_payment->order_id }}</td>
                                                <td>
                                                    <div class="action-btn me-2">
                                                        @if ($bank_transfer_payment->payment_type == 'Bank Transfer')
                                                            <a class="mx-3 btn btn-sm  align-items-center bg-primary"
                                                                data-url="{{ route('invoice.bank.request.edit', $bank_transfer_payment->id) }}"
                                                                data-ajax-popup="true" data-size="md"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-title="{{ __('Request Action') }}"
                                                                data-bs-original-title="{{ __('Action') }}">
                                                                <i class="ti ti-caret-right text-white"></i>
                                                            </a>
                                                        @elseif($bank_transfer_payment->payment_type == 'Bank Account')
                                                            <a class="mx-3 btn btn-sm  align-items-center bg-primary"
                                                                data-url="{{ route('invoice.bankaccount.request.edit', $bank_transfer_payment->id) }}"
                                                                data-ajax-popup="true" data-size="md"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-title="{{ __('Request Action') }}"
                                                                data-bs-original-title="{{ __('Action') }}">
                                                                <i class="ti ti-caret-right text-white"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                    <div class="action-btn">
                                                        @if ($bank_transfer_payment->payment_type == 'Bank Transfer')
                                                            {{ Form::open(['route' => ['bank-transfer-request.destroy', $bank_transfer_payment->id], 'class' => 'm-0']) }}
                                                            @method('DELETE')
                                                            <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $bank_transfer_payment->id }}"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            {{ Form::close() }}
                                                        @elseif($bank_transfer_payment->payment_type == 'Bank Account')
                                                            {{ Form::open(['route' => ['invoice.bankaccount.request.destroy', $bank_transfer_payment->id], 'class' => 'm-0']) }}
                                                            @method('DELETE')
                                                            <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $bank_transfer_payment->id }}"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            {{ Form::close() }}
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @foreach ($invoice->payments as $key => $payment)
                                            <tr>
                                                <td>{{ company_date_formate($payment->date) }}</td>
                                                <td>{{ currency_format_with_sym($payment->amount) }}</td>
                                                <td>{{ $payment->payment_type }}</td>
                                                @if (module_is_active('Account'))
                                                    <td>{{ !empty($payment->bankAccount) ? $payment->bankAccount->bank_name . ' ' . $payment->bankAccount->holder_name : '--' }}
                                                    </td>
                                                @else
                                                    <td>--</td>
                                                @endif

                                                <td>{{ !empty($payment->reference) ? $payment->reference : '--' }}</td>
                                                <td>{{ !empty($payment->description) ? $payment->description : '--' }}
                                                </td>
                                                <td>
                                                    @if (!empty($payment->add_receipt) && empty($payment->receipt) && check_file($payment->add_receipt))
                                                        <div class="d-flex">
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
                                                                    data-bs-toggle="tooltip" title="{{ __('Show') }}"
                                                                    target="_blank">
                                                                    <i class="ti ti-crosshair text-white"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @elseif (!empty($payment->receipt) && empty($payment->add_receipt) && $payment->payment_type == 'Stripe')
                                                        <a href="{{ $payment->receipt }}" target="_blank"><i
                                                                class="ti ti-file"></i></a>
                                                    @elseif($payment->payment_type == 'Bank Transfer' || $payment->payment_type == 'Bank Account')
                                                        <a href="{{ !empty($payment->receipt) ? (check_file($payment->receipt) ? get_file($payment->receipt) : '#!') : '#!' }}"
                                                            target="_blank"><i class="ti ti-file"></i></a>
                                                    @else
                                                        --
                                                    @endif
                                                </td>
                                                <td>{{ !empty($payment->order_id) ? $payment->order_id : '--' }}</td>
                                                @permission('invoice payment delete')
                                                    <td>
                                                        <div class="action-btn">
                                                            {{ Form::open(['route' => ['invoice.payment.destroy', $invoice->id, $payment->id], 'class' => 'm-0']) }}
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
                                                    </td>
                                                @endpermission
                                            </tr>
                                        @endforeach
                                    @else
                                        @include('layouts.nodatafound')
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="credit-summary" role="tabpanel" aria-labelledby="pills-user-tab-3">
                    @if (module_is_active('Account'))
                        @include('account::invoice.invoice_section')
                    @endif
                </div>
                <div class="tab-pane fade" id="invoice-attechment" role="tabpanel" aria-labelledby="pills-user-tab-4">
                    <div class="row">
                        {{-- <h5 class="d-inline-block my-3">{{ __('Attachments') }}</h5> --}}
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
                                            @forelse($invoice_attachment as $key =>$attachment)
                                                <td>{{ ++$key }}</td>
                                                <td>{{ $attachment->file_name }}</td>
                                                <td>{{ $attachment->file_size }}</td>
                                                <td>{{ company_date_formate($attachment->created_at) }}</td>
                                                <td>
                                                    <div class="action-btn me-2">
                                                        <a href="{{ url($attachment->file_path) }}"
                                                            data-bs-toggle="tooltip"
                                                            class="mx-3 btn btn-sm align-items-center bg-primary"
                                                            title="{{ __('Download') }}" target="_blank" download>
                                                            <i class="ti ti-download text-white"></i>
                                                        </a>
                                                    </div>
                                                    <div class="action-btn">
                                                        {{ Form::open(['route' => ['invoice.attachment.destroy', $attachment->id], 'class' => 'm-0']) }}
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
                                    <th class="text-dark">{{ __('Date') }}</th>
                                    <th class="text-dark">{{ __('Amount') }}</th>
                                    <th class="text-dark">{{ __('Payment Type') }}</th>
                                    <th class="text-dark">{{ __('Account') }}</th>
                                    <th class="text-dark">{{ __('Reference') }}</th>
                                    <th class="text-dark">{{ __('Description') }}</th>
                                    <th class="text-dark">{{ __('Receipt') }}</th>
                                    <th class="text-dark">{{ __('OrderId') }}</th>
                                </tr>
                            </thead>
                            @if (!empty($invoice->payments) || !empty($bank_transfer_payments))
                                @foreach ($bank_transfer_payments as $bank_transfer_payment)
                                    <tr>
                                        <td>{{ company_datetime_formate($bank_transfer_payment->created_at) }}
                                        </td>
                                        <td class="text-right">
                                            {{ currency_format_with_sym($bank_transfer_payment->price) }}</td>
                                        <td>{{ $bank_transfer_payment->payment_type }}</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            @if (!empty($bank_transfer_payment->attachment))
                                                <a href="{{ get_file($bank_transfer_payment->attachment) }}"
                                                    target="_blank"> <i class="ti ti-file"></i></a>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>{{ $bank_transfer_payment->order_id }}</td>
                                    </tr>
                                @endforeach
                                @foreach ($invoice->payments as $key => $payment)
                                    <tr>
                                        <td>{{ company_date_formate($payment->date) }}</td>
                                        <td>{{ currency_format_with_sym($payment->amount) }}</td>
                                        <td>{{ $payment->payment_type }}</td>
                                        @if (module_is_active('Account'))
                                            <td>{{ !empty($payment->bankAccount) ? $payment->bankAccount->bank_name . ' ' . $payment->bankAccount->holder_name : '--' }}
                                            </td>
                                        @else
                                            <td>--</td>
                                        @endif

                                        <td>{{ !empty($payment->reference) ? $payment->reference : '--' }}</td>
                                        <td>{{ !empty($payment->description) ? $payment->description : '--' }}
                                        </td>
                                        <td>
                                            @if (!empty($payment->add_receipt) && empty($payment->receipt) && check_file($payment->add_receipt))
                                                <div class="d-flex">
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
                                                            data-bs-toggle="tooltip" title="{{ __('Show') }}"
                                                            target="_blank">
                                                            <i class="ti ti-crosshair text-white"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @elseif (!empty($payment->receipt) && empty($payment->add_receipt) && $payment->payment_type == 'Stripe')
                                                <a href="{{ $payment->receipt }}" target="_blank"><i
                                                        class="ti ti-file"></i></a>
                                            @elseif($payment->payment_type == 'Bank Transfer' || $payment->payment_type == 'Bank Account')
                                                <a href="{{ !empty($payment->receipt) ? (check_file($payment->receipt) ? get_file($payment->receipt) : '#!') : '#!' }}"
                                                    target="_blank"><i class="ti ti-file"></i></a>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>{{ !empty($payment->order_id) ? $payment->order_id : '--' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                @include('layouts.nodatafound')
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
