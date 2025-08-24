@extends('layouts.main')
@section('page-title')
    {{ __('Manage Attendance List') }}
@endsection
@section('page-breadcrumb')
    {{ __('Attendance List') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@php
    $company_settings = getCompanyAllSetting();
@endphp
@section('page-action')
    <div>
        @permission('attendance import')
            <a href="#" class="btn btn-sm btn-primary me-1" data-ajax-popup="true" data-title="{{ __('Import Attendance') }}"
                data-url="{{ route('attendance.file.import') }}" data-toggle="tooltip" title="{{ __('Import') }}"><i
                    class="ti ti-file-import"></i>
            </a>
        @endpermission
        @permission('attendance create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Attendance') }}"
                data-url="{{ route('attendance.create') }}" data-toggle="tooltip" title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    <div class="row row-gap-4 align-items-end justify-content-xl-end">
                            <div style="width: auto" class="mb-1">
                                {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}<br>
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="monthly" value="monthly" name="type"
                                        class="form-check-input pointer"
                                        {{ isset($_GET['type']) && $_GET['type'] == 'monthly' ? 'checked' : 'checked' }}>
                                    <label class="form-check-label pointer" for="monthly">{{ __('Monthly') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="daily" value="daily" name="type"
                                        class="form-check-input pointer"
                                        {{ isset($_GET['type']) && $_GET['type'] == 'daily' ? 'checked' : '' }}>
                                    <label class="form-check-label pointer" for="daily">{{ __('Daily') }}</label>
                                </div>
                            </div>

                                <div class="col-xl-3 col-md-4 col-sm-6 col-12 month">
                                    <div class="btn-box form-group mb-0">
                                        {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                        {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'month-btn form-control month-btn']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-4 col-sm-6 col-12 date d-none">
                                    <div class="btn-box form-group mb-0">
                                        {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                        {!! Form::date('date', isset($_GET['date']) ? $_GET['date'] : null, [
                                            'class' => 'form-control ',
                                            'placeholder' => 'Select Date',
                                        ]) !!}
                                    </div>
                                </div>
                                @if (in_array(Auth::user()->type, Auth::user()->not_emp_type))
                                    <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                                        <div class="btn-box form-group mb-0">
                                            {{ Form::label('branch', !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'), ['class' => 'form-label']) }}
                                            {{ Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                                        <div class="btn-box form-group mb-0">
                                            {{ Form::label('department', !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department'), ['class' => 'form-label']) }}
                                            {{ Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control select']) }}
                                        </div>
                                    </div>
                                @endif
                        <div class="col-auto mb-1">
                            <div class="row">
                                <div class="col-auto">
                                    <a class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="{{ __('Apply') }}"
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
    </div>
    <div class="col-sm-12">
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
        $('input[name="type"]:radio').on('change', function(e) {
            var type = $(this).val();
            if (type == 'monthly') {
                $('.month').addClass('d-block');
                $('.month').removeClass('d-none');
                $('.date').addClass('d-none');
                $('.date').removeClass('d-block');
            } else {
                $('.date').addClass('d-block');
                $('.date').removeClass('d-none');
                $('.month').addClass('d-none');
                $('.month').removeClass('d-block');
            }
        });
        $('input[name="type"]:radio:checked').trigger('change');
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
                    $('#department').append('<option value="" disabled>{{ __('All') }}</option>');

                    $.each(data, function(key, value) {
                        $('#department').append('<option value="' + key + '">' + value + '</option>');
                    });
                    $('#department').val('');
                }
            });
        }
    </script>
@endpush
