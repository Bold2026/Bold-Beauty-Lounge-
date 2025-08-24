@php
    $warningBy = $warning->warning_by ?? null;
    $warningTo = $warning->warning_to ?? null;
@endphp
{{ Form::model($warning, ['route' => ['warning.update', $warning->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
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
                    {{ Form::select('warning_by', $employees, $warningBy, ['id' => 'warining_by', 'class' => 'form-control ', 'placeholder' => __('Select Employee'), 'required' => 'required']) }}
                </div>
            </div>
        @endif
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('warning_to', __('Warning To'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('warning_to', $employees, $warningTo, ['id' => 'warining_to', 'class' => 'form-control ', 'placeholder' => __('Select Employee'), 'required' => 'required']) }}
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
                {{ Form::date('warning_date', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Select Date', 'min' => date('Y-m-d')]) }}
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'), 'rows' => '3', 'required' => 'required']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Update'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}

<script>
    $(document).ready(function () {
        const $fromSelect = $('#warining_by');
        const $againstSelect = $('#warining_to');

        const originalOptions = $againstSelect.find('option').clone();

        function filterAgainstOptions(selectedFrom) {
            $againstSelect.empty();

            originalOptions.each(function () {
                if ($(this).val() !== selectedFrom || $(this).val() === '') {
                    $againstSelect.append($(this).clone());
                }
            });

            const originalSelected = "{{ $warningTo }}";
            if (originalSelected && originalSelected !== selectedFrom) {
                $againstSelect.val(originalSelected);
            }
        }

        filterAgainstOptions($fromSelect.val());

        $fromSelect.on('change', function () {
            filterAgainstOptions($(this).val());
        });
    });
</script>