@if ($employees->is_disable == 1)
    <span>
        @if (!empty($employees->employee_id))
            @permission('employee show')
                <div class="action-btn  me-2">
                    <a href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employees->id)) }}"
                        class="mx-3 btn btn-sm bg-warning align-items-center" data-bs-toggle="tooltip" title=""
                        data-bs-original-title="{{ __('View') }}">
                        <i class="ti ti-eye text-white"></i>
                    </a>
                </div>
            @endpermission
        @endif
        @permission('employee edit')
            <div class="action-btn  me-2">
                <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employees->ID)) }}"
                    class="mx-3 btn btn-sm bg-info align-items-center" data-bs-toggle="tooltip" title=""
                    data-bs-original-title="{{ __('Edit') }}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endpermission
        @if (!empty($employees->employee_id))
            @permission('employee delete')
                <div class="action-btn">
                    {{ Form::open(['route' => ['employee.destroy', $employees->id], 'class' => 'm-0']) }}
                    @method('DELETE')
                    <a class="mx-3 btn btn-sm bg-danger align-items-center bs-pass-para show_confirm"
                        data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-bs-original-title="Delete"
                        aria-label="Delete" data-confirm="{{ __('Are You Sure?') }}"
                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                        data-confirm-yes="delete-form-{{ $employees->id }}"><i
                            class="ti ti-trash text-white text-white"></i></a>
                    {{ Form::close() }}
                </div>
            @endpermission
        @endif
    </span>
@else
    <div class="action-btn">
        <a href="#" class="btn btn-sm d-inline-flex align-items-center bg-dark" data-title="{{ __('User Is Disable') }}"
            data-bs-toggle="tooltip" data-bs-original-title="{{ __('User Is Disable') }}"><span class="text-white"><i
                    class="ti ti-lock"></i></span></a>
    </div>
@endif
