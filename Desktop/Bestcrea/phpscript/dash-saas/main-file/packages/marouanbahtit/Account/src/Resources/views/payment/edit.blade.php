{{ Form::model($payment, array('route' => array('payment.update', $payment->id), 'method' => 'PUT','enctype' => 'multipart/form-data', 'class'=>'needs-validation', 'novalidate')) }}
<div class="modal-body">
    <div class="text-end">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn',['template_module' => 'payment','module'=>'Account'])
        @endif
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                {{ Form::date('date',null, ['class' => 'form-control ','required'=>'required','placeholder' => 'Select Date','max' => date('Y-m-d')]) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                {{ Form::number('amount',null, array('class' => 'form-control','required'=>'required','min'=>'0','step'=>'0.01','placeholder' => 'Enter Amount')) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('account_id', __('Account'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::select('account_id',$accounts,null, array('class' => 'form-control ','required'=>'required','placeholder' => 'Select Account')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('vendor_id', __('Vendor'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::select('vendor_id', $vendors,null, array('class' => 'form-control ','required'=>'required','placeholder' => 'Select Vendor')) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('category_id', __('Category'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::select('category_id', $categories,null, array('class' => 'form-control ','required'=>'required','placeholder' => 'Select Category')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                {{ Form::text('reference',null, array('class' => 'form-control','placeholder' => 'Enter Reference','required'=>'required')) }}
            </div>
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::textarea('description',null, array('class' => 'form-control','placeholder' => 'Enter Description','rows'=>3,'required'=>'required')) }}
        </div>
        <div class="form-group">
            {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => 'form-label']) }}
            <div class="choose-file">
                <label for="add_receipt" class="form-label">
                    <input type="file" name="add_receipt" id="add_receipt" class="form-control file" style="width: 758px;" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])" data-filename="add_receipt">
                </label>
                <img id="blah" width="20%" class="mt-3" src="{{ !empty($payment->add_receipt) ? get_file($payment->add_receipt):'' }}">
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}



