{{ Form::model($overtime, ['route' => ['overtime.update', $overtime->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Title')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('number_of_days', __('Number of days'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('number_of_days', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Number of days'), 'min' => '0']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('hours', __('Hours'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('hours', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Hours'), 'step' => '0.10']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('rate', __('Rate'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('rate', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Rate'), 'step' => '0.10']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label date_label']) }}<x-required></x-required>
                {{ Form::date('start_date', null, ['class' => 'form-control ', 'required' => 'required', 'max' => date('Y-m-d')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label date_label']) }}<x-required></x-required>
                {{ Form::date('end_date', null, ['class' => 'form-control ', 'required' => 'required', 'min' => date('Y-m-d')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                {{ Form::select('status', $status, null, ['class' => 'form-control ']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Update'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}
