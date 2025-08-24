{{ Form::open(['route' => ['iprestrict.store'], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('ip', __('IP'), ['class' => 'form-label']) }}
            {{ Form::text('ip', null, ['class' => 'form-control', 'placeholder' => __('Enter Ip')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
