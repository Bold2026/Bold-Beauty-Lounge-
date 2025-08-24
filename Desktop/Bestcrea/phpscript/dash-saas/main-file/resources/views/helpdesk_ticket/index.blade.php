@extends('layouts.main')

@section('page-title')
    {{ __('Manage Tickets') }}
@endsection
@section('page-breadcrumb')
    {{ __('Tickets') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
    <div class="row">
        <div class="col-auto pe-0">
            <select class="form-select" id="projects" style="width: 121px;">
                <option value="">{{ __('All Tickets') }}</option>
                <option value="in-progress">{{ __('In Progress') }}</option>
                <option value="on-hold">{{ __('On Hold') }}</option>
                <option value="closed">{{ __('Closed') }}</option>
            </select>
        </div>
        <div class="col-auto ps-3 mt-1">
            @permission('helpdesk ticket create')
                <a href="{{ route('helpdesk.create') }}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip"
                    data-bs-placement="top" title="{{ __('Create') }}"><i class="ti ti-plus text-white"></i></a>
            @endpermission
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            @if (session()->has('ticket_id') || session()->has('smtp_error'))
                <div class="alert alert-info bg-pr">
                    @if (session()->has('ticket_id'))
                        {!! Session::get('ticket_id') !!}
                        {{ Session::forget('ticket_id') }}
                    @endif
                    @if (session()->has('smtp_error'))
                        {!! Session::get('smtp_error') !!}
                        {{ Session::forget('smtp_error') }}
                    @endif
                </div>
            @endif
        </div>
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
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
@endpush
