{{ Form::open(array('url' => 'labels','class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-12">
                {{ Form::label('name', __('Label Name'),['class'=>'col-form-label']) }} <x-required></x-required>
                {{ Form::text('name', '', array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Label Name'),'maxlength' => '30')) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('pipeline_id', __('Pipeline'),['class'=>'col-form-label']) }} <x-required></x-required>
                {{ Form::select('pipeline_id', $pipelines,null, array('class' => 'form-control select2','required'=>'required')) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('name', __('Color'),['class'=>'col-form-label']) }} <x-required></x-required>
                <div class="row gutters-xs">
                    @foreach($colors as $color)
                        <div class="col-auto">
                            <label class="colorinput">
                                <input name="color" type="radio" value="{{$color}}" class="colorinput-input" id="color" required>
                                <span class="colorinput-color bg-{{$color}}"></span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <p class="text-danger d-none" id="color_validation">{{__('The color filed is required.')}}</p>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="submit" class="btn  btn-primary" id="submit">{{__('Create')}}</button>
    </div>

{{ Form::close() }}

<script>
    $(function(){
        $("#submit").click(function() {
            var color = $("input[name='color']:checked").val();
            var validColors = ['primary', 'secondary', 'danger', 'warning', 'info'];

            if (color && validColors.includes(color)) {
                $('#color_validation').addClass('d-none');
            } else {
                $('#color_validation').removeClass('d-none');
                return false;
            }
        });
    });

</script>
