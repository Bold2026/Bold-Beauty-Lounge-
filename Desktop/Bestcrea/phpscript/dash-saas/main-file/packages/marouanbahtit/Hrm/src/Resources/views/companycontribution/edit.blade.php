{{ Form::model($companycontribution, ['route' => ['companycontribution.update', $companycontribution->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Title')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('type', $companycontributiontype, null, ['class' => 'form-control amount_type ', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', $companycontribution->type == 'percentage' ? __('Percentage') : __('Amount'), ['class' => 'form-label amount_label']) }}<x-required></x-required>
                {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Amount'), 'step' => '0.01']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Update'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}
