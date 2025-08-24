@stack('purchase-signature')
<div class="action-btn me-2">
    <a href="#" class="btn btn-sm align-items-center cp_link bg-primary"
        data-link="{{ route('purchases.link.copy', \Crypt::encrypt($purchase->id)) }}" data-bs-toggle="tooltip"
        title="{{ __('Copy') }}" data-original-title="{{ __('Click to copy purchasse link') }}">
        <i class="ti ti-file text-white"></i>
    </a>
</div>
@permission('purchase show')
    <div class="action-btn me-2">
        <a href="{{ route('purchases.show', \Crypt::encrypt($purchase->id)) }}"
            class="mx-3 btn btn-sm align-items-center bg-warning" data-bs-toggle="tooltip" title="{{ __('View') }}"
            data-original-title="{{ __('Detail') }}">
            <i class="ti ti-eye text-white"></i>
        </a>
    </div>
@endpermission
@if ($purchase->status != 4)
    @permission('purchase edit')
        <div class="action-btn me-2">
            <a href="{{ route('purchases.edit', \Crypt::encrypt($purchase->id)) }}"
                class="mx-3 btn btn-sm align-items-center bg-info" data-bs-toggle="tooltip" title="Edit"
                data-original-title="{{ __('Edit') }}">
                <i class="ti ti-pencil text-white"></i>
            </a>
        </div>
    @endpermission
    @permission('purchase delete')
        <div class="action-btn">
            {!! Form::open([
                'method' => 'DELETE',
                'route' => ['purchases.destroy', $purchase->id],
                'class' => 'delete-form-btn',
                'id' => 'delete-form-' . $purchase->id,
            ]) !!}
            <a href="#" class="mx-3 btn btn-sm align-items-center show_confirm bg-danger" data-bs-toggle="tooltip"
                title="{{ __('Delete') }}" data-original-title="{{ __('Delete') }}"
                data-confirm="{{ __('Are You Sure?') }}"
                data-confirm-yes="document.getElementById('delete-form-{{ $purchase->id }}').submit();">
                <i class="ti ti-trash text-white"></i>
            </a>
            {!! Form::close() !!}
        </div>
    @endpermission
@endif
