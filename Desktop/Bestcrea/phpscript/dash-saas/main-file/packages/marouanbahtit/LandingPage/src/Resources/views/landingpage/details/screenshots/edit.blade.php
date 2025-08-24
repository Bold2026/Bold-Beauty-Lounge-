{{Form::model(null, array('route' => array('screenshots_update', $key), 'method' => 'POST','enctype' => "multipart/form-data")) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                {{ Form::text('screenshots_heading',$screenshot['screenshots_heading'], ['class' => 'form-control ', 'placeholder' => __('Enter Heading'),'required'=>'required'])  }}
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('screenshot', __('Screenshot'), ['class' => 'form-label']) }}
                <input type="file" name="screenshots" class="form-control" onchange="document.getElementById('image1').src = window.URL.createObjectURL(this.files[0])" required>
                @if (check_file($screenshot['screenshots']))
                    <div class="logo-content my-4 ">
                        <img id="image1" class="w-20 logo" src="{{get_file($screenshot['screenshots'])}}"
                        style="filter: drop-shadow(2px 3px 7px #011C4B);">
                    </div>
                @else
                    <img id="image1" width="25%" class="mt-3 mb-2">
                @endif
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}
