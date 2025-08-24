@extends('layouts.main')
@section('page-title')
    {{ __('Cash Flow') }}
@endsection
@section('page-breadcrumb')
    {{ __('Report') }},
    {{ __('Cash Flow') }}
@endsection
@push('css')

@endpush
@push('scripts')

@endpush
@section('page-action')
<div>
    <a  class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Download') }}">
        <i class="ti ti-download"></i>
    </a>
</div>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => array('report.quarterly.cashflow'), 'method' => 'GET', 'id' => 'quarterly_cashflow']) }}
                    <div class="col-xl-12">

                        <div class="row justify-content-between">
                            <div class="col-xl-3">
                                <ul class="nav nav-pills my-3" id="pills-tab" role="tablist">
                                    <li class="nav-item me-2">
                                        <a class="nav-link" id="pills-home-tab" data-bs-toggle="pill"
                                           href="{{ route('report.cash.flow') }}"
                                           onclick="window.location.href = '{{ route('report.cash.flow') }}'" role="tab"
                                           aria-controls="pills-home" aria-selected="true">{{ __('Monthly') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active" id="pills-profile-tab" data-bs-toggle="pill"
                                           href="{{ route('report.quarterly.cashflow') }}"
                                           onclick="window.location.href = '{{ route('report.quarterly.cashflow') }}'" role="tab"
                                           aria-controls="pills-profile" aria-selected="false">{{ __('Quarterly') }}</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-xl-9">
                                <div class="row justify-content-end align-items-center">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('year', __('Year'),['class'=>'form-label'])}}
                                            {{ Form::select('year',$yearList,isset($_GET['year'])?$_GET['year']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>

                                    <div class="col-auto mt-4">
                                        <a class="btn btn-sm btn-primary me-1" onclick="document.getElementById('quarterly_cashflow').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('report.cash.flow')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
    </div>
</div>

<div id="printableArea1">
    <div class="row mt-1">
        <div class="col">
            <input type="hidden" value="{{__('Quarterly Cashflow').' '.'Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange']}}" id="filenames-quarterly">
            <div class="card p-4 mb-4">
                <label class="report-text gray-text mb-0">{{__('Report')}} :</label>
                <h6 class="report-text mb-0">{{__('Quarterly Cashflow')}}</h6>
            </div>
        </div>
        <div class="col">
            <div class="card p-4 mb-4">
                <label class="report-text gray-text mb-0">{{__('Duration')}} :</label>
                <h6 class="report-text mb-0">{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</h6>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="pb-3">{{__('Income')}}</h5>
                            <div class="table-responsive mt-3 mb-3">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th width="20%">{{__('Category')}}</th>
                                        @foreach($four_month as $month)
                                            <th>{{$month}}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td width="25%" class="font-bold"><span>{{__('Revenue : ')}}</span></td>
                                            @foreach($RevenueTotal as $revenue)
                                                <td width="15%">{{currency_format_with_sym(!empty($revenue) ?? '0.00')}}</td>
                                            @endforeach
                                        </tr>
                                        <tr>

                                            <td width="25%" class="font-bold">{{__('Invoice : ')}}</td>
                                            @foreach($invoiceTotal as $invoice)
                                                <td width="15%">{{currency_format_with_sym($invoice)}}</td>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td colspan="13" class="font-bold"><span>{{__('Total Income =  Revenue + Invoice ')}}</span></td>
                                        </tr>
                                        <tr>
                                            <td width="20%" class="text-dark">{{__('Total Income')}}</td>
                                            @foreach($chartIncomeArr as $i=>$income)
                                                <td>{{currency_format_with_sym($income)}}</td>
                                            @endforeach
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                            <div class="col-sm-12">
                                <h5>{{__('Expense')}}</h5>
                                <div class="table-responsive mt-3">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                            <th width="20%">{{__('Category')}}</th>
                                            @foreach($four_month as $month)
                                                <th>{{$month}}</th>
                                            @endforeach
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td width="20%" class="font-bold">{{__('Payment')}}</td>
                                                @foreach($paymentTotal as $i=>$payment)
                                                    <td>{{currency_format_with_sym($payment)}}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td width="20%" class="font-bold">{{__('PaySlip')}}</td>
                                                @foreach($paySlipTotal as $i=>$paySlip)
                                                    <td>{{currency_format_with_sym($paySlip)}}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td width="20%" class="font-bold">{{__('Bill')}}</td>
                                                @foreach($billTotal as $i=>$bill)
                                                    <td>{{currency_format_with_sym($bill)}}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td colspan="13" class="font-bold"><span>{{__('Total Expense =  Payment + PaySlip + Bill ')}}</span></td>
                                            </tr>
                                            <tr>
                                                <td width="20%" class="text-dark">{{__('Total Expenses')}}</td>
                                                @foreach($chartExpenseArr as $i=>$expense)
                                                    <td>{{currency_format_with_sym($expense)}}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="table-responsive mt-1">
                                    <table class="table mb-0">
                                        <thead>
                                        <tr>
                                            <th colspan="13" class="font-bold"><span>{{__('Net Profit = Total Income - Total Expense')}}</span></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td width="20%" class="text-dark">{{__('Net Profit')}}</td>
                                                @foreach($netProfitArray as $i=>$profit)
                                                    <td>{{currency_format_with_sym($profit)}}</td>
                                                @endforeach
                                            </tr>
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
</div>
@endsection

@push('scripts')
<script src="{{ asset('packages/workdo/Account/src/Resources/assets/js/html2pdf.bundle.min.js') }}"></script>

<script>
    var filename = $('#filenames-quarterly').val();

    function saveAsPDF() {
        var element = document.getElementById('printableArea1');
        var opt = {
            margin: 0.3,
            filename: filename,
            image: {type: 'jpeg', quality: 1},
            html2canvas: {scale: 4, dpi: 72, letterRendering: true},
            jsPDF: {unit: 'in', format: 'A2'}
        };
        html2pdf().set(opt).from(element).save();
    }
</script>

@endpush
