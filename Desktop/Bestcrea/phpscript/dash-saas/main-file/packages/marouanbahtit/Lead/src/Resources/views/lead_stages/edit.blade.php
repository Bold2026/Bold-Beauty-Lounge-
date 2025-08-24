
{{ Form::model($leadStage, array('route' => array('lead-stages.update', $leadStage->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-12">
                {{ Form::label('name', __('Lead Stage Name'),['class'=>'col-form-label']) }}<x-required></x-required>
                {{ Form::text('name', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Lead Stage Name'),'maxlength' => '30')) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('pipeline_id', __('Pipeline'),['class'=>'col-form-label']) }}<x-required></x-required>
                {{ Form::select('pipeline_id', $pipelines,null, array('class' => 'form-control select2','required'=>'required')) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
    </div>

{{ Form::close() }}
