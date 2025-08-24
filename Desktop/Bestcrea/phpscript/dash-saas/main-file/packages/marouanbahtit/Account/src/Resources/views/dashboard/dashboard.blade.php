@extends('layouts.main')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('page-breadcrumb')
    {{ __('Account') }}
@endsection
@push('css')
    <style>
    .nav-pills .nav-item a:hover,
    .nav-pills .nav-item a:focus {
        color: #0CAF60;
    }

    .nav-pills .nav-item a.active {
        text-decoration: none;
    }
    </style>
@endpush
@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="row row-gap mb-4 ">
        <div class="col-xl-6 col-12">
            <div class="dashboard-card">
                <img src="{{ asset('assets/images/layer.png')}}" class="dashboard-card-layer" alt="layer">
                <div class="card-inner">
                    <div class="card-content">
                        <h2>{{Auth::user()->ActiveWorkspaceName()}}</h2>
                        <p>{{ __('Simplifies accounting with streamlined invoicing, bill tracking, and real-time financial insights.') }}</p>
                    </div>
                    <div class="card-icon  d-flex align-items-center justify-content-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="118" height="118" viewBox="0 0 118 118" fill="none">
                        <path opacity="0.6" d="M103.25 58.7416V88.4877C103.25 98.321 98.3333 103.238 88.5 103.238H29.5C19.6667 103.238 14.75 98.321 14.75 88.4877V58.6924C16.225 60.4132 18.0933 61.741 20.3058 62.626C25.7142 64.7401 33.1383 67.2479 41.7917 69.0179C43.8075 69.4112 45.7741 68.4275 46.905 66.7558C49.56 62.7733 53.985 59.8729 59 59.8729C64.015 59.8729 68.44 62.7733 71.095 66.7558C72.275 68.4275 74.2416 69.4112 76.2575 69.0179C84.9108 67.2479 92.335 64.7401 97.6941 62.626C99.8575 61.741 101.775 60.4133 103.25 58.7416Z" fill="#18BF6B"/>
                        <path d="M103.25 44.2412V58.7451C101.775 60.4168 99.8575 61.7445 97.6941 62.6295C92.335 64.7437 84.9108 67.2514 76.2575 69.0214C74.2416 69.4147 72.275 68.431 71.095 66.7593C68.44 62.7768 64.015 59.8765 59 59.8765C53.985 59.8765 49.56 62.7768 46.905 66.7593C45.7741 68.431 43.8075 69.4147 41.7917 69.0214C33.1383 67.2514 25.7142 64.7437 20.3058 62.6295C18.0933 61.7445 16.225 60.4168 14.75 58.6959V44.2412C14.75 34.4079 19.6667 29.4912 29.5 29.4912H88.5C98.3333 29.4912 103.25 34.4079 103.25 44.2412Z" fill="#18BF6B"/>
                        <path opacity="0.6" d="M82.3504 22.1247V29.4997H74.9754V22.1247C74.9754 21.4363 74.4346 20.8955 73.7463 20.8955H44.2463C43.5579 20.8955 43.0171 21.4363 43.0171 22.1247V29.4997H35.6421V22.1247C35.6421 17.4047 39.4771 13.5205 44.2463 13.5205H73.7463C78.4663 13.5205 82.3504 17.4047 82.3504 22.1247Z" fill="#18BF6B"/>
                        <path d="M59.0061 78.6663C56.297 78.6663 54.0796 76.4637 54.0796 73.7497C54.0796 71.0357 56.2577 68.833 58.9668 68.833H59.0061C61.7152 68.833 63.9129 71.0357 63.9129 73.7497C63.9129 76.4637 61.7152 78.6663 59.0061 78.6663Z" fill="#18BF6B"/>
                    </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-12">
            <div class="row dashboard-wrp">
                <div class="col-sm-6 col-12">
                    <div class="dashboard-project-card">
                        <div class="card-inner  d-flex justify-content-between">
                            <div class="card-content">
                                <div class="theme-avtar bg-white">
                                    <i class="ti ti-users text-danger"></i>
                                </div>
                                <a href="{{ route('customer.index') }}"><h3 class="mt-3 mb-0 text-danger">{{ __('Customers') }}</h3>                                </a>
                            </div>
                            <h3 class="mb-0">{{ \Workdo\Account\Entities\AccountUtility::countCustomers() }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-12">
                    <div class="dashboard-project-card">
                        <div class="card-inner  d-flex justify-content-between">
                            <div class="card-content">
                                <div class="theme-avtar bg-white">
                                    <i class="ti ti-note"></i>
                                </div>
                                <a href="{{ route('vendors.index') }}"><h3 class="mt-3 mb-0">{{ __('Vendors') }}</h3></a>
                            </div>
                            <h3 class="mb-0">{{ \Workdo\Account\Entities\AccountUtility::countVendors() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-12">
                    <div class="dashboard-project-card">
                        <div class="card-inner  d-flex justify-content-between">
                            <div class="card-content">
                                <div class="theme-avtar bg-white">
                                    <i class="ti ti-file-invoice"></i>
                                </div>
                                <a href="{{ route('invoice.index') }}"><h3 class="mt-3 mb-0">{{ __('Invoices') }}</h3></a>
                            </div>
                            <h3 class="mb-0">{{ \App\Models\Invoice::countInvoices() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-12">
                    <div class="dashboard-project-card">
                        <div class="card-inner d-flex justify-content-between">
                            <div class="card-content">
                                <div class="theme-avtar bg-white">
                                    <i class="ti ti-report-money"></i>
                                </div>
                                <a href="{{ route('bill.index') }}"><h3 class="mt-3 mb-0">{{ __('Bills') }}</h3></a>
                            </div>
                            <h3 class="mb-0">{{ \Workdo\Account\Entities\AccountUtility::countBills() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
            <div class="col-xxl-7 d-flex flex-column">
                <div class="card h-100" >
                    <div class="card-header">
                        <h5 class="mt-1 mb-0">{{ __('Account Balance') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive custom-scrollbar account-info-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Bank') }}</th>
                                        <th>{{ __('Holder Name') }}</th>
                                        <th>{{ __('Balance') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bankAccountDetail as $bankAccount)
                                        <tr class="font-style">
                                            <td>{{ $bankAccount->bank_name }}</td>
                                            <td class="text-capitalize">{{ $bankAccount->holder_name }}</td>
                                            <td>{{ currency_format_with_sym($bankAccount->opening_balance) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">
                                                <div class="text-center">
                                                    <h6>{{ __('there is no account balance') }}</h6>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mt-1 mb-0">{{ __('Cashflow') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="cash-flow"></div>
                    </div>
                </div>

            </div>

            <div class="col-xxl-7">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Income & Expense') }}
                            <span class="float-end text-muted">{{ __('Current Year') . ' - ' . $currentYear }}</span>
                        </h5>

                    </div>
                    <div class="card-body">
                        <div id="incExpBarChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-5 d-flex flex-column">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>{{ __('Income By Category') }}
                            <span class="float-end text-muted">{{ __('Year') . ' - ' . $currentYear }}</span>
                        </h5>

                    </div>
                    <div class="card-body d-flex flex-column justify-content-center h-100">
                        <div id="incomeByCategory"></div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mt-1 mb-0">{{ __('Latest Income') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive custom-scrollbar account-info-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Amount Due') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestIncome as $income)
                                        <tr>
                                            <td>{{ company_date_formate($income->date) }}</td>
                                            <td>{{ !empty($income->customer) ? $income->customer->name : '-' }}</td>
                                            <td>{{ currency_format_with_sym($income->amount) }}</td>
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
            <div class="col-xxl-5 d-flex flex-column">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>{{ __('Expense By Category') }}
                            <span class="float-end text-muted">{{ __('Year') . ' - ' . $currentYear }}</span>
                        </h5>

                    </div>
                    <div class="card-body d-flex flex-column justify-content-center h-100">
                        <div id="expenseByCategory"></div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-7 d-flex flex-column">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mt-1 mb-0">{{ __('Latest Expense') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive custom-scrollbar account-info-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Vendor') }}</th>
                                        <th>{{ __('Amount Due') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestExpense as $expense)
                                        <tr>
                                            <td>{{ company_date_formate($expense->date) }}</td>
                                            <td>{{ !empty($expense->vendor) ? $expense->vendor->name : '-' }}</td>
                                            <td>{{ currency_format_with_sym($expense->amount) }}</td>
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
            <div class="col-xxl-5 d-flex flex-column">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mt-1 mb-0">{{ __('Income Vs Expense') }}</h5>
                    </div>
                    <div class="card-body h-100">
                        <div class="row row-gap">

                            <div class="col-md-6 col-12 d-flex flex-column justify-content-center">
                                <div class="d-flex align-items-start">
                                    <div class="badge theme-avtar bg-primary">
                                        <i class="ti ti-report-money"></i>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted text-sm mb-0">{{ __('Income Today') }}</p>
                                        <h4 class="mb-0 text-success">
                                            {{ currency_format_with_sym(\Workdo\Account\Entities\AccountUtility::todayIncome()) }}
                                        </h4>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12 d-flex flex-column justify-content-center">
                                <div class="d-flex align-items-start">
                                    <div class="badge theme-avtar bg-info">
                                        <i class="ti ti-file-invoice"></i>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted text-sm mb-0">{{ __('Expense Today') }}</p>
                                        <h4 class="mb-0 text-info">
                                            {{ currency_format_with_sym(\Workdo\Account\Entities\AccountUtility::todayExpense()) }}
                                        </h4>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12 d-flex flex-column justify-content-center">
                                <div class="d-flex align-items-start">
                                    <div class="badge theme-avtar bg-warning">
                                        <i class="ti ti-report-money"></i>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted text-sm mb-0">{{ __('Income This Month') }}</p>
                                        <h4 class="mb-0 text-warning">
                                            {{ currency_format_with_sym(\Workdo\Account\Entities\AccountUtility::incomeCurrentMonth()) }}
                                        </h4>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12 d-flex flex-column justify-content-center">
                                <div class="d-flex align-items-start">
                                    <div class="badge theme-avtar bg-danger">
                                        <i class="ti ti-file-invoice"></i>
                                    </div>
                                    <div class="ms-2">
                                        <p class="text-muted text-sm mb-0">{{ __('Expense This Month') }}</p>
                                        <h4 class="mb-0 text-danger">
                                            {{ currency_format_with_sym(\Workdo\Account\Entities\AccountUtility::expenseCurrentMonth()) }}
                                        </h4>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-7 d-flex flex-column">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mt-1 mb-0">{{ __('Recent Invoices') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive custom-scrollbar account-info-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Issue Date') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentInvoice as $invoice)
                                        <tr>
                                            <td>{{ \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id) }}
                                            </td>
                                            <td>{{ !empty($invoice->customer) ? $invoice->customer->name : '' }} </td>
                                            <td>{{ company_date_formate($invoice->issue_date) }}</td>
                                            <td>{{ company_date_formate($invoice->due_date) }}</td>
                                            <td>{{ currency_format_with_sym($invoice->getTotal()) }}</td>
                                            <td>
                                                @if ($invoice->status == 0)
                                                    <span
                                                        class="p-2 px-3 badge bg-info">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 1)
                                                    <span
                                                        class="p-2 px-3 badge bg-primary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 2)
                                                    <span
                                                        class="p-2 px-3 badge bg-secondary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 3)
                                                    <span
                                                        class="p-2 px-3 badge bg-warning">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                @elseif($invoice->status == 4)
                                                    <span
                                                        class="p-2 px-3 badge bg-success">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                @endif
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
            <div class="col-xxl-5 d-flex flex-column">
                <div class="card h-100">
                    <div class="card-header">
                        <ul class="nav nav-pills information-tab" id="pills-tab" role="tablist" style="width: fit-content">
                            <li class="nav-item">
                                <a class="nav-link active" id="pills-Dashboard-tab" data-bs-toggle="pill"
                                    href="#invoice_weekly_statistics" role="tab" aria-controls="pills-home"
                                    aria-selected="true">{{ __('Invoices Weekly Statistics') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                                    href="#invoice_monthly_statistics" role="tab" aria-controls="pills-profile"
                                    aria-selected="false">{{ __('Invoices Monthly Statistics') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="invoice_weekly_statistics" role="tabpanel"
                                aria-labelledby="pills-home-tab">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0 ">
                                        <tbody class="list">
                                            <tr>
                                                <td class="border-top-0">
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Invoice Generated') }}
                                                    </p>

                                                </td>
                                                <td class="border-top-0">
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($weeklyInvoice['invoiceTotal']) }}
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Paid') }}</p>
                                                </td>
                                                <td>
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($weeklyInvoice['invoicePaid']) }}
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Due') }}</p>
                                                </td>
                                                <td>
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($weeklyInvoice['invoiceDue']) }}
                                                    </h4>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="invoice_monthly_statistics" role="tabpanel"
                                aria-labelledby="pills-profile-tab">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0 ">
                                        <tbody class="list">
                                            <tr>
                                                <td class="border-top-0">
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Invoice Generated') }}
                                                    </p>

                                                </td>
                                                <td class="border-top-0">
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($monthlyInvoice['invoiceTotal']) }}
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Paid') }}</p>
                                                </td>
                                                <td>
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($monthlyInvoice['invoicePaid']) }}
                                                    </h4>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Due') }}</p>
                                                </td>
                                                <td>
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($monthlyInvoice['invoiceDue']) }}
                                                    </h4>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-7 d-flex flex-column">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mt-1 mb-0">{{ __('Recent Bills') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive  custom-scrollbar account-info-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Vendor') }}</th>
                                        <th>{{ __('Bill Date') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentBill as $bill)
                                        <tr>
                                            <td>{{ \Workdo\Account\Entities\Bill::billNumberFormat($bill->bill_id) }}
                                            </td>
                                            <td>{{ !empty($bill->vendor_name) ? $bill->vendor_name : '' }} </td>
                                            <td>{{ company_date_formate($bill->bill_date) }}</td>
                                            <td>{{ company_date_formate($bill->due_date) }}</td>
                                            <td>{{ currency_format_with_sym($bill->getTotal()) }}</td>
                                            <td>
                                                @if ($bill->status == 0)
                                                    <span
                                                        class="p-2 px-3 badge bg-info">{{ __(\Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                @elseif($bill->status == 1)
                                                    <span
                                                        class="p-2 px-3 badge bg-primary">{{ __(\Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                @elseif($bill->status == 2)
                                                    <span
                                                        class="p-2 px-3 badge bg-secondary">{{ __(\Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                @elseif($bill->status == 3)
                                                    <span
                                                        class="p-2 px-3 badge bg-warning">{{ __(\Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                @elseif($bill->status == 4)
                                                    <span
                                                        class="p-2 px-3 badge bg-success">{{ __(\Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                                @endif
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
            <div class="col-xxl-5 d-flex flex-column">
                <div class="card h-100">
                    <div class="card-header">
                        <ul class="nav nav-pills information-tab" id="pills-tab" role="tablist" style="width: fit-content">
                            <li class="nav-item">
                                <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                                    href="#bills_weekly_statistics" role="tab" aria-controls="pills-home"
                                    aria-selected="true">{{ __('Bills Weekly Statistics') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                                    href="#bills_monthly_statistics" role="tab" aria-controls="pills-profile"
                                    aria-selected="false">{{ __('Bills Monthly Statistics') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">

                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="bills_weekly_statistics" role="tabpanel"
                                aria-labelledby="pills-home-tab">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0 ">
                                        <tbody class="list">
                                            <tr>
                                                <td class="border-top-0">
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Bill Generated') }}</p>

                                                </td>
                                                <td class="border-top-0">
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($weeklyBill['billTotal']) }}</h4>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Paid') }}</p>
                                                </td>
                                                <td>
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($weeklyBill['billPaid']) }}</h4>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Due') }}</p>
                                                </td>
                                                <td>
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($weeklyBill['billDue']) }}</h4>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="bills_monthly_statistics" role="tabpanel"
                                aria-labelledby="pills-profile-tab">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0 ">
                                        <tbody class="list">
                                            <tr>
                                                <td class="border-top-0">
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Bill Generated') }}</p>

                                                </td>
                                                <td class="border-top-0">
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($monthlyBill['billTotal']) }}</h4>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Paid') }}</p>
                                                </td>
                                                <td>
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($monthlyBill['billPaid']) }}</h4>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h5 class="mb-0">{{ __('Total') }}</h5>
                                                    <p class="text-muted text-sm mb-0">{{ __('Due') }}</p>
                                                </td>
                                                <td>
                                                    <h4 class="text-muted">
                                                        {{ currency_format_with_sym($monthlyBill['billDue']) }}</h4>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @if (module_is_active('Goal'))
                @include('goal::dashboard.dshboard_div')
            @endif
        </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        (function() {
            var chartBarOptions = {
                series: [{
                        name: "{{ __('Income') }}",
                        data: {!! json_encode($incExpLineChartData['income']) !!}
                    },
                    {
                        name: "{{ __('Expense') }}",
                        data: {!! json_encode($incExpLineChartData['expense']) !!}
                    }
                ],

                chart: {
                    height: 300,
                    type: 'area',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode($incExpLineChartData['day']) !!},
                    title: {
                        text: '{{ __('Date') }}'
                    }
                },
                colors: ['#ffa21d', '#FF3A6E'],

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                yaxis: {
                    title: {
                        text: '{{ __('Amount') }}'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#cash-flow"), chartBarOptions);
            arChart.render();
        })();

        (function() {
            var options = {
                chart: {
                    height: 180,
                    type: 'bar',
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [{
                    name: "{{ __('Income') }}",
                    data: {!! json_encode($incExpBarChartData['income']) !!}
                }, {
                    name: "{{ __('Expense') }}",
                    data: {!! json_encode($incExpBarChartData['expense']) !!}
                }],
                xaxis: {
                    categories: {!! json_encode($incExpBarChartData['month']) !!},
                },
                colors: ['#3ec9d6', '#FF3A6E'],
                fill: {
                    type: 'solid',
                },
                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                },
                markers: {
                    size: 4,
                    colors: ['#3ec9d6', '#FF3A6E', ],
                    opacity: 0.9,
                    strokeWidth: 2,
                    hover: {
                        size: 7,
                    }
                }
            };
            var chart = new ApexCharts(document.querySelector("#incExpBarChart"), options);
            chart.render();
        })();

        (function() {
            var options = {
                chart: {
                    height: 140,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                series: {!! json_encode($expenseCatAmount) !!},
                colors: {!! json_encode($expenseCategoryColor) !!},
                labels: {!! json_encode($expenseCategory) !!},
                legend: {
                    show: true
                }
            };
            var chart = new ApexCharts(document.querySelector("#expenseByCategory"), options);
            chart.render();
        })();

        (function() {
            var options = {
                chart: {
                    height: 140,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                series: {!! json_encode($incomeCatAmount) !!},
                colors: {!! json_encode($incomeCategoryColor) !!},
                labels: {!! json_encode($incomeCategory) !!},
                legend: {
                    show: true
                }
            };
            var chart = new ApexCharts(document.querySelector("#incomeByCategory"), options);
            chart.render();
        })();
    </script>
@endpush
