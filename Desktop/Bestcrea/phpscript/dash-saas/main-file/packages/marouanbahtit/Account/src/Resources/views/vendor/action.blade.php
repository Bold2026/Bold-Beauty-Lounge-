@if($vendor->is_disable == 1)
<span>
    @if (!empty($vendor['vendor_id']))
        @permission('vendor show')
            <div class="action-btn  me-2">
                <a href="{{ route('vendors.show', \Crypt::encrypt($vendor['id'])) }}"
                    class="mx-3 btn bg-warning btn-sm align-items-center"
                    data-bs-toggle="tooltip" title="{{ __('View') }}">
                    <i class="ti ti-eye text-white text-white"></i>
                </a>
            </div>
        @endpermission
    @endif
    @permission('vendor edit')
        <div class="action-btn  me-2">
            <a  class="mx-3 btn bg-info btn-sm  align-items-center"
                data-url="{{ route('vendors.edit', $vendor['id']) }}"
                data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
                title="" data-title="{{ __('Edit Vendor') }}"
                data-bs-original-title="{{ __('Edit') }}">
                <i class="ti ti-pencil text-white"></i>
            </a>
        </div>
    @endpermission
    @if (!empty($vendor['vendor_id']))
        @permission('vendor delete')
            <div class="action-btn">
                {{ Form::open(['route' => ['vendors.destroy', $vendor['id']], 'class' => 'm-0']) }}
                @method('DELETE')
                <a
                    class="mx-3 btn bg-danger btn-sm  align-items-center bs-pass-para show_confirm"
                    data-bs-toggle="tooltip" title=""
                    data-bs-original-title="Delete" aria-label="Delete"
                    data-confirm="{{ __('Are You Sure?') }}"
                    data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                    data-confirm-yes="delete-form-{{ $vendor['id'] }}"><i
                        class="ti ti-trash text-white text-white"></i></a>
                {{ Form::close() }}
            </div>
        @endpermission
    @endif
</span>
@else
<div class="action-btn">
    <a href="#" class="btn btn-sm d-inline-flex align-items-center bg-dark" data-title="{{ __('Lock') }}"
        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Vendor Is Disable') }}"><span class="text-white"><i
                class="ti ti-lock"></i></span></a>
</div>
@endif
