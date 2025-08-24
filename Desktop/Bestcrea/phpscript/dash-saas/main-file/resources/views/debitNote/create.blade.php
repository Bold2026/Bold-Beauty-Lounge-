{{ Form::open(array('route' => array('purchases.debit.note.store',$purchase_id),'mothod'=>'post','class' => 'needs-validation', 'novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('debit_note', __('Debit Note'),['class'=>'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                <select class="form-control select" required="required" id="debit_note" name="debit_note">
                    <option value>{{ __('Select Debit Note') }}</option>
                    @foreach ($debitNotes as $key => $debitNote)
                        <option value="{{ $key }}">{{ \Workdo\Account\Entities\CustomerDebitNotes::debitNumberFormat($debitNote) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('date', __('Date'),['class'=>'form-label']) }}<x-required></x-required>
                {{Form::date('date',null,array('class'=>'form-control ','required'=>'required','placeholder'=>'Select Date'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::number('amount', 0, array('class' => 'form-control','required'=>'required','step'=>'0.01','min'=>'0','placeholder'=>__('Enter Amount'))) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
                {{ Form::textarea('description', '', array('class' => 'form-control','rows'=>3,'placeholder'=>__('Enter Description'))) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}


<script>
    $(document).on('click' , '#debit_note', function(){
        var debit_note = $(this).val();
        $.ajax({
            url: "{{route('debit-note.price')}}",
            method:'POST',
            data: {
                "debit_note": debit_note,
                "_token": "{{ csrf_token() }}",
            },
            success: function (data) {
                if (data !== undefined) {
                    $('#amount').val(data);
                    $('input[name="amount"]').attr('max', data);
                    $('input[name="amount"]').attr('min', 0);
                }
            }
        });
    });
</script>
