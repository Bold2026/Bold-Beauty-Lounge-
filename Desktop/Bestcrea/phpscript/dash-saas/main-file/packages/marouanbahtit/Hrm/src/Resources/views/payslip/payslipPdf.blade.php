@extends('layouts.main')
@section('page-title')
    {{ __('Payslip') }}
@endsection
@section('page-action')
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
@section('content')
    <div class="main-content">
        <div class="row justify-content-center">
            <div class="col-xxl-8">
                <div class="card p-3 mb-0">
                    <div class="invoice" id="printableArea">
                        <div class="invoice-print">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="invoice-title d-flex  justify-content-between">
                                        <div class="">
                                            <img src="{{ get_file(sidebar_logo()) }}" alt="{{ config('app.name', 'WorkDo') }}"
                                                class="logo logo-lg"style="max-width: 150px; object-fit: cover;">
                                        </div>
                                        <div class="text-md-right" style="text-align: end; font-size:20px;">
                                            <a class="btn btn-sm btn-primary text-white" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                title="{{ __('Download') }}" onclick="saveAsPDF()"><i class="ti ti-download"></i></a>
                                        </div>
                                    </div>
                                    <div class="invoice-billed p-sm-4 p-3  mt-3">
                                        <div class="row row-gap">
                                            <div class="col-sm-6">
                                                <ul class="p-0 gap-1 mb-0 d-flex flex-column">
                                                    <li class="list-none gap-1 d-flex"><strong>{{ __('Name') }} :</strong> <p class="mb-0 flex-1">{{ $employee->name }}</p></li>
                                                    <li class="list-none gap-1 d-flex"><strong>{{ __('Position') }} :</strong> <p class="mb-0 flex-1">{{ __('Employee') }}</p></li>
                                                    <li class="list-none gap-1 d-flex">  <strong>{{ __('Salary Date') }} :</strong>
                                                        <p class="mb-0 flex-1">{{ company_date_formate($employee->created_at) }}</p></li>
                                                </ul>
                                            </div>
                                            <div class="col-sm-6 text-md-right">
                                                <ul class="p-0 gap-1 mb-0 d-flex flex-column">
                                                    <li class="list-none d-flex gap-1">
                                                        <strong>{{ !empty($company_settings['company_name']) ? $company_settings['company_name'] : '' }} :
                                                        </strong>
                                                        <p class="mb-0 flex-1">{{ !empty($company_settings['company_address']) ? $company_settings['company_address'] : '' }}
                                                            ,
                                                            {{ !empty($company_settings['company_city']) ? $company_settings['company_city'] : '' }},
                                                            {{ !empty($company_settings['company_state']) ? $company_settings['company_state'] : '' }}</p>
                                                    </li>
                                                    <li class="list-none d-flex gap-1">
                                                        <strong>{{ __('Salary Slip') }} :</strong>
                                                        <p class="mb-0">{{ company_date_formate($payslip->salary_month) }}</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive invoice-summary p-0">
                                <table class="table table-striped table-hover payslip-table table-md mb-4">
                                    <tbody>
                                        <tr>
                                            <th class="bg-primary text-white text-start">{{ __('Earning') }}</th>
                                            <th class="bg-primary text-white text-center">{{ __('Title') }}</th>
                                            <th class="text-right bg-primary text-white text-center">{{ __('Amount') }}</th>
                                        </tr>
                                        <tr>
                                            <td class="text-start">{{ __('Basic Salary') }}</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">
                                                {{ currency_format_with_sym($payslip->basic_salary) }}</td>
                                        </tr>
                                        @php
                                            $allowances = json_decode($payslipDetail['payslip']->allowance);
                                        @endphp
                                        @foreach ($allowances as $allowance)
                                            <tr>
                                                <td class="text-start">{{ __('Allowance') }}</td>
                                                <td class="text-center">{{ $allowance->title }}</td>
                                                @if ($allowance->type != 'percentage')
                                                    <td class="text-center">
                                                        {{ currency_format_with_sym($allowance->amount) }}</td>
                                                @else
                                                    <td class="text-center">{{ $allowance->amount }}%
                                                        ({{ currency_format_with_sym(($allowance->amount * $payslip->basic_salary) / 100) }})
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        @php
                                            $commissions = json_decode($payslipDetail['payslip']->commission);
                                        @endphp
                                        @foreach ($commissions as $commission)
                                            <tr>
                                                <td class="text-start">{{ __('Commission') }}</td>
                                                <td class="text-center">{{ $commission->title }}</td>
                                                @if ($commission->type != 'percentage')
                                                    <td class="text-center">
                                                        {{ currency_format_with_sym($commission->amount) }}</td>
                                                @else
                                                    <td class="text-center">{{ $commission->amount }}%
                                                        ({{ currency_format_with_sym(($commission->amount * $payslip->basic_salary) / 100) }})
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach

                                        @php
                                            $other_payments = json_decode($payslipDetail['payslip']->other_payment);
                                        @endphp
                                        @foreach ($other_payments as $other_payment)
                                            <tr>
                                                <td class="text-start">{{ __('Other Payment') }}</td>
                                                <td class="text-center">{{ $other_payment->title }}</td>
                                                @if ($other_payment->type != 'percentage')
                                                    <td class="text-center">
                                                        {{ currency_format_with_sym($other_payment->amount) }}</td>
                                                @else
                                                    <td class="text-center">{{ $other_payment->amount }}%
                                                        ({{ currency_format_with_sym(($other_payment->amount * $payslip->basic_salary) / 100) }})
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach

                                        @php
                                            $overtimes = json_decode($payslipDetail['payslip']->overtime);
                                        @endphp
                                        @foreach ($overtimes as $overtime)
                                            <tr>
                                                <td class="text-start">{{ __('OverTime') }}</td>
                                                <td class="text-center">{{ $overtime->title }}</td>
                                                <td class="text-center">
                                                    {{ currency_format_with_sym($overtime->number_of_days * $overtime->hours * $overtime->rate) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        @php
                                            $company_contributions = json_decode($payslipDetail['payslip']->company_contribution);
                                        @endphp
                                        @foreach ($company_contributions as $company_contribution)
                                            <tr>
                                                <td class="text-start">{{ __('Company Contribution') }}</td>
                                                <td class="text-center">{{ $company_contribution->title }}</td>
                                                @if ($company_contribution->type != 'percentage')
                                                    <td class="text-center">
                                                        {{ currency_format_with_sym($company_contribution->amount) }}</td>
                                                @else
                                                    <td class="text-center">{{ $company_contribution->amount }}%
                                                        ({{ currency_format_with_sym(($company_contribution->amount * $payslip->basic_salary) / 100) }})
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-responsive  invoice-summary p-0">
                                <table class="table table-striped payslip-table table-hover table-md">
                                    <tbody>
                                        <tr>
                                            <th class="bg-primary text-white text-start">{{ __('Deduction') }}</th>
                                            <th class="bg-primary text-white text-center">{{ __('Title') }}</th>
                                            <th class="text-right bg-primary text-white text-center">{{ __('Amount') }}</th>
                                        </tr>

                                        @php
                                            $loans = json_decode($payslipDetail['payslip']->loan);
                                        @endphp
                                        @foreach ($loans as $loan)
                                            <tr>
                                                <td class="text-start">{{ __('Loan') }}</td>
                                                <td class="text-center">{{ $loan->title }}</td>
                                                @if ($loan->type != 'percentage')
                                                    <td class="text-center">
                                                        {{ currency_format_with_sym($loan->amount) }}</td>
                                                @else
                                                    <td class="text-center">{{ $loan->amount }}%
                                                        ({{ currency_format_with_sym(($loan->amount * $payslip->basic_salary) / 100) }})
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach

                                        @php
                                            $saturation_deductions = json_decode(
                                                $payslipDetail['payslip']->saturation_deduction,
                                            );
                                        @endphp
                                        @foreach ($saturation_deductions as $saturation_deduction)
                                            <tr>
                                                <td class="text-start">{{ __('Saturation Deduction') }}</td>
                                                <td class="text-center">{{ $saturation_deduction->title }}</td>
                                                @if ($saturation_deduction->type != 'percentage')
                                                    <td class="text-center">
                                                        {{ currency_format_with_sym($saturation_deduction->amount) }}
                                                    </td>
                                                @else
                                                    <td class="text-center">{{ $saturation_deduction->amount }}%
                                                        ({{ currency_format_with_sym(($saturation_deduction->amount * $payslip->basic_salary) / 100) }})
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="row row-gap mt-4">
                                <div class="col-md-6">
                                    <div class="invoice-billed p-3">
                                        <div class="card mb-3 p-2">
                                            <strong>{{__('Earning')}}</strong>
                                        </div>
                                        <div class="invoice-detail-item d-flex align-items-center justify-content-between pb-2 ps-2">
                                            <div class="invoice-detail-name font-weight-bold">{{ __('Total Earning') }}
                                            </div>
                                            <div class="invoice-detail-value">
                                                <strong>{{ currency_format_with_sym($payslipDetail['totalEarning']) }}</strong></div>
                                        </div>
                                        <div class="invoice-detail-item d-flex align-items-center justify-content-between pb-2 ps-2">
                                            <div class="invoice-detail-name">
                                                {{ __('Taxable Earning')}}
                                                <p class="mb-0 text-primary" style="font-size: 10px">{{__('(Total Earning - Total Deduction)')}}</p>
                                            </div>
                                            <div class="invoice-detail-value">
                                                <strong>{{ currency_format_with_sym($payslipDetail['taxable_earning']) }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 ">
                                    <div class="invoice-billed p-3">
                                        <div class="card mb-3 p-2">
                                            <strong>{{__('Deduction')}}</strong>
                                        </div>
                                        <div class="invoice-detail-item d-flex align-items-center justify-content-between pb-2 ps-2">
                                            <div class="invoice-detail-name font-weight-bold">{{ __('Total Deduction') }}
                                            </div>
                                            <div class="invoice-detail-value">
                                                <strong>{{ currency_format_with_sym($payslipDetail['totalDeduction']) }}</strong></div>
                                        </div>
                                        <div class="invoice-detail-item d-flex align-items-center justify-content-between pb-2 ps-2">
                                            <div class="invoice-detail-name">
                                                {{ __('Tax')}}
                                                <p class="mb-0 text-primary" style="font-size: 10px">{{__('(Total Earning x '.$payslipDetail['tax_rate'].'%)')}}</p>
                                            </div>
                                            <div class="invoice-detail-value">
                                                <strong>{{ currency_format_with_sym($payslipDetail['tax_amount']) }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card net-salary p-3 mt-3 mb-0">
                            <div class="invoice-detail-item d-flex align-items-center justify-content-between">
                                <div class="invoice-detail-name">
                                <strong>{{ __('Net Salary')}}</strong>
                                    <p class="mb-0 text-primary" style="font-size: 10px">{{__('Taxable Earning - Tax    ')}}</p>
                                </div>
                                <div class="invoice-detail-value">
                                    <strong>{{ currency_format_with_sym($payslip->net_payble) }}</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('packages/workdo/Hrm/src/Resources/assets/js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var button = document.querySelector('.btn-primary');
            button.style.display = 'none';
            var opt = {
                margin: 0.2,
                filename: '{{ $employee->name }}',
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A4'
                }
            };
            html2pdf().set(opt).from(element).save().finally(() => {
                button.style.display = '';
            });
        }
    </script>
@endsection
