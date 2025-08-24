@extends('layouts.main')
@section('page-title')
    {{ __('Manage Proposal') }}
@endsection
@section('page-breadcrumb')
    {{ __('Proposal') }}
@endsection
@section('page-action')
    <div class="d-flex">
        @stack('addButtonHook')
        @if (module_is_active('ProductService'))
            @permission('category create')
                <a href="{{ route('category.index') }}"data-size="md" class="btn btn-sm btn-primary me-2"
                    data-bs-toggle="tooltip"data-title="{{ __('Setup') }}" title="{{ __('Setup') }}"><i
                        class="ti ti-settings"></i></a>
            @endpermission
        @endif
        @if ((module_is_active('ProductService') && module_is_active('Account')) || module_is_active('Taskly'))
            @permission('proposal manage')
                <a href="{{ route('proposal.grid.view') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Grid View') }}"
                    class="btn btn-sm btn-primary btn-icon me-2">
                    <i class="ti ti-layout-grid"></i>
                </a>
                <a href="{{ route('proposal.stats.view') }}" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Quick Stats') }}" class="btn btn-sm btn-primary btn-icon me-2">
                    <i class="ti ti-filter"></i>
                </a>
            @endpermission
            @permission('proposal create')
                <a href="{{ route('proposal.create', 0) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i>
                </a>
            @endpermission
        @endif

    </div>
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('content')
    <div class="row">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['proposal.index'], 'method' => 'GET', 'id' => 'frm_submit']) }}
                    <div class="row d-flex align-items-center justify-content-end">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('issue_date', __('Date'), ['class' => 'text-type']) }}
                                {{ Form::text('issue_date', isset($_GET['issue_date']) ? $_GET['issue_date'] : null, ['class' => 'form-control flatpickr-to-input', 'placeholder' => 'Select Date']) }}
                            </div>
                        </div>
                        @if (\Auth::user()->type != 'client')
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('customer', __('Customer'), ['class' => 'text-type']) }}
                                    {{ Form::select('customer', $customer, isset($_GET['customer']) ? $_GET['customer'] : '', ['class' => 'form-control', 'placeholder' => 'Select Client']) }}
                                </div>
                            </div>
                        @endif
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('status', __('Status'), ['class' => 'text-type']) }}
                                {{ Form::select('status', ['' => 'Select Status'] + $status, isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end mt-4 d-flex">
                            <a class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip"
                                title="{{ __('Apply') }}" id="applyfilter"
                                data-original-title="{{ __('apply') }}">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="#!" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                title="{{ __('Reset') }}" id="clearfilter"
                                data-original-title="{{ __('Reset') }}">
                                <span class="btn-inner--icon"><i
                                        class="ti ti-trash-off text-white-off"></i></span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
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
