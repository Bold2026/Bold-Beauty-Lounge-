@php
    $complaintFrom = $complaint->complaint_from ?? null;
    $complaintAgainst = $complaint->complaint_against ?? null;
@endphp
{{ Form::model($complaint, ['route' => ['complaint.update', $complaint->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="text-end">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn',['template_module' => 'complaint','module'=>'Hrm'])
        @endif
    </div>
    <div class="row">
        @if (in_array(Auth::user()->type, Auth::user()->not_emp_type))
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('complaint_from', __('Complaint From'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::select('complaint_from', $employees, $complaintFrom, ['id' => 'complaint_from', 'class' => 'form-control ', 'placeholder' => __('Select Employee'), 'required' => 'required']) }}
                </div>
            </div>
        @endif
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('complaint_against', __('Complaint Against'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('complaint_against', $employees, $complaintAgainst, ['id' => 'complaint_against', 'class' => 'form-control ', 'placeholder' => __('Select Employee'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Complaint Title'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('complaint_date', __('Complaint Date'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('complaint_date', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Select Date'), 'min' => date('Y-m-d')]) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'), 'rows' => '3', 'required' => 'required']) }}
            </div>
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
        const $fromSelect = $('#complaint_from');
        const $againstSelect = $('#complaint_against');

        const originalOptions = $againstSelect.find('option').clone();

        function filterAgainstOptions(selectedFrom) {
            $againstSelect.empty();

            originalOptions.each(function () {
                if ($(this).val() !== selectedFrom || $(this).val() === '') {
                    $againstSelect.append($(this).clone());
                }
            });

            const originalSelected = "{{ $complaintAgainst }}";
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
