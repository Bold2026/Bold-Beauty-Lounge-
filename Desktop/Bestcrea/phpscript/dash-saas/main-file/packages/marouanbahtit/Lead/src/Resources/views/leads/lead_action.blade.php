@permission('lead edit')
    <div class="action-btn me-2">
        <a data-url="{{ URL::to('leads/' . $lead->id . '/labels') }}"
            data-ajax-popup="true" data-toggle="tooltip" data-title="{{__('Labels')}}"
            title="{{ __('Labels') }}"
            class="mx-3 btn btn-sm align-items-center bg-primary"><i class="ti ti-copy"></i></a>
    </div>
@endpermission
@permission('lead show')
    @if($lead->is_active)
        <div class="action-btn me-2">
            <a href="{{route('leads.show',$lead->id)}}" class="mx-3 btn btn-sm align-items-center bg-warning" data-toggle="tooltip" data-original-title="{{__('View')}}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('View')}}"><i class="ti ti-eye text-white"></i></a>
        </div>
    @endif
@endpermission
@permission('lead edit')
    <div class="action-btn me-2">
        <a data-size="lg" data-url="{{ URL::to('leads/'.$lead->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Lead')}}" class="mx-3 btn btn-sm align-items-center bg-info" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Edit')}}" ><i class="ti ti-pencil text-white"></i></a>
    </div>
@endpermission
@permission('lead delete')
    <div class="action-btn me-2">
        {!! Form::open(['method' => 'DELETE', 'route' => ['leads.destroy', $lead->id],'id'=>'delete-form-'.$lead->id]) !!}
            <a href="#!" class="mx-3 btn btn-sm align-items-center show_confirm bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Delete')}}" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                <span class="text-white"> <i class="ti ti-trash"></i></span></a>
        {!! Form::close() !!}
    </div>
@endpermission