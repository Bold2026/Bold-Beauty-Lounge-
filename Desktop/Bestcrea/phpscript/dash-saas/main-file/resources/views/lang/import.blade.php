{{ Form::open(['route' => 'import.lang.json', 'method' => 'POST','enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('file', __('Upload Zip File'), ['class' => 'form-label']) }}
                {{ Form::file('file',  ['class' => 'form-control', 'required' => true]) }}
                <div class=" text-xs mt-1">
                    <span class="text-danger text-xs">{{ __('Import Zip file which you have downloaded from old version') }}</span><br>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Import') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
