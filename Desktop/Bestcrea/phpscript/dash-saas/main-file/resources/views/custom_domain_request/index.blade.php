@extends('layouts.main')
@section('page-title')
    {{ __('Custom Domain Request') }}
@endsection
@section('page-breadcrumb')
    {{ __('Custom Domain Request') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('content')
    <!-- [ Main Content ] start -->
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
    <!-- [ Main Content ] end -->
@endsection
@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
@endpush
