
{{ Form::open(array('url' => 'deals','enctype'=>'multipart/form-data','class'=>'needs-validation','novalidate')) }}
<div class="modal-body">
    <div class="text-end">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn',['template_module' => 'deal','module'=>'Lead'])
        @endif
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
                <div class="col-6 form-group">
                    {{ Form::label('name', __('Deal Name'),['class'=>'col-form-label']) }}<x-required></x-required>
                    {{ Form::text('name', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Deal Name'))) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('price', __('Price'),['class'=>'col-form-label']) }}
                    {{ Form::number('price', 0, array('class' => 'form-control','min'=>0)) }}
                </div>
                <x-mobile name="phone" label="{{__('Phone No')}}" placeholder="{{__('Enter Phone No')}}" required></x-mobile>
                <div class="col-12 form-group">
                    {{ Form::label('company_id', __('Clients'),['class'=>'col-form-label']) }}<x-required></x-required>
                    {{ Form::select('clients[]', $clients,null, array('class' => 'form-control choices','id'=>'choices-multiple','multiple' => true)) }}

                    @if(count($clients) <= 0 && Auth::user()->type == 'company')
                        <div class="text-muted text-xs">
                            {{__('Please create new clients')}} <a href="{{route('users.index')}}">{{__('here')}}</a>.
                        </div>
                    @endif
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
    <button type="submit" class="btn  btn-primary" id="submit">{{__('Create')}}</button>
</div>
{{ Form::close() }}

<script>
    $(function(){
        $("#submit").click(function() {
            var client =  $("#choices-multiple option:selected").length;
            if(client == 0){
                $('#clients_validation').removeClass('d-none')
                return false;
            } else {
                $('#clients_validation').addClass('d-none')
            }
        });
    });
</script>
