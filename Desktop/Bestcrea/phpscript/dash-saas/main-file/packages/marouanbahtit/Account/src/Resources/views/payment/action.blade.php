@if (Laratrust::hasPermission('expense payment delete') || Laratrust::hasPermission('expense payment edit'))
    @permission('expense payment edit')
        <div class="action-btn  me-2">
            <a class="mx-3 btn bg-info btn-sm align-items-center" data-url="{{ route('payment.edit', $payment->id) }}"
                data-ajax-popup="true" data-title="{{ __('Edit Payment') }}" data-size="lg" data-bs-toggle="tooltip"
                title="{{ __('Edit') }}" data-original-title="{{ __('Edit') }}">
                <i class="ti ti-pencil text-white"></i>
            </a>
        </div>
    @endpermission
    @permission('expense payment delete')
        <div class="action-btn">
            {{ Form::open(['route' => ['payment.destroy', $payment->id], 'class' => 'm-0']) }}
            @method('DELETE')
            <a class="mx-3 btn bg-danger btn-sm  align-items-center bs-pass-para show_confirm" data-bs-toggle="tooltip" title=""
                data-bs-original-title="Delete" aria-label="Delete" data-confirm="{{ __('Are You Sure?') }}"
                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                data-confirm-yes="delete-form-{{ $payment->id }}"><i class="ti ti-trash text-white text-white"></i></a>
            {{ Form::close() }}
        </div>
    @endpermission
@endif
