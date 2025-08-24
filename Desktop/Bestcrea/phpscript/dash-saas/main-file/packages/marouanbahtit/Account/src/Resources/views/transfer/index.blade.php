@extends('layouts.main')
@section('page-title')
    {{ __('Bank Balance Transfer') }}
@endsection
@section('page-breadcrumb')
    {{ __('Bank Balance Transfer') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
    <div>
        @permission('bank account create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Transfer') }}"
                data-url="{{ route('bank-transfer.create') }}" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="mt-2" id="multiCollapseExample1">

            <div class="card">
                <div class="card-body">

                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">

                                <div class="col-3">
                                </div>

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mb-2  month">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                        {{ Form::text('date', isset($_GET['date']) ? $_GET['date'] : null, ['class' => 'form-control flatpickr-to-input', 'placeholder' => 'Select Date']) }}

                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mb-2 date">
                                    <div class="btn-box">
                                        {{ Form::label('f_account', __('From Account'), ['class' => 'form-label']) }}
                                        {{ Form::select('f_account', $account, isset($_GET['f_account']) ? $_GET['f_account'] : '', ['class' => 'form-control ', 'placeholder' => 'Select Account']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mb-2">
                                    <div class="btn-box">
                                        {{ Form::label('t_account', __('To Account'), ['class' => 'form-label']) }}
                                        {{ Form::select('t_account', $account, isset($_GET['t_account']) ? $_GET['t_account'] : '', ['class' => 'form-control ', 'placeholder' => 'Select Account']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto d-flex">
                                    <a class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                        id="applyfilter" data-original-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="#!" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"
                                        title="{{ __('Reset') }}" id="clearfilter"
                                        data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                    </a>
                                </div>
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
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}

    <script>
        $(document).on('change', '#from_account', function() {
            var account_id = $(this).val();
            $.ajax({
                type: 'GET',
                url: "{{ url('bank-transfer/get-opening-balance') }}/" + account_id,
                success: function(response) {
                    if (response.success) {
                        $('.bank_amount').val(response.balance);
                        $('.bank_amount').attr('max',response.balance);
                    } else {
                        $('.bank_amount').val(0);
                        toastrs('Error', 'Opening balance not found!', 'error');
                    }
                },
                error: function() {
                    $('.bank_amount').val(0);
                    toastrs('Error', 'Something went wrong. Please try again!', 'error');
                }
            });
        });
    </script>
@endpush
