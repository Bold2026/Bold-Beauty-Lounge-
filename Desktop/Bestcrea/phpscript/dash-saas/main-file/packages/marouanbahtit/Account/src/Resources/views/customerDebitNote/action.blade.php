@permission('debitnote edit')
    <div class="action-btn  me-2">
        <a data-url="{{ route('bill.debit-custom.edit', [$customdebitNote->bill, $customdebitNote->id]) }}" data-ajax-popup="true"
            data-title="{{ __('Edit Debit Note') }}" href="#" class="mx-3 btn btn-sm align-items-center bg-info"
            data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-original-title="{{ __('Edit') }}">
            <i class="ti ti-pencil text-white"></i>
        </a>
    </div>
@endpermission
@permission('debitnote delete')
    <div class="action-btn  me-2">
        {!! Form::open([
            'method' => 'DELETE',
            'route' => ['bill.delete.custom-debit', $customdebitNote->bill, $customdebitNote->id],
            'id' => 'delete-form-' . $customdebitNote->id,
        ]) !!}

        <a href="#" class="bg-danger mx-3 btn btn-sm align-items-center show_confirm" data-bs-toggle="tooltip"
            title="{{ __('Delete') }}" data-original-title="{{ __('Delete') }}" data-confirm="{{ __('Are You Sure?') }}"
            data-confirm-yes="document.getElementById('delete-form-{{ $customdebitNote->id }}').submit();">
            <i class="ti ti-trash text-white"></i>
        </a>
        {!! Form::close() !!}
    </div>
@endpermission
