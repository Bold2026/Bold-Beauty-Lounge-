{{Form::open(array('route'=>array('productslogtime.store'),'method'=>'post', 'class' => 'needs-validation', 'novalidate'))}}
<div class="modal-body">
    <div class="text-end">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn',['template_module' => 'parts_logtime','module'=>'CMMS'])
        @endif
    </div>
    <input type="hidden" name="product_id" value="{{$product_id}}">
    <div class="row">
        <div class="col-md-12 form-group">
            {{Form::label('date',__('Date'),['class'=>'form-label']) }}<x-required></x-required>
            {{Form::date('date',null,array('class'=>'form-control','placeholder'=>__('Enter Date'),'required'=>'required'))}}
        </div>
        <div class="col-md-12 form-group">
            {{Form::label('description',__('Description'),['class'=>'form-label']) }}<x-required></x-required>
            {{Form::textarea('description',null,array('class'=>'form-control','placeholder'=>__('Enter Description'),'required'=>'required' , 'rows' => '3'))}}
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}


