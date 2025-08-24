
@if(!empty($task))
{{ Form::model($task, array('route' => array('deals.tasks.update', $deal->id, $task->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
@else
{{ Form::open(array('route' => ['deals.tasks.store',$deal->id],'class'=>'needs-validation','novalidate')) }}
@endif
<div class="modal-body">
    <div class="text-end">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn',['template_module' => 'deal task','module'=>'Lead'])
        @endif
    </div>
    <div class="row">
        <div class="col-12 form-group">
            {{ Form::label('name', __('Name'),['class'=>'col-form-label']) }}<x-required></x-required>
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Name'))) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('date', __('Date'),['class'=>'col-form-label']) }}<x-required></x-required>
            {{ Form::date('date', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('time', __('Time'),['class'=>'col-form-label']) }}<x-required></x-required>
            {{ Form::time('time', null, array('class' => 'form-control timepicker','id' => 'time','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('priority', __('Priority'),['class'=>'col-form-label']) }}<x-required></x-required>
            <select class="form-control" name="priority" required id="priority">
                @foreach($priorities as $key => $priority)
                    <option value="{{$key}}" @if(isset($task) && $task->priority == $key) selected @endif>{{__($priority)}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 form-group">
            {{ Form::label('status', __('Status'),['class'=>'col-form-label']) }}<x-required></x-required>
            <select class="form-control" name="status" required id="status">
                @foreach($status as $key => $st)
                    <option value="{{$key}}" @if(isset($task) && $task->status == $key) selected @endif>{{__($st)}}</option>
                @endforeach
            </select>
        </div>
        @if(empty($task))
            @stack('calendar')
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    @if(isset($task))
        <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
    @else
        <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
    @endif
</div>

{{ Form::close() }}
