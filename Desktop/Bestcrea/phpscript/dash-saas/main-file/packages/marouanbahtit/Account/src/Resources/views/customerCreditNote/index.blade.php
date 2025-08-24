@extends('layouts.main')
@section('page-title')
    {{ __('Manage Credit Notes') }}
@endsection
@section('page-breadcrumb')
    {{ __('Credit Note') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Credit Note') }}</li>
@endsection

@section('page-action')
@permission('creditnote create')
    <div class="float-end">
        <a data-url="{{ route('create.custom.credit.note') }}" data-ajax-popup="true" data-bs-toggle="tooltip"
            title="{{ __('Create') }}" title="{{ __('Create') }}" data-title="{{ __('Create Credit Note') }}"
            class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endpermission
@endsection
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
<script>
    $(document).on('click' , '#item' , function(){
        var item_id = $(this).val();
        $.ajax({
            url: "{{route('credit-invoice.itemprice')}}",
            method:'POST',
            data: {
                "item_id": item_id, 
                "_token": "{{ csrf_token() }}",
            },
            success: function (data) {
                if (data !== undefined) {
                    $('#amount').val(data);
                    $('input[name="amount"]').attr('min', 0);
                }
            }
        });        
    });
</script>
@endpush