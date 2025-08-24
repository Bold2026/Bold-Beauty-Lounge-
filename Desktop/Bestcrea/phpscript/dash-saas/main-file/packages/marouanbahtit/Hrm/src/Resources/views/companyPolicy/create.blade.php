@php
    $company_settings = getCompanyAllSetting();
@endphp

{{ Form::open(['url' => 'company-policy', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="text-end">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn', [
                'template_module' => 'company policy',
                'module' => 'Hrm',
            ])
        @endif
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('branch', !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('branch', $branches, null, ['class' => 'form-control    ', 'placeholder' => __('Select ' . (!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __(' Branch'))), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Company Policy Title')]) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('document', __('Attachment'), ['class' => 'form-label']) }}
                <div class="choose-file form-group">
                    <label for="attachment">
                        <input type="file" class="form-control file" name="attachment" id="attachment"
                            onchange="previewImage(this)" data-filename="attachment">
                    </label>
                    <hr>
                    <img id="blah" width="100" src="" style="display: none;" />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}

<script>
    function previewImage(input) {
        const imgPreview = document.getElementById('blah');
        if (input.files && input.files[0]) {
            imgPreview.src = URL.createObjectURL(input.files[0]);
            imgPreview.style.display = 'block';
        } else {
            imgPreview.style.display = 'none';
        }
    }
</script>
