@extends('layouts.main')

@section('page-title')
    {{ __('Manage Deals') }} @if ($pipeline)
        - {{ $pipeline->name }}
    @endif
@endsection

@push('css')
    @include('layouts.includes.datatable-css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}">
    <style>
        .comp-card {
            height: 140px;
        }
    </style>
@endpush

@section('page-breadcrumb')
    {{ __('Deals') }}
@endsection

@section('page-action')
    <div class="d-flex">
        
        @if ($pipeline)
            <div class="col-auto me-3">
                {{ Form::open(['id' => 'change-pipeline']) }}
                {{ Form::select('default_pipeline_id', $pipelines, $pipeline->id, ['class' => 'form-control mx-2 custom-form-select', 'id' => 'default_pipeline_id']) }}
                {{ Form::close() }}
            </div>
        @endif
        <div class="col-auto pt-2" style="display: inline-table;">
            @stack('addButtonHook')
        </div>
        @permission('deal import')
            <div class="col-auto pt-2">
                <a href="#" class="btn btn-sm btn-primary me-2" data-ajax-popup="true" data-title="{{ __('Deal Import') }}"
                    data-url="{{ route('deal.file.import') }}" data-toggle="tooltip" title="{{ __('Import') }}"><i
                        class="ti ti-file-import"></i>
                </a>
            </div>
        @endpermission
        <div class="col-auto pt-2">
            <a href="{{ route('deals.index') }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Kanban View') }}" class="btn btn-sm btn-primary btn-icon me-2"><i class="ti ti-table"></i> </a>
        </div>
        @permission('deal create')
            <div class="col-auto pt-2">
                <a class="btn btn-sm btn-primary btn-icon col-auto" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="{{ __('Create Deal') }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Deal') }}"
                    data-url="{{ route('deals.create') }}"><i class="ti ti-plus text-white"></i></a>
            </div>
        @endpermission
    </div>
@endsection
@section('content')
    @if ($pipeline)
        <div class="row">
            <div class="col-xl-3 col-6">
                <div class="card comp-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-b-20">{{ __('Total Deals') }}</h6>
                                <h3 class="text-primary">{{ $cnt_deal['total'] }}</h3>
                            </div>
                            <div class="col-auto theme-avtar bg-success badge me-2">
                                <i class="fas fa-rocket text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-6">
                <div class="card comp-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-b-20">{{ __('This Month Total Deals') }}</h6>
                                <h3 class="text-info">{{ $cnt_deal['this_month'] }}</h3>
                            </div>
                            <div class="col-auto theme-avtar bg-info badge me-2">
                                <i class="fas fa-rocket text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-6">
                <div class="card comp-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-b-20">{{ __('This Week Total Deals') }}</h6>
                                <h3 class="text-warning">{{ $cnt_deal['this_week'] }}</h3>
                            </div>
                            <div class="col-auto theme-avtar bg-warning badge me-2">
                                <i class="fas fa-rocket text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-6">
                <div class="card comp-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-b-20">{{ __('Last 30 Days Total Deals') }}</h6>
                                <h3 class="text-danger">{{ $cnt_deal['last_30days'] }}</h3>
                            </div>
                            <div class="col-auto theme-avtar bg-danger badge me-2">
                                <i class="fas fa-rocket text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
    @endif
@endsection

@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush
