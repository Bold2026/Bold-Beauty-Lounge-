<div class="border">
    <div class="p-3 border-bottom accordion-header">
        <div class="row align-items-center">
            <div class="col">
                <h5>{{ __('Info') }}</h5>
            </div>
            <div id="p1" class="col-auto text-end text-primary h3">
                <a image-url="{{ asset('packages/workdo/LandingPage/src/Resources/assets/infoimages/screenshotsection.png') }}"
                   data-url="{{ route('info.image.view',['landingpage','screenshots']) }}" class="view-images pt-2">
                    <i class="ti ti-info-circle pointer"></i>
                </a>
            </div>
            <div class="col-auto justify-content-end d-flex">
                <a data-size="mx" data-url="{{ route('screenshots_create') }}" data-ajax-popup="true" title="{{__('Create Screenshots')}}" data-bs-toggle="tooltip" data-title="{{__('Create Screenshots')}}"  class="btn btn-sm btn-primary">
                    <i class="ti ti-plus text-light"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{__('No')}}</th>
                        <th>{{__('Name')}}</th>
                        <th>{{__('Action')}}</th>
                    </tr>
                </thead>
                <tbody>
                   @if (is_array($screenshots) || is_object($screenshots))
                   @php
                        $no = 1
                    @endphp
                        @foreach ($screenshots as $key => $value)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $value['screenshots_heading'] }}</td>
                                <td>
                                    <span>
                                        <div class="action-btn  me-2">
                                                <a href="#" class="bg-info btn btn-sm align-items-center" data-url="{{ route('screenshots_edit',$key) }}" data-ajax-popup="true" data-title="{{__('Edit Screenshot')}}" data-size="mx" data-bs-toggle="tooltip"  title="{{__('Edit')}}" data-original-title="{{__('Edit Info')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        <div class="action-btn ">
                                            {!! Form::open(['method' => 'GET', 'route' => ['screenshots_delete', $key],'id'=>'delete-form-'.$key]) !!}
                                                <a href="#" class="bg-danger btn btn-sm align-items-center bs-pass-para show_confirm" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="{{ 'delete-form-'.$key}}">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                                {!! Form::close() !!}
                                            </div>
                                        </span>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

