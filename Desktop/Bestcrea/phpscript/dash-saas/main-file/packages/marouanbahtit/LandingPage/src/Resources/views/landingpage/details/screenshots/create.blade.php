{{ Form::open(array('route' => 'screenshots_store', 'method'=>'post', 'enctype' => "multipart/form-data")) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('Heading', __('Heading'), ['class' => 'form-label']) }}
                    {{ Form::text('screenshots_heading',null, ['class' => 'form-control ', 'placeholder' => __('Enter Heading'),'required'=>'required']) }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('screenshots', __('Screenshots'), ['class' => 'form-label']) }}
                    <input type="file" name="screenshots" class="form-control" onchange="document.getElementById('image1').src = window.URL.createObjectURL(this.files[0])" required>
                    <img id="image1" width="25%" class="mt-3">
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
    </div>
{{ Form::close() }}

