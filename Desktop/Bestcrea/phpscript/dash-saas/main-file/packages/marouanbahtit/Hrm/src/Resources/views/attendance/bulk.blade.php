@extends('layouts.main')
@section('page-title')
    {{ __('Manage Bulk Attendance') }}
@endsection
@section('page-breadcrumb')
    {{ __('Bulk Attendance') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
@section('content')
    <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
        <div class=" mt-2" id="" style="">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['attendance.bulkattendances'], 'method' => 'get', 'id' => 'bulkattendance_filter']) }}
                    <div class="row row-gap-4 align-items-end justify-content-xl-end">
                        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                            <div class="btn-box form-group mb-0">
                                {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                {!! Form::date('date', isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Select Date','max'=>date('Y-m-d')
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                            <div class="btn-box form-group mb-0">
                                {{ Form::label('branch', !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'), ['class' => 'form-label']) }}
                                {{ Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control  ', 'placeholder' => __('Select '.(!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('select Branch'))), 'id' => 'branch']) }}
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                            <div class="btn-box form-group mb-0">
                                {{ Form::label('department', !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department'), ['class' => 'form-label']) }}
                                {{ Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control select ', 'placeholder' => __('Select '.(!empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department'))), 'id' => 'department']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end mt-4">
                            <a  class="btn btn-sm btn-primary me-1"
                                onclick="document.getElementById('bulkattendance_filter').submit(); return false;"
                                data-bs-toggle="tooltip" title="{{ __('Apply') }}" data-bs-original-title="apply">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{ route('attendance.bulkattendance') }}" class="btn btn-sm btn-danger"
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
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                {{ Form::open(['route' => ['attendance.bulkattendance'], 'method' => 'post']) }}
                <div class="table-responsive">
                    <table class="table" id="">
                        <thead>
                            <tr>
                                <th width="10%">{{ __('Employee Id') }}</th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch') }}
                                </th>
                                <th>{{ !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department') }}
                                </th>
                                <th>
                                    <div class="form-group my-auto">
                                        <div class="custom-control ">
                                            <input class="form-check-input" type="checkbox" name="present_all"
                                                id="present_all" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="present_all">
                                                {{ __('Attendance') }}</label>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                                @php
                                    $attendance = Workdo\Hrm\Entities\Employee::present_status($employee->id, isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'));
                                @endphp
                                <tr>
                                    <td class="Id">
                                        <input type="hidden" value="{{ $employee->id }}" name="employee_id[]">
                                        @if (!empty($employee->employee_id))
                                            @permission('employee show')
                                                <a class="btn btn-outline-primary"
                                                    href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ Workdo\Hrm\Entities\Employee::employeeIdFormat($employee->employee_id) }}</a>
                                            @else
                                                <a
                                                    class="btn btn-outline-primary">{{ Workdo\Hrm\Entities\Employee::employeeIdFormat($employee->employee_id) }}</a>
                                            @endpermission
                                        @endif
                                    </td>
                                    <td>{{ $employee->name }}</td>
                                    <td>
                                        {{ !empty(Workdo\Hrm\Entities\Employee::Branchs($employee->branch_id)) ? Workdo\Hrm\Entities\Employee::Branchs($employee->branch_id)->name : '--' }}
                                    </td>
                                    <td>
                                        {{ !empty(Workdo\Hrm\Entities\Employee::Departments($employee->department_id)) ? Workdo\Hrm\Entities\Employee::Departments($employee->department_id)->name : '--' }}
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="form-check-input present" type="checkbox"
                                                            name="present-{{ $employee->id }}"
                                                            id="present{{ $employee->id }}"
                                                            {{ !empty($attendance) && $attendance->status == 'Present' ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                            for="present{{ $employee->id }}"></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="col-md-8 present_check_in {{ empty($attendance) ? 'd-none' : '' }} ">
                                                <div class="row">
                                                    <label class="col-md-2 control-label">{{ __('In') }}</label>
                                                    <div class="col-md-4">
                                                        <input type="time" class="form-control timepicker"
                                                            name="in-{{ $employee->id }}"
                                                            value="{{ !empty($attendance) && $attendance->clock_in != '00:00:00' ? $attendance->clock_in : $company_settings['company_start_time'] }}">
                                                    </div>

                                                    <label for="inputValue"
                                                        class="col-md-2 control-label">{{ __('Out') }}</label>
                                                    <div class="col-md-4">
                                                        <input type="time" class="form-control timepicker"
                                                            name="out-{{ $employee->id }}"
                                                            value="{{ !empty($attendance) && $attendance->clock_out != '00:00:00' ? $attendance->clock_out : $company_settings['company_end_time'] }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                                @empty
                                @include('layouts.nodatafound')
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="attendance-btn float-end pt-4">
                    <input type="hidden" value="{{ isset($_GET['date']) ? $_GET['date'] : date('Y-m-d') }}"
                        name="date">
                    <input type="hidden" value="{{ isset($_GET['branch']) ? $_GET['branch'] : '' }}" name="branch">
                    <input type="hidden" value="{{ isset($_GET['department']) ? $_GET['department'] : '' }}"
                        name="department">
                    {{ Form::submit(__('Update'), ['class' => 'btn btn-primary']) }}
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            if ($('.daterangepicker').length > 0) {
                $('.daterangepicker').daterangepicker({
                    format: 'yyyy-mm-dd',
                    locale: {
                        format: 'YYYY-MM-DD'
                    },
                });
            }
        });
    </script>
    <script>
        $('#present_all').click(function(event) {
            if (this.checked) {
                $('.present').each(function() {
                    this.checked = true;
                });

                $('.present_check_in').removeClass('d-none');
                $('.present_check_in').addClass('d-block');

            } else {
                $('.present').each(function() {
                    this.checked = false;
                });
                $('.present_check_in').removeClass('d-block');
                $('.present_check_in').addClass('d-none');
            }
        });
        $('.present').click(function(event) {
            var div = $(this).parent().parent().parent().parent().find('.present_check_in');

            if (this.checked) {
                div.removeClass('d-none');
                div.addClass('d-block');

            } else {
                div.removeClass('d-block');
                div.addClass('d-none');
            }
        });
    </script>
    <script type="text/javascript">
        $(document).on('change', '#branch', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(branch_id) {
            var data = {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            }
            $.ajax({
                url: '{{ route('hrm.employee.getdepartment') }}',
                method: 'POST',
                data: data,
                success: function(data) {
                    $('#department').empty();
                    $('#department').append(
                    '<option value="" disabled>{{ __('Select Department') }}</option>');

                    $.each(data, function(key, value) {
                        $('#department').append('<option value="' + key + '">' + value + '</option>');
                    });
                    $('#department').val('');
                }
            });
        }
    </script>

@endpush
