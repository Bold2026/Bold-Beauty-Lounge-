<span>
    <div class="action-btn me-2">
        <a href="{{ route('coupons.show',$coupon->id) }}" class="mx-3 btn btn-sm align-items-center bg-warning" data-bs-toggle="tooltip" title="{{__('View')}}">
            <i class="ti ti-eye text-white"></i>
        </a>
    </div>
    @permission('coupon edit')
        <div class="action-btn me-2">
                <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-size="lg" data-url="{{ route('coupons.edit',$coupon->id) }}" data-ajax-popup="true" data-title="{{__('Edit Coupon')}}" data-bs-toggle="tooltip"  title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil text-white"></i>
            </a>
        </div>
    @endpermission
    @permission('coupon delete')
        <div class="action-btn">
            {{ Form::open(['route' => ['coupons.destroy', $coupon->id], 'class' => 'm-0']) }}
            @method('DELETE')
            <a href="#"
                class="mx-3 btn btn-sm align-items-center show_confirm bg-danger"
                data-bs-toggle="tooltip" title=""
                data-bs-original-title="{{__('Delete')}}" aria-label="{{__('Delete')}}"
                data-confirm-yes="delete-form-{{ $coupon->id }}"><i
                    class="ti ti-trash text-white text-white"></i></a>
            {{ Form::close() }}
        </div>
    @endpermission
</span>
