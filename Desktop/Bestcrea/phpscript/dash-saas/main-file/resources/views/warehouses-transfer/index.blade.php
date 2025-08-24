@extends('layouts.main')
@section('page-title')
    {{__('Warehouse Transfer')}}
@endsection
@push('script-page')
@endpush
@section('page-breadcrumb')
    {{__('Warehouse Transfer')}}
@endsection
@section('page-action')
    <div>
        @permission('warehouse create')
            <a data-size="lg" data-url="{{ route('warehouses-transfer.create') }}" data-ajax-popup="true"
               data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Warehouse Transfer')}}"  class="btn btn-sm btn-primary">
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


    <script>
        $(document).ready(function () {
            var w_id = $('#warehouse_id').val();
            getProduct(w_id);
        });
        $(document).on('change', 'select[name=from_warehouse]', function ()
        {
            var warehouse_id = $(this).val();
            getProduct(warehouse_id);
        });

        function getProduct(wid)
        {
            $.ajax({
                url: '{{route('warehouses-transfer.getproduct')}}',
                type: 'POST',
                data: {
                    "warehouse_id": wid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#product_id').empty();

                    $("#product_div").html('');
                    $('#product_div').append('<label for="product" class="form-label">{{__('Product')}}</label>');
                    $('#product_div').append('<select class="form-control" id="product_id" name="product_id"></select>');
                    $('#product_id').append('<option value="">{{__('Select Product')}}</option>');

                    $.each(data.ware_products, function (key, value) {
                        $('#product_id').append('<option value="' + key + '">' + value + '</option>');
                    });

                    $('select[name=to_warehouse]').empty();
                    $.each(data.to_warehouses, function(key, value) {
                        var option = '<option value="' + key + '">' + value + '</option>';
                        $('select[name=to_warehouse]').append(option);
                    });
                }

            });
        }

        $(document).on('change', '#product_id', function () {
            var product_id = $(this).val();
            getQuantity(product_id);
        });

        function getQuantity(pid) {
            $.ajax({
                url: '{{route('warehouses-transfer.getquantity')}}',
                type: 'POST',
                data: {
                    "product_id": pid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    if (Array.isArray(data)) {
                        $('#quantity').val(data[0]);
                    } else {
                        $('#quantity').val(data);
                    }
                }
            });
        }
    </script>
@endpush
