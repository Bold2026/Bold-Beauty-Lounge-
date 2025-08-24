@extends('layouts.main')
@section('page-title')
    {{ __('Manage Invoices') }}
@endsection
@section('page-breadcrumb')
    {{ __('Invoices') }}
@endsection
@section('page-action')
    <div class="d-flex">
        @stack('addButtonHook')
        @if (module_is_active('ProductService') && (module_is_active('Account') || module_is_active('Taskly')))
        @permission('category create')
            <a href="{{ route('category.index') }}"data-size="md" class="btn btn-sm btn-primary me-2"
                data-bs-toggle="tooltip"data-title="{{ __('Setup') }}" title="{{ __('Setup') }}"><i
                    class="ti ti-settings"></i></a>
        @endpermission
            @permission('invoice manage')
                <a href="{{ route('invoice.index') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('List View') }}"
                    class="btn btn-sm btn-primary btn-icon me-2">
                    <i class="ti ti-list"></i>
                </a>
                <a href="{{ route('invoice.status.view') }}"  data-bs-toggle="tooltip" data-bs-original-title="{{__('Quick Stats')}}" class="btn btn-sm btn-primary btn-icon me-2">
                    <i class="ti ti-filter"></i>
                </a>
            @endpermission
            @permission('invoice create')
                <a href="{{ route('invoice.create', 0) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i>
                </a>
            @endpermission
        @endif
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['invoice.grid.view'], 'method' => 'GET', 'id' => 'customer_submit']) }}
                    <div class="row d-flex align-items-center justify-content-end">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}
                                {{ Form::text('issue_date', isset($_GET['issue_date']) ? $_GET['issue_date'] : null, ['class' => 'form-control flatpickr-to-input', 'placeholder' => 'Select Date']) }}
                            </div>
                        </div>
                        @if (\Auth::user()->type != 'client')
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('customer', __('Customer'), ['class' => 'form-label']) }}
                                    {{ Form::select('customer', $customer, isset($_GET['customer']) ? $_GET['customer'] : '', ['class' => 'form-control select', 'placeholder' => 'Select Customer']) }}
                                </div>
                            </div>
                        @endif
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                {{ Form::select('status', ['' => 'Select Status'] + $status, isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end mt-4 d-flex">
                            <a href="#" class="btn btn-sm btn-primary me-2" onclick="document.getElementById('customer_submit').submit(); return false;" data-toggle="tooltip" data-original-title="{{ __('Apply') }}" title="{{ __('Apply') }}" data-bs-toggle="tooltip">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{ route('invoice.grid.view') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                data-original-title="{{ __('Reset') }}"  title="{{ __('Reset') }}">
                                <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="row mb-4 project-wrp d-flex">
                @isset($invoices)
                @foreach($invoices as $invoice)
                        <div class="col-xxl-3 col-xl-4 col-sm-6 col-12">
                            <div class="project-card">
                                <div class="project-card-inner">
                                    <div class="project-card-header d-flex justify-content-between">
                                        @if($invoice->status == 0)
                                            <span class="badge bg-info p-2 px-3">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 1)
                                            <span class="badge bg-primary p-2 px-3">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 2)
                                            <span class="badge bg-secondary p-2 px-3">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 3)
                                            <span class="badge bg-warning p-2 px-3">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 4)
                                            <span class="badge bg-success p-2 px-3">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 5)
                                            <span class="badge bg-success p-2 px-3">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @endif
                                        <button type="button"
                                            class="btn btn-light dropdown-toggle d-flex align-items-center justify-content-center"
                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="ti ti-dots-vertical text-black"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-end pointer">
                                            <a href="#"  data-link="{{route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))}}" class="dropdown-item cp_link" >
                                                <i class="ti ti-file me-1"></i> {{__('Click To Copy Invoice Link')}}
                                            </a>
                                            @permission('invoice duplicate')
                                            {!! Form::open([
                                                'method' => 'get',
                                                'route' => ['invoice.duplicate', $invoice->id],
                                                'id' => 'duplicate-form-' . $invoice->id,
                                            ]) !!}
                                                    <a href="#!" class="show_confirm dropdown-item" data-text="{{ __('You want to confirm duplicate this invoice. Press Yes to continue or No to go back') }}"
                                                    data-confirm-yes="duplicate-form-{{ $invoice->id }}">
                                                    <i class="ti ti-copy me-1"></i> {{ __('Duplicate') }}
                                                    </a>
                                                {!! Form::close() !!}
                                            @endpermission
                                            @if(module_is_active('EInvoice'))
                                                @permission('download invoice')
                                                    @include('einvoice::download.grid_generate_invoice',['invoice_id'=>$invoice->id])
                                                @endpermission
                                            @endif
                                            <a href="#" class="dropdown-item"
                                                data-url="{{ route('delivery-form.pdf', \Crypt::encrypt($invoice->id)) }}" data-ajax-popup="true"
                                                data-size="lg" data-title="{{ __('Invoice Delivery Form') }}">
                                                <i class="ti ti-clipboard-list me-1"></i> {{__('Invoice Delivery Form')}}
                                            </a>
                                            @permission('invoice show')
                                                <a href="{{route('invoice.show',\Crypt::encrypt($invoice->id))}}" class="dropdown-item" data-toggle="tooltip" data-original-title="{{__('View')}}">
                                                    <i class="ti ti-eye me-1"></i> {{__('View')}}
                                                </a>
                                            @endpermission
                                            @if (module_is_active('ProductService') && $invoice->invoice_module == 'taskly' ? module_is_active('Taskly') : module_is_active('Account'))
                                                @permission('invoice edit')
                                                    <a href="{{ route('invoice.edit', \Crypt::encrypt($invoice->id)) }}" class="dropdown-item" >
                                                        <i class="ti ti-pencil me-1 "></i> {{__('Edit')}}
                                                    </a>
                                                @endpermission
                                            @endif
                                            @permission('invoice delete')
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id]]) !!}
                                                    <a href="#!" class="show_confirm dropdown-item text-danger">
                                                        <i class="ti ti-trash me-1 "></i> {{ __('Delete') }}
                                                    </a>
                                                {!! Form::close() !!}
                                            @endpermission
                                        </div>
                                    </div>
                                    <div class="project-card-content">
                                        <div class="project-content-top">
                                            <div class="user-info  d-flex align-items-center">
                                                @if (Laratrust::hasPermission('invoice show'))
                                                    <a href="{{route('invoice.show',\Crypt::encrypt($invoice->id))}}">{{ App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id) }}</a>
                                                @else
                                                    <a href="#">{{ App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id) }}</a>
                                                @endif
                                            </div>
                                            <div class="row align-items-center mt-3">
                                                <div class="col-6">
                                                    <h6 class="mb-0 text-break">{{currency_format_with_sym($invoice->getTotal())}}</h6>
                                                    <span class="text-sm text-muted">{{__('Total Amount')}}</span>
                                                </div>
                                                <div class="col-6">
                                                    <h6 class="mb-0 text-break">{{currency_format_with_sym($invoice->getDue())}}</h6>
                                                    <span class="text-sm text-muted">{{__('Due Amount')}}</span>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mt-3">
                                                <div class="col-6">
                                                    <h6 class="mb-0 text-break">{{company_date_formate($invoice->issue_date)}}</h6>
                                                    <span class="text-sm text-muted">{{__('Issue Date')}}</span>
                                                </div>
                                                <div class="col-6">
                                                    <h6 class="mb-0 text-break">{{company_date_formate($invoice->due_date)}}</h6>
                                                    <span class="text-sm text-muted">{{__('Due Date')}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="project-content-bottom d-flex align-items-center justify-content-between gap-2">
                                            <div class="d-flex align-items-center gap-2 user-image">
                                                @if (\Auth::user()->type != 'client')
                                                    @if (!empty($invoice->customers))
                                                        <div class="user-group pt-2">
                                                                <img alt="image" data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="{{ $invoice->customers->name }}"
                                                                    @if ($invoice->customers->avatar) src="{{ get_file($invoice->customers->avatar) }}" @else src="{{ get_file('avatar.png') }}" @endif
                                                                    class="rounded-circle " width="25" height="25">
                                                        </div>
                                                    @endif
                                            @endif
                                            </div>
                                            <div class="comment d-flex align-items-center gap-2">
                                                @permission('invoice show')
                                                    <a class="btn btn-sm btn-warning" href="{{route('invoice.show',\Crypt::encrypt($invoice->id))}}" class="dropdown-item" data-toggle="tooltip" data-original-title="{{__('View')}}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                @endpermission
                                                @permission('invoice duplicate')
                                                {!! Form::open([
                                                    'method' => 'get',
                                                    'route' => ['invoice.duplicate', $invoice->id],
                                                    'id' => 'duplicate-form-' . $invoice->id,
                                                ]) !!}
                                                        <a href="#!" class="show_confirm btn btn-sm bg-secondary" data-text="{{ __('You want to confirm duplicate this invoice. Press Yes to continue or No to go back') }}"
                                                        data-confirm-yes="duplicate-form-{{ $invoice->id }}">
                                                        <i class="ti ti-copy text-white"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                @endpermission
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endisset
                @auth('web')
                    @permission('invoice create')
                        <div class="col-xxl-3 col-xl-4 col-sm-6 col-12">
                            <div class="project-card-inner">
                                <a href="{{ route('invoice.create', 0) }}" class="btn-addnew-project " data-size="md"
                                    data-title="{{ __('Create New Invoice') }}">
                                    <div class="badge bg-primary proj-add-icon">
                                        <i class="ti ti-plus"></i>
                                    </div>
                                    <h6 class="my-2 text-center">{{ __('New Invoice') }}</h6>
                                    <p class="text-muted text-center">{{ __('Click here to add New Invoice') }}</p>
                                </a>
                            </div>
                        </div>
                    @endpermission
                @endauth
            </div>
        </div>
        {!! $invoices->links('vendor.pagination.global-pagination') !!}
    </div>
@endsection
@push('scripts')
    <script>
        $(document).on("click",".cp_link",function() {
            var value = $(this).attr('data-link');
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val(value).select();
                document.execCommand("copy");
                $temp.remove();
                toastrs('success', '{{__('Link Copy on Clipboard')}}', 'success')
        });
    </script>
@endpush
