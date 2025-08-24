@extends('layouts.main')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('page-breadcrumb')
    {{ __('Hrm') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Hrm/src/Resources/assets/css/main.css') }}">
@endpush
@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif
    <div class="row row-gap mb-4">
        <div class="col-xxl-6 col-12">
            <div class="dashboard-card">
                <img src="{{ asset('assets/images/layer.png') }}" class="dashboard-card-layer" alt="layer">
                <div class="card-inner">
                    <div class="card-content">
                        <h2>{{ !empty($ActiveWorkspaceName) ? $ActiveWorkspaceName->name : 'WorkDo' }}</h2>
                        <p>{{ __('Streamline HR with seamless tasks, smooth recruitment, and efficient payroll') }} </p>
                        <div class="btn-wrp d-flex gap-3">
                            {{-- <a href="javascript:" class="btn btn-primary" tabindex="0">
                                <i class="ti ti-share text-white"></i>
                            </a> --}}
                        </div>
                    </div>
                    <div class="card-icon  d-flex align-items-center justify-content-center">
                        <svg width="76" height="76" viewBox="0 0 76 76" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.6" d="M38.1506 16.5773C42.3568 16.4974 45.7018 13.0228 45.6219 8.81671C45.542 4.61057 42.0674 1.26561 37.8611 1.34553C33.6549 1.42545 30.3099 4.89998 30.3898 9.10612C30.4697 13.3123 33.9443 16.6572 38.1506 16.5773Z" fill="#18BF6B"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M52.092 32.1431C52.092 24.3929 45.7509 18.0518 38.0006 18.0518C30.2503 18.0518 23.9092 24.3929 23.9092 32.1431H52.092Z" fill="#18BF6B"/>
                            <path opacity="0.6" d="M57.6183 21.6691C61.8245 21.5892 65.1696 18.1146 65.0897 13.9085C65.0097 9.70237 61.5351 6.35741 57.3289 6.43733C53.1227 6.51724 49.7777 9.99178 49.8576 14.1979C49.9375 18.404 53.4121 21.749 57.6183 21.6691Z" fill="#18BF6B"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M71.5361 36.4467C71.1261 29.057 64.9538 23.1387 57.4664 23.1387C49.979 23.1387 43.8066 29.057 43.3968 36.4467C43.3851 36.6581 43.4535 36.8441 43.5988 36.9978C43.7443 37.1516 43.9264 37.23 44.1381 37.23H70.7949C71.0066 37.23 71.1887 37.1516 71.3342 36.9978C71.4794 36.8441 71.5478 36.6579 71.5361 36.4467Z" fill="#18BF6B"/>
                            <path opacity="0.6" d="M26.1576 14.1962C26.2459 9.99004 22.9077 6.50869 18.7015 6.42036C14.4953 6.33203 11.0139 9.67017 10.9256 13.8763C10.8372 18.0824 14.1754 21.5638 18.3817 21.6521C22.5879 21.7405 26.0693 18.4023 26.1576 14.1962Z" fill="#18BF6B"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M32.6045 36.4467C32.1946 29.057 26.0223 23.1387 18.5348 23.1387C11.0473 23.1387 4.87499 29.057 4.46516 36.4467C4.45343 36.6581 4.52186 36.8441 4.66718 36.9978C4.81265 37.1516 4.99478 37.23 5.20645 37.23H31.8633C32.075 37.23 32.2571 37.1516 32.4026 36.9978C32.5477 36.8441 32.6162 36.6579 32.6045 36.4467Z" fill="#18BF6B"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.21875 45.687V39.8828H72.7816V45.687H66.9575C66.1913 49.5304 64.6791 53.1047 62.5785 56.2528L66.6988 60.3731L58.4902 68.5814L54.3702 64.4612C51.2222 66.5624 47.6476 68.0739 43.8044 68.8404V74.6644H32.1961V68.8401C28.3525 68.0741 24.7786 66.5619 21.6306 64.4611L17.5102 68.5816L9.30187 60.3731L13.4225 56.2528C11.3214 53.1049 9.80997 49.5305 9.04359 45.6872H3.21875V45.687Z" fill="#18BF6B"/>
                            <path opacity="0.6" fill-rule="evenodd" clip-rule="evenodd" d="M21.2686 39.8831V39.8828H54.7323V39.8831C54.7323 49.1239 47.2411 56.6151 38.0003 56.6151C28.7597 56.6151 21.2686 49.1239 21.2686 39.8831Z" fill="#55B986"/>
                            </svg>
                    </div>
                </div>
            </div>
        </div>
        @if (Auth::user()->type == 'company')
            <div class="col-xxl-6 col-12">
                <div class="row d-flex dashboard-wrp">
                    <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                        <div class="dashboard-project-card">
                            <div class="card-inner  d-flex justify-content-between">
                                <div class="card-content">
                                    <div class="theme-avtar bg-white">
                                        <i class="ti ti-user text-danger"></i>
                                    </div>
                                    <a href="{{ route('employee.index') }}">
                                        <h3 class="mt-3 mb-0 text-danger">{{ __('Total Employee') }}</h3>
                                    </a>
                                </div>
                                <h3 class="mb-0">{{ $countEmployee }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                        <div class="dashboard-project-card">
                            <div class="card-inner  d-flex justify-content-between">
                                <div class="card-content">
                                    <div class="theme-avtar bg-white">
                                        <i class="ti ti-calendar"></i>
                                    </div>
                                    <a href="{{ route('leave.index') }}"><h3 class="mt-3 mb-0">{{ __('Total Leaves') }}</h3></a>
                                </div>
                                <h3 class="mb-0">{{ $Totalleaves }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                        <div class="dashboard-project-card">
                            <div class="card-inner  d-flex justify-content-between">
                                <div class="card-content">
                                    <div class="theme-avtar bg-white">
                                        <i class="ti ti-bell"></i>
                                    </div>
                                    <a href="{{ route('event.index') }}"><h3 class="mt-3 mb-0">{{ __('Total Event') }}</h3></a>
                                </div>
                                <h3 class="mb-0">{{ $Totalevent }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-xxl-6 col-12">
                <div class="card mb-0" style="min-height: 220px">
                    <div class="card-header">
                        <h5>{{ __('Mark Attandance ') }}<span>{{ company_date_formate(date('Y-m-d')) }}</span></h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted pb-0-5">
                            {{ __('My Office Time: ' . $officeTime['startTime'] . ' to ' . $officeTime['endTime']) }}
                        </p>
                        <div class="row">
                            <div class="col-md-6 float-right border-right">
                                {{ Form::open(['url' => 'attendance/attendance', 'method' => 'post']) }}

                                @if (empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                    <button type="submit" value="0" name="in" id="clock_in"
                                        class="btn btn-primary">{{ __('CLOCK IN') }}</button>
                                @else
                                    <button type="submit" value="0" name="in" id="clock_in"
                                        class="btn btn-primary disabled" disabled>{{ __('CLOCK IN') }}</button>
                                @endif
                                {{ Form::close() }}
                            </div>
                            <div class="col-md-6 float-left">
                                @if (!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                    {{ Form::model($employeeAttendance, ['route' => ['attendance.update', $employeeAttendance->id], 'method' => 'PUT']) }}
                                    <button type="submit" value="1" name="out" id="clock_out"
                                        class="btn btn-danger">{{ __('CLOCK OUT') }}</button>
                                @else
                                    <button type="submit" value="1" name="out" id="clock_out"
                                        class="btn btn-danger disabled" disabled>{{ __('CLOCK OUT') }}</button>
                                @endif
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="row">
        @if (!in_array(Auth::user()->type, Auth::user()->not_emp_type))
            <div class="col-xxl-12">
                <div class="row">
                    <div class="col-xxl-7">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __("Holiday's ") }}</h5>
                            </div>
                            <div class="card-body">
                                <div id='calendar' class='calendar'></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-5">
                        <div class="card">
                            <div class="card-header card-body table-border-style">
                                <h5>{{ __('Announcement List') }}</h5>
                            </div>
                            <div class="card-body" style="height: 270px; overflow:auto">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Start Date') }}</th>
                                                <th>{{ __('End Date') }}</th>
                                                <th>{{ __('Description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @forelse ($announcements as $announcement)
                                                <tr>
                                                    <td>{{ $announcement->title }}</td>
                                                    <td>{{ company_date_formate($announcement->start_date) }}</td>
                                                    <td>{{ company_date_formate($announcement->end_date) }}</td>
                                                    <td>{{ $announcement->description }}</td>
                                                </tr>
                                            @empty
                                                @include('layouts.nodatafound')
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-xxl-12">
                <div class="row">
                    <div class="col-xxl-5 d-flex flex-column">
                        <div class="card h-100">
                            <div class="card-header table-border-style">
                                <h5>{{ __("Today's Not Clock In") }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive  custom-scrollbar account-info-table">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @forelse ($notClockIns as $notClockIn)
                                                <tr>
                                                    <td>{{ $notClockIn->name }}</td>
                                                    <td><span class="absent-btn">{{ __('Absent') }}</span></td>
                                                </tr>
                                            @empty
                                                @include('layouts.nodatafound')
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card h-100">
                            <div class="card-header card-body table-border-style">
                                <h5>{{ __('Announcement List') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive  custom-scrollbar account-info-table">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Start Date') }}</th>
                                                <th>{{ __('End Date') }}</th>
                                                <th>{{ __('Description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @forelse ($announcements as $announcement)
                                                <tr>
                                                    <td>{{ $announcement->title }}</td>
                                                    <td>{{ company_date_formate($announcement->start_date) }}</td>
                                                    <td>{{ company_date_formate($announcement->end_date) }}</td>
                                                    <td>{{ $announcement->description }}</td>
                                                </tr>
                                            @empty
                                                @include('layouts.nodatafound')
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-7 d-flex flex-column">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5>{{ __("Holiday's & Event's") }}</h5>
                            </div>
                            <div class="card-body d-flex flex-column h-100 justify-center card-635 ">
                                <div id='calendar' class='calendar'></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @stack('work-permit-dashboard')
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
                events: {!! json_encode($events) !!},
            });
            calendar.render();
        })();
    </script>
@endpush
