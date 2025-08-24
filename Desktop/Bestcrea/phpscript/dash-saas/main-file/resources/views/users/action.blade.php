@php
    if (Auth::user()->type == 'super admin') {
        $plural_name = __('Customers');
        $singular_name = __('Customer');
    } else {
        $plural_name = __('Users');
        $singular_name = __('User');
    }
@endphp
@if ($user->is_disable == 1 || Auth::user()->type == 'super admin')
    @if (Auth::user()->type == 'super admin')
        <div class="action-btn me-2">
            <a data-url="{{ route('company.info', $user->id) }}"
                class="btn btn-sm d-inline-flex align-items-center bg-primary" data-ajax-popup="true" data-size="lg"
                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Admin Hub') }}"
                data-title="{{ __('Company Info') }}"> <span class="text-white"><i class="ti ti-atom"></i></a>
        </div>
        <div class="action-btn me-2">
            <a href="{{ route('login.with.company', $user->id) }}"
                class="btn btn-sm d-inline-flex align-items-center bg-secondary" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Login As Company') }}"> <span class="text-white"><i
                        class="ti ti-replace"></i></a>
        </div>
        <div class="action-btn me-2">
            <a href="#!" data-url="{{ route('upgrade.plan', $user->id) }}" data-ajax-popup="true" data-size="xl"
                class="btn btn-sm d-inline-flex align-items-center bg-primary" data-bs-toggle="tooltip"
                data-title="{{ __('Upgrade Plan') }}" data-bs-original-title="{{ __('Upgrade Plan') }}">
                <span class="text-white"><i class="ti ti-trophy"></i></span>
            </a>
        </div>
    @endif
    @if(admin_setting('email_verification') == 'on' && $user->email_verified_at == null)
        <div class="action-btn me-2">
            <a href="{{ route('user.verified', $user->id) }}" class="btn btn-sm d-inline-flex align-items-center bg-secondary"  data-bs-toggle="tooltip" data-bs-original-title="{{ __('Verified Now') }}"> <span class="text-white"><i class="ti ti-checks"></i></a>
        </div>
    @endif
    @permission('user reset password')
        <div class="action-btn me-2">
            <a href="#" class="btn btn-sm d-inline-flex align-items-center bg-warning"
                data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}" data-ajax-popup="true"
                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Reset Password') }}"
                data-title="{{ __('Reset Password') }}"> <span class="text-white"><i class="ti ti-adjustments"></i></a>
        </div>
    @endpermission
    @permission('user login manage')
        @if ($user->is_enable_login == 1)
            <div class="action-btn me-2">
                <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}"
                    class="btn btn-sm d-inline-flex align-items-center bg-danger" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Login Disable') }}"> <span class="text-white"><i
                            class="ti ti-road-sign"></i></a>
            </div>
        @elseif ($user->is_enable_login == 0 && $user->password == null)
            <div class="action-btn me-2">
                <a href="#" data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}" data-ajax-popup="true"
                    data-size="md" class="btn btn-sm d-inline-flex align-items-center login_enable bg-secondary"
                    data-title="{{ __('New Password') }}" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('New Password') }}"> <span class="text-white"><i
                            class="ti ti-road-sign"></i></a>
            </div>
        @else
            <div class="action-btn me-2">
                <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}"
                    class="btn btn-sm d-inline-flex align-items-center login_enable bg-success" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Login Enable') }}"> <span class="text-white"> <i
                            class="ti ti-road-sign"></i>
                </a>
            </div>
        @endif
    @endpermission
    @permission('user edit')
        <div class="action-btn me-2">
            <a href="#" class="btn btn-sm d-inline-flex align-items-center bg-info"
                data-url="{{ route('users.edit', $user->id) }}" class="dropdown-item" data-ajax-popup="true"
                data-title="{{ __('Update ' . $singular_name) }}" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Edit') }}"> <span class="text-white"> <i
                        class="ti ti-pencil"></i></span></a>
        </div>
    @endpermission
    @permission('user delete')
        <div class="action-btn">
            {{ Form::open(['route' => ['users.destroy', $user->id], 'class' => 'm-0']) }}
            @method('DELETE')
            <a href="#" class="btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Delete') }}"
                aria-label="{{ __('Delete') }}" data-confirm-yes="delete-form-{{ $user->id }}"><i
                    class="ti ti-trash text-white"></i></a>
            {{ Form::close() }}
        </div>
    @endpermission
@else
    <div class="action-btn">
        <a href="#" class="btn btn-sm d-inline-flex align-items-center bg-dark" data-title="{{ __('Lock') }}"
            data-bs-toggle="tooltip" data-bs-original-title="{{ __('User Is Disable') }}"><span class="text-white"><i
                    class="ti ti-lock"></i></span></a>
    </div>
@endif
