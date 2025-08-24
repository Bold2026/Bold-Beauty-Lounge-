@extends('layouts.main')
@php
    if(Auth::user()->type=='super admin')
    {
        $plural_name = __('Customers');
        $singular_name = __('Customer');
    }
    else{

        $plural_name =__('Users');
        $singular_name =__('User');
    }
@endphp
@section('page-title')
{{$plural_name}}
@endsection
@section('page-breadcrumb')
{{ $plural_name}}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
<div class="d-flex">
    @permission('user logs history')
        <a href="{{ route('users.userlog.history') }}" class="btn btn-sm btn-primary me-2"
                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('User Logs History') }}"><i class="ti ti-user-check"></i>
        </a>
    @endpermission

    @permission('user import')
        <a href="#" class="btn btn-sm btn-primary me-2" data-ajax-popup="true" data-title="{{ __('Import') }}"
            data-url="{{ route('users.file.import') }}" data-toggle="tooltip" title="{{ __('Import') }}"><i
                class="ti ti-file-import"></i>
        </a>
    @endpermission
    @permission('user manage')
        <a href="{{ route('users.index') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Grid View') }}"
            class="btn btn-sm btn-primary btn-icon me-2">
            <i class="ti ti-layout-grid"></i>
        </a>
    @endpermission
    @permission('user create')
        <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create New '.($singular_name)) }}"  data-url="{{ route('users.create') }}" data-bs-toggle="tooltip"  data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection
@section('content')
    <!-- [ Main Content ] start -->
        <div class="row">
            @if (\Auth::user()->type != 'super admin')
                <div class="" id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(['route' => ['users.index'], 'method' => 'GET', 'id' => 'user_submit']) }}
                            <div class="row d-flex align-items-center justify-content-end">
                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                        {{ Form::text('name', isset($_GET['name']) ? $_GET['name'] : null, ['class' => 'form-control','placeholder' => __('Enter Name')]) }}

                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                                        {{ Form::text('email', isset($_GET['email']) ? $_GET['email'] : null, ['class' => 'form-control', 'placeholder' => __('Enter Email')]) }}
                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('role', __('Role'), ['class' => 'form-label']) }}
                                        {{ Form::select('role', $roles, isset($_GET['role']) ? $_GET['role'] : '', ['class' => 'form-control select', 'placeholder' => __('All')]) }}
                                    </div>
                                </div>
                                <div class="col-auto float-end mt-4 d-flex">
                                    <a href="#!" class="btn btn-sm btn-primary me-2"
                                        id="applyfilter"
                                        data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                        data-original-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('users.list.view') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                        title="" data-bs-original-title={{ __('Reset') }}>
                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                    </a>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            {{ $dataTable->table(['width' => '100%']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <!-- [ Main Content ] end -->
@endsection
@push('scripts')
@include('layouts.includes.datatable-js')
{{ $dataTable->scripts() }}
<script>
    $(document).on('change', '#password_switch', function() {
        if($(this).is(':checked'))
        {
            $('.ps_div').removeClass('d-none');
            $('#password').attr("required",true);

        } else {
            $('.ps_div').addClass('d-none');
            $('#password').val(null);
            $('#password').removeAttr("required");
        }
    });
    $(document).on('click', '.login_enable', function() {
        setTimeout(function() {
             $('.modal-body').append($('<input>', {type: 'hidden',val: 'true',name: 'login_enable' }));
        }, 1000);
    });
</script>

@endpush
