@extends('layouts.main')

@section('page-title')
    {{ __('Manage Leads') }} @if ($pipeline)
        - {{ $pipeline->name }}
    @endif
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
    <link rel="stylesheet" href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}">
@endpush

@section('page-breadcrumb')
    {{ __('Leads') }}
@endsection
@section('page-action')
    <div class="d-flex">
        @if ($pipeline)
            <div class="col-auto me-3">
                {{ Form::open(['id' => 'change-pipeline']) }}
                {{ Form::select('default_pipeline_id', $pipelines, $pipeline->id, ['class' => 'form-control custom-form-select mx-2', 'id' => 'default_pipeline_id']) }}
                {{ Form::close() }}
            </div>
        @endif
        <div class="col-auto pt-2" style="display: inline-table;">
            @stack('addButtonHook')
        </div>
        @permission('lead import')
            <div class="col-auto pt-2">
                <a class="btn btn-sm btn-primary btn-icon me-2" data-ajax-popup="true" data-title="{{ __('Lead Import') }}"
                    data-url="{{ route('lead.file.import') }}" data-size="md" data-toggle="tooltip" title="{{ __('Import') }}"><i
                        class="ti ti-file-import"></i>
                </a>
            </div>
        @endpermission
        <div class="col-auto pt-2">
            <a href="{{ route('leads.index') }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Kanban View') }}" class="btn btn-sm btn-primary btn-icon me-2"><i class="ti ti-table"></i> </a>
        </div>
        @permission('lead create')
            <div class="col-auto pt-2">
                <a class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="{{ __('Create Lead') }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create Lead') }}"
                    data-url="{{ route('leads.create') }}"><i class="ti ti-plus text-white"></i></a>
            </div>
        @endpermission
    </div>
@endsection

@section('content')
    @if ($pipeline)
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
