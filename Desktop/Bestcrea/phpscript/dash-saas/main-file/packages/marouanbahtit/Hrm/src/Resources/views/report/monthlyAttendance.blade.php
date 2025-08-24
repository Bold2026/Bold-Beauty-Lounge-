@extends('layouts.main')
@section('page-title')
    {{ __('Manage Monthly Attendance') }}
@endsection
@section('page-breadcrumb')
    {{ __('Monthly Attendance') }}
@endsection
@section('page-action')
    <div>

    </div>
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
@section('content')
    <style>
        .form-check-input {
            cursor: pointer;
        }

        .form-label {
            cursor: pointer;
        }
    </style>
    <div class="row">
        <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
            <div class=" mt-2 " id="" style="">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['report.monthly.attendance'], 'method' => 'get', 'id' => 'report_monthly_attendance']) }}
                        <div class="row row-gap-4 align-items-end justify-content-xxl-end">
                            <div style="width: auto" class="mb-1">
                                {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}<br>
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="monthly" value="monthly" name="type"
                                        class="form-check-input"
                                        {{ isset($_GET['type']) && $_GET['type'] == 'monthly' ? 'checked' : 'checked' }}>
                                    {{ Form::label('monthly', __('Monthly'), ['class' => '']) }}
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="weekly" value="weekly" name="type"
                                        class="form-check-input weekly "
                                        {{ isset($_GET['type']) && $_GET['type'] == 'weekly' ? 'checked' : '' }}>
                                    {{ Form::label('weekly', __('Weekly'), ['class' => '']) }}
                                </div>
                            </div>

                            <div class="col-xxl-2 col-md-4 col-sm-6 col-12 month">
                                <div class="btn-box form-group mb-0">
                                    {{ Form::label('month', __(' Month'), ['class' => 'form-label']) }}
                                    {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'month-btn form-control month-btn']) }}
                                </div>
                            </div>
                            <div class="col-xxl-2 col-md-4 col-sm-6 col-12 week d-none">
                                <div class="btn-box form-group mb-0">
                                    {{ Form::label('week', __(' Week'), ['class' => 'form-label']) }}
                                    {{ Form::week('week', isset($_GET['week']) ? $_GET['week'] : date('Y-\WW'), ['class' => 'week-btn form-control week-btn']) }}
                                </div>
                            </div>
                            <div class="col-xxl-2 col-md-4 col-sm-6 col-12 ">
                                <div class="btn-box form-group mb-0">
                                    {{ Form::label('branch', !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'), ['class' => 'form-label']) }}
                                    {{ Form::select('branch_id', $branch, isset($_GET['branch_id']) ? $_GET['branch_id'] : null, ['class' => 'form-control', 'id' => 'branch_id', 'required' => 'required', 'placeholder' => __('Select ' . (!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('select Branch')))]) }}

                                </div>
                            </div>
                            <div class="col-xxl-2 col-md-4 col-sm-6 col-12 ">
                                <div class="department_div form-group mb-0">
                                    {{ Form::label('department', !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department'), ['class' => 'form-label ']) }}
                                    {{ Form::select('department_id', [], isset($_GET['department_id']) ? $_GET['department_id'] : null, ['class' => 'form-control department_id', 'id' => 'department_id', 'required' => 'required', 'placeholder' => __('Select ' . (!empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department')))]) }}
                                </div>
                            </div>

                            <div class="col-xxl-2 col-md-4 col-sm-6 col-12">
                                <div class="form-group mb-0">
                                {{ Form::label('employee', __(' Employee'), ['class' => 'form-label']) }}
                                <div class="" id="employee_div">
                                    <select class="form-control choices" name="employee_id[]" id="employee_id"
                                        placeholder="Select Employee">
                                    </select>
                                </div>
                                </div>
                            </div>

                            <div class="col-auto float-end mb-1">
                                <a class="btn btn-sm btn-primary me-1"
                                    onclick="document.getElementById('report_monthly_attendance').submit(); return false;"
                                    data-bs-toggle="tooltip" title="{{ __('Apply') }}" data-bs-original-title="apply">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route('report.monthly.attendance') }}" class="btn btn-sm btn-danger"
                                    data-bs-toggle="tooltip" title="{{ __('Reset') }}" data-bs-original-title="Reset">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
        <div id="printableArea">
            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-primary badge">
                                        <i class="ti ti-report"></i>
                                    </div>
                                    <div class="ms-3">
                                        <input type="hidden"
                                            value="{{ $data['branch'] . ' ' . __('Branch') . ' ' . $data['curMonth'] . ' ' . __('Attendance Report of') . ' ' . $data['department'] . ' ' . 'Department' }}"
                                            id="filename">
                                        <h5 class="mb-1">{{ __('Report') }}</h5>
                                        <div>
                                            <p class="text-muted text-sm mb-1">{{ __('Attendance Summary') }}</p>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($data['branch'] != 'All')
                    <div class="col">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="theme-avtar bg-secondary badge">
                                            <i class="ti ti-sitemap"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="mb-1">
                                                {{ !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch') }}
                                            </h5>
                                            <p class="text-muted text-sm mb-1">
                                                {{ $data['branch'] }} </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($data['department'] != 'All')
                    <div class="col">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="theme-avtar bg-primary badge">
                                            <i class="ti ti-template"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="mb-1">
                                                {{ !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department') }}
                                            </h5>
                                            <p class="text-muted text-sm mb-1">{{ $data['department'] }}</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-secondary badge">
                                        <i class="ti ti-sum"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-1">{{ __('Duration') }}</h5>
                                        <p class="text-muted text-sm mb-1">{{ $data['curMonth'] }}
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="card mon-card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-primary badge">
                                        <i class="ti ti-file-report"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-1">{{ __('Attendance') }}</h5>
                                        <div>
                                            <p class="text-muted text-sm mb-1">{{ __('Total present') }}:
                                                {{ $data['totalPresent'] }}</p>
                                            <p class="text-muted text-sm mb-1">{{ __('Total leave') }}:
                                                {{ $data['totalLeave'] }}</p>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card mon-card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-secondary badge">
                                        <i class="ti ti-clock"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-1">{{ __('Overtime') }}</h5>
                                        <p class="text-muted text-sm mb-1">
                                            {{ __('Total overtime in hours') }} :
                                            {{ number_format($data['totalOvertime'], 2) }}</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card mon-card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-primary badge">
                                        <i class="ti ti-info-circle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-1">{{ __('Early leave') }}</h5>
                                        <p class="text-muted text-sm mb-1">{{ __('Total early leave in hours') }}:
                                            {{ number_format($data['totalEarlyLeave'], 2) }}</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card mon-card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-secondary badge">
                                        <i class="ti ti-alarm"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-1">{{ __('Employee late') }}</h5>
                                        <p class="text-muted text-sm mb-1">{{ __('Total late in hours') }} :
                                            {{ number_format($data['totalLate'], 2) }}
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>


            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-body table-border-style">
                            <div class="table-responsive py-4 attendance-table-responsive">
                                <table class="table ">
                                    <thead>
                                        <tr>
                                            <th class="active">{{ __('Name') }}</th>
                                            @foreach ($dates as $date)
                                                <th>{{ $date }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employeesAttendance as $attendance)
                                            <tr>
                                                <td>{{ $attendance['name'] }}</td>
                                                @foreach ($attendance['status'] as $status)
                                                    <td>
                                                        @if ($status == 'P')
                                                            <i
                                                                class="badge bg-success p-2   ">{{ __('P') }}</i>
                                                        @elseif($status == 'A')
                                                            <i
                                                                class="badge bg-danger p-2">{{ __('A') }}</i>
                                                        @endif
                                                    </td>
                                                @endforeach
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
@endsection
@push('scripts')
    <script>
        $('input[name="type"]:radio').on('change', function(e) {
            var type = $(this).val();
            if (type == 'monthly') {
                $('.month').addClass('d-block');
                $('.month').removeClass('d-none');
                $('.week').addClass('d-none');
                $('.week').removeClass('d-block');
            } else {
                $('.week').addClass('d-block');
                $('.week').removeClass('d-none');
                $('.month').addClass('d-none');
                $('.month').removeClass('d-block');
            }
        });

        $('input[name="type"]:radio:checked').trigger('change');
    </script>

    <script>
        $(document).ready(function() {
            var b_id = $('#branch_id').val();
            getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch_id]', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(bid) {

            $.ajax({
                url: '{{ route('report.getdepartment') }}',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('.department_id').empty();
                    $('.department_id').append('<option value="0"> {{ __('All') }} </option>');
                    var selectedDepartmentId =
                        '{{ isset($_GET['department_id']) ? $_GET['department_id'] : 0 }}';
                    $.each(data, function(key, value) {
                        var option = $('<option></option>').attr('value', key).text(value);

                        if (key == selectedDepartmentId) {
                            option.attr('selected', 'selected');
                        }
                        $('.department_id').append(option);

                    });
                }
            });
        }

        $(document).ready(function() {
            var department_id = $('#department_id').val();
            getEmployee(department_id);
        });

        $(document).on('change', '.department_id', function() {
            var department_id = $(this).val();
            getEmployee(department_id);
        });

        function getEmployee(did) {
            $.ajax({
                url: '{{ route('report.getemployee') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#employee_div').empty(); // Clear the div before adding new select

                    // Create a new select element with a unique ID
                    var emp_select = `<select class="form-control employee_id" name="employee_id[]" id="choices-multiple1" placeholder="Select Employee" multiple>
                                    <option value="0">{{ __('All') }}</option>
                                </select>`;

                    $('#employee_div').html(emp_select); // Add the new select element

                    // Populate the select with options
                    $.each(data, function(key, value) {
                        $('#choices-multiple1').append('<option value="' + key + '">' + value +
                            '</option>');
                    });

                    // Initialize Choices.js on the new select element
                    new Choices('#choices-multiple1', {
                        removeItemButton: true,
                    });

                }
            });
        }
    </script>
@endpush
