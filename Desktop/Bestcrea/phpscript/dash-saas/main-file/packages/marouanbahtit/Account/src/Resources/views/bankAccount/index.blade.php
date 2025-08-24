@extends('layouts.main')
@section('page-title')
    {{ __('Manage Bank Account') }}
@endsection

@section('page-breadcrumb')
{{ __('Bank Account') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
@endpush

@section('page-action')
<div class="d-flex">
    @stack('addButtonHook')
    @permission('bank account create')
        <a  class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Bank Account') }}" data-url="{{route('bank-account.create')}}" data-bs-toggle="tooltip"  data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection
@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
