@extends('layouts.main')
@section('page-title')
    {{ __('Item Detail') }}
@endsection
@section('page-breadcrumb')
    {{ __('Item') }} , {{ __('Item Details') }}
@endsection

@section('page-action')
    <div>
        @stack('addButtonHook')
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#useradd-0"
                                class="list-group-item list-group-item-action border-0 active">{{ __('Overview') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-1"
                                class="list-group-item list-group-item-action border-0 services {{ $productService->type == 'service' ? 'd-none' : '' }}">{{ __('Vendors') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-2"
                                class="list-group-item list-group-item-action border-0 services {{ $productService->type == 'service' ? 'd-none' : '' }}">{{ __('Purchases') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-3"
                                class="list-group-item list-group-item-action border-0 services {{ $productService->type == 'service' ? 'd-none' : '' }}">{{ __('Warehouse') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-4"
                                class="list-group-item list-group-item-action border-0">{{ __('Reports') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#useradd-5"
                                class="list-group-item list-group-item-action border-0">{{ __('Log Time') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <div id="useradd-0">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="tab-content" id="pills-tabContent">
                                            <div class="tab-pane fade active show" id="details-tab" role="tabpanel"
                                                aria-labelledby="pills-user-tab-1">
                                                <div class="row">
                                                    <div class="col-md-6 col-12 mb-4">
                                                        <div class="card h-100 mb-0">
                                                            <div class="card-body text-center">
                                                                <div class="card_img text-center">
                                                                    @php
                                                                        if (check_file($productService->image) == false) {
                                                                            $path = asset('packages/workdo/ProductService/src/Resources/assets/image/img01.jpg');
                                                                        } else {
                                                                            $path = get_file($productService->image);
                                                                        }
                                                                    @endphp

                                                                    <img class="seo_image rounded border border-2 border-primary"
                                                                        src="{{ $path }}"
                                                                        alt="{{ $path }}">
                                                                </div>
                                                                <h6 class="mt-3 mb-0 text-center">
                                                                    {{ $productService->name }} </h6>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-12 mb-4">
                                                        <div class="card h-100 mb-0">
                                                            <div class="card-body">
                                                                <h5>{{ __('Details') }} : </h5><br>
                                                                <div class="row">
                                                                    <dt class="col-sm-4 h6 text-lg">{{ __('Name') }}
                                                                    </dt>
                                                                    <dd class="col-sm-8 text-lg">
                                                                        {{ $productService->name }}
                                                                    </dd>
                                                                    <dt class="col-sm-4 h6 text-lg">{{ __('SKU') }}
                                                                    </dt>
                                                                    <dd class="col-sm-8 text-lg">
                                                                        {{ $productService->sku }}
                                                                    </dd>
                                                                    @if ($productService->type == 'product' || 'parts')
                                                                        <dt class="col-sm-4 h6 text-lg">
                                                                            {{ __('Quantity') }}</dt>
                                                                        <dd class="col-sm-8 text-lg">
                                                                            {{ $productService->quantity }}
                                                                        </dd>
                                                                    @else
                                                                        -
                                                                    @endif
                                                                    <dt class="col-sm-4 h6 text-lg">{{ __('Tax') }}
                                                                    </dt>
                                                                    @php
                                                                        $tax_id = explode(',', $productService->tax_id);
                                                                        $tax_names = \Workdo\ProductService\Entities\Tax::whereIn('id', $tax_id)->get();
                                                                    @endphp
                                                                    <dd class="col-sm-8 text-lg">
                                                                        @foreach ($tax_names as $tax_name)
                                                                            {{ $tax_name->name }}
                                                                        @endforeach
                                                                    </dd>
                                                                    <dt class="col-sm-4 h6 text-lg">
                                                                        {{ __('Description') }}</dt>
                                                                    <dd class="col-sm-8 text-lg">
                                                                        {{ $productService->description }}
                                                                    </dd>
                                                                </div>

                                                                {{-- if warranty add on active and warranty is active then warranty details show --}}
                                                                @stack('warranty-details')

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane active show fade" role="tabpanel" aria-labelledby="pills-user-tab-2">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <dt class="col-sm-4 h6 text-lg">
                                                                            {{ __('Purchase Price') }}
                                                                        </dt>
                                                                        <dd class="col-sm-8 text-lg">
                                                                            {{ $productService->purchase_price }}
                                                                        </dd>
                                                                        <dt class="col-sm-4 h6 text-lg">
                                                                            {{ __('Unit') }}
                                                                        </dt>
                                                                        <dd class="col-sm-8 text-lg">
                                                                            {{ optional($productService->units)->name ??'' }}

                                                                        </dd>
                                                                        <dt class="col-sm-4 h6 text-lg">
                                                                            {{ __('Sale Price') }}
                                                                        </dt>
                                                                        <dd class="col-sm-8 text-lg">
                                                                            {{ $productService->sale_price }}
                                                                        </dd>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div id="useradd-1" class="{{ $productService->type == 'service' ? 'd-none' : '' }}">
                        <div id="item_vendor" class="">
                            <div class="">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <h5>
                                                    {{ __('Vendors') }}
                                                </h5>
                                                <a class="btn btn-sm btn-primary" data-ajax-popup="true"
                                                    data-size="lg" data-title="{{ __('Create Vendor') }}"
                                                    data-url="{{ route('vendors.create') }}"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Create') }}">
                                                    <i class="ti ti-plus"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th> {{ __('Name') }}</th>
                                                            <th scope="col">{{ __('Email') }}</th>
                                                            <th width="10%"> {{ __('Action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($vendors as $vendor)
                                                            <tr>
                                                                <td class="font-style">{{ $vendor->name }}</td>
                                                                <td class="font-style">{{ $vendor->email }}</td>
                                                                <td class="Action">
                                                                    <span>
                                                                        @permission('vendor edit')
                                                                            <div class="action-btn me-2">
                                                                                <a class="mx-3 btn btn-sm  align-items-center bg-info"
                                                                                    data-url="{{ route('vendors.edit', $vendor['id']) }}"
                                                                                    data-ajax-popup="true" data-size="lg"
                                                                                    data-bs-toggle="tooltip" title=""
                                                                                    data-title="{{ __('Edit Vendor') }}"
                                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                                    <i class="ti ti-pencil text-white"></i>
                                                                                </a>
                                                                            </div>
                                                                        @endpermission
                                                                        @if (!empty($vendor['vendor_id']))
                                                                            @permission('vendor delete')
                                                                                <div class="action-btn">
                                                                                    {{ Form::open(['route' => ['vendors.destroy', $vendor['id']], 'class' => 'm-0 d-flex']) }}
                                                                                    @method('DELETE')
                                                                                    <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                                                        data-bs-toggle="tooltip" title=""
                                                                                        data-bs-original-title="Delete"
                                                                                        aria-label="Delete"
                                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                                        data-confirm-yes="delete-form-{{ $vendor['id'] }}"><i
                                                                                            class="ti ti-trash text-white text-white"></i></a>
                                                                                    {{ Form::close() }}
                                                                                </div>
                                                                            @endpermission
                                                                        @endif
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            @include('layouts.nodatafound')
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="useradd-2" class="{{ $productService->type == 'service' ? 'd-none' : '' }}">
                        <div id="item_purchase" class="">
                            <div class="">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                    <h5 class="">
                                                        {{ __('Purchases') }}
                                                    </h5>
                                                    <a href="{{ route('purchases.create', 0) }}"
                                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                                        title="{{ __('Create') }}">
                                                        <i class="ti ti-plus"></i>
                                                    </a>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th> {{ __('Vendor') }}</th>
                                                        <th> {{ __('Purchase Date') }}</th>
                                                        <th width="10%"> {{ __('Action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($purchases as $purchase)
                                                        <tr>

                                                            <td>{{ !empty($purchase->user->name)?$purchase->user->name:'' }}</td>
                                                            <td class="font-style">
                                                                {{ company_date_formate($purchase->purchase_date) }}
                                                            </td>
                                                            <td class="Action">
                                                                <span>
                                                                    @permission('purchase edit')
                                                                        <div class="action-btn me-2">
                                                                            <a href="{{ route('purchases.edit', \Crypt::encrypt($purchase->id)) }}"
                                                                                class="mx-3 btn btn-sm align-items-center bg-info"
                                                                                data-bs-toggle="tooltip" title="Edit"
                                                                                data-original-title="{{ __('Edit') }}">
                                                                                <i class="ti ti-pencil text-white"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endpermission
                                                                    @permission('purchase delete')
                                                                        <div class="action-btn">
                                                                            {!! Form::open([
                                                                                'method' => 'DELETE',
                                                                                'route' => ['purchases.destroy', $purchase->id],
                                                                                'class' => 'delete-form-btn d-flex',
                                                                                'id' => 'delete-form-' . $purchase->id,
                                                                            ]) !!}
                                                                            <a href="#"
                                                                                class="mx-3 btn btn-sm align-items-center show_confirm bg-danger"
                                                                                data-bs-toggle="tooltip"
                                                                                title="{{ __('Delete') }}"
                                                                                data-original-title="{{ __('Delete') }}"
                                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                                data-confirm-yes="document.getElementById('delete-form-{{ $purchase->id }}').submit();">
                                                                                <i class="ti ti-trash text-white"></i>
                                                                            </a>
                                                                            {!! Form::close() !!}
                                                                        </div>
                                                                    @endpermission
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        @include('layouts.nodatafound')
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="useradd-3" class="{{ $productService->type == 'service' ? 'd-none' : '' }}">
                        <div class="card ">
                            <div class="card-body table-border-style full-card">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>{{__('Warehouse') }}</th>
                                            <th>{{__('Quantity')}}</th>

                                        </tr>
                                        </thead>
                                        <tbody>

                                        @foreach ($products as $product)
                                            @if(!empty($product->warehouse()))
                                                <tr>
                                                    <td>{{ !empty($product->warehouse())?$product->warehouse()->name:'-' }}</td>
                                                    <td>{{ $product->quantity }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="useradd-4">
                        <div class="card">
                            <div class="card-header">

                                <h5 class="mb-0">{{ __('Reports') }}</h5>
                            </div>

                            <div class="card-body">
                                <div class="row row-gap-4">
                                    @if ($productService->type == 'product' )
                                    <div class="col-xxl-5 col-md-6 col-12">
                                        <div class="card mb-0">
                                            <div class="card-body">
                                                <div class="row align-items-center justify-content-between">
                                                    <div class="col-auto mb-3 mb-sm-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="theme-avtar badge bg-warning">
                                                                <i class="ti ti-shopping-cart"></i>
                                                            </div>
                                                            <div class="ms-3">
                                                                <small class="text-muted">{{ __('Total') }}</small>
                                                                <h6 class="m-0">{{ __('Product Sold') }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto text-end">
                                                        <h4 class="m-0">{{ $total_sold }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @elseif ($productService->type == 'parts')
                                    <div class="col-xxl-5 col-md-6 col-12">
                                        <div class="card mb-0">
                                            <div class="card-body">
                                                <div class="row align-items-center justify-content-between">
                                                    <div class="col-auto mb-3 mb-sm-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="theme-avtar badge bg-warning">
                                                                <i class="ti ti-shopping-cart"></i>
                                                            </div>
                                                            <div class="ms-3">
                                                                <small class="text-muted">{{ 'Total' }}</small>
                                                                <h6 class="m-0">{{ __('Parts Used') }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto text-end">
                                                        <h4 class="m-0">{{ $total_products_use }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="col-xxl-5 col-md-6 col-12">
                                        <div class="card mb-0">
                                            <div class="card-body">
                                                <div class="row align-items-center justify-content-between">
                                                    <div class="col-auto mb-3 mb-sm-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="theme-avtar badge bg-primary">
                                                                <i class="ti ti-shopping-cart"></i>
                                                            </div>
                                                            <div class="ms-3">
                                                                <small class="text-muted">{{ 'Total' }}</small>
                                                                <h6 class="m-0">{{ 'Cost' }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto text-end">
                                                        <h4 class="m-0">
                                                            {{ !empty($total_cost->total_cost) ? $total_cost->total_cost : 0 }}
                                                        </h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="useradd-5">
                        <div class="card">
                            <div class="card-header">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <h5 class="mb-0">{{ __('Log Time') }}</h5>

                                            <div class="float-end">
                                                <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md"
                                                    data-title="{{ __('Create Log Time') }}"
                                                    data-url="{{ route('productslogtime.create', ['product_id' => $productService->id]) }}"
                                                    data-toggle="tooltip" title="{{ __('Create') }}">
                                                    <i class="ti ti-plus text-white"></i>
                                                </a>
                                            </div>
                                </div>
                            </div>

                            <div class="card-body">
                            <div class="table-responsive">
                                <table class="table dataTable3">
                                    <thead>
                                        <tr>
                                            <th class="text-wrap text-break">{{ __('Description') }}</th>
                                            <th class="text-end">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($productslogtime as $partslogtime_val)
                                        <tr>
                                            <td class="text-wrap text-break" style="min-width: 300px;">
                                                <i class="ti ti-clock me-2"></i>{{ company_date_formate($partslogtime_val->date) }}
                                                - {{ $partslogtime_val->description }}
                                            </td>
                                            <td class="Action text-end">
                                                <span>
                                                    <div class="action-btn me-2">
                                                        <a class="mx-3 btn btn-sm align-items-center bg-info"
                                                            data-url="{{ route('productslogtime.edit', $partslogtime_val->id) }}"
                                                            data-ajax-popup="true" data-size="md"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-title="{{ __('Edit Log Time') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>

                                                    <div class="action-btn">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['productslogtime.destroy', $partslogtime_val->id],
                                                            'class' => 'delete-form-btn d-flex',
                                                            'id' => 'delete-form-' . $partslogtime_val->id,
                                                        ]) !!}
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm align-items-center show_confirm bg-danger"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-original-title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $partslogtime_val->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            if ($("input[value='service']").is(":checked")) {
                ;
                $('.services').addClass('d-none')
                $('.services').removeClass('d-block');
            }
        });
    </script>
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>
@endpush
