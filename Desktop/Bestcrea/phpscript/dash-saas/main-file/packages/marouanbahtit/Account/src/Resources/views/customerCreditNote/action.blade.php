@permission('creditnote edit')
<div class="action-btn me-2">
    <a data-url="{{ route('invoice.edit.custom-credit',[$customcreditNote->invoice,$customcreditNote->id]) }}" data-ajax-popup="true" data-title="{{__('Edit Credit Note')}}" href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
        <i class="ti ti-pencil text-white"></i>
    </a>
</div>
@endpermission
@permission('creditnote delete')
<div class="action-btn">
    {{Form::open(array('route'=>array('invoice.custom-note.delete', $customcreditNote->invoice,$customcreditNote->id),'class' => 'm-0'))}}
    @method('DELETE')
    <a href="#"
       class="mx-3 btn bg-danger btn-sm  align-items-center bs-pass-para show_confirm"
       data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
       aria-label="Delete" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}"  data-confirm-yes="delete-form-{{$customcreditNote->id}}"><i
            class="ti ti-trash text-white text-white"></i></a>
    {{Form::close()}}
</div>
@endpermission
