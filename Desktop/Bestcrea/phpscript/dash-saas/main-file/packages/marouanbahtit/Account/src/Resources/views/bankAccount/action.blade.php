@permission('bank account show')
    <div class="action-btn me-2">
        <a class="btn btn-sm align-items-center bg-warning"
            data-url="{{ route('bank-account.show', $account->id) }}"
            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title=""
            data-title="{{ __('Bank Account Detail') }}" data-bs-original-title="{{ __('View') }}">
            <i class="ti ti-eye text-white"></i>
        </a>
    </div>
@endpermission

@if($account->holder_name!='Cash')
    @permission('bank account edit')
        <div class="action-btn  me-2">
            <a  class="mx-3 btn bg-info btn-sm align-items-center" data-url="{{ route('bank-account.edit',$account->id) }}" data-ajax-popup="true" title="{{__('Edit')}}" data-title="{{__('Edit Bank Account')}}"data-bs-toggle="tooltip"  data-size="md"  data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil text-white"></i>
            </a>
        </div>
    @endpermission
    @permission('bank account delete')
        <div class="action-btn">
            {{Form::open(array('route'=>array('bank-account.destroy', $account->id),'class' => 'm-0'))}}
            @method('DELETE')
                <a
                    class="mx-3 bg-danger btn btn-sm  align-items-center bs-pass-para show_confirm"
                    data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                    aria-label="Delete" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}"  data-confirm-yes="delete-form-{{$account->id}}"><i
                        class="ti ti-trash text-white text-white"></i></a>
            {{Form::close()}}
        </div>
    @endpermission
@else
    -
@endif
