
{{ Form::open(array('route' => ['deals.emails.store',$deal->id],'class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="text-end">
            @if (module_is_active('AIAssistant'))
                @include('aiassistant::ai.generate_ai_btn',['template_module' => 'deal_email','module'=>'Lead'])
            @endif
        </div>
        <div class="row">
            <div class="col-6 form-group">
                {{ Form::label('to', __('Mail To'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::email('to', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Email'))) }}
            </div>
            <div class="col-6 form-group">
                {{ Form::label('subject', __('Subject'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::text('subject', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Subject'))) }}
            </div>
            <div class="col-12 form-group">
                {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
                {{ Form::textarea('description',null, array('class' => 'form-control summernote' , 'id' => 'deal-email-summernote')) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
    </div>
{{ Form::close() }}

<script>
    if ($(".summernote").length > 0) {
        $('.summernote').summernote({
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough']],
                ['list', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'unlink']],
            ],
            height: 200,
        });
    }
</script>

