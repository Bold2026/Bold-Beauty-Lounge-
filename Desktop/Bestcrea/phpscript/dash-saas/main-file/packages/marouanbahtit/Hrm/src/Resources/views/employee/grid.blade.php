@extends('layouts.main')

@section('page-title')
    {{ __('Manage Employee') }}
@endsection

@section('title')
    {{ __('Manage Employee') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }}
@endsection

@section('page-action')
    <div class="d-flex">
        @stack('addButtonHook')
        @permission('employee import')
            <a href="#" class="btn btn-sm btn-primary me-2" data-ajax-popup="true" data-title="{{ __('Employee Import') }}"
                data-url="{{ route('employee.file.import') }}" data-toggle="tooltip" title="{{ __('Import') }}"><i
                    class="ti ti-file-import"></i>
            </a>
        @endpermission
        <a href="{{ route('employee.index') }}" class="btn btn-sm btn-primary btn-icon me-2" data-bs-toggle="tooltip"
            title="{{ __('List View') }}">
            <i class="ti ti-list text-white"></i>
        </a>
        @permission('employee create')
            <a href="{{ route('employee.create') }}" data-title="{{ __('Create New Employee') }}" data-bs-toggle="tooltip"
                title="" class="btn btn-sm btn-primary me-1" data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection

