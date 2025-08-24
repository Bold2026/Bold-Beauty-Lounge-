{{ Form::open(array('route' => array('custom-debits.note'),'mothod'=>'post', 'class'=>'needs-validation', 'novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="d-flex align-items-center gap-3">
                <label class="form-check-label">
                    <input type="radio" name="type" value="bill" class="form-check-input" checked> {{ __('Bill') }}
                </label>
                <label class="form-check-label">
                    <input type="radio" name="type" value="purchase" class="form-check-input"> {{ __('Purchase') }}
                </label>
            </div>
        </div>
        <div class="form-group col-md-12 bill_section">
            {{ Form::label('bill', __('Bill'),['class'=>'form-label']) }}<x-required></x-required>
                <select class="form-control select" required="required" id="bill" name="bill">
                    <option value>{{__('Select Bill')}}</option>
                    @foreach($bills as $key=>$bill)
                        <option value="{{$key}}">{{ Workdo\Account\Entities\Bill::billNumberFormat($bill)}}</option>
                    @endforeach
                </select>
        </div>
        <div class="form-group col-md-12 d-none purchase_section">
            {{ Form::label('bill', __('Purchase'),['class'=>'form-label']) }}<x-required></x-required>
                <select class="form-control select" id="purchase" name="purchase">
                    <option value>{{__('Select Purchase')}}</option>
                    @foreach($purchases as $key=>$purchase)
                        <option value="{{$key}}">{{ App\Models\Purchase::purchaseNumberFormat($purchase)}}</option>
                    @endforeach
                </select>
        </div>
        <div class="form-group col-md-12 items d-none">
            {{ Form::label('item', __('Item'), ['class' => 'form-label']) }}<x-required></x-required>
            <select class="form-control select" required="required" id="item" name="bill_product">
            </select>
        </div>
        <div class="form-group col-md-12 amount d-none">
            {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}<x-required></x-required>
            <div class="form-icon-user amountnote">
                {{ Form::number('amount', 0, array('class' => 'form-control','required'=>'required', 'min' => 0.01 ,'step'=>'0.01','placeholder'=>__('Enter Amount'))) }}
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
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).on('click' , 'select[name="bill"] , select[name="purchase"]' , function(){
        var bill_id = $(this).val();
        var type = $('input[name="type"]:checked').val();
        $.ajax({
            url: "{{route('debit-bill.items')}}",
            method:'POST',
            data: {
                "bill_id": bill_id, 
                "type"   : type,
                "_token" : "{{ csrf_token() }}"
            },
            success: function (data) {
                $('.notes').remove();
                if(data.type == 'withproduct') {
                    $('.amount').removeClass('d-none');
                    $('.items').removeClass('d-none');
                    $('#amount').val(0).attr('max' , data.getDue);
                    $('#item').empty();
                    $('#item').append("<option value=''>{{ __('Select Item') }}</option>");
                    $.each(data.items, function (key, value) {
                        $('#item').append('<option value="' + value.id + '">' + value.product_name + '</option>');
                    });
                }
                else {
                    $('.items').addClass('d-none');
                    $('#item').removeAttr('required');
                    $('.amount').removeClass('d-none');
                    $('#amount').val(data.amount).attr('max' , data.getDue);
                }   
                $('.amountnote').after( 
                    '<small class="text-danger notes">Note: You can add maximum amount up to ' + data.getDue + '</small>'
                );    
            }
        });             
    });

    $(document).on('click' , 'input[name="type"]' , function(){
        var value = $(this).val();
        if(value == 'bill')
        {
            $('.purchase_section').addClass('d-none');
            $('#bill').attr('required', true);
            $('#purchase').removeAttr('required');
            $('.bill_section').removeClass('d-none');
        }  
        else
        {
            $('.bill_section').addClass('d-none');
            $('.purchase_section').removeClass('d-none');
            $('#purchase').attr('required', true);
            $('#bill').removeAttr('required');
        }          
    });
</script>