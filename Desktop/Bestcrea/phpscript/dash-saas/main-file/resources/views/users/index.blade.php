@extends('layouts.main')
@if (Auth::user()->type == 'super admin')
    @section('page-title')
        {{ __('Customers') }}
    @endsection
    @section('page-breadcrumb')
        {{ __('Customers') }}
    @endsection
@else
    @section('page-title')
        {{ __('Users') }}
    @endsection
    @section('page-breadcrumb')
        {{ __('Users') }}
    @endsection
@endif
@section('page-action')
    <div class="d-flex">
        @permission('user logs history')
            <a href="{{ route('users.userlog.history') }}" class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip"
                data-bs-placement="top" title="{{ __('User Logs History') }}"><i class="ti ti-user-check"></i>
            </a>
        @endpermission
        @permission('user import')
            <a href="#" class="btn btn-sm btn-primary me-2" data-ajax-popup="true" data-title="{{ __('Import Customers') }}"
                data-url="{{ route('users.file.import') }}" data-bs-toggle="tooltip" title="{{ __('Import Customers') }}"><i
                    class="ti ti-file-import"></i>
            </a>
        @endpermission
        @permission('user manage')
            <a href="{{ route('users.list.view') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('List View') }}"
                class="btn btn-sm btn-primary btn-icon me-2">
                <i class="ti ti-list"></i>
            </a>
        @endpermission
        @if (Auth::user()->type == 'super admin')
            @permission('user create')
                <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md"
                    data-title="{{ __('Create New Customer') }}" data-url="{{ route('users.create') }}"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i>
                </a>
            @endpermission
        @else
            @permission('user create')
                <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md"
                    data-title="{{ __('Create New User') }}" data-url="{{ route('users.create') }}"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i>
                </a>
            @endpermission
        @endif
    </div>
