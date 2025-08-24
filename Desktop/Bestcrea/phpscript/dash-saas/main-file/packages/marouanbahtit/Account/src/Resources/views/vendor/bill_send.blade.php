{{ Form::open(['route' => ['vendor.bill.send.mail', $bill_id], 'class'=>'needs-validation', 'novalidate']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-12">
                {{ Form::label('email', __('Email'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::email('email', '', ['class' => 'form-control', 'required' => 'required','placeholder'=>'Enter Email']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        {{ Form::submit(__('Send'), ['class' => 'btn  btn-primary']) }}
    </div>
{{ Form::close() }}
