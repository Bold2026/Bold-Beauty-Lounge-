@extends('layouts.main')
@section('page-title')
    {{ __('Manage Bills') }}
@endsection
@section('page-breadcrumb')
    {{ __('Bill') }}
@endsection
@section('page-action')
    <div class="d-flex">
        @stack('addButtonHook')
        <a href="{{ route('bill.index') }}" class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip"
            title="{{ __('List View') }}">
            <i class="ti ti-list text-white"></i>
        </a>
        @if (module_is_active('ProductService'))
            @permission('bill create')
                <a href="{{ route('category.index') }}"data-size="md" class="btn btn-sm btn-primary me-2"
                    data-bs-toggle="tooltip"data-title="{{ __('Setup') }}" title="{{ __('Setup') }}"><i
                        class="ti ti-settings"></i></a>

                <a href="{{ route('bills.create', 0) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i>
                </a>
            @endpermission
        @endif
    </div>
@endsection
@push('css')
    <style>
        .bill_status {
            min-width: 94px;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="mt-2" id="multiCollapseExample1">

            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['bill.grid'], 'method' => 'GET', 'id' => 'frm_submit']) }}
                    <div class="row d-flex align-items-center justify-content-end">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 month">
                            <div class="btn-box">
                                {{ Form::label('bill_date', __('Date'), ['class' => 'form-label']) }}
                                {{ Form::date('bill_date', isset($_GET['bill_date']) ? $_GET['bill_date'] : null, ['class' => 'form-control form-control flatpickr-to-input', 'placeholder' => 'Select Date']) }}
                            </div>
                        </div>
                        @if (\Auth::user()->type != 'vendor')
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 date">
                                <div class="btn-box">
                                    {{ Form::label('vendor', __('Vendor'), ['class' => 'form-label']) }}
                                    {{ Form::select('vendor', $vendor, isset($_GET['vendor']) ? $_GET['vendor'] : '', ['class' => 'form-control ', 'placeholder' => 'Select Vendor']) }}
                                </div>
                            </div>
                        @endif
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                {{ Form::select('status', $status, isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control ', 'placeholder' => 'Select Status']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end mt-4">
                            <a class="btn btn-sm btn-primary me-1"
                                onclick="document.getElementById('frm_submit').submit(); return false;"
                                data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                data-original-title="{{ __('apply') }}">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{ route('bill.grid') }}" class="btn btn-sm btn-danger"
                                data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                data-original-title="{{ __('Reset') }}">
                                <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                            </a>
                        </div>
                    </div>


                    {{ Form::close() }}
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="row mb-4 project-wrp d-flex">
                @isset($bills)
                    @foreach ($bills as $bill)
                        <div class="col-xxl-3 col-xl-4 col-sm-6 col-12 ">
                            <div class="project-card">
                                <div class="project-card-inner">
                                    <div class="project-card-header d-flex justify-content-between h-100">

                                        @if ($bill->status == 0)
                                            <span
                                                class="badge bg-info p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 1)
                                            <span
                                                class="badge bg-primary p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 2)
                                            <span
                                                class="badge bg-secondary p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 3)
                                            <span
                                                class="badge bg-warning p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                        @elseif($bill->status == 4)
                                            <span
                                                class="badge bg-success p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                        @endif
                                        <button type="button"
                                            class="btn btn-light dropdown-toggle d-flex align-items-center justify-content-center"
                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="ti ti-dots-vertical text-black"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-end pointer">
                                            <a href="#"
                                                data-link="{{ route('pay.billpay', \Illuminate\Support\Facades\Crypt::encrypt($bill->id)) }}"
                                                class="dropdown-item cp_link">
                                                <i class="ti ti-file me-1"></i> {{ __('Click to copy bill link') }}
                                            </a>
                                        @if($bill->status != 4)
                                            @permission('bill edit')
                                                <a
                                                    href="{{ route('bill.edit', \Crypt::encrypt($bill->id)) }}"class="dropdown-item">
                                                    <i class="ti ti-pencil me-1"></i> {{ __('Edit') }}
                                                </a>
                                            @endpermission
                                            @permission('bill delete')
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['bill.destroy', $bill->id]]) !!}
                                                <a href="#!" class="show_confirm dropdown-item">
                                                    <span class="text-danger"><i class="ti ti-trash me-1"></i> {{ __('Delete') }}</span>
                                                </a>
                                                {!! Form::close() !!}
                                            @endpermission
                                        @endif
                                        </div>
                                    </div>
                                    <div class="project-card-content">
                                        <div class="project-content-top">
                                            <div class="user-info  d-flex align-items-center">
                                                @if (Laratrust::hasPermission('bill show'))
                                                    <a
                                                        href="{{ route('bill.show', \Crypt::encrypt($bill->id)) }}">{{ Workdo\Account\Entities\Bill::billNumberFormat($bill->bill_id) }}</a>
                                                @else
                                                    <a>{{ Workdo\Account\Entities\Bill::billNumberFormat($bill->bill_id) }}</a>
                                                @endif
                                            </div>
                                            <div class="row align-items-center mt-3">
                                                <div class="col-6">
                                                    <h6 class="mb-0 text-break">{{ currency_format_with_sym($bill->getTotal()) }}</h6>
                                                    <span class="text-sm text-muted">{{ __('Total Amount') }}</span>
                                                </div>
                                                <div class="col-6">
                                                    <h6 class="mb-0 text-break">{{ currency_format_with_sym($bill->getDue()) }}</h6>
                                                    <span class="text-sm text-muted">{{ __('Due Amount') }}</span>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mt-3">
                                                <div class="col-6">
                                                    <h6 class="mb-0 text-break">{{ company_date_formate($bill->bill_date) }}</h6>
                                                    <span class="text-sm text-muted">{{ __('Issue Date') }}</span>
                                                </div>
                                                <div class="col-6">
                                                    <h6 class="mb-0 text-break">{{ company_date_formate($bill->due_date) }}</h6>
                                                    <span class="text-sm text-muted">{{ __('Due Date') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="project-content-bottom d-flex align-items-center justify-content-between gap-2">
                                            <div class="d-flex align-items-center gap-2 user-image">
                                                @if (\Auth::user()->type != 'vendor')
                                                    <div class="user-group">
                                                        <img alt="image" data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ !empty($bill->vendor_name) ? $bill->vendor_name : '' }}"
                                                            @if (!empty($bill->avatar) ? $bill->avatar : '') src="{{ get_file(!empty($bill->avatar) ? $bill->avatar : '') }}" @else src="{{ 'uploads/users-avatar/avatar.png' }}" @endif
                                                            class="rounded-circle " width="25" height="25">
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="comment d-flex align-items-center gap-2">
                                                @permission('bill show')
                                                    <a class="btn btn-sm btn-warning" href="{{ route('bill.show', \Crypt::encrypt($bill->id)) }}"
                                                class="dropdown-item" data-toggle="tooltip" title="{{__('View')}}"
                                                        data-original-title="{{ __('View') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                @endpermission
                                                @permission('bill duplicate')
                                                    {!! Form::open([
                                                        'method' => 'get',
                                                        'route' => ['bill.duplicate', $bill->id],
                                                        'id' => 'duplicate-form-' . $bill->id,
                                                    ]) !!}
                                                    <a href="#!" class="show_confirm btn btn-sm bg-secondary"
                                                    data-text="{{ __('You want to confirm duplicate this bill. Press Yes to continue or Cancel to go back') }}" title="{{__('Duplicate')}}" data-toggle="tooltip"
                                                        data-confirm-yes="duplicate-form-{{ $bill->id }}">
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
                    @permission('bill create')
                        <div class="col-xxl-3 col-xl-4 col-sm-6 col-12 ">
                            <div class="project-card-inner">
                                <a href="{{ route('bills.create', 0) }}" class="btn-addnew-project " data-size="md"
                                    data-title="{{ __('Create New Bill') }}">
                                    <div class="badge bg-primary proj-add-icon">
                                        <i class="ti ti-plus"></i>
                                    </div>
                                    <h6 class="my-2 text-center">{{ __('New Bill') }}</h6>
                                    <p class="text-muted text-center">{{ __('Click here to add New Bill') }}</p>
                                </a>
                            </div>
                        </div>
                    @endpermission
                @endauth
            </div>
        </div>
        {!! $bills->links('vendor.pagination.global-pagination') !!}
    </div>
@endsection
@push('scripts')
    <script>
        $(document).on("click", ".cp_link", function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('success', '{{ __('Link Copy on Clipboard') }}', 'success')
        });
    </script>
@endpush
