{{ Form::open(['url' => 'warning', 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="text-end">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn',['template_module' => 'warning','module'=>'Hrm'])
        @endif
    </div>
    <div class="row">
        @if (in_array(Auth::user()->type, Auth::user()->not_emp_type))
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('warning_by', __('Warning By'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::select('warning_by', $employees, null, ['id' => 'warning_by', 'class' => 'form-control ', 'placeholder' => __('Select Employee'), 'required' => 'required']) }}
                </div>
            </div>
        @endif
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('warning_to', __('Warning To'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('warning_to', $employees, null, ['id' => 'warning_to', 'class' => 'form-control ', 'placeholder' => __('Select Employee'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => __('Enter Subject'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('warning_date', __('Warning Date'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('warning_date', date('Y-m-d'), ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Select Date', 'min' => date('Y-m-d')]) }}
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'), 'rows' => '3', 'required' => 'required']) }}
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
    $(document).ready(function() {
        const $bySelect = $('#warning_by');
        const $toSelect = $('#warning_to');

        const allAgainstOptions = $toSelect.find('option').clone();

        $bySelect.on('change', function() {
            const selectedFrom = $(this).val();

            $toSelect.empty();

            allAgainstOptions.each(function() {
                if ($(this).val() !== selectedFrom) {
                    $toSelect.append($(this).clone());
                }
            });

            $toSelect.val('');
        });
    });
</script>