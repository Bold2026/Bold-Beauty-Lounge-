@extends('layouts.main')
@section('page-title')
    {{ __('Manage Holiday') }}
@endsection
@section('page-breadcrumb')
    {{ __('Holiday') }}
@endsection
@section('page-action')
    <div>
        @permission('holiday import')
            <a href="#" class="btn btn-sm btn-primary me-1" data-ajax-popup="true" data-title="{{ __('Holiday Import') }}"
                data-url="{{ route('holiday.file.import') }}" data-toggle="tooltip" title="{{ __('Import') }}"><i
                    class="ti ti-file-import"></i>
            </a>
        @endpermission
        <a href="{{ route('holiday.index') }}" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('List View') }}">
            <i class="ti ti-list-check"></i>
        </a>
        @permission('holiday create')
            <a  class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md"
                data-title="{{ __('Create Holiday') }}" data-url="{{ route('holiday.create') }}" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Hrm/src/Resources/assets/css/main.css') }}">
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['holiday.calender'], 'method' => 'get', 'id' => 'holiday_filter']) }}
                    <div class="d-flex row align-items-center justify-content-end">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'form-control ', 'placeholder' => 'Select Date']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'form-control ', 'placeholder' => 'Select Date']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end mt-4">
                            <a  class="btn btn-sm btn-primary me-1"
                                onclick="document.getElementById('holiday_filter').submit(); return false;"
                                data-bs-toggle="tooltip" title="{{ __('Apply') }}" data-bs-original-title="apply">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{ route('holiday.calender') }}" class="btn btn-sm btn-danger"
                                data-bs-toggle="tooltip" title="{{ __('Reset') }}" data-bs-original-title="Reset">
                                <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

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
                    <h4 class="mb-4">{{ __('Holiday List') }}</h4>
                    <ul class="event-cards list-group list-group-flush mt-3 w-100">
                        @forelse ($current_month_event as $event)
                            <li class="list-group-item card mb-3">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            <div class="theme-avtar badge bg-primary">
                                                <i class="ti ti-calendar-event"></i>
                                            </div>
                                            <div class="ms-3">
                                                <h6 class="card-text small text-primary">{{ $event->occasion }}</h6>
                                                <div class="card-text small text-dark">{{ __('Start Date :') }}
                                                    {{ company_date_formate($event->start_date) }}
                                                </div>
                                                <div class="card-text small text-dark">{{ __('End Date :') }}
                                                    {{ company_date_formate($event->end_date) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto text-right">
                                        <div class="">
                                            @permission('event edit')
                                                <div class="action-btn  me-2">
                                                    <a class="mx-3 btn btn-sm bg-info align-items-center" data-url="{{ URL::to('holiday/' . $event->id . '/edit') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ $event->occasion }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endpermission
                                            @permission('event delete')
                                                <div class="action-btn">
                                                    {{ Form::open(['route' => ['holiday.destroy', $event->id], 'class' => 'm-0']) }}
                                                    @method('DELETE')
                                                    <a class="mx-3 btn btn-sm bg-danger align-items-center bs-pass-para show_confirm" data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                        aria-label="Delete" data-confirm="{{ __('Are You Sure?') }}"
                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="delete-form-{{ $event->id }}"><i class="ti ti-trash text-white text-white"></i></a>
                                                    {{ Form::close() }}
                                                </div>
                                            @endpermission
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <div class="text-center">
                                <h6>{{ __('There is no Holiday in this month') }}</h6>
                            </div>
                        @endforelse

                    </ul>
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
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
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
                events: {!! $arrHolidays !!},
            });
            calendar.render();
        })();
    </script>
    <script>
        $(document).on('click', '.holiday-edit', function(e) {
            e.preventDefault();
            var event = $(this);
            var title = $(this).find('.fc-event-title-container .fc-event-title').html();
            var size = 'md';
            var url = $(this).attr('href');
            $("#commonModal .modal-title").html(title);
            $("#commonModal .modal-dialog").addClass('modal-' + size);
            $.ajax({
                url: url,
                success: function(data) {
                    $('#commonModal .body').html(data);
                    $("#commonModal").modal('show');
                    common_bind();
                    select2();
                },
                error: function(data) {
                    data = data.responseJSON;
                    toastrs('Error', data.error, 'error')
                }
            });
        });
    </script>
@endpush
