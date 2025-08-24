{{ Form::open(array('route' => array('bill.createpayment', $bill->id),'method'=>'post','enctype' => 'multipart/form-data', 'class'=>'needs-validation', 'novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('date', __('Date'),['class'=>'form-label']) }}<x-required></x-required>
                {{Form::date('date',null,array('class'=>'form-control ','required'=>'required','placeholder'=>'Select Date','max' => date('Y-m-d')))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::number('amount',$bill->getDue(), array('class' => 'form-control','required'=>'required','min'=>'0','step'=>'0.01','max' => $bill->getDue())) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('account_id', __('Account'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::select('account_id',$accounts,null, array('class' => 'form-control ','required'=>'required','placeholder'=>'Select Account')) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::text('reference',null, array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Reference')) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
                {{ Form::textarea('description', '', array('class' => 'form-control','rows'=>3,'placeholder'=>__('Enter Description'))) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => 'form-label']) }}
            <div class="choose-file">
                <label for="image" class="form-label">
                    <input type="file" name="add_receipt" id="image" class="form-control" style="width: 460px;" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                </label>
                <img id="blah" width="25%" class="mt-3">
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary" id="submit">
</div>
{{ Form::close() }}
 