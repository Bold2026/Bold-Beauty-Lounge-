@extends('layouts.main')
@section('page-title')
    {{ __('Manage Debit Notes') }}
@endsection
@section('page-breadcrumb')
    {{ __('Debit Note') }}
@endsection
@push('script-page')
@endpush
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
@endpush
@section('page-action')
    <div class="float-end">
        @permission('debitnote create')
            <a href="#" data-url="{{ route('bill.custom.debit.note') }}" data-ajax-popup="true"
                data-title="{{ __('Create Debit Note') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" multi-collapse mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-3">
                                <div class="btn-box">
                                    {{ Form::label('debit_type', __('Type'), ['class' => 'form-label']) }}
                                    {{ Form::select('debit_type', $debit_type, isset($_GET['debit_type']) ? $_GET['debit_type'] : '0', ['class' => 'form-control']) }}
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
        var type = $('input[name="type"]:checked').val();

        $.ajax({
            url: "{{route('debit-bill.itemprice')}}",
            method:'POST',
            data: {
                "item_id": item_id,
                "type"   : type,
                "_token" : "{{ csrf_token() }}",
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