@section('content')
    <div class="row row-gap-2 mb-4">
        @foreach ($employees as $employee)
            <div class="col-xxl-3 col-xl-4 col-md-6">
                <div class="card user-card">
                    <div class="card-header border border-bottom p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="user-img-wrp d-flex align-items-center">
                                <div class="user-image rounded border-2 border border-primary">
                                    <a href="{{ check_file($employee->avatar) ? get_file($employee->avatar) : 'uploads/users-avatar/avatar.png' }}"
                                        target="_blank">
                                        <img src="{{ check_file($employee->avatar) ? get_file($employee->avatar) : 'uploads/users-avatar/avatar.png' }}"
                                            alt="user-image" class="h-100 w-100">
                                    </a>
                                </div>
                                <div class="user-content">
                                    @permission('employee show')
                                        @if (!empty($employee->employee_id) && $employee->is_disable == 1)
                                            <a
                                                href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">
                                                <h4 class="mb-2 text-break">{{ ucfirst($employee->name) }}</h4>
                                            </a>
                                        @else
                                            <h4 class="mb-2 text-break">{{ ucfirst($employee->name) }}</h4>
                                        @endif
                                    @else
                                        <h4 class="mb-2 text-break">{{ ucfirst($employee->name) }}</h4>
                                    @endpermission
                                    <span class="text-dark text-md">{{ $employee->email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body text-center p-3">
                        <div class="bottom-icons d-flex flex-wrap align-items-center justify-content-between">
                            <div class="edit-btn-wrp d-flex flex-wrap align-items-center">
                                @if ($employee->is_disable == 1)
                                    @if (Laratrust::hasPermission('employee show') ||
                                            Laratrust::hasPermission('employee edit') ||
                                            Laratrust::hasPermission('employee delete'))
                                        @if (!empty($employee->employee_id))
                                            @permission('employee show')
                                                <a href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                                    class="btn btn-sm border" data-bs-whatever="{{ __('Employee Details') }}"
                                                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}">
                                                    <svg width="16" height="16" viewBox="0 0 15 15" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M7.9997 10.8869C6.40637 10.8869 5.11304 9.59353 5.11304 8.00019C5.11304 6.40686 6.40637 5.11353 7.9997 5.11353C9.59304 5.11353 10.8864 6.40686 10.8864 8.00019C10.8864 9.59353 9.59304 10.8869 7.9997 10.8869ZM7.9997 6.11353C6.9597 6.11353 6.11304 6.96019 6.11304 8.00019C6.11304 9.04019 6.9597 9.88686 7.9997 9.88686C9.0397 9.88686 9.88637 9.04019 9.88637 8.00019C9.88637 6.96019 9.0397 6.11353 7.9997 6.11353Z"
                                                            fill="#060606" />
                                                        <path
                                                            d="M7.99967 14.0134C5.493 14.0134 3.12633 12.5467 1.49967 10C0.792999 8.90003 0.792999 7.10669 1.49967 6.00003C3.133 3.45336 5.49967 1.98669 7.99967 1.98669C10.4997 1.98669 12.8663 3.45336 14.493 6.00003C15.1997 7.10003 15.1997 8.89336 14.493 10C12.8663 12.5467 10.4997 14.0134 7.99967 14.0134ZM7.99967 2.98669C5.84633 2.98669 3.78633 4.28003 2.34633 6.54003C1.84633 7.32003 1.84633 8.68003 2.34633 9.46003C3.78633 11.72 5.84633 13.0134 7.99967 13.0134C10.153 13.0134 12.213 11.72 13.653 9.46003C14.153 8.68003 14.153 7.32003 13.653 6.54003C12.213 4.28003 10.153 2.98669 7.99967 2.98669Z"
                                                            fill="#060606" />
                                                    </svg>
                                                </a>
                                            @endpermission
                                            @permission('employee edit')
                                                <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                                    data-size="md" class="btn btn-sm border"
                                                    data-bs-original-title="{{ __('Edit') }}"
                                                    data-bs-whatever="{{ __('Edit Employee') }}" data-bs-toggle="tooltip">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        viewBox="0 0 16 16" fill="none">
                                                        <path
                                                            d="M1.56382 11.5713C1.40611 11.5713 1.24871 11.5112 1.12827 11.3908C0.887704 11.1502 0.887704 10.7603 1.12827 10.5197L10.7553 0.892668C10.9956 0.6521 11.3858 0.6521 11.6264 0.892668C11.867 1.13324 11.867 1.5232 11.6264 1.76376L1.99937 11.3908C1.87924 11.5109 1.72153 11.5713 1.56382 11.5713Z"
                                                            fill="#060606" />
                                                        <path
                                                            d="M0.61263 16.0078C0.569815 16.0078 0.526383 16.0031 0.482952 15.9939C0.150284 15.9224 -0.0616371 15.595 0.00982476 15.2623L0.961623 10.8258C1.03308 10.4932 1.36206 10.2819 1.69318 10.3527C2.02585 10.4242 2.23777 10.7516 2.16631 11.0843L1.21451 15.5208C1.1526 15.81 0.896938 16.0078 0.61263 16.0078Z"
                                                            fill="#060606" />
                                                        <path
                                                            d="M5.04863 15.056C4.89092 15.056 4.73352 14.9959 4.61308 14.8755C4.37251 14.6349 4.37251 14.245 4.61308 14.0044L14.2401 4.37767C14.4804 4.1371 14.8706 4.1371 15.1112 4.37767C15.3518 4.61824 15.3518 5.0082 15.1112 5.24877L5.48448 14.8755C5.36404 14.9959 5.20633 15.056 5.04863 15.056Z"
                                                            fill="#060606" />
                                                        <path
                                                            d="M0.611348 16.0078C0.32704 16.0078 0.0716875 15.81 0.0094664 15.5208C-0.0616875 15.1881 0.149926 14.8607 0.482593 14.7892L4.91908 13.8374C5.25206 13.7669 5.57949 13.9782 5.65064 14.3105C5.7218 14.6432 5.51018 14.9706 5.17752 15.0421L0.741027 15.9939C0.697595 16.0034 0.654163 16.0078 0.611348 16.0078Z"
                                                            fill="#060606" />
                                                        <path
                                                            d="M12.9331 7.17142C12.7754 7.17142 12.6177 7.11136 12.4976 6.99092L9.01287 3.50623C8.7723 3.26566 8.7723 2.8757 9.01287 2.63514C9.25313 2.39457 9.6437 2.39457 9.88396 2.63514L13.3687 6.11983C13.6092 6.36039 13.6092 6.75035 13.3687 6.99092C13.2485 7.11136 13.0908 7.17142 12.9331 7.17142Z"
                                                            fill="#060606" />
                                                        <path
                                                            d="M14.6757 5.42925C14.518 5.42925 14.3603 5.36919 14.2399 5.24875C13.9993 5.00818 13.9993 4.61822 14.2399 4.37735C14.5827 4.03452 14.7715 3.57032 14.7715 3.0707C14.7715 2.57109 14.5827 2.10689 14.2399 1.76406C13.8967 1.42092 13.4325 1.2321 12.9329 1.2321C12.4333 1.2321 11.9691 1.42092 11.6263 1.76406C11.386 2.00463 10.996 2.00494 10.7549 1.76406C10.5143 1.52349 10.5143 1.13353 10.7549 0.892657C11.3303 0.316958 12.1037 0 12.9329 0C13.7618 0 14.5356 0.316958 15.111 0.892657C15.6867 1.46805 16.0036 2.2415 16.0036 3.0707C16.0036 3.89991 15.6867 4.67336 15.111 5.24875C14.9911 5.36888 14.8334 5.42925 14.6757 5.42925Z"
                                                            fill="#060606" />
                                                    </svg>
                                                </a>
                                            @endpermission
                                            @permission('employee delete')
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['employee.destroy', $employee->id]]) !!}
                                                <a href="#!" class="btn btn-sm border  show_confirm"
                                                    data-bs-toggle="tooltip" data-bs-original-title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        viewBox="0 0 16 16" fill="none">
                                                        <g clip-path="url(#clip0_11_8426)">
                                                            <path
                                                                d="M13.625 1.875H11.2812V1.40625C11.2812 0.630844 10.6504 0 9.875 0H6.125C5.34959 0 4.71875 0.630844 4.71875 1.40625V1.875H2.375C1.59959 1.875 0.96875 2.50584 0.96875 3.28125C0.96875 3.904 1.37578 4.43316 1.93766 4.61753L2.77375 14.7105C2.83397 15.4336 3.44953 16 4.17513 16H11.8249C12.5505 16 13.1661 15.4336 13.2263 14.7103L14.0623 4.6175C14.6242 4.43316 15.0312 3.904 15.0312 3.28125C15.0312 2.50584 14.4004 1.875 13.625 1.875ZM5.65625 1.40625C5.65625 1.14778 5.86653 0.9375 6.125 0.9375H9.875C10.1335 0.9375 10.3438 1.14778 10.3438 1.40625V1.875H5.65625V1.40625ZM12.292 14.6327C12.2719 14.8737 12.0667 15.0625 11.8249 15.0625H4.17513C3.93328 15.0625 3.72809 14.8737 3.70806 14.6329L2.88419 4.6875H13.1158L12.292 14.6327ZM13.625 3.75H2.375C2.11653 3.75 1.90625 3.53972 1.90625 3.28125C1.90625 3.02278 2.11653 2.8125 2.375 2.8125H13.625C13.8835 2.8125 14.0938 3.02278 14.0938 3.28125C14.0938 3.53972 13.8835 3.75 13.625 3.75Z"
                                                                fill="#060606" />
                                                            <path
                                                                d="M6.12409 13.6272L5.65534 6.06472C5.63931 5.80631 5.41566 5.60978 5.1585 5.62588C4.90009 5.64191 4.70363 5.86435 4.71963 6.12272L5.18838 13.6853C5.20378 13.9338 5.41016 14.125 5.65578 14.125C5.92725 14.125 6.14075 13.8964 6.12409 13.6272Z"
                                                                fill="#060606" />
                                                            <path
                                                                d="M8 5.625C7.74112 5.625 7.53125 5.83487 7.53125 6.09375V13.6562C7.53125 13.9151 7.74112 14.125 8 14.125C8.25888 14.125 8.46875 13.9151 8.46875 13.6562V6.09375C8.46875 5.83487 8.25888 5.625 8 5.625Z"
                                                                fill="#060606" />
                                                            <path
                                                                d="M10.8415 5.62591C10.5837 5.60987 10.3606 5.80634 10.3446 6.06475L9.87587 13.6272C9.85991 13.8856 10.0564 14.1081 10.3147 14.1241C10.5733 14.1401 10.7956 13.9435 10.8116 13.6852L11.2803 6.12275C11.2963 5.86434 11.0999 5.64191 10.8415 5.62591Z"
                                                                fill="#060606" />
                                                        </g>
                                                        <defs>
                                                            <clipPath id="clip0_11_8426">
                                                                <rect width="16" height="16" fill="white" />
                                                            </clipPath>
                                                        </defs>
                                                    </svg>
                                                </a>
                                                {!! Form::close() !!}
                                            @endpermission
                                        @endif
                                    @endif
                                @else
                                    <a class="btn btn-sm border" data-title="{{ __('User Is Disable') }}"
                                        data-bs-original-title="{{ __('User Is Disable') }}" data-bs-toggle="tooltip">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M8.00009 0.470581C5.64715 0.470581 3.76479 2.35293 3.76479 4.70588V6.58823C2.96479 6.58823 2.35303 7.19999 2.35303 7.99999V14.1176C2.35303 14.9176 2.96479 15.5294 3.76479 15.5294H12.2354C13.0354 15.5294 13.6471 14.9176 13.6471 14.1176V7.99999C13.6471 7.19999 13.0354 6.58823 12.2354 6.58823V4.70588C12.2354 2.35293 10.353 0.470581 8.00009 0.470581ZM12.706 7.99999V14.1176C12.706 14.4 12.5177 14.5882 12.2354 14.5882H3.76479C3.48244 14.5882 3.2942 14.4 3.2942 14.1176V7.99999C3.2942 7.71764 3.48244 7.5294 3.76479 7.5294H12.2354C12.5177 7.5294 12.706 7.71764 12.706 7.99999ZM4.70597 6.58823V4.70588C4.70597 2.87058 6.16479 1.41176 8.00009 1.41176C9.83538 1.41176 11.2942 2.87058 11.2942 4.70588V6.58823H4.70597Z"
                                                fill="#060606" />
                                            <path
                                                d="M8.00014 8.94116C7.20014 8.94116 6.58838 9.55293 6.58838 10.3529C6.58838 10.9647 6.96485 11.4823 7.52956 11.6706V12.7059C7.52956 12.9882 7.71779 13.1765 8.00014 13.1765C8.2825 13.1765 8.47073 12.9882 8.47073 12.7059V11.6706C9.03544 11.4823 9.41191 10.9647 9.41191 10.3529C9.41191 9.55293 8.80014 8.94116 8.00014 8.94116ZM8.00014 10.8235C7.71779 10.8235 7.52956 10.6353 7.52956 10.3529C7.52956 10.0706 7.71779 9.88234 8.00014 9.88234C8.2825 9.88234 8.47073 10.0706 8.47073 10.3529C8.47073 10.6353 8.2825 10.8235 8.00014 10.8235Z"
                                                fill="#060606" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                            @if (!empty($employee->employee_id))
                                <span class="badge bg-primary p-2 px-3">
                                    @permission('employee show')
                                        @if ($employee->is_disable == 1)
                                            <a class="text-white"
                                                href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ Workdo\Hrm\Entities\Employee::employeeIdFormat($employee->employee_id) }}</a>
                                        @else
                                            <a
                                                class="text-white">{{ Workdo\Hrm\Entities\Employee::employeeIdFormat($employee->employee_id) }}</a>
                                        @endif
                                    @else
                                        <a
                                            class="text-white">{{ Workdo\Hrm\Entities\Employee::employeeIdFormat($employee->employee_id) }}</a>
                                    @endpermission
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @permission('employee create')
            <div class="col-xxl-3 col-xl-4 col-md-6">
                <a href="{{ route('employee.create') }}" class="btn-addnew-project border-primary"
                    data-title="{{ __('Create New Employee') }}">
                    <div class="badge bg-primary proj-add-icon">
                        <i class="ti ti-plus my-2"></i>
                    </div>
                    <h6 class="my-2 text-center">{{ __('New Employee') }}</h6>
                    <p class="text-muted text-center">{{ __('Click here to add New Employee') }}</p>
                </a>
            </div>
        @endpermission
    </div>
    {!! $employees->links('vendor.pagination.global-pagination') !!}
@endsection
