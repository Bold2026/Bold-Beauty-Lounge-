@permission('product&service show')
    <div class="action-btn me-2">
        <a class="mx-3 btn btn-sm align-items-center bg-warning" href="{{ route('product-service.show', $productService->id) }}"
            data-size="md" data-bs-toggle="tooltip" title="{{ __('View') }}">
            <i class="ti ti-eye text-white"></i>
        </a>
    </div>
@endpermission
@permission('product&service edit')
    <div class="action-btn me-2">
        <a href="{{ route('product-service.edit', $productService->id) }}"
            class="mx-3 btn btn-sm d-inline-flex align-items-center bg-info" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
            <span class="text-white"> <i class="ti ti-pencil"></i></span></a>
    </div>
@endpermission
@permission('product&service delete')
    <div class="action-btn">
        {!! Form::open([
            'method' => 'DELETE',
            'route' => ['product-service.destroy', $productService->id],
            'id' => 'delete-form-' . $productService->id,
        ]) !!}
        <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger" data-bs-toggle="tooltip"
            title="{{ __('Delete') }}"
            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"><i
                class="ti ti-trash text-white text-white"></i></a>
        {!! Form::close() !!}
    </div>
@endpermission
