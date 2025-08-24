@extends('layouts.main')

@section('page-title')
    {{ __('Manage Event') }}
@endsection

@section('page-breadcrumb')
    {{ __('Event') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Hrm/src/Resources/assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('packages/workdo/Hrm/src/Resources/assets/css/custom.css') }}">
@endpush
@section('page-action')
    <div class="d-flex">
        @stack('addButtonHook')
        @permission('event create')
            <a data-url="{{ route('event.create') }}" data-ajax-popup="true" data-size="lg"
                data-title="{{ __('Create Event') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Calendar') }}</h5>
                </div>
                <div class="card-body">
                    <div id='calendar' class='calendar'></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">

            <div class="card">
                <div class="card-body">
                    <h4 class="mb-4">{{ __('Upcoming Events') }}</h4>
                    <ul class="event-cards list-group list-group-flush mt-3 w-100">
                        <li class="list-group-item card mb-3">
                            <div class="row align-items-center justify-content-between">
                                <div class=" align-items-center">
                                    @forelse ($current_month_event as $event)
                                        <div class="card mb-3 border shadow-none">
                                            <div class="px-3">
                                                <div class="row align-items-center">
                                                    <div class="col ml-n2">
                                                        <h5 class="tcard-text small text-primary">
                                                            {{ !empty($event->title) ? $event->title : '' }}
                                                        </h5>
                                                        <p class="card-text small text-dark mt-0">
                                                            {{ __('Start Date : ') }}
                                                            {{ company_date_formate($event->start_date) }}<br>
                                                            {{ __('End Date : ') }}
                                                            {{ company_date_formate($event->end_date) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-auto text-right">
                                                        <div class="mb-4">
                                                            @permission('event edit')
                                                                <div class="action-btn me-2">
                                                                    <a class="mx-3 btn btn-sm  align-items-center bg-info"
                                                                        data-url="{{ route('event.edit', $event->id) }}"
                                                                        data-ajax-popup="true" data-title="{{ $event->title }}"
                                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                                        title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endpermission
                                                            @permission('event delete')
                                                                <div class="action-btn ">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['event.destroy', $event->id],
                                                                        'id' => 'delete-form-' . $event->id,
                                                                    ]) !!}
                                                                    <a href="#!"
                                                                        class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger "
                                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                                        title="{{ __('Delete') }}" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                                                                        <i class="ti ti-trash text-white"></i></a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endpermission
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center">
                                            <h6>{{ __('There is no event in this month') }}</h6>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection


@push('scripts')
    <script src="{{ asset('packages/workdo/Hrm/src/Resources/assets/js/main.min.js') }}"></script>
    <script type="text/javascript">
        (function() {
            var etitle;
            var etype;
            var etypeclass;
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridDay,timeGridWeek,dayGridMonth'
                },
                buttonText: {
                    today: "{{ __('Today') }}",
                    timeGridDay: "{{ __('Day') }}",
                    timeGridWeek: "{{ __('Week') }}",
                    dayGridMonth: "{{ __('Month') }}"
                },
                themeSystem: 'bootstrap',
                slotDuration: '00:10:00',
                navLinks: true,
                droppable: true,
                selectable: true,
                selectMirror: true,
                editable: true,
                dayMaxEvents: true,
                handleWindowResize: true,
                firstDay: {{ company_setting('calendar_start_day') ?? 0 }},
                events: {!! $arrEvents !!},


            });

            calendar.render();
        })();
    </script>
    <script>
        $(document).ready(function() {
            var b_id = $('#branch_id').val();
            getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch_id]', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(bid) {

            $.ajax({
                url: '{{ route('event.getdepartment') }}',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {


                    $('.department_id').empty();
                    var emp_selct = ` <select class="form-control  department_id" name="department_id[]" id="choices-multiple"
                                            placeholder="Select Department" multiple >
                                            </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value="0"> {{ __('All') }} </option>');
                    $.each(data, function(key, value) {
                        $('.department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });


                }
            });
        }

        $(document).on('change', '.department_id', function() {
            var department_id = $(this).val();
            getEmployee(department_id);
        });

        function getEmployee(did) {
            $.ajax({
                url: '{{ route('event.getemployee') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('.employee_id').empty();
                    var emp_selct = ` <select class="form-control  employee_id" name="employee_id[]" id="choices-multiple1"
                                            placeholder="Select Employee" multiple >
                                            </select>`;
                    $('.employee_div').html(emp_selct);

                    $('.employee_id').append('<option value="0"> {{ __('All') }} </option>');
                    $.each(data, function(key, value) {
                        $('.employee_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple1', {
                        removeItemButton: true,
                    });

                }
            });
        }

        $(document).on('change', '.event_color', function(e) {
            $('.event_color').parent().removeClass('event_color_active');
            $(this).parent().addClass('event_color_active');
        });
    </script>
    <script>
        $(document).on('click', '.fc-daygrid-event', function(e) {
            e.preventDefault();
            var event = $(this);
            var title = $(this).find('.fc-event-title').html();
            var size = 'md';
            var url = $(this).attr('href');
            $("#commonModal .modal-title ").html(title);
            $("#commonModal .modal-dialog").addClass('modal-' + size);
            $.ajax({
                url: url,
                success: function(data) {
                    $('#commonModal .body').html(data);
                    $("#commonModal").modal('show');
                    if ($(".flatpickr-input").length) {
                        $(".flatpickr-input").flatpickr({
                            enableTime: false,
                            dateFormat: "Y-m-d",
                        });
                    }

                },
                error: function(data) {
                    data = data.responseJSON;
                    toastrs('Error', data.error, 'error')
                }
            });
        });
    </script>
@endpush
