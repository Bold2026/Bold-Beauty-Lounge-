@extends('layouts.main')
@section('page-title')
    {{__('Manage Warehouse')}}
@endsection
@push('script-page')
@endpush
@section('page-breadcrumb')
  {{__('Warehouse')}}
@endsection
@section('page-action')
    <div class="d-flex">
        @stack('addButtonHook')
        @permission('warehouse import')
            <a href="#"  class="btn btn-sm btn-primary me-2" data-ajax-popup="true" data-title="{{__('Warehouse Import')}}" data-url="{{ route('warehouses.file.import') }}"  data-toggle="tooltip" title="{{ __('Import') }}"><i class="ti ti-file-import"></i>
            </a>
        @endpermission
        @permission('warehouse create')
            <a data-size="lg" data-url="{{ route('warehouses.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Warehouse')}}"  class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('content')
    <div class="row">
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
@endsection
@push('scripts')
@include('layouts.includes.datatable-js')
{{ $dataTable->scripts() }}
@endpush
