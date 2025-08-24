<div class="action-btn">
    {!! Form::open([
        'method' => 'DELETE',
        'route' => ['warehouses-transfer.destroy', $warehouse_transfer->id],
        'id' => 'delete-form-' . $warehouse_transfer->id,
    ]) !!}
    <a href="#"
        class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
        data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i
            class="ti ti-trash text-white"></i></a>
    {!! Form::close() !!}
</div>