@endsection
@section('content')
    <!-- [ Main Content ] start -->
    <div class="row row-gap-2 mb-4">
        @if (\Auth::user()->type != 'super admin')
            <div class="" id="multiCollapseExample1">
                <div class="card mb-0">
                    <div class="card-body">
                        {{ Form::open(['route' => ['users.index'], 'method' => 'GET', 'id' => 'user_submit']) }}
                        <div class="row d-flex align-items-center justify-content-end">
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                    {{ Form::text('name', isset($_GET['name']) ? $_GET['name'] : null, ['class' => 'form-control', 'placeholder' => __('Enter Name')]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                                    {{ Form::text('email', isset($_GET['email']) ? $_GET['email'] : null, ['class' => 'form-control', 'placeholder' => __('Enter Email')]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('role', __('Role'), ['class' => 'form-label']) }}
                                    {{ Form::select('role', $roles, isset($_GET['role']) ? $_GET['role'] : '', ['class' => 'form-control select text-capitalize', 'placeholder' => __('All')]) }}
                                </div>
                            </div>
                            <div class="col-auto float-end mt-4 d-flex">
                                <a href="#" class="btn btn-sm btn-primary me-2"
                                    onclick="document.getElementById('user_submit').submit(); return false;"
                                    data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                    data-original-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route('users.index') }}" id="clearfilter" class="btn btn-sm btn-danger"
                                    data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                    data-original-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        @endif
        <div id="loading-bar-spinner" class="spinner">
            <div class="spinner-icon"></div>
        </div>
        @foreach ($users as $user)
            <div class="col-xxl-3 col-xl-4 col-md-6">
                <div class="card user-card">
                    <div class="card-header p-3 border border-bottom h-100">
                        @if (Auth::user()->type == 'super admin')
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary p-2 px-3">{{ ucfirst($user->type) }}</span>
                            </div>
                            <div class="card-header-right">
                                @permission('user manage')
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="true">
                                            <i class="feather icon-more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" data-popper-placement="bottom-end">
                                            @permission('user edit')
                                                <a href="#!" data-url="{{ route('users.edit', $user->id) }}"
                                                    data-ajax-popup="true" data-size="md" class="dropdown-item"
                                                    data-title="{{ __('Update Customer') }}"
                                                    data-bs-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil"></i>
                                                    <span class="ms-2">{{ __('Edit') }}</span>
                                                </a>
                                            @endpermission
                                            @permission('user delete')
                                                {{ Form::open(['route' => ['users.destroy', $user->id], 'class' => 'm-0']) }}
                                                @method('DELETE')
                                                <a href="#!" class="dropdown-item bs-pass-para show_confirm"
                                                    data-bs-placement="top" data-bs-original-title="{{ __('Delete') }}" aria-label="Delete"
                                                    data-confirm="{{ __('Are You Sure?') }}"
                                                    data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                    data-confirm-yes="delete-form-{{ $user->id }}">
                                                    <i class="ti ti-trash"></i>
                                                    <span class="ms-2">{{ __('Delete') }}</span>
                                                </a>
                                                {{ Form::close() }}
                                            @endpermission

                                            <a href="{{ route('login.with.company', $user->id) }}" class="dropdown-item"
                                                data-bs-original-title="{{ __('Login As Company') }}">
                                                <i class="ti ti-replace me-1"></i>
                                                <span class="ms-2">{{ __('Login As Company') }}</span>
                                            </a>

                                            @if (admin_setting('email_verification') == 'on' && $user->email_verified_at == null)
                                                <a href="{{ route('user.verified', $user->id) }}" class="dropdown-item"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Verified Now') }}">
                                                    <i class="ti ti-checks me-1"></i>
                                                    <span class="ms-2">{{ __('Verified Now') }}</span>
                                                </a>
                                            @endif

                                            <a href="#!" data-url="{{ route('upgrade.plan', $user->id) }}"
                                                data-ajax-popup="true" data-size="xl" class="dropdown-item"
                                                data-title="{{ __('Upgrade Plan') }}"
                                                data-bs-original-title="{{ __('Upgrade Plan') }}">
                                                <i class="ti ti-trending-up"></i>
                                                <span class="ms-2">{{ __('Upgrade Plan') }}</span>
                                            </a>

                                            @permission('user reset password')
                                                <a href="#!"
                                                    data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}"
                                                    data-ajax-popup="true" data-size="md" class="dropdown-item"
                                                    data-title="{{ __('Reset Password') }}"
                                                    data-bs-original-title="{{ __('Reset Password') }}">
                                                    <i class="ti ti-adjustments"></i>
                                                    <span class="ms-2">{{ __('Reset Password') }}</span>
                                                </a>
                                            @endpermission
                                            @permission('user login manage')
                                                @if ($user->is_enable_login == 1)
                                                    <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}"
                                                        class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-danger ms-2"> {{ __('Login Disable') }}</span>
                                                    </a>
                                                @elseif ($user->is_enable_login == 0 && $user->password == null)
                                                    <a href="#"
                                                        data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}"
                                                        data-ajax-popup="true" data-size="md" class="dropdown-item login_enable"
                                                        data-title="{{ __('New Password') }}" class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-success ms-2"> {{ __('Login Enable') }}</span>
                                                    </a>
                                                @else
                                                    <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}"
                                                        class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-success ms-2"> {{ __('Login Enable') }}</span>
                                                    </a>
                                                @endif
                                            @endpermission
                                        </div>
                                    </div>
                                @endpermission
                            </div>
                        @else
                            <div class="user-img-wrp d-flex align-items-center">
                                <div class="user-image rounded border-2 border border-primary">
                                    <img src="{{ check_file($user->avatar) ? get_file($user->avatar) : get_file('uploads/users-avatar/avatar.png') }}"
                                        alt="user-image" class="h-100 w-100">
                                </div>
                                <div class="user-content">
                                    <h4 class="mb-2">{{ $user->name }}</h4>
                                    <span class="text-dark text-md">{{ $user->email }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-3  text-center">
                        @if (Auth::user()->type == 'super admin')
                            <div class="user-image rounded border-2 border border-primary m-auto">
                                <img src="{{ check_file($user->avatar) ? get_file($user->avatar) : get_file('uploads/users-avatar/avatar.png') }}"
                                    alt="user-image" class="h-100 w-100 ">
                            </div>
                            <h4 class="mt-2">{{ $user->name }}</h4>
                            <span class="text-dark text-md">{{ $user->email }}</span>

                            <div class="mt-4">
                                <div class="row justify-content-between align-items-center">
                                    <div class="col-6 text-center">
                                        <span
                                            class="d-block font-bold mb-0">{{ !empty($user->plan) ? (!empty($user->plan->name) ? $user->plan->name : 'Basic Plan') : 'Plan Not Activated' }}</span>
                                    </div>
                                    <div class="col-6 text-center Id ">
                                        <a href="#" data-url="{{ route('company.info', $user->id) }}"
                                            data-size="lg" data-ajax-popup="true"
                                            class="btn btn-outline-primary text-break px-3"
                                            data-title="{{ __('Company Info') }}">{{ __('AdminHub') }}</a>
                                    </div>
                                    <div class="col-12">
                                        <hr class="my-3">
                                    </div>
                                    @php
                                        $plan_expire_date = !empty($user->plan_expire_date)
                                            ? $user->plan_expire_date
                                            : '';
                                        if ($plan_expire_date == '0000-00-00') {
                                            $plan_expire_date = date('d-m-Y');
                                        }
                                        if (empty($plan_expire_date)) {
                                            $plan_expire_date = !empty($user->trial_expire_date)
                                                ? $user->trial_expire_date
                                                : '--';
                                        }
                                    @endphp
                                    <div class="col-12 text-center pb-2">
                                        <span class="text-dark text-md">{{ __('Plan Expired :') }}
                                            @if (!empty($user->plan))
                                                {{ company_date_formate($plan_expire_date) }}
                                            @else
                                                --
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bottom-icons d-flex flex-wrap align-items-center justify-content-between">
                                @if ($user->is_disable == 1)
                                    <div class="edit-btn-wrp d-flex flex-wrap align-items-center">
                                        @permission('user edit')
                                            <a href="#!" data-url="{{ route('users.edit', $user->id) }}"
                                                data-ajax-popup="true" data-size="md"
                                                data-title="{{ __('Update User') }}"
                                                data-bs-original-title="{{ __('Edit') }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" class="btn btn-sm border">
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
                                        @permission('user delete')
                                            {{ Form::open(['route' => ['users.destroy', $user->id], 'class' => 'm-0']) }}
                                            @method('DELETE')
                                            <a href="#!" aria-label="Delete" data-confirm="{{ __('Are You Sure?') }}"
                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                data-confirm-yes="delete-form-{{ $user->id }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" data-bs-original-title="{{ __('Delete') }}"
                                                class="btn btn-sm border bs-pass-para show_confirm">
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
                                            {{ Form::close() }}
                                        @endpermission
                                        @permission('user reset password')
                                            <a href="#!"
                                                data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}"
                                                data-ajax-popup="true" data-size="md" class="btn btn-sm border"
                                                data-title="{{ __('Reset Password') }}"
                                                data-bs-original-title="{{ __('Reset Password') }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 16 16" fill="none">
                                                    <g clip-path="url(#clip0_11_8441)">
                                                        <path
                                                            d="M14.492 1.50803C12.4812 -0.502691 9.20956 -0.50266 7.19884 1.50803C5.82553 2.88134 5.34866 4.90568 5.94306 6.74187L0.137344 12.5476C0.0494062 12.6355 0 12.7548 0 12.8791V15.5312C0 15.7901 0.209906 16 0.468812 16H3.12087C3.24525 16 3.36444 15.9506 3.45241 15.8627L4.11553 15.1995C4.21681 15.0982 4.26625 14.9562 4.24969 14.8139L4.16694 14.1019L5.15394 14.0089C5.37806 13.9877 5.55556 13.8103 5.57669 13.5862L5.66978 12.599L6.38181 12.6818C6.51475 12.6973 6.64781 12.6552 6.74775 12.5662C6.84762 12.4773 6.90478 12.3499 6.90478 12.2161V11.343H7.76197C7.88537 11.343 8.00378 11.2944 8.09156 11.2076L9.25631 10.0563C11.0929 10.6517 13.1183 10.1749 14.492 8.80112C16.5027 6.79043 16.5027 3.51871 14.492 1.50803ZM13.829 8.13815C12.6457 9.32143 10.8707 9.69078 9.307 9.07922C9.13444 9.01175 8.93837 9.05218 8.80665 9.1824L7.56937 10.4054H6.43594C6.17703 10.4054 5.96712 10.6153 5.96712 10.8742V11.6896L5.30212 11.6123C5.17684 11.5978 5.05103 11.6343 4.953 11.7136C4.855 11.793 4.79306 11.9084 4.78122 12.034L4.67956 13.1118L3.60187 13.2133C3.47631 13.2252 3.36084 13.2871 3.28147 13.3851C3.20212 13.4831 3.16559 13.609 3.18016 13.7342L3.29209 14.6969L2.92666 15.0624H0.937594V13.0734L6.81562 7.19534C6.94731 7.06365 6.98859 6.8665 6.92075 6.69306C6.30912 5.12937 6.67853 3.35443 7.86181 2.17109C9.507 0.525965 12.1838 0.525965 13.8289 2.17109C15.4741 3.81618 15.4741 6.49303 13.829 8.13815Z"
                                                            fill="#060606" />
                                                        <path
                                                            d="M13.1659 2.83406C12.6175 2.28566 11.7252 2.28569 11.1769 2.83406C10.6285 3.38244 10.6285 4.27472 11.1769 4.82309C11.7252 5.37147 12.6175 5.37153 13.1659 4.82309C13.7143 4.27472 13.7143 3.38244 13.1659 2.83406ZM12.5029 4.16009C12.3201 4.34287 12.0227 4.34291 11.8399 4.16009C11.6571 3.97731 11.6571 3.67991 11.8399 3.49709C12.0232 3.31384 12.3197 3.31384 12.5029 3.49709C12.6862 3.68034 12.6861 3.97684 12.5029 4.16009Z"
                                                            fill="#060606" />
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0_11_8441">
                                                            <rect width="16" height="16" fill="white" />
                                                        </clipPath>
                                                    </defs>
                                                </svg>
                                            </a>
                                        @endpermission
                                        @permission('user login manage')
                                            @if ($user->is_enable_login == 1)
                                                <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}"
                                                    class="btn btn-sm border login-disabled"
                                                    data-bs-original-title="{{ __('Login Disable') }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="top">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        viewBox="0 0 16 16" fill="none">
                                                        <g clip-path="url(#clip0_11_8451)">
                                                            <path
                                                                d="M9.96354 0.833332C9.05187 0.833319 8.31694 0.833306 7.739 0.911012C7.13894 0.991692 6.63366 1.16429 6.23238 1.56557C5.88242 1.91552 5.7056 2.34557 5.61278 2.8509C5.52258 3.34194 5.50533 3.94287 5.50131 4.66389C5.49978 4.94002 5.72238 5.16512 5.99852 5.16666C6.27466 5.1682 6.49976 4.94559 6.5013 4.66945C6.50536 3.94045 6.52429 3.42373 6.59632 3.03157C6.66573 2.65369 6.7772 2.43497 6.93947 2.27267C7.124 2.08817 7.38307 1.96787 7.8722 1.9021C8.3758 1.83439 9.0432 1.83333 10.0001 1.83333H10.6668C11.6237 1.83333 12.2911 1.83439 12.7947 1.9021C13.2839 1.96787 13.5429 2.08817 13.7275 2.27267C13.912 2.45718 14.0323 2.71623 14.0981 3.20541C14.1657 3.70898 14.1668 4.37639 14.1668 5.33333V10.6667C14.1668 11.6236 14.1657 12.291 14.0981 12.7946C14.0323 13.2838 13.912 13.5428 13.7275 13.7273C13.5429 13.9119 13.2839 14.0321 12.7947 14.0979C12.2911 14.1656 11.6237 14.1667 10.6668 14.1667H10.0001C9.0432 14.1667 8.3758 14.1656 7.8722 14.0979C7.38307 14.0321 7.124 13.9119 6.93947 13.7273C6.7772 13.565 6.66573 13.3463 6.59632 12.9685C6.52429 12.5763 6.50536 12.0595 6.5013 11.3305C6.49976 11.0544 6.27466 10.8318 5.99852 10.8333C5.72238 10.8349 5.49978 11.06 5.50131 11.3361C5.50533 12.0571 5.52258 12.6581 5.61278 13.1491C5.7056 13.6544 5.88242 14.0845 6.23238 14.4345C6.63366 14.8357 7.13894 15.0083 7.739 15.089C8.31694 15.1667 9.05187 15.1667 9.96354 15.1667H10.7034C11.6151 15.1667 12.35 15.1667 12.928 15.089C13.5281 15.0083 14.0333 14.8357 14.4346 14.4345C14.8359 14.0331 15.0085 13.5279 15.0891 12.9279C15.1669 12.3499 15.1668 11.615 15.1668 10.7033V5.29675C15.1668 4.38503 15.1669 3.65015 15.0891 3.07217C15.0085 2.47209 14.8359 1.96685 14.4346 1.56557C14.0333 1.16429 13.5281 0.991692 12.928 0.911012C12.35 0.833306 11.6151 0.833319 10.7034 0.833332H9.96354Z"
                                                                fill="#ff3a6e" />
                                                            <path
                                                                d="M1.33399 7.49933C1.05784 7.49933 0.833988 7.7232 0.833988 7.99933C0.833988 8.27546 1.05784 8.49933 1.33399 8.49933H9.31567L8.0086 9.61973C7.79893 9.7994 7.77467 10.1151 7.95434 10.3247C8.13407 10.5344 8.44974 10.5587 8.6594 10.379L10.9927 8.379C11.1035 8.284 11.1673 8.14533 11.1673 7.99933C11.1673 7.8534 11.1035 7.71473 10.9927 7.61973L8.6594 5.61972C8.44974 5.44001 8.13407 5.46429 7.95434 5.67395C7.77467 5.88362 7.79893 6.19926 8.0086 6.37898L9.31567 7.49933H1.33399Z"
                                                                fill="#ff3a6e" />
                                                        </g>
                                                        <defs>
                                                            <clipPath id="clip0_11_8451">
                                                                <rect width="16" height="16" fill="white" />
                                                            </clipPath>
                                                        </defs>
                                                    </svg>
                                                </a>
                                            @elseif ($user->is_enable_login == 0 && $user->password == null)
                                                <a href="#"
                                                    data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}"
                                                    data-ajax-popup="true" data-size="md"
                                                    class="btn btn-sm border login_enable"
                                                    data-title="{{ __('New Password') }}"
                                                    data-bs-original-title="{{ __('Login Enable') }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="top">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        viewBox="0 0 16 16" fill="none">
                                                        <g clip-path="url(#clip0_11_8451)">
                                                            <path
                                                                d="M9.96354 0.833332C9.05187 0.833319 8.31694 0.833306 7.739 0.911012C7.13894 0.991692 6.63366 1.16429 6.23238 1.56557C5.88242 1.91552 5.7056 2.34557 5.61278 2.8509C5.52258 3.34194 5.50533 3.94287 5.50131 4.66389C5.49978 4.94002 5.72238 5.16512 5.99852 5.16666C6.27466 5.1682 6.49976 4.94559 6.5013 4.66945C6.50536 3.94045 6.52429 3.42373 6.59632 3.03157C6.66573 2.65369 6.7772 2.43497 6.93947 2.27267C7.124 2.08817 7.38307 1.96787 7.8722 1.9021C8.3758 1.83439 9.0432 1.83333 10.0001 1.83333H10.6668C11.6237 1.83333 12.2911 1.83439 12.7947 1.9021C13.2839 1.96787 13.5429 2.08817 13.7275 2.27267C13.912 2.45718 14.0323 2.71623 14.0981 3.20541C14.1657 3.70898 14.1668 4.37639 14.1668 5.33333V10.6667C14.1668 11.6236 14.1657 12.291 14.0981 12.7946C14.0323 13.2838 13.912 13.5428 13.7275 13.7273C13.5429 13.9119 13.2839 14.0321 12.7947 14.0979C12.2911 14.1656 11.6237 14.1667 10.6668 14.1667H10.0001C9.0432 14.1667 8.3758 14.1656 7.8722 14.0979C7.38307 14.0321 7.124 13.9119 6.93947 13.7273C6.7772 13.565 6.66573 13.3463 6.59632 12.9685C6.52429 12.5763 6.50536 12.0595 6.5013 11.3305C6.49976 11.0544 6.27466 10.8318 5.99852 10.8333C5.72238 10.8349 5.49978 11.06 5.50131 11.3361C5.50533 12.0571 5.52258 12.6581 5.61278 13.1491C5.7056 13.6544 5.88242 14.0845 6.23238 14.4345C6.63366 14.8357 7.13894 15.0083 7.739 15.089C8.31694 15.1667 9.05187 15.1667 9.96354 15.1667H10.7034C11.6151 15.1667 12.35 15.1667 12.928 15.089C13.5281 15.0083 14.0333 14.8357 14.4346 14.4345C14.8359 14.0331 15.0085 13.5279 15.0891 12.9279C15.1669 12.3499 15.1668 11.615 15.1668 10.7033V5.29675C15.1668 4.38503 15.1669 3.65015 15.0891 3.07217C15.0085 2.47209 14.8359 1.96685 14.4346 1.56557C14.0333 1.16429 13.5281 0.991692 12.928 0.911012C12.35 0.833306 11.6151 0.833319 10.7034 0.833332H9.96354Z"
                                                                fill="#0CAF60" />
                                                            <path
                                                                d="M1.33399 7.49933C1.05784 7.49933 0.833988 7.7232 0.833988 7.99933C0.833988 8.27546 1.05784 8.49933 1.33399 8.49933H9.31567L8.0086 9.61973C7.79893 9.7994 7.77467 10.1151 7.95434 10.3247C8.13407 10.5344 8.44974 10.5587 8.6594 10.379L10.9927 8.379C11.1035 8.284 11.1673 8.14533 11.1673 7.99933C11.1673 7.8534 11.1035 7.71473 10.9927 7.61973L8.6594 5.61972C8.44974 5.44001 8.13407 5.46429 7.95434 5.67395C7.77467 5.88362 7.79893 6.19926 8.0086 6.37898L9.31567 7.49933H1.33399Z"
                                                                fill="#0CAF60" />
                                                        </g>
                                                        <defs>
                                                            <clipPath id="clip0_11_8451">
                                                                <rect width="16" height="16" fill="white" />
                                                            </clipPath>
                                                        </defs>
                                                    </svg>
                                                </a>
                                            @else
                                                <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}"
                                                    class="btn btn-sm border"
                                                    data-bs-original-title="{{ __('Login Enable') }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="top">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        viewBox="0 0 16 16" fill="none">
                                                        <g clip-path="url(#clip0_11_8451)">
                                                            <path
                                                                d="M9.96354 0.833332C9.05187 0.833319 8.31694 0.833306 7.739 0.911012C7.13894 0.991692 6.63366 1.16429 6.23238 1.56557C5.88242 1.91552 5.7056 2.34557 5.61278 2.8509C5.52258 3.34194 5.50533 3.94287 5.50131 4.66389C5.49978 4.94002 5.72238 5.16512 5.99852 5.16666C6.27466 5.1682 6.49976 4.94559 6.5013 4.66945C6.50536 3.94045 6.52429 3.42373 6.59632 3.03157C6.66573 2.65369 6.7772 2.43497 6.93947 2.27267C7.124 2.08817 7.38307 1.96787 7.8722 1.9021C8.3758 1.83439 9.0432 1.83333 10.0001 1.83333H10.6668C11.6237 1.83333 12.2911 1.83439 12.7947 1.9021C13.2839 1.96787 13.5429 2.08817 13.7275 2.27267C13.912 2.45718 14.0323 2.71623 14.0981 3.20541C14.1657 3.70898 14.1668 4.37639 14.1668 5.33333V10.6667C14.1668 11.6236 14.1657 12.291 14.0981 12.7946C14.0323 13.2838 13.912 13.5428 13.7275 13.7273C13.5429 13.9119 13.2839 14.0321 12.7947 14.0979C12.2911 14.1656 11.6237 14.1667 10.6668 14.1667H10.0001C9.0432 14.1667 8.3758 14.1656 7.8722 14.0979C7.38307 14.0321 7.124 13.9119 6.93947 13.7273C6.7772 13.565 6.66573 13.3463 6.59632 12.9685C6.52429 12.5763 6.50536 12.0595 6.5013 11.3305C6.49976 11.0544 6.27466 10.8318 5.99852 10.8333C5.72238 10.8349 5.49978 11.06 5.50131 11.3361C5.50533 12.0571 5.52258 12.6581 5.61278 13.1491C5.7056 13.6544 5.88242 14.0845 6.23238 14.4345C6.63366 14.8357 7.13894 15.0083 7.739 15.089C8.31694 15.1667 9.05187 15.1667 9.96354 15.1667H10.7034C11.6151 15.1667 12.35 15.1667 12.928 15.089C13.5281 15.0083 14.0333 14.8357 14.4346 14.4345C14.8359 14.0331 15.0085 13.5279 15.0891 12.9279C15.1669 12.3499 15.1668 11.615 15.1668 10.7033V5.29675C15.1668 4.38503 15.1669 3.65015 15.0891 3.07217C15.0085 2.47209 14.8359 1.96685 14.4346 1.56557C14.0333 1.16429 13.5281 0.991692 12.928 0.911012C12.35 0.833306 11.6151 0.833319 10.7034 0.833332H9.96354Z"
                                                                fill="#0CAF60" />
                                                            <path
                                                                d="M1.33399 7.49933C1.05784 7.49933 0.833988 7.7232 0.833988 7.99933C0.833988 8.27546 1.05784 8.49933 1.33399 8.49933H9.31567L8.0086 9.61973C7.79893 9.7994 7.77467 10.1151 7.95434 10.3247C8.13407 10.5344 8.44974 10.5587 8.6594 10.379L10.9927 8.379C11.1035 8.284 11.1673 8.14533 11.1673 7.99933C11.1673 7.8534 11.1035 7.71473 10.9927 7.61973L8.6594 5.61972C8.44974 5.44001 8.13407 5.46429 7.95434 5.67395C7.77467 5.88362 7.79893 6.19926 8.0086 6.37898L9.31567 7.49933H1.33399Z"
                                                                fill="#0CAF60" />
                                                        </g>
                                                        <defs>
                                                            <clipPath id="clip0_11_8451">
                                                                <rect width="16" height="16" fill="white" />
                                                            </clipPath>
                                                        </defs>
                                                    </svg>
                                                </a>
                                            @endif
                                        @endpermission
                                        @if (admin_setting('email_verification') == 'on' && $user->email_verified_at == null)
                                            <a href="{{ route('user.verified', $user->id) }}" class="btn btn-sm border"
                                                data-bs-original-title="{{ __('Verified Now') }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 16 16" fill="none">
                                                    <path
                                                        d="M7.43728 9.62096C7.57668 9.62096 7.71538 9.56311 7.81423 9.44956L12.1177 4.51741C12.2993 4.30941 12.2778 3.99346 12.0696 3.81211C11.8623 3.63071 11.5464 3.65146 11.3643 3.86021L7.06083 8.79236C6.87918 9.00036 6.90068 9.31631 7.10893 9.49766C7.20363 9.58041 7.32058 9.62096 7.43728 9.62096Z"
                                                        fill="#060606" />
                                                    <path
                                                        d="M15.3936 3.37898C15.1861 3.19783 14.8702 3.21883 14.6883 3.42708L7.65216 11.4908L4.25126 7.71513C4.06596 7.50983 3.75006 7.49323 3.54496 7.67828C3.33986 7.86308 3.32326 8.17928 3.50811 8.38458L5.49256 10.5877L4.70636 11.4901L1.30621 7.71568C1.12091 7.51038 0.805013 7.49378 0.599913 7.67883C0.394813 7.86388 0.378213 8.17983 0.563063 8.38513L4.34116 12.5787C4.43611 12.6839 4.57116 12.744 4.71276 12.744H4.71691C4.85996 12.7428 4.99571 12.6803 5.08971 12.5724L6.16676 11.3361L7.28651 12.5792C7.38146 12.6844 7.51651 12.7445 7.65811 12.7445H7.66201C7.80506 12.7433 7.94081 12.681 8.03481 12.5731L15.4418 4.08438C15.6234 3.87628 15.6019 3.56063 15.3936 3.37898Z"
                                                        fill="#060606" />
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <a class="btn btn-sm border" data-title="{{ __('Lock') }}"
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
                                <span class="badge bg-primary p-2 px-3">{{ ucfirst($user->type) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
        @auth('web')
        @if (Auth::user()->type == 'super admin')
            @permission('user create')
                <div class="col-xxl-3 col-xl-4 col-md-6">
                    <a href="#" class="btn-addnew-project border-primary p-4" data-ajax-popup="true" data-size="md"
                        data-title="{{ __('Create New Customer') }}" data-url="{{ route('users.create') }}">
                        <div class="bg-primary proj-add-icon">
                            <i class="ti ti-plus my-2"></i>
                        </div>
                        <h6 class="mt-4 mb-2">{{ __('New Customer') }}</h6>
                        <p class="text-muted text-center mb-0">{{ __('Click here to Create New Customer') }}</p>
                    </a>
                </div>
            @endpermission
        @else
            @permission('user create')
                <div class="col-xxl-3 col-xl-4 col-md-6">
                    <a href="#" class="btn-addnew-project border-primary p-4" data-ajax-popup="true" data-size="md"
                        data-title="{{ __('Create New User') }}" data-url="{{ route('users.create') }}">
                        <div class="bg-primary proj-add-icon">
                            <i class="ti ti-plus my-2"></i>
                        </div>
                        <h6 class="mt-4 mb-2">{{ __('New User') }}</h6>
                        <p class="text-muted text-center mb-0">{{ __('Click here to Create New User') }}</p>
                    </a>
                </div>
            @endpermission
        @endif
        @endauth
    </div>
    {!! $users->links('vendor.pagination.global-pagination') !!}
    <!-- [ Main Content ] end -->
@endsection
@push('scripts')
    {{-- Password  --}}
    <script>
        $(document).on('change', '#password_switch', function() {
            if ($(this).is(':checked')) {
                $('.ps_div').removeClass('d-none');
                $('#password').attr("required", true);

            } else {
                $('.ps_div').addClass('d-none');
                $('#password').val(null);
                $('#password').removeAttr("required");
            }
        });
        $(document).on('click', '.login_enable', function() {
            setTimeout(function() {
                $('.modal-body').append($('<input>', {
                    type: 'hidden',
                    val: 'true',
                    name: 'login_enable'
                }));
            }, 2000);
        });
    </script>
@endpush
