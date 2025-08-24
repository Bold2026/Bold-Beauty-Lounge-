{{ Form::model($debitNote, array('route' => array('bill.custom.edit',$debitNote->bill, $debitNote->id), 'method' => 'post', 'class'=>'needs-validation', 'novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="d-flex align-items-center gap-3">
                <label class="form-check-label">
                    <input type="radio" name="type" value="bill" class="form-check-input" {{ $debitNote->type == 'bill' ? 'checked' : ''}} disabled> {{ __('Bill') }}
                </label>
                <label class="form-check-label">
                    <input type="radio" name="type" value="purchase" class="form-check-input" {{ $debitNote->type == 'purchase' ? 'checked' : ''}} disabled> {{ __('Purchase') }}
                </label>
            </div>
        </div>
        <div class="form-group  col-md-12 bill_section">
            {{ Form::label('bill', __('Bill'), ['class' => 'form-label']) }}<x-required></x-required>
            <select class="form-control select" required="required" id="bill" name="bill" disabled>
                <option value>{{ __('Select Bill') }}</option>
                @foreach ($bills as $key => $bill)
                    <option value="{{ $key }}" {{ $key == $debitNote->bill ? 'selected'  : ''}}>{{ Workdo\Account\Entities\Bill::billNumberFormat($bill) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-12 purchase_section">
            {{ Form::label('bill', __('Purchase'),['class'=>'form-label']) }}<x-required></x-required>
                <select class="form-control select" id="purchase" name="purchase" disabled>
                    <option value>{{__('Select Purchase')}}</option>
                    @foreach($purchases as $key=>$purchase)
                        <option value="{{$key}}" {{ $key == $debitNote->bill ? 'selected'  : ''}}>{{ App\Models\Purchase::purchaseNumberFormat($purchase)}}</option>
                    @endforeach
                </select>
        </div>
        <div class="form-group col-md-12 items d-none">
            {{ Form::label('item', __('Item'), ['class' => 'form-label']) }}<x-required></x-required>
            <select class="form-control select" required="required" id="item" name="bill_product">
            </select>
        </div>
        <div class="form-group amount col-md-12 d-none">
            {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}<x-required></x-required>
            <div class="form-icon-user amountnote">
                {{ Form::number('amount', null, array('class' => 'form-control','required'=>'required','step'=>'0.01', 'min' => 0.01 ,'placeholder'=>__('Enter Amount'))) }}
            </div>
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}<x-required></x-required>
            {{Form::date('date',null,array('class'=>'form-control','required'=>'required','max' => date('Y-m-d')))}}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'3','placeholder'=>__('Enter Description')]) !!}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready (function(){
        var type = $('input[name="type"]:checked').val();
        if(type == 'bill') {
            bill_id = $('#bill').val();
            $('.purchase_section').addClass('d-none');
        }
        else {
            bill_id = $('#purchase').val();
            $('.bill_section').addClass('d-none');
        }   
        $.ajax({
        url: "{{route('debit-bill.items')}}",
        method:'POST',
        data: {
            "bill_id": bill_id, 
            "type"   : type,
            "_token": "{{ csrf_token() }}",
        },
        success: function (data) {    
                $('.notes').remove();
                if(data.type == 'withproduct') {
                $('.amount').removeClass('d-none');
                $('#amount').attr('max' , data.getDue);
                $('.items').removeClass('d-none');                    
                $('#item').empty();
                $('#item').append("<option value=''>{{ __('Select Item') }}</option>");
                    $.each(data.items, function (key, value) {
                        var select = '';
                        if (value.id == '{{ $debitNote->bill_product }}') {
                            select = 'selected';
                        }
                        $('#item').append('<option value="' + value.id + '"  ' + select + '>' +
                        value.product_name + '</option>');
                    });
                }
                else {
                    $('.items').addClass('d-none');
                    $('#item').removeAttr('required');
                    $('.amount').removeClass('d-none');
                    $('#amount').attr('max' , data.getDue);
                } 
                $('.amountnote').after( 
                    '<small class="text-danger notes">Note: You can add maximum amount up to ' + data.getDue + '</small>'
                );    
            }            
        });    
    });
</script>
