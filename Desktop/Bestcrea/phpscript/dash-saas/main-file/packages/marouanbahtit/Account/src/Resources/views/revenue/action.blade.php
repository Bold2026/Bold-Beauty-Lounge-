@permission('revenue edit')
    <div class="action-btn me-2">
        <a class="mx-3 btn bg-info btn-sm align-items-center"
            data-url="{{ route('revenue.edit', $revenue->id) }}"
            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
            data-title="{{ __('Edit Revenue') }}" title="{{ __('Edit') }}">
            <i class="ti ti-pencil text-white"></i>
        </a>
    </div>
@endpermission
@permission('revenue delete')
    <div class="action-btn">
        {{ Form::open(['route' => ['revenue.destroy', $revenue->id], 'class' => 'm-0']) }}
        @method('DELETE')
        <a class="mx-3 btn bg-danger btn-sm  align-items-center bs-pass-para show_confirm"
            data-bs-toggle="tooltip" title=""
            data-bs-original-title="Delete" aria-label="Delete"
            data-confirm="{{ __('Are You Sure?') }}"
            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
            data-confirm-yes="delete-form-{{ $revenue->id }}"><i
                class="ti ti-trash text-white text-white"></i></a>
        {{ Form::close() }}
    </div>
@endpermission
