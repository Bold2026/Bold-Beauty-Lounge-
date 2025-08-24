
{{ Form::open(array('url' => 'leads','enctype'=>'multipart/form-data','class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="text-end mb-3">
            <!-- @if (module_is_active('AIAssistant'))
                @include('aiassistant::ai.generate_ai_btn',['template_module' => 'lead','module'=>'Lead'])
            @endif -->
        </div>
        @if(module_is_active('CustomField') && !$customFields->isEmpty())
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#tab-1" role="tab" aria-controls="pills-home" aria-selected="true">{{__('Lead Detail')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" href="#tab-2" role="tab" aria-controls="pills-profile" aria-selected="false">{{__('Custom Fields')}}</a>
                </li>
            </ul>
        @endif
        <div class="tab-content tab-bordered">
            <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
                <div class="row">
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('subject', __('Subject'),['class'=>'form-label']) }}<x-required></x-required>
                        {{ Form::text('subject', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Subject'))) }}
                    </div>
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('user_id', __('User'),['class'=>'form-label']) }}<x-required></x-required>
                        {{ Form::select('user_id', $users,null, array('class' => 'form-control select2','required'=>'required')) }}
                        @if(count($users) == 1)
                            <div class="text-muted text-xs">
                                {{__('Please create new users')}} <a href="{{route('users')}}">{{__('here')}}</a>.
                            </div>
                        @endif
                    </div>
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('name', __('Name'),['class'=>'form-label']) }}<x-required></x-required>
                        {{ Form::text('name', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Name'))) }}
                    </div>
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('email', __('Email'),['class'=>'form-label']) }}<x-required></x-required>
                        {{ Form::text('email', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Email'))) }}
                    </div>
                    <x-mobile name="phone" label="{{__('Phone No')}}" divClass="col-md-6" placeholder="{{__('Enter Phone No')}}" required></x-mobile>
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('follow_up_date', __('Follow Up Date'),['class'=>'form-label']) }}
                        {{ Form::date('follow_up_date', null, array('class' => 'form-control')) }}
                    </div>
                </div>
            </div>
            @if(module_is_active('CustomField') && !$customFields->isEmpty())
            <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                <div class="col-md-6">
                    @include('custom-field::formBuilder')
                </div>
            </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
    </div>

{{ Form::close() }}

