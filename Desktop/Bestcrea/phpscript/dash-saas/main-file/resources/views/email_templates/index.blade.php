@extends('layouts.main')
@section('page-title')
    {{ __('Email Templates') }}
@endsection
@section('page-breadcrumb')
    {{ __('Email Templates') }}
@endsection
@section('page-action')
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
        <!-- [ basic-table ] end -->
    </div>
@endsection

@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
@endpush
