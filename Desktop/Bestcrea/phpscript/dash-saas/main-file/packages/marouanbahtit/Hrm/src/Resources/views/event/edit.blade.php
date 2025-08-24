<style>
    .event_color_active {
    box-shadow: inset 0 0 0 2px #000;
}
</style>
@if (Auth::user()->type == 'company' || !in_array(\Auth::user()->type, \Auth::user()->not_emp_type))
    {{ Form::model($event, ['route' => ['event.update', $event->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
    <div class="modal-body">
        <div class="text-end">
            @if (module_is_active('AIAssistant'))
                @include('aiassistant::ai.generate_ai_btn',['template_module' => 'event','module'=>'Hrm'])
            @endif
        </div>
        <div class="row">
            <div class="form-group">
                {{ Form::label('title', __('Event Title'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Event Title'),'required' => 'required']) }}
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('start_date', __('Event start Date'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::date('start_date', null, ['class' => 'form-control ', 'autocomplete' => 'off','required' => 'required', 'min' => date('Y-m-d')]) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('end_date', __('Event End Date'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::date('end_date', null, ['class' => 'form-control ', 'autocomplete' => 'off','required' => 'required', 'min' => date('Y-m-d')]) }}
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('color', __('Event Select Color'), ['class' => 'form-label d-block mb-3']) }}
                <div class=" btn-group-toggle btn-group-colors event-tag" data-toggle="buttons">
                    <label
                        class="btn bg-info p-3 {{ $event->color == 'event-info'
                            ? 'custom_color_radio_button event_color_active
                                                                                                                        '
                            : '' }} "><input
                            type="radio" name="color" class="d-none event_color" value="event-info"
                            {{ $event->color == 'event-info' ? 'checked' : '' }}></label>

                    <label
                        class="btn bg-warning p-3 {{ $event->color == 'event-warning' ? 'custom_color_radio_button event_color_active' : '' }}"><input
                            type="radio" class="d-none event_color" name="color" value="event-warning"
                            {{ $event->color == 'event-warning' ? 'checked' : '' }}></label>

                    <label
                        class="btn bg-danger p-3 {{ $event->color == 'event-danger' ? 'custom_color_radio_button event_color_active' : '' }}"><input
                            type="radio" name="color" class="d-none event_color" value="event-danger"
                            {{ $event->color == 'event-danger' ? 'checked' : '' }}></label>


                    <label
                        class="btn bg-success p-3 {{ $event->color == 'event-success' ? 'custom_color_radio_button event_color_active' : '' }}"><input
                            type="radio" class="d-none event_color" name="color" value="event-success"
                            {{ $event->color == 'event-success' ? 'checked' : '' }}></label>

                    <label class="btn p-3 {{ $event->color == 'event-primary' ? 'custom_color_radio_button event_color_active' : '' }}"
                        style="background-color: #51459d !important"><input type="radio" class="d-none event_color"
                            name="color" value="event-primary"
                            {{ $event->color == 'event-primary' ? 'checked' : '' }}></label>
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('description', __('Event Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter Event Description')]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

    </div>
    {{ Form::close() }}
@endif

@if (Auth::user()->type == 'hr')
    {{ Form::model($event, ['route' => ['event.update', $event->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
    <div class="modal-body">
        <div class="form-label">
            <div class="text-end">
                @if (module_is_active('AIAssistant'))
                    @include('aiassistant::ai.generate_ai_btn',['template_module' => 'event','module'=>'Hrm'])
                @endif
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {{ Form::label('title', __('Event Title'), ['class' => 'form-label']) }}<x-required></x-required>
                        {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Event Title')]) }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('start_date', __('Event start Date'), ['class' => 'form-label']) }}<x-required></x-required>
                        {{ Form::date('start_date', null, ['class' => 'form-control ', 'autocomplete' => 'off', 'min' => date('Y-m-d')]) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('end_date', __('Event End Date'), ['class' => 'form-label']) }}<x-required></x-required>
                        {{ Form::date('end_date', null, ['class' => 'form-control ', 'autocomplete' => 'off', 'min' => date('Y-m-d')]) }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        {{ Form::label('color', __('Event Select Color'), ['class' => 'form-label d-block mb-3']) }}

                        <div class=" btn-group-toggle btn-group-colors event-tag" data-toggle="buttons">
                            <label
                                class="btn bg-info p-3 {{ $event->color == 'event-info'
                                    ? 'custom_color_radio_button' : '' }} "><input
                                    type="radio" name="color" class="d-none event_color" value="event-info"
                                    {{ $event->color == 'event-info' ? 'checked' : '' }}></label>

                            <label
                                class="btn bg-warning p-3 {{ $event->color == 'event-warning' ? 'custom_color_radio_button' : '' }}"><input
                                    type="radio" class="d-none event_color" name="color" value="event-warning"
                                    {{ $event->color == 'event-warning' ? 'checked' : '' }}></label>

                            <label
                                class="btn bg-danger p-3 {{ $event->color == 'event-danger' ? 'custom_color_radio_button' : '' }}"><input
                                    type="radio" name="color" class="d-none event_color" value="event-danger"
                                    {{ $event->color == 'event-danger' ? 'checked' : '' }}></label>


                            <label
                                class="btn bg-success p-3 {{ $event->color == 'event-success' ? 'custom_color_radio_button' : '' }}"><input
                                    type="radio" class="d-none event_color" name="color" value="event-success"
                                    {{ $event->color == 'event-success' ? 'checked' : '' }}></label>

                            <label class="btn p-3 {{ $event->color == 'event-primary' ? 'custom_color_radio_button' : '' }}"
                                style="background-color: #51459d !important"><input type="radio" class="d-none event_color"
                                    name="color" value="event-primary"
                                    {{ $event->color == 'event-primary' ? 'checked' : '' }}></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {{ Form::label('description', __('Event Description'), ['class' => 'form-label']) }}
                        {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter Event Description')]) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

    </div>
    {{ Form::close() }}
@endif
