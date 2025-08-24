@permission('leave approver manage')
    <div class="action-btn me-2">
        <a class="mx-3 btn btn-sm bg-warning align-items-center" data-url="{{ URL::to('leave/' . $leaves->id . '/action') }}"
            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="" data-title="{{ __('Manage Leave') }}"
            data-bs-original-title="{{ __('Leave Action') }}">
            <i class="ti ti-caret-right text-white"></i>
        </a>
    </div>
@endpermission
@if ($leaves->status == 'Pending')
    @permission('leave edit')
        <div class="action-btn  me-2">
            <a class="mx-3 btn btn-sm bg-info align-items-center" data-url="{{ URL::to('leave/' . $leaves->id . '/edit') }}"
                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                data-title="{{ __('Edit Leave') }}" data-bs-original-title="{{ __('Edit') }}">
                <i class="ti ti-pencil text-white"></i>
            </a>
        </div>
    @endpermission

    @permission('leave delete')
        <div class="action-btn">
            {{ Form::open(['route' => ['leave.destroy', $leaves->id], 'class' => 'm-0']) }}
            @method('DELETE')
            <a class="mx-3 btn btn-sm bg-danger align-items-center bs-pass-para show_confirm" data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                data-bs-original-title="Delete" aria-label="Delete" data-confirm="{{ __('Are You Sure?') }}"
                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                data-confirm-yes="delete-form-{{ $leaves->id }}"><i class="ti ti-trash text-white text-white"></i></a>
            {{ Form::close() }}
        </div>
    @endpermission
@endif
