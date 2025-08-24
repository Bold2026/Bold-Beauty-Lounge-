@extends('layouts.main')
@section('page-title')
    {{__('Manage Chart of Accounts')}}
@endsection

@section('page-breadcrumb')
    {{ __('Chart of Account') }}
@endsection

@push('scripts')
    <script>
        $(document).on('change', '#sub_type', function() {
            $('.acc_check').removeClass('d-none');
            var type = $(this).val();
            $.ajax({
                url: '{{ route('charofAccount.subType') }}',
                type: 'POST',
                data: {
                    "type": type,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#parent').empty();
                    $.each(data, function(key, value) {
                        $('#parent').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                }
            });
        });
        $(document).on('click', '#account', function() {
            const element = $('#account').is(':checked');
            $('.acc_type').addClass('d-none');
            if (element==true) {
                $('.acc_type').removeClass('d-none');
            } else {
                $('.acc_type').addClass('d-none');
            }
        });

    </script>
@endpush

@section('page-action')
    <div class="d-flex">
        @stack('header_button')
        @permission('product&service create')
            <a href="{{ route('category.index') }}" data-size="md"  class="me-2 btn btn-sm btn-primary" data-bs-toggle="tooltip"data-title="{{__('Setup')}}" title="{{__('Setup')}}">
                <i class="ti ti-settings"></i>
            </a>
        @endpermission

        @permission('chartofaccount create')
            <a href="#" data-url="{{ route('chart-of-account.create') }}" data-bs-toggle="tooltip" title="{{__('Create')}}" data-size="lg" data-ajax-popup="true" data-title="{{__('Create Account')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card" id="show_filter">
                    <div class="card-body">
                        {{ Form::open(['route' => ['chart-of-account.index'], 'method' => 'GET', 'id' => 'report_bill_summary']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate form-control']) }}
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate form-control']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary me-1"
                                           onclick="document.getElementById('report_bill_summary').submit(); return false;"
                                           data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                           data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>

                                        <a href="{{ route('chart-of-account.index') }}" class="btn btn-sm btn-danger "
                                           data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                           data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon">
                                            <i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($chartAccounts as $typeId=>$accounts)
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6>{{ $types->find($typeId)->name }}</h6>
                    </div>
                    <div class="card-body table-border-style" id="type-section-{{ $typeId }}">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="10%"> {{ __('Code') }}</th>
                                        <th width="30%"> {{ __('Name') }}</th>
                                        <th width="20%"> {{ __('Type') }}</th>
                                        <th width="20%"> {{ __('Parent Account Name') }}</th>
                                        <th width="20%"> {{ __('Balance') }}</th>
                                        <th width="10%"> {{ __('Status') }}</th>
                                        <th width="10%"> {{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($accounts as $key => $account)
                                        @php
                                            $balance = 0;
                                            $totalDebit   = 0;
                                            $totalCredit  = 0;
                                            $totalBalance = \Workdo\Account\Entities\AccountUtility::getAccountBalance($account->id , $filter['startDateRange'] , $filter['endDateRange']);
                                        @endphp

                                        <tr>
                                            <td>{{ !empty($account->code) ? $account->code  :'-'}}</td>

                                            @if (module_is_active('DoubleEntry'))
                                                <td>
                                                    <a href="{{ route('report.ledger', $account->id) }}?account={{ $account->id }}">{{ $account->name }}</a>
                                                </td>
                                            @else
                                                <td class="text-primary">{{ $account->name }}</td>
                                            @endif

                                            <td>{{!empty($account->subType)?$account->subType->name:'-'}}</td>
                                            <td>{{ !empty($account->parentAccount) ? $account->parentAccount->name : '-' }}</td>

                                            <td>
                                                @if(!empty($totalBalance))
                                                    {{currency_format_with_sym($totalBalance)}}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td>
                                                @if($account->is_enabled==1)
                                                    <span class="badge bg-primary p-2 px-3">{{__('Enabled')}}</span>
                                                @else
                                                    <span class="badge bg-danger p-2 px-3">{{__('Disabled')}}</span>
                                                @endif
                                            </td>

                                            <td class="Action">

                                                @if (module_is_active('DoubleEntry'))
                                                    @include('double-entry::setting.add_button',['account_id'=> $account->id])
                                                @endif

                                                
                                                @if($account->name != 'Accounts Receivable' && $account->name != 'Accounts Payable')
                                                    @permission('chartofaccount edit')
                                                        <div class="action-btn me-2">
                                                            <a href="#" class="bg-info mx-3 btn btn-sm align-items-center" data-url="{{ route('chart-of-account.edit',$account->id) }}" data-ajax-popup="true" data-title="{{__('Edit Account')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('chartofaccount delete')
                                                        <div class="action-btn">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['chart-of-account.destroy', $account->id]]) !!}
                                                            <a href="#!" class=" bg-danger btn btn-sm align-items-center text-white show_confirm" data-bs-toggle="tooltip" title='Delete'>
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endpermission
                                                @else
                                                    <div class="action-btn me-2">
                                                            <a href="#" class="bg-black mx-3 btn btn-sm align-items-center">
                                                                <i class="ti ti-lock text-white"></i>
                                                            </a>
                                                    </div>
                                                @endif                                                
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">
                                {!! $accounts->links('vendor.pagination.global-pagination') !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
@section('scripts')
    <script>
        $(document).on('click', '.pagination a', function (e) {
            e.preventDefault();

            let url = $(this).attr('href'); // Get the pagination link URL
            let targetSection = $(this).closest('.table-border-style').attr('id'); // Identify section to reload

            // Load paginated data via AJAX
            $.ajax({
                url: url,
                success: function (data) {
                    // Replace the target section with updated data
                    $('#' + targetSection).html($(data).find('#' + targetSection).html());
                },
                error: function () {
                    alert('Failed to load data.');
                }
            });
        });
    </script>
@endsection
