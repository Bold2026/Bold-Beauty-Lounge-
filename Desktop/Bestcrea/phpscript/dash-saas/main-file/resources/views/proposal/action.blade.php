
    <div class="action-btn me-2">
        <a href="#" class="btn btn-sm align-items-center cp_link bg-primary" data-link="{{route('pay.proposalpay',\Illuminate\Support\Facades\Crypt::encrypt($proposal->id))}}" data-bs-toggle="tooltip" title="{{__('Copy')}}" data-original-title="{{__('Click to copy proposal link')}}">
            <i class="ti ti-file text-white"></i>
        </a>
    </div>
    @if($proposal->status != 0 && $proposal->status != 3 )
        @if ($proposal->is_convert == 0 && $proposal->is_convert_retainer ==0)
            @permission('proposal convert invoice')
                <div class="action-btn me-2">
                    {!! Form::open([
                        'method' => 'get',
                        'route' => ['proposal.convert', $proposal->id],
                        'id' => 'proposal-form-' . $proposal->id,
                    ]) !!}
                    <a href="#"
                        class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-success"
                        data-bs-toggle="tooltip" title=""
                        data-bs-original-title="{{ __('Convert to Invoice') }}"
                        aria-label="{{__('Delete')}}"
                        data-text="{{ __('You want to confirm convert to Invoice. Press Yes to continue or No to go back') }}"
                        data-confirm-yes="proposal-form-{{ $proposal->id }}">
                        <i class="ti ti-exchange text-white"></i>
                    </a>
                    {{ Form::close() }}
                </div>
            @endpermission
        @elseif($proposal->is_convert ==1)
            @permission('invoice show')
                <div class="action-btn me-2">
                    <a href="{{ route('invoice.show', \Crypt::encrypt($proposal->converted_invoice_id)) }}"
                        class="mx-3 btn btn-sm  align-items-center bg-success"
                        data-bs-toggle="tooltip"
                        title="{{ __('Already convert to Invoice') }}">
                        <i class="ti ti-eye text-white"></i>
                    </a>
                </div>
            @endpermission
        @endif
    @endif
    @if (module_is_active('Retainer'))
        @include('retainer::setting.convert_retainer', ['proposal' => $proposal ,'type' =>'list'])
    @endif
    
    @permission('proposal duplicate')
        <div class="action-btn me-2">
            {!! Form::open([
                'method' => 'get',
                'route' => ['proposal.duplicate', $proposal->id],
                'id' => 'duplicate-form-' . $proposal->id,
            ]) !!}
            <a href="#"
                class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-secondary"
                data-bs-toggle="tooltip" title=""
                data-bs-original-title="{{ __('Duplicate') }}"
                aria-label="Delete"
                data-text="{{ __('You want to confirm duplicate this proposal. Press Yes to continue or No to go back') }}"
                data-confirm-yes="duplicate-form-{{ $proposal->id }}">
                <i class="ti ti-copy text-white text-white"></i>
            </a>
            {{ Form::close() }}
        </div>
    @endpermission

    @permission('proposal show')
        <div class="action-btn me-2">
            <a href="{{ route('proposal.show', \Crypt::encrypt($proposal->id)) }}"
                class="mx-3 btn btn-sm align-items-center bg-warning"
                data-bs-toggle="tooltip" title="{{ __('View') }}"
                data-original-title="{{ __('Detail') }}">
                <i class="ti ti-eye text-white text-white"></i>
            </a>
        </div>
    @endpermission

    @if (module_is_active('ProductService') && ($proposal->proposal_module == 'taskly' ? module_is_active('Taskly') : module_is_active('Account')) && ($proposal->proposal_module == 'cmms' ? module_is_active('CMMS') : module_is_active('Account')))
        @permission('proposal edit')
            <div class="action-btn me-2">
                <a href="{{ route('proposal.edit', \Crypt::encrypt($proposal->id)) }}"
                    class="mx-3 btn btn-sm  align-items-center bg-info"
                    data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Edit') }}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endpermission
    @endif

    @permission('proposal delete')
        <div class="action-btn me-2">
            {{ Form::open(['route' => ['proposal.destroy', $proposal->id], 'class' => 'm-0']) }}
            @method('DELETE')
            <a href="#"
                class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                data-bs-toggle="tooltip" title=""
                data-bs-original-title="{{__('Delete')}}" aria-label="{{__('Delete')}}"
                data-confirm="{{ __('Are You Sure?') }}"
                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                data-confirm-yes="delete-form-{{ $proposal->id }}"><i
                    class="ti ti-trash text-white text-white"></i></a>
            {{ Form::close() }}
        </div>
    @endpermission
