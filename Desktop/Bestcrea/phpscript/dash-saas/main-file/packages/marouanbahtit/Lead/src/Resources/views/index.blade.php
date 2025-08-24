@extends('layouts.main')

@section('page-title')
    {{ __('Dashboard') }}
@endsection

@section('page-breadcrumb')
    {{ __('CRM') }}
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Lead/src/Resources/assets/css/main.css') }}" />
@endpush

@php
    $setting = getCompanyAllsetting();
@endphp

@push('scripts')
    <script src="{{ asset('packages/workdo/Lead/src/Resources/assets/js/main.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        (function() {
            var etitle;
            var etype;
            var etypeclass;
            var calendar = new FullCalendar.Calendar(document.getElementById('event_calendar'), {
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
                initialDate: '{{ $transdate }}',
                slotDuration: '00:10:00',
                navLinks: true,
                droppable: true,
                selectable: true,
                selectMirror: true,
                editable: true,
                dayMaxEvents: true,
                handleWindowResize: true,
                firstDay: {{ company_setting('calendar_start_day') ?? 0 }},
                events: {!! json_encode($calenderTasks) !!},
            });
            calendar.render();
        })();

        $(document).on('click', '.fc-daygrid-event', function(e) {
            if (!$(this).hasClass('deal')) {
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
                    },
                    error: function(data) {
                        data = data.responseJSON;
                        toastrs('Error', data.error, 'error')
                    }
                });
            }
        });
    </script>

    <script>
        @if (\Auth::user()->type == 'client')
            (function() {
                @if (!empty($dealdata['date']))
                    var options = {
                        chart: {
                            height: 288,
                            type: 'area',
                            toolbar: {
                                show: false,
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            width: 2,
                            curve: 'smooth'
                        },

                        series: [{
                            name: "{{ __('Won Deal by day') }}",
                            data: {!! json_encode($dealdata['deal']) !!}
                        }, ],

                        xaxis: {
                            categories: {!! json_encode($dealdata['date']) !!},

                        },
                        colors: ['#6fd943', '#2633cb'],

                        grid: {
                            strokeDashArray: 4,
                        },
                        legend: {
                            show: false,
                        },
                        yaxis: {
                            tickAmount: 3,
                        }

                    };
                @endif
                var chart = new ApexCharts(document.querySelector("#deal_data"), options);
                chart.render();
            })();
        @endif
    </script>

    <script>
        (function() {
            @if (!empty($chartcall['date']))
                var options = {
                    chart: {
                        height: 288,
                        type: 'area',
                        toolbar: {
                            show: false,
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        width: 2,
                        curve: 'smooth'
                    },

                    series: [{
                        name: "{{ __('Deal calls by day') }}",
                        data: {!! json_encode($chartcall['dealcall']) !!}
                    }, ],

                    xaxis: {
                        categories: {!! json_encode($chartcall['date']) !!},

                    },
                    colors: ['#6fd943', '#2633cb'],

                    grid: {
                        strokeDashArray: 4,
                    },
                    legend: {
                        show: false,
                    },
                    yaxis: {
                        tickAmount: 3,
                    }

                };
            @endif
            var chart = new ApexCharts(document.querySelector("#callchart"), options);
            chart.render();
        })();
    </script>


    <script>
        var WorkedHoursChart = (function() {
            var $chart = $('#deal_stage');

            function init($this) {
                var options = {
                    chart: {
                        height: 308,
                        type: 'bar',
                        zoom: {
                            enabled: false
                        },
                        toolbar: {
                            show: false
                        },
                        shadow: {
                            enabled: false,
                        },

                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '30%',
                            borderRadius: 10,
                            dataLabels: {
                                position: 'top',
                            },
                        }
                    },
                    stroke: {
                        show: true,
                        width: 1,
                        colors: ['#fff']
                    },
                    series: [{
                        name: 'Platform',
                        data: {!! json_encode($dealStageData) !!},
                    }],
                    xaxis: {
                        labels: {
                            // format: 'MMM',
                            style: {
                                colors: '#293240',
                                fontSize: '12px',
                                fontFamily: "sans-serif",
                                cssClass: 'apexcharts-xaxis-label',
                            },
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: true,
                            borderType: 'solid',
                            color: '#f2f2f2',
                            height: 6,
                            offsetX: 0,
                            offsetY: 0
                        },
                        title: {
                            text: 'Platform'
                        },
                        categories: {!! json_encode($dealStageName) !!},
                    },
                    yaxis: {
                        labels: {
                            style: {
                                color: '#f2f2f2',
                                fontSize: '12px',
                                fontFamily: "Open Sans",
                            },
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: true,
                            borderType: 'solid',
                            color: '#f2f2f2',
                            height: 6,
                            offsetX: 0,
                            offsetY: 0
                        }
                    },
                    fill: {
                        type: 'solid',
                        opacity: 1

                    },
                    markers: {
                        size: 4,
                        opacity: 0.7,
                        strokeColor: "#000",
                        strokeWidth: 3,
                        hover: {
                            size: 7,
                        }
                    },
                    grid: {
                        borderColor: '#f2f2f2',
                        strokeDashArray: 5,
                    },
                    dataLabels: {
                        enabled: false
                    }
                }
                // Get data from data attributes
                var dataset = $this.data().dataset,
                    labels = $this.data().labels,
                    color = $this.data().color,
                    height = $this.data().height,
                    type = $this.data().type;

                // Init chart
                var chart = new ApexCharts($this[0], options);
                // Draw chart
                setTimeout(function() {
                    chart.render();
                }, 300);
            }

            // Events
            if ($chart.length) {
                $chart.each(function() {
                    init($(this));
                });
            }
        })();
    </script>

    <script>
        var timezone = '{{ !empty($setting['defult_timezone']) ? $setting['defult_timezone'] : 'Asia/Kolkata' }}';

        let today = new Date(new Date().toLocaleString("en-US", {
            timeZone: timezone
        }));
        var curHr = today.getHours()
        var target = document.getElementById("greetings");

        if (curHr < 12) {
            target.innerHTML = "Good Morning,";
        } else if (curHr < 17) {
            target.innerHTML = "Good Afternoon,";
        } else {
            target.innerHTML = "Good Evening,";
        }
    </script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Lead/src/Resources/assets/css/custom.css') }}">
@endpush
@section('content')
    <div class="row row-gap mb-4">
        @if (\Auth::user()->type == 'company')
            <div class="col-xxl-6 col-12">
            @else
                <div class="col-xxl-7 col-12">
        @endif
        <div class="row row-gap">
            <div class="col-md-12 col-12">
                <div class="dashboard-card">
                    <img src="{{ asset('assets/images/layer.png') }}" class="dashboard-card-layer" alt="layer">
                    <div class="card-inner">
                        <div class="card-content">
                            <h2>{{ $workspace->name }}</h2>
                            <p>{{ __('Manage customer relationships to boost satisfaction and growth.') }}</p>
                        </div>
                        <div class="card-icon  d-flex align-items-center justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="71" height="71" viewBox="0 0 71 71" fill="none">
                                <path d="M27.3215 28.9901C35.3066 28.9901 41.7798 22.5405 41.7798 14.5844C41.7798 6.62836 35.3066 0.178711 27.3215 0.178711C19.3365 0.178711 12.8633 6.62836 12.8633 14.5844C12.8633 22.5405 19.3365 28.9901 27.3215 28.9901Z" fill="#18BF6B"/>
                                <path opacity="0.6" d="M46.4644 39.5139C43.4751 37.4647 39.5568 36.1934 34.5181 36.1934H20.0598C5.38466 36.1934 0.179688 46.8897 0.179688 56.0733C0.179688 64.2845 4.55331 68.6062 12.8307 68.6062H35.9278C36.5423 68.6062 37.0119 68.1378 37.0119 67.5256C37.0119 67.2123 36.777 66.8666 36.6505 66.7334C35.6384 65.4729 34.8073 64.2485 34.1929 63.2041C32.2049 59.8547 32.2049 55.7491 34.1929 52.3637C36.2351 48.9856 40.1351 43.9615 46.2257 41.4333C47.0462 41.0911 47.1981 40.0181 46.4644 39.5139Z" fill="#18BF6B"/>
                                <path d="M69.9416 55.1372C67.7006 51.4278 62.7123 45.1973 54.3988 45.1973C46.0853 45.1973 41.097 51.4278 38.856 55.1372C37.88 56.7939 37.88 58.8106 38.856 60.4673C41.097 64.1767 46.0853 70.4072 54.3988 70.4072C62.7123 70.4072 67.7006 64.1767 69.9416 60.4673C70.9175 58.8106 70.9175 56.7939 69.9416 55.1372ZM54.3988 62.304C51.9047 62.304 49.8806 60.2872 49.8806 57.8023C49.8806 55.3173 51.8685 53.3005 54.3626 53.3005H54.3988C56.8928 53.3005 58.917 55.3173 58.917 57.8023C58.917 60.2872 56.8928 62.304 54.3988 62.304Z" fill="#18BF6B"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        @if (\Auth::user()->type == 'company')
            <div class="col-xxl-6 col-12">
            @else
                <div class="col-xxl-5 col-12">
        @endif
        <div class="row d-flex dashboard-wrp">
            @if (\Auth::user()->type == 'company')
                <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                @else
                    <div class="col-md-6 col-sm-6 col-12 d-flex flex-wrap">
            @endif
            <div class="dashboard-project-card">
                <div class="card-inner  d-flex justify-content-between">
                    <div class="card-content">
                        <div class="theme-avtar bg-white">
                            <i class="ti ti-rocket text-danger"></i>
                        </div>
                        <a href="{{ route('deals.index') }}">
                            <h3 class="mt-3 mb-0 text-danger">{{ __('Total Deal') }}</h3>
                        </a>
                    </div>
                    <h3 class="mb-0">{{ $arrCount['deal'] ?? '0' }}</h3>
                </div>
            </div>
        </div>
        @if (isset($arrCount['task']))
            <div class="col-md-6 col-sm-6 col-12 d-flex flex-wrap">
                <div class="dashboard-project-card">
                    <div class="card-inner  d-flex justify-content-between">
                        <div class="card-content">
                            <div class="theme-avtar bg-white">
                                <i class="ti ti-subtask"></i>
                            </div>
                            <h3 class="mt-3 mb-0">{{ __('Total Task') }}</h3>
                        </div>
                        <h3 class="mb-0">{{ $arrCount['task'] ?? '0' }}</h3>
                    </div>
                </div>
            </div>
        @endif
        @if (isset($arrCount['client']))
            <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                <div class="dashboard-project-card">
                    <div class="card-inner  d-flex justify-content-between">
                        <div class="card-content">
                            <div class="theme-avtar bg-white">
                                <i class="ti ti-user"></i>
                            </div>
                            <h3 class="mt-3 mb-0">{{ __('Total Client') }}</h3>
                        </div>
                        <h3 class="mb-0">{{ $arrCount['client'] ?? '0' }}</h3>
                    </div>
                </div>
            </div>
        @endif
        @if (isset($arrCount['user']))
            <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                <div class="dashboard-project-card">
                    <div class="card-inner  d-flex justify-content-between">
                        <div class="card-content">
                            <div class="theme-avtar bg-white">
                                <i class="ti ti-users"></i>
                            </div>
                            <h3 class="mt-3 mb-0">{{ __('Total User') }}</h3>
                        </div>
                        <h3 class="mb-0">{{ $arrCount['user'] ?? '0' }}</h3>
                    </div>
                </div>
            </div>
        @endif
        </div>
        </div>
    </div>

    <div class="row">

        @php
            $class = '';
            if (count($arrCount) < 3) {
                $class = 'col-lg-4 col-md-4';
            } else {
                $class = 'col-lg-3 col-md-3';
            }
        @endphp

        <div class="col-xxl-7 d-flex flex-column">
            <div class="mb-4 h-100">
                <div class="card h-100 mb-0">
                    <div class="card-header">
                        <h5>{{ __('Calendar') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="w-100" id='event_calendar'></div>
                    </div>
                </div>
            </div>
            <div class="mb-4 h-100">
                <div class="card h-100 mb-0">
                    <div class="card-header">
                        <h5>{{ __('Recently created deals') }}</h5>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive custom-scrollbar account-info-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{__('Deal Name')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Created At')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($deals as $deal)
                                        <tr>
                                            <td>{{$deal->name}}</td>
                                            <td>{{$deal->stage->name}}</td>
                                            <td>{{$deal->created_at}}</td>
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

        @if (\Auth::user()->type == 'company')
            <div class="col-xxl-5 d-flex flex-column">
        @else
            <div class="col-xxl-5">
        @endif

            @if (!empty($dealdata))
                @if (\Auth::user()->type == 'client')
                <div class="mb-4">
                    <div class="card h-100 mb-0">
                        <div class="card-header ">
                            @if (\Auth::user()->type != 'super admin')
                                <h5>{{ __('Won Deals by day') }}</h5>
                            @endif
                        </div>
                        <div class="card-body p-2">
                            <div id="deal_data" data-color="primary" data-height="230">
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endif


            @if (!empty($chartcall))
                <div class="mb-4 ">
                    <div class="card h-100 mb-0">
                        <div class="card-header ">
                            @if(\Auth::user()->type != 'super admin')
                            <h5>{{__('Deal calls by day')}}</h5>
                            @endif
                        </div>
                        <div class="card-body p-2">
                            <div id="callchart" data-color="primary"  data-height="230"></div>
                        </div>
                    </div>
                </div>
            @endif
            @if (!empty($dealStageData))
                    <div class="mb-4">
                        @if(\Auth::user()->type == 'company')
                            <div class="card h-100 mb-0">
                                <div class="card-header ">
                                    <h5>{{__('Deals by stage')}}</h5>
                                </div>
                                <div class="card-body p-2">
                                    <div id="deal_stage" data-color="primary"  data-height="230"></div>
                                </div>
                            </div>
                        @endif
                </div>

            @endif

            @if (\Auth::user()->type == 'company')
                <div class="mb-4 h-100">
            @else
                <div class="mb-4">
            @endif
                <div class="card h-100 mb-0">
                    <div class="card-header">
                        <h5>{{ __('Recently modified deals') }}</h5>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive custom-scrollbar account-info-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{__('Deal Name')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Updated At')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($modifiedDeals as $deal)
                                        <tr>
                                            <td>{{$deal->name}}</td>
                                            <td>{{$deal->stage->name}}</td>
                                            <td>{{$deal->updated_at}}</td>
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

@endsection
