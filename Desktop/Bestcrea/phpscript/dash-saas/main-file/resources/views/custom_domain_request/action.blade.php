    @if($custom_domain_request->status != 1)
        <div class="action-btn me-2">
            <a class="mx-3 btn btn-sm align-items-center bg-primary" href="{{route('custom_domain_request.request',[$custom_domain_request->id,1])}}"
                title="{{__('Accept')}}" data-bs-toggle="tooltip">
            <span> <i class="ti ti-check text-white"></i></span>
            </a>
        </div>
        <div class="action-btn me-2">
            <a class="mx-3 btn btn-sm align-items-center bg-warning" href="{{route('custom_domain_request.request',[$custom_domain_request->id,0])}}"
                title="{{__('Reject')}}" data-bs-toggle="tooltip" class="bg-warning">
            <span> <i class="ti ti-x text-white"></i></span>
            </a>
        </div>
    @endif

    <div class="action-btn">
        {{ Form::open(['route' => ['custom_domain_request.destroy', $custom_domain_request->id], 'class' => 'm-0']) }}
        @method('DELETE')
        <a href="#"
            class="mx-3 btn btn-sm align-items-center show_confirm bg-danger"
            data-bs-toggle="tooltip" title=""
            data-bs-original-title="{{__('Delete')}}" aria-label="{{__('Delete')}}"
            data-confirm-yes="delete-form-{{ $custom_domain_request->id }}"><i
                class="ti ti-trash text-white text-white"></i></a>
        {{ Form::close() }}
    </div>
