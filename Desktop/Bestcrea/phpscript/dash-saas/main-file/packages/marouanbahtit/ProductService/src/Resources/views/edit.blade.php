@extends('layouts.main')
@section('page-title')
    {{ __('Edit Items') }}
@endsection
@section('page-breadcrumb')
    {{ __('Items') }}
@endsection
@section('page-action')
    <div class="col-auto" style="width: 143px;">
        <select class="form-select" name="item_type" id="item_type" required="">
            <option value="">{{ __('Select Type') }}</option>
            <option value="product" @if ($productService->type == 'product') selected @endif>
                {{ __('Product') }}</option>
            <option value="service" @if ($productService->type == 'service') selected @endif>
                {{ __('Service') }}</option>
            <option value="parts" @if ($productService->type == 'parts') selected @endif>
                {{ __('Parts') }}</option>
            @if (module_is_active('RentalManagement'))
                <option value="rent" @if ($productService->type == 'rent') selected @endif>
                    {{ __('Rent') }}</option>
            @endif
            @if (module_is_active('MusicInstitute'))
                <option value="music institute" @if ($productService->type == 'music institute') selected @endif>
                    {{ __('Music Institute') }}</option>
            @endif
            @if (module_is_active('RestaurantMenu'))
                <option value="restaurants" @if ($productService->type == 'restaurants') selected @endif>
                    {{ __('Restaurant') }}</option>
            @endif
            @if (module_is_active('Bookings'))
                <option value="bookings" @if ($productService->type == 'bookings') selected @endif>
                    {{ __('Booking') }}</option>
            @endif
            @if (module_is_active('Facilities'))
                <option value="facilities" @if ($productService->type == 'facilities') selected @endif>
                    {{ __('Facilities') }}</option>
            @endif
            @if (module_is_active('Fleet'))
                <option value="fleet" @if ($productService->type == 'fleet') selected @endif>
                    {{ __('Fleet') }}</option>
            @endif
            @if (module_is_active('ConsignmentManagement'))
                <option value="consignment" @if ($productService->type == 'consignment') selected @endif>
                    {{ __('Consignment') }}</option>
            @endif
            @if (module_is_active('OpticalAndEyeCareCenter'))
                <option value="optical eyecare" @if ($productService->type == 'optical eyecare') selected @endif>
                    {{ __('Optical & Eye Care') }}</option>
            @endif
            @if (module_is_active('JewelleryStoreManagement'))
                <option value="jewellery store" @if ($productService->type == 'jewellery store') selected @endif>
                    {{ __('jewellery store') }}</option>
            @endif
            @if (module_is_active('HairAndCareStudio'))
                <option value="hair & care studio" @if ($productService->type == 'hair & care studio') selected @endif>
                    {{ __('Hair & Care Studio') }}</option>
            @endif

        </select>
    </div>
@endsection
@section('content')
    <div class="row">
        <div id="loader" class="card card-flush">
            <div class="card-body">
                <div class="row">
                    <img class="loader" src="{{ asset('public/images/loader.gif') }}" alt="">
                </div>
            </div>
        </div>
    </div>
    {{ Form::model($productService, ['route' => ['product-service.update', $productService->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
    {{ Form::hidden('type', $productService->type, ['id' => 'type']) }}
    <div class="section_div">
    </div>
    {{ Form::close() }}
@endsection
@push('scripts')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#item_type').change(function() {
                var selectedType = $(this).val();
                $('#type').val(selectedType);
            });
        });

        $(document).ready(function() {
            ItemSectionGet();
        });

        $('#item_type').on('change', function() {
            ItemSectionGet();
            $.ajax({
                beforeSend: function() {
                    $(".loader-wrapper").removeClass('d-none');
                },
            });
        });

        function ItemSectionGet() {
            var item_type = $('#item_type').val();
            var action = "edit";
            var item_id = "{{ $productService->id }}";
            $.ajax({
                url: '{{ route('product.section.type') }}',
                type: 'POST',
                data: {
                    "item_type": item_type,
                    "item_id": item_id,
                    "action": action,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response != false) {
                        $('.section_div').html(response.html);
                        $("#loader").addClass('d-none');
                        $(".loader-wrapper").addClass('d-none');
                        JsSearchBox();
                        choices();

                    } else {
                        $('.section_div').html('');
                        toastrs('Error', 'Something went wrong please try again !', 'error');
                    }
                }
            });
        }

        function changetab(tabname) {
            var someTabTriggerEl = document.querySelector('button[data-bs-target="' + tabname + '"]');
            var actTab = new bootstrap.Tab(someTabTriggerEl);
            actTab.show();
        }

        function generateSKU() {
            var sku = 'SKU-' + Math.random().toString(24).substr(2, 7);
            $('input[name=sku]').val(sku.toUpperCase());
        }
    </script>
@endpush
