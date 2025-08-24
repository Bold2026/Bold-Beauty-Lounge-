@permission('warehouse show')
    <div class="action-btn me-2">
        <a href="{{ route('warehouses.show', $warehouse->id) }}"
            class="mx-3 btn btn-sm align-items-center bg-warning"
            data-bs-toggle="tooltip" title="{{ __('View') }}"><i class="ti ti-eye text-white"></i></a>
    </div>
@endpermission
@permission('warehouse edit')
    <div class="action-btn me-2">
        <a class="mx-3 btn btn-sm  align-items-center bg-info" data-url="{{ route('warehouses.edit', $warehouse->id) }}"
            data-ajax-popup="true" data-size="lg " data-bs-toggle="tooltip" title="{{ __('Edit') }}"
            data-title="{{ __('Edit Warehouse') }}">
            <i class="ti ti-pencil text-white"></i>
        </a>
    </div>
@endpermission
@permission('warehouse delete')
    <div class="action-btn">
        {!! Form::open([
            'method' => 'DELETE',
            'route' => ['warehouses.destroy', $warehouse->id],
            'id' => 'delete-form-' . $warehouse->id,
        ]) !!}
        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger" data-bs-toggle="tooltip"
            title="{{ __('Delete') }}"><i class="ti ti-trash text-white"></i></a>
        {!! Form::close() !!}
    </div>
@endpermission
