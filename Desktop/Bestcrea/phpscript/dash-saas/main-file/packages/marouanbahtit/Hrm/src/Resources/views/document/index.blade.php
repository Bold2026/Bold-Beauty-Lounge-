@extends('layouts.main')
@section('page-title')
    {{ __('Manage Document') }}
@endsection
@section('page-breadcrumb')
{{ __('Document') }}
@endsection
@section('page-action')
<div class="d-flex">
    @stack('addButtonHook')
    @permission('document create')
        <a  class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Document') }}" data-url="{{route('document.create')}}" data-bs-toggle="tooltip"  data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection
@push('css')
@include('layouts.includes.datatable-css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Hrm/src/Resources/assets/css/custom.css')}}">
@endpush
@section('content')
<div class="row">
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
@endpush
