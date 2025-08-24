@extends('layouts.main')
@section('page-title')
    {{ __('Manage Items') }}
@endsection
@section('page-breadcrumb')
    {{ __('Items') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
    @permission('product&service create')
        <div class="d-flex">
            @stack('addButtonHook')

            <a href="{{ route('category.index') }}"data-size="md" class="btn btn-sm btn-primary me-2"
                data-bs-toggle="tooltip"data-title="{{ __('Setup') }}" title="{{ __('Setup') }}"><i
                    class="ti ti-settings"></i></a>

            <a href="{{ route('productstock.index') }}"data-size="md" class="btn btn-sm btn-primary me-2"
                data-bs-toggle="tooltip"data-title="{{ __(' Product Stock') }}" title="{{ __('Product Stock') }}"><i
                    class="ti ti-shopping-cart"></i></a>

            @permission('product&service import')
                <a href="#" class="btn btn-sm btn-primary me-2" data-ajax-popup="true"
                    data-title="{{ __('Product & Service Import') }}" data-url="{{ route('product-service.file.import') }}"
                    data-toggle="tooltip" title="{{ __('Import') }}"><i class="ti ti-file-import"></i>
                </a>
            @endpermission

            <a href="{{ route('product-service.grid') }}" class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip"
                data-title="{{ __('Grid View') }}" title="{{ __('Grid View') }}"><i
                    class="ti ti-layout-grid text-white"></i></a>

            @permission('product&service create')
            <a href="{{ route('product-service.create') }}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip"
                data-bs-placement="top" data-title="{{ __('Create New Product') }}" title="{{ __('Create') }}"><i
                    class="ti ti-plus text-white"></i></a>
            @endpermission

        </div>
    @endpermission
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" multi-collapse mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-6">
                                <div class="row">

                                    <div class="col-xl-6 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('item_type', __('Item'), ['class' => 'form-label']) }}
                                            {{ Form::select('item_type', $product_type, isset($_GET['item_type']) ? $_GET['item_type'] : '', ['class' => 'form-control ', 'placeholder' => __('Select Item Type')]) }}
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('category', __('Category'), ['class' => 'form-label']) }}
                                            {{ Form::select('category', $category, isset($_GET['category']) ? $_GET['category'] : '', ['class' => 'form-control ', 'placeholder' => __('Select Category')]) }}
                                        </div>
                                    </div>
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
                                            class="ti ti-trash-off text-white-off "></i></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
@endpush
