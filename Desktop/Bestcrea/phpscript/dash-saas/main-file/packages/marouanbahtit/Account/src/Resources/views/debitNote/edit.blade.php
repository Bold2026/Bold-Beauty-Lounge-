{{ Form::model($debitNote, array('route' => array('bill.edit.debit.updatenote',$debitNote->bill, $debitNote->id), 'method' => 'post','class'=>'needs-validation', 'novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('debit_note', __('Debit Note'),['class'=>'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                {{Form::text('debit_note', !empty($debitNote->debitNote) ? \Workdo\Account\Entities\CustomerDebitNotes::debitNumberFormat($debitNote->debitNote->debit_id) : '',array('class'=>'form-control ','required'=>'required' , 'disabled'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('date', __('Date'),['class'=>'form-label']) }}<x-required></x-required>
                {{Form::date('date',null,array('class'=>'form-control ','required'=>'required','placeholder'=>'Select Date','max' => date('Y-m-d')))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::number('amount',null, array('class' => 'form-control','required'=>'required','min'=>'0','step'=>'0.01','placeholder'=> __('Enter Amount'))) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
                {{ Form::textarea('description',null, array('class' => 'form-control','rows'=>3,'placeholder'=> __('Enter Description'))) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function () {
        var amount = parseFloat($('#amount').val());
        var debitNoteId = "{{ $debitNote->debit_note }}";
        $.ajax({
            url: "{{ route('debit-note.price') }}",
            method: 'POST',
            data: {
                debit_note: debitNoteId,
                amount:amount,
                _token: "{{ csrf_token() }}"
            },
            success: function (data) {
                if (data !== undefined) {
                    $('input[name="amount"]').attr('max', data);
                    $('input[name="amount"]').attr('min', 0);
                }
            }
        });
    });
</script>
