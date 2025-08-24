@extends('layouts.main')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        (function() {
            var chartBarOptions = {
                series: [{
                    name: '{{ __('Order') }}',
                    data: {!! json_encode($chartData['data']) !!},

                }, ],

                chart: {
                    height: 400,
                    type: 'area',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode($chartData['label']) !!},
                    title: {
                        text: '{{ __('Months') }}'
                    }
                },
                colors: ['#6fd944', '#6fd944'],

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                yaxis: {
                    title: {
                        text: '{{ __('Order') }}'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#chart-sales"), chartBarOptions);
            arChart.render();
        })();
    </script>
@endpush
@section('content')
<div class="row row-gap mb-4">
    <div class="col-xxl-6 col-12 d-xxl-flex">
        <div class="d-flex flex-wrap w-100 row-gap">
            <div class="col-md-9 col-12">
                <div class="dashboard-card">
                    <img src="{{ asset('assets/images/layer.png')}}" class="dashboard-card-layer" alt="layer">
                    <div class="card-inner">
                        <div class="card-content">
                            <h2>{{ auth()->user()->name }}</h2>
                            <p>{{ __('The keys to the kingdom are in your hands – welcome to your Super Admin Dashboard!') }}</p>
                            <div class="btn-wrp d-flex gap-3">
                                <a href="javascript:" class="btn btn-primary d-flex align-items-center gap-1 cp_link" tabindex="0" data-link="{{ url('/') }}" data-bs-toggle="tooltip" data-bs-placement="top"
                                title="" data-bs-original-title="Click to copy site link">
                                    <i class="ti ti-link text-white"></i>
                                <span> {{ __('Landing Page') }}</span></a>
                                <a href="javascript:" class="btn btn-primary socialShareButton share-btn" id="socialShareButton" tabindex="0">
                                    <i class="ti ti-share text-white"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-icon  d-flex align-items-center justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="70" height="72" viewBox="0 0 70 72" fill="none">
                                <path d="M18.1943 51.8056C17.9018 51.8056 17.6231 51.6336 17.5019 51.3473C17.3387 50.9647 17.5173 50.5224 17.8996 50.3597L25.5184 47.1193C25.9014 46.958 26.344 47.1343 26.5057 47.5173C26.6689 47.8999 26.4903 48.3422 26.108 48.5049L18.4892 51.7453C18.3927 51.7861 18.2927 51.8056 18.1943 51.8056Z" fill="url(#paint0_linear_392_35)"/>
                                <path d="M51.7981 51.8056C51.6996 51.8056 51.5997 51.7861 51.5033 51.7454L43.8845 48.505C43.5022 48.3421 43.3235 47.9 43.4867 47.5174C43.6492 47.1344 44.0925 46.9579 44.474 47.1193L52.0928 50.3597C52.4751 50.5226 52.6538 50.9647 52.4906 51.3473C52.3693 51.6336 52.0907 51.8056 51.7981 51.8056Z" fill="url(#paint1_linear_392_35)"/>
                                <path d="M34.9998 32.1779C34.5837 32.1779 34.2471 31.8408 34.2471 31.4251V24.781C34.2471 24.3654 34.5838 24.0283 34.9998 24.0283C35.4157 24.0283 35.7525 24.3654 35.7525 24.781V31.4251C35.7525 31.8408 35.4157 32.1779 34.9998 32.1779Z" fill="url(#paint2_linear_392_35)"/>
                                <path d="M47.5079 42.9468V44.9229C47.5079 45.1843 47.3196 45.4111 47.062 45.4572L44.7784 45.8762C44.5246 47.1872 44.0056 48.4058 43.2867 49.4785L44.6054 51.3892C44.7553 51.6045 44.7284 51.8966 44.5438 52.0812L43.1482 53.4768C42.9599 53.6651 42.6677 53.692 42.4523 53.5421L40.5377 52.2234C39.4689 52.9423 38.2502 53.4576 36.9393 53.7112L36.5203 55.9987C36.4742 56.2563 36.2512 56.4448 35.986 56.4448H34.0137C33.7485 56.4448 33.5255 56.2563 33.4794 55.9987L33.0604 53.7112C31.7494 53.4576 30.5346 52.9423 29.462 52.2234L27.5474 53.5421C27.3321 53.6881 27.04 53.6651 26.8515 53.4768L25.4559 52.0812C25.2713 51.8966 25.2444 51.6045 25.3943 51.3892L26.713 49.4785C25.9941 48.4059 25.4751 47.1872 25.2213 45.8762L22.9377 45.4572C22.6802 45.4111 22.4917 45.1843 22.4917 44.9229V42.9468C22.4917 42.6854 22.6802 42.4586 22.9377 42.4125L25.2213 41.9934C25.4751 40.6825 25.9941 39.4636 26.713 38.3911L25.3943 36.4804C25.2444 36.2652 25.2713 35.9729 25.4559 35.7883L26.8515 34.3928C27.0398 34.2044 27.3321 34.1774 27.5474 34.3274L29.462 35.646C30.5346 34.9271 31.7495 34.412 33.0604 34.1582L33.4794 31.8707C33.5255 31.6131 33.7485 31.4248 34.0137 31.4248H35.986C36.2512 31.4248 36.4742 31.6131 36.5203 31.8707L36.9393 34.1582C38.2504 34.412 39.4691 34.9271 40.5377 35.646L42.4523 34.3274C42.6677 34.1774 42.9598 34.2044 43.1482 34.3928L44.5438 35.7883C44.7284 35.9729 44.7553 36.265 44.6054 36.4804L43.2867 38.3911C44.0056 39.4636 44.5246 40.6825 44.7784 41.9934L47.062 42.4125C47.3196 42.4586 47.5079 42.6855 47.5079 42.9468Z" fill="#18BF6B"/>
                                <path d="M39.5599 43.9349C39.5599 46.454 37.5179 48.4939 34.9989 48.4939C32.4819 48.4939 30.4399 46.4538 30.4399 43.9349C30.4399 41.4178 32.4819 39.376 34.9989 39.376C37.5179 39.376 39.5599 41.4179 39.5599 43.9349Z" fill="url(#paint3_linear_392_35)"/>
                                <path opacity="0.6" d="M35.0015 12.1563C38.1871 12.1563 40.7696 9.57385 40.7696 6.38822C40.7696 3.20258 38.1871 0.620117 35.0015 0.620117C31.8159 0.620117 29.2334 3.20258 29.2334 6.38822C29.2334 9.57385 31.8159 12.1563 35.0015 12.1563Z" fill="#18BF6B"/>
                                <path d="M45.2767 24.7802H24.7232C23.4057 24.7802 22.3601 23.5902 22.6152 22.2981C23.7712 16.5094 28.8799 12.1572 35.0001 12.1572C41.1203 12.1572 46.2289 16.5094 47.3848 22.2981C47.6398 23.5901 46.6028 24.7802 45.2767 24.7802Z" fill="#18BF6B"/>
                                <path opacity="0.6" d="M12.7637 58.7559C15.9493 58.7559 18.5318 56.1735 18.5318 52.9878C18.5318 49.8022 15.9493 47.2197 12.7637 47.2197C9.57807 47.2197 6.99561 49.8022 6.99561 52.9878C6.99561 56.1735 9.57807 58.7559 12.7637 58.7559Z" fill="#18BF6B"/>
                                <path d="M23.0391 71.3798H2.48541C1.16786 71.3798 0.122326 70.1898 0.377423 68.8977C1.53338 63.109 6.64213 58.7568 12.7623 58.7568C18.8825 58.7568 23.9911 63.109 25.147 68.8977C25.4021 70.1898 24.3651 71.3798 23.0391 71.3798Z" fill="#18BF6B"/>
                                <path opacity="0.6" d="M57.2388 58.7559C60.4244 58.7559 63.0069 56.1735 63.0069 52.9878C63.0069 49.8022 60.4244 47.2197 57.2388 47.2197C54.0532 47.2197 51.4707 49.8022 51.4707 52.9878C51.4707 56.1735 54.0532 58.7559 57.2388 58.7559Z" fill="#18BF6B"/>
                                <path d="M67.5142 71.3798H46.9605C45.643 71.3798 44.5974 70.1898 44.8525 68.8977C46.0085 63.109 51.1172 58.7568 57.2374 58.7568C63.3576 58.7568 68.4662 63.109 69.6221 68.8977C69.8772 70.1898 68.8402 71.3798 67.5142 71.3798Z" fill="#18BF6B"/>
                                <defs>
                                <linearGradient id="paint0_linear_392_35" x1="22.0037" y1="51.8056" x2="22.0037" y2="47.0594" gradientUnits="userSpaceOnUse">
                                <stop offset="0.0168" stop-color="#CCCCCC"/>
                                <stop offset="1" stop-color="#F2F2F2"/>
                                </linearGradient>
                                <linearGradient id="paint1_linear_392_35" x1="47.9887" y1="51.8056" x2="47.9887" y2="47.0595" gradientUnits="userSpaceOnUse">
                                <stop offset="0.0168" stop-color="#CCCCCC"/>
                                <stop offset="1" stop-color="#F2F2F2"/>
                                </linearGradient>
                                <linearGradient id="paint2_linear_392_35" x1="34.9998" y1="32.1779" x2="34.9998" y2="24.0282" gradientUnits="userSpaceOnUse">
                                <stop offset="0.0168" stop-color="#CCCCCC"/>
                                <stop offset="1" stop-color="#F2F2F2"/>
                                </linearGradient>
                                <linearGradient id="paint3_linear_392_35" x1="34.9999" y1="48.494" x2="34.9999" y2="39.376" gradientUnits="userSpaceOnUse">
                                <stop offset="0.0168" stop-color="#CCCCCC"/>
                                <stop offset="1" stop-color="#F2F2F2"/>
                                </linearGradient>
                                </defs>
                                </svg>
                        </div>
                    </div>
                    <div id="sharingButtonsContainer" class="sharingButtonsContainer" style="display: none;">
                        <div class="Demo1 gap-2 d-flex align-items-center justify-content-center hidden"></div>
                    </div>
                </div>
            </div>
            @if (module_is_active('LandingPage'))
                @include('landingpage::layouts.dash_qr')
            @endif
        </div>
    </div>
    <div class="col-xxl-6 col-12">
        <div class="row d-flex dashboard-wrp">
            <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                <div class="dashboard-project-card">
                    <div class="card-inner  d-flex justify-content-between">
                        <div class="card-content">
                            <div class="theme-avtar bg-white">
                                <i class="ti ti-users text-danger"></i>
                            </div>
                            <a href="{{ route('users.index') }}"><h3 class="mt-3 mb-0 text-danger">{{ __('Total Customers') }}</h3></a>
                            <h6 class="text-danger pt-3">{{ __('Paid Customers') }} </h6>
                            <h4 class="text-dark">{{ $user['total_paid_user'] }}</h4>
                        </div>
                        <h3 class="mb-0">{{ $user->total_user }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                <div class="dashboard-project-card">
                    <div class="card-inner  d-flex justify-content-between">
                        <div class="card-content">
                            <div class="theme-avtar bg-white">
                                <i class="ti ti-shopping-cart"></i>
                            </div>
                            <a href="{{ route('plan.order.index') }}"><h3 class="mt-3 mb-0">{{ __('Total Orders') }}</h3></a>
                             <h6 class="text-primary pt-3">{{ __('Order Amount') }}</h6>
                             <h4 class="text-dark">{{ super_currency_format_with_sym($user['total_orders_price']) }}</h4>
                        </div>
                        <h3 class="mb-0">{{ $user->total_orders }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                <div class="dashboard-project-card">
                    <div class="card-inner  d-flex justify-content-between">
                        <div class="card-content">
                            <div class="theme-avtar bg-white">
                                <i class="ti ti-trophy"></i>
                            </div>
                            <a href="{{ route('plan.list') }}"><h3 class="mt-3 mb-0">{{ __('Total Plans') }}</h3></a>
                            <h6 class="text-warning mt-2">{{ __('Popular Plan') }}</h6>
                            <h4 class="text-dark">{{ !empty($user->popular_plan) ? $user->popular_plan->name : '' }}</h4>
                        </div>
                        <h3 class="mb-0">{{ $user->total_plans }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <div class="row">
        <div class="col-md-12 col-lg-12 col-12">
            <h4 class="h4 font-weight-400">{{ __('Recent Order') }}</h4>
            <div class="card">
                <div class="chart">
                    <div id="chart-sales" data-color="primary" data-height="350" class="p-3"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@if (module_is_active('LandingPage'))
    @include('landingpage::layouts.dash_qr_scripts')
@endif
