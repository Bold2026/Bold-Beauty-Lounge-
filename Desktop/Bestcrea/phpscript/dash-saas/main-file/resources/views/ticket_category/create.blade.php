{{ Form::open(['route' => 'helpdeskticket-category.store','class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('name', '', ['class' => 'form-control', 'required' => 'required','placeholder' => 'Enter Name']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('color', __('Color'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::color('color', '', ['class' => 'form-control jscolor', 'required' => 'required']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <div class="text-end">
        <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
    </div>
</div>
{{ Form::close() }}
