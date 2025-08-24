@php
    $admin_settings = getAdminAllSetting();
    $currancy_symbol = admin_setting('defult_currancy_symbol');
    $settings = \Workdo\LandingPage\Entities\LandingPageSetting::settings();

    // $userprice = !empty($plan) ? $plan->price_per_user_monthly : 0;
    // $planprice = !empty($plan) ? $plan->package_price_monthly : 0;
    // $workspaceprice = !empty($plan) ? $plan->price_per_workspace_monthly : 0;
    // $userpriceyearly = !empty($plan) ? $plan->price_per_user_yearly : 0;
    // $planpriceyearly = !empty($plan) ? $plan->package_price_yearly : 0;
    // $workspacepriceyearly = !empty($plan) ? $plan->price_per_workspace_yearly : 0;

    $super_admin_settings = json_encode(getAdminAllSetting());
@endphp
@extends($layout)
@section('page-title')
    {{ __('Pricing') }}
@endsection
@section('content')
    <!-- wrapper start -->
    <div class="wrapper">
        @if (admin_setting('custome_package') == 'on' && admin_setting('plan_package') == 'on')
            <div class="container">
                <div class="tab-head-row">
                    <ul class="tabs d-flex justify-content-center">
                        <li>
                            <a href="{{ route('apps.pricing.plan') }}"
                                class="nav-link active">{{ __('Pre-Packaged Subscription') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('apps.pricing') }}" class="nav-link">{{ __('Usage Subscription') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        @endif
        <section class="pricing-banner common-banner-section">
            <div class="offset-container offset-left">
                <div class="row row-gap align-items-center justify-content-center">
                    <div class="col-lg-9 col-md-7 col-12">
                        <div class="common-banner-content">
                            <div class="section-title text-center">
                                <h1><b>{{ __('Simple Pricing') }}</b></h1>
                                <p>{{ __('Choose extensions that best match your business needs') }}</p>
                            </div>
                            <div class="pricing-switch">
                                <label class="switch ">
                                    <span class="lbl time-monthly active">{{ __('Monthly') }}</span>
                                    <input type="checkbox" name="time-period" class="switch-change plan-period-switch">
                                    <span class="slider round"></span>
                                    <span class="lbl time-yearly">{{ __('Yearly') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-5 col-12">
                        <div class="banner-image">
                            <img src="{{ asset('market_assets/images/dash-banner-image.png') }}" alt="">
                            <div class="ripple-icon position-top">
                                <div class="pulse0"></div>
                                <div class="pulse1"></div>
                                <div class="pulse2"></div>
                                <div class="pulse3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="plan-card-section padding-bottom ">
            <div class="container">
                <div class="basic-plan-card-wrap d-flex no-wrap">
                    <div class="compare-plans d-flex direction-column">
                        <div class="compare-plan-title">
                            <h4>{{ __('Compare our plans') }}</h4>
                        </div>
                        <ul class="compare-plan-opction p-0">
                            @foreach ($modules as $module)
                                @if (!isset($module->display) || $module->display == true)
                                    <li>
                                        <a target="_new"
                                            href="{{ route('software.details', $module->alias) }}">{{ $module->alias }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    @foreach ($plan as $single_plan)
                        @if ($single_plan->status == 1)
                            @php
                                $plan_modules = !empty($single_plan->modules)
                                    ? explode(',', $single_plan->modules)
                                    : [];
                            @endphp
                            <div class="basic-plan-card d-flex direction-column">
                                <div class="basic-plan text-center">
                                    <h4>{{ !empty($single_plan->name) ? $single_plan->name : __('Basic') }}</h4>
                                    <div class="price justify-content-center">
                                        <ins class="per_month_price">{{ super_currency_format_with_sym($single_plan->package_price_monthly) }}<span
                                                class="off-type">{{ __('/Per Month') }}</span></ins>
                                        <ins class="per_year_price d-none">{{ super_currency_format_with_sym($single_plan->package_price_yearly) }}<span
                                                class="off-type">{{ __('/Per Year') }}</span></ins>
                                    </div>
                                    <ul class="plan-info">
                                        <li>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="9" height="8"
                                                viewBox="0 0 9 8" fill="none">
                                                <path
                                                    d="M8.34762 1.03752C8.18221 0.872095 7.91403 0.872095 7.74858 1.03752L2.67378 6.11237L0.723112 4.1617C0.557699 3.99627 0.289518 3.99629 0.124072 4.1617C-0.0413573 4.32712 -0.0413573 4.5953 0.124072 4.76073L2.37426 7.01088C2.53962 7.1763 2.808 7.17618 2.9733 7.01088L8.34762 1.63656C8.51305 1.47115 8.51303 1.20295 8.34762 1.03752Z"
                                                    fill="#0CAF60" />
                                            </svg>
                                            <span>{{ __('Max User :') }}
                                                <b>{{ $single_plan->number_of_user == -1 ? 'Unlimited' : (!empty($single_plan->number_of_user) ? $single_plan->number_of_user : 'Unlimited') }}</b></span>
                                        </li>
                                        <li>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="9" height="8"
                                                viewBox="0 0 9 8" fill="none">
                                                <path
                                                    d="M8.34762 1.03752C8.18221 0.872095 7.91403 0.872095 7.74858 1.03752L2.67378 6.11237L0.723112 4.1617C0.557699 3.99627 0.289518 3.99629 0.124072 4.1617C-0.0413573 4.32712 -0.0413573 4.5953 0.124072 4.76073L2.37426 7.01088C2.53962 7.1763 2.808 7.17618 2.9733 7.01088L8.34762 1.63656C8.51305 1.47115 8.51303 1.20295 8.34762 1.03752Z"
                                                    fill="#0CAF60" />
                                            </svg>
                                            <span>{{ __('Max Workspace :') }}
                                                <b>{{ $single_plan->number_of_workspace == -1 ? 'Unlimited' : (!empty($single_plan->number_of_workspace) ? $single_plan->number_of_workspace : 'Unlimited') }}</b></span>
                                        </li>
                                        <li>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="9" height="8"
                                                viewBox="0 0 9 8" fill="none">
                                                <path
                                                    d="M8.34762 1.03752C8.18221 0.872095 7.91403 0.872095 7.74858 1.03752L2.67378 6.11237L0.723112 4.1617C0.557699 3.99627 0.289518 3.99629 0.124072 4.1617C-0.0413573 4.32712 -0.0413573 4.5953 0.124072 4.76073L2.37426 7.01088C2.53962 7.1763 2.808 7.17618 2.9733 7.01088L8.34762 1.63656C8.51305 1.47115 8.51303 1.20295 8.34762 1.03752Z"
                                                    fill="#0CAF60" />
                                            </svg>
                                            <span>{{ __('Free Trial Days :') }}
                                                <b>{{ !empty($single_plan->trial_days) ? $single_plan->trial_days : 0 }}</b></span>
                                        </li>
                                    </ul>
                                </div>
                                <ul class="basic-plan-ul compare-plan-opction">
                                    @foreach ($modules as $module)
                                        @php
                                            $id = strtolower(preg_replace('/\s+/', '_', $module->name));
                                        @endphp
                                        @if (!isset($module->display) || $module->display == true)
                                            @if (in_array($module->name, $plan_modules))
                                                <li>
                                                    <a href="#">
                                                        <img src="{{ asset('images/right.svg') }}">
                                                    </a>
                                                </li>
                                            @else
                                                <li>
                                                    <a href="#">
                                                        <img src="{{ asset('images/wrong.svg') }}">
                                                    </a>
                                                </li>
                                            @endif
                                        @endif
                                    @endforeach
                                    <li class="plan-btn">
                                        <div class="d-flex flex-column gap-2">

                                            <a class="btn btn-primary user-register-btn" data-id="{{ Crypt::encrypt($single_plan->id) }}">{{ __('Subscription') }}</a>
                                            @if ($single_plan->trial == 1 && $single_plan->is_free_plan != 1)
                                            <a data-id="{{ Crypt::encrypt($single_plan->id) }}" class="btn btn-outline-dark user-trial">{{ __('Start Free Trial') }}</a>
                                            @endif
                                        </div>

                                    </li>
                                </ul>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

        <section class="review-section padding-bottom">
            <div class="container">
                <div class="review-slider">
                    @if (is_array(json_decode($settings['reviews'], true)) || is_object(json_decode($settings['reviews'], true)))
                        @foreach (json_decode($settings['reviews'], true) as $key => $value)
                            <div class="review-content-itm">
                                <div class="review-content">
                                    <div class="section-title">
                                        <div class="quats">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="83" height="59"
                                                viewBox="0 0 83 59" fill="none">
                                                <path
                                                    d="M17.4193 58.9563C12.8711 58.9563 8.86601 57.2932 5.40398 53.9669C1.94195 50.5727 0.210938 45.8209 0.210938 39.7115C0.210938 34.2809 1.33101 29.0878 3.57114 24.1324C5.81128 19.109 8.76418 14.5609 12.4299 10.4879C16.1634 6.34704 20.2364 2.85108 24.6488 0L35.1367 7.73865C33.9827 8.689 32.5232 10.2503 30.7582 12.4226C28.9933 14.5948 27.2962 16.9707 25.667 19.5503C24.0378 22.1298 22.9178 24.5397 22.3068 26.7798C25.701 27.8659 28.4842 29.7327 30.6564 32.3801C32.8965 34.9597 34.0166 38.3199 34.0166 42.4607C34.0166 47.4841 32.3195 51.4892 28.9254 54.476C25.5991 57.4629 21.7638 58.9563 17.4193 58.9563ZM65.073 58.9563C60.457 58.9563 56.4519 57.2932 53.0578 53.9669C49.6636 50.5727 47.9666 45.8209 47.9666 39.7115C47.9666 34.3487 49.0866 29.1896 51.3268 24.2342C53.6348 19.2108 56.6216 14.6288 60.2873 10.4879C64.0209 6.27917 68.0259 2.7832 72.3026 0L82.8923 7.73865C81.6704 8.82478 80.177 10.42 78.412 12.5244C76.6471 14.6288 74.9839 16.9707 73.4226 19.5503C71.8613 22.0619 70.7413 24.4718 70.0624 26.7798C73.3887 27.8659 76.1719 29.7327 78.412 32.3801C80.6522 34.9597 81.7722 38.3199 81.7722 42.4607C81.7722 47.4841 80.0752 51.4892 76.681 54.476C73.3548 57.4629 69.4854 58.9563 65.073 58.9563Z"
                                                    fill="#002332" />
                                            </svg>
                                        </div>
                                        <div class="subtitle">
                                            {!! $value['review_header_tag'] !!}
                                        </div>
                                        <h2> {!! $value['review_heading'] !!}</h2>
                                    </div>
                                    <p> {!! $value['review_description'] !!} </p>
                                    <div class="btn-group">
                                        <a href="{{ route('apps.pricing') }}"
                                            class="btn btn-white">{{ __('Get the Package') }} <svg
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 16 16" fill="none">
                                                <g clip-path="url(#clip0_14_726)">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M5.88967 10.9856C6.96087 11.2611 7.75238 12.233 7.75238 13.3897C7.75238 14.7607 6.64043 15.8721 5.26877 15.8721C3.89711 15.8721 2.78516 14.7607 2.78516 13.3897C2.78516 12.233 3.57667 11.2611 4.64787 10.9856V10.5959C4.64787 8.7099 6.1768 7.18097 8.06283 7.18097C9.26304 7.18097 10.236 6.20801 10.236 5.00781V3.09158L8.81233 4.51524C8.56985 4.75772 8.17672 4.75772 7.93424 4.51524C7.69176 4.27276 7.69176 3.87963 7.93424 3.63715L10.4179 1.15354C10.6603 0.91106 11.0535 0.91106 11.2959 1.15354L13.7796 3.63715C14.022 3.87962 14.022 4.27276 13.7796 4.51524C13.5371 4.75771 13.1439 4.75772 12.9015 4.51524L11.4778 3.09158V5.00781C11.4778 6.89384 9.94887 8.42278 8.06283 8.42278C6.86263 8.42278 5.88967 9.39573 5.88967 10.5959V10.9856ZM6.51058 13.3897C6.51058 14.0743 5.95517 14.6303 5.26877 14.6303C4.58237 14.6303 4.02696 14.0743 4.02696 13.3897C4.02696 12.7052 4.58237 12.1492 5.26877 12.1492C5.95517 12.1492 6.51058 12.7052 6.51058 13.3897Z"
                                                        fill="white"></path>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_14_726">
                                                        <rect width="14.9017" height="14.9017" fill="white"
                                                            transform="translate(0.921875 0.97168)"></rect>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </a>
                                        <a href="{!! $value['review_live_demo_link'] !!}" class="link-btn">{!! $value['review_live_demo_button_text'] !!}<svg
                                                xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 20 20" fill="none">
                                                <g clip-path="url(#clip0_7_820)">
                                                    <path
                                                        d="M9.33984 1.18359L9.33985 18.519L4.612 18.519C2.87125 18.519 1.4601 17.1079 1.4601 15.3671L1.4601 4.33549C1.4601 2.59475 2.87125 1.18359 4.612 1.18359L9.33984 1.18359Z"
                                                        fill="#002332"></path>
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M17.222 19.3066C18.5276 19.3066 19.5859 18.2483 19.5859 16.9427L19.5859 15.6294L19.5859 2.75918C19.5859 1.45362 18.5276 0.39526 17.222 0.39526L10.1302 0.39526L9.77566 0.39526C9.34047 0.39526 8.98768 0.748047 8.98768 1.18324C8.98768 1.61842 9.34047 1.97121 9.77566 1.97121L10.1302 1.97121L17.222 1.97121C17.6572 1.97121 18.01 2.324 18.01 2.75918L18.01 15.6294L18.01 16.9427C18.01 17.3779 17.6572 17.7307 17.222 17.7307L10.1302 17.7307L9.77566 17.7307C9.34047 17.7307 8.98769 18.0835 8.98769 18.5187C8.98769 18.9539 9.34047 19.3066 9.77566 19.3066L10.1302 19.3066L17.222 19.3066ZM7.72693 18.5187C7.72693 18.0835 7.37414 17.7307 6.93895 17.7307L6.22977 17.7307C5.79459 17.7307 5.4418 18.0835 5.4418 18.5187C5.4418 18.9539 5.79459 19.3066 6.22977 19.3066L6.93895 19.3066C7.37414 19.3066 7.72693 18.9539 7.72693 18.5187ZM7.72693 1.18324C7.72693 0.748047 7.37414 0.39526 6.93895 0.39526L6.22977 0.39526C5.79459 0.39526 5.4418 0.748047 5.4418 1.18324C5.4418 1.61842 5.79459 1.97121 6.22977 1.97121L6.93895 1.97121C7.37414 1.97121 7.72693 1.61842 7.72693 1.18324ZM4.18104 18.5187C4.18104 18.0835 3.82825 17.7307 3.39307 17.7307L3.03848 17.7307C2.99569 17.7307 2.95423 17.7274 2.9142 17.7211C2.48429 17.6535 2.08101 17.9472 2.01344 18.3772C1.94588 18.8071 2.23962 19.2103 2.66953 19.2779C2.79021 19.2969 2.91347 19.3066 3.03848 19.3066L3.39307 19.3066C3.82825 19.3066 4.18104 18.9539 4.18104 18.5187ZM4.18104 1.18324C4.18104 0.748048 3.82825 0.395261 3.39307 0.395261L3.03848 0.395261C2.91347 0.395261 2.7902 0.405034 2.66953 0.423997C2.23962 0.491559 1.94588 0.894841 2.01344 1.32475C2.08101 1.75466 2.48429 2.0484 2.9142 1.98084C2.95423 1.97455 2.99569 1.97121 3.03848 1.97121L3.39307 1.97121C3.82825 1.97121 4.18104 1.61842 4.18104 1.18324ZM1.60405 17.9678C2.03396 17.9002 2.3277 17.4969 2.26014 17.067C2.25384 17.027 2.25051 16.9855 2.25051 16.9427L2.25051 16.5881C2.25051 16.1529 1.89772 15.8002 1.46253 15.8002C1.02735 15.8002 0.674557 16.1529 0.674557 16.5881L0.674557 16.9427C0.674557 17.0677 0.68433 17.191 0.703293 17.3117C0.770857 17.7416 1.17414 18.0353 1.60405 17.9678ZM1.60405 1.73415C1.17414 1.66659 0.770856 1.96033 0.703292 2.39024C0.684329 2.51091 0.674556 2.63417 0.674556 2.75918L0.674556 3.11377C0.674556 3.54896 1.02734 3.90175 1.46253 3.90175C1.89772 3.90175 2.2505 3.54896 2.2505 3.11377L2.2505 2.75918C2.2505 2.7164 2.25384 2.67493 2.26013 2.6349C2.3277 2.20499 2.03396 1.80171 1.60405 1.73415ZM1.46253 14.5394C1.89772 14.5394 2.25051 14.1866 2.25051 13.7514L2.25051 13.0422C2.25051 12.6071 1.89772 12.2543 1.46253 12.2543C1.02735 12.2543 0.674556 12.6071 0.674556 13.0422L0.674556 13.7514C0.674557 14.1866 1.02735 14.5394 1.46253 14.5394ZM1.46253 10.9935C1.89772 10.9935 2.25051 10.6407 2.25051 10.2055L2.25051 9.49636C2.2505 9.06118 1.89772 8.70839 1.46253 8.70839C1.02735 8.70839 0.674556 9.06118 0.674556 9.49636L0.674556 10.2055C0.674556 10.6407 1.02735 10.9935 1.46253 10.9935ZM1.46253 7.44763C1.89772 7.44763 2.2505 7.09484 2.2505 6.65966L2.2505 5.95048C2.2505 5.51529 1.89772 5.16251 1.46253 5.16251C1.02735 5.16251 0.674556 5.51529 0.674556 5.95048L0.674556 6.65966C0.674556 7.09484 1.02735 7.44763 1.46253 7.44763ZM6.97806 9.06298C6.54288 9.06298 6.19009 9.41577 6.19009 9.85095C6.19009 10.2861 6.54288 10.6389 6.97806 10.6389L11.3798 10.6389L10.3611 11.6577C10.0534 11.9654 10.0534 12.4643 10.3611 12.7721C10.6688 13.0798 11.1677 13.0798 11.4754 12.7721L13.8394 10.4081C13.9871 10.2604 14.0702 10.0599 14.0702 9.85095C14.0702 9.64197 13.9871 9.44154 13.8394 9.29377L11.4754 6.92985C11.1677 6.62213 10.6688 6.62213 10.3611 6.92985C10.0534 7.23757 10.0534 7.73649 10.3611 8.04421L11.3798 9.06298L6.97806 9.06298Z"
                                                        fill="white"></path>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_7_820">
                                                        <rect width="18.9114" height="18.9114" fill="white"
                                                            transform="translate(0.675781 0.395508)"></rect>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <span class="slider__label sr-only">
                </div>
            </div>
        </section>
        @if ($settings['is_faq_section_active'] == 'on')
            <section class="faq-section padding-top padding-bottom">
                <div class="container">
                    <div class="section-title text-center">
                        <h2>
                            {!! !empty($settings['faq_heading']) ? $settings['faq_heading'] : '' !!}
                        </h2>
                        <p>
                            {!! !empty($settings['faq_description']) ? $settings['faq_description'] : '' !!}
                        </p>
                    </div>
                    <div class="faq-list">
                        @if (is_array(json_decode($settings['faqs'], true)) || is_object(json_decode($settings['faqs'], true)))
                            @foreach (json_decode($settings['faqs'], true) as $key => $value)
                                <div class="set has-children">
                                    <a href="javascript:;" class="acnav-label">
                                        <span class="acnav-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                                viewBox="0 0 36 36" fill="none">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M18 33C9.71573 33 3 26.2843 3 18C3 9.71573 9.71573 3 18 3C26.2843 3 33 9.71573 33 18C33 26.2843 26.2843 33 18 33ZM18 6C11.3726 6 6 11.3726 6 18C6 24.6274 11.3726 30 18 30C24.6274 30 30 24.6274 30 18C30 11.3726 24.6274 6 18 6ZM18 16.125C19.0355 16.125 19.875 16.9645 19.875 18V24C19.875 25.0355 19.0355 25.875 18 25.875C16.9645 25.875 16.125 25.0355 16.125 24V18C16.125 16.9645 16.9645 16.125 18 16.125ZM18 10.5C16.9645 10.5 16.125 11.3395 16.125 12.375C16.125 13.4105 16.9645 14.25 18 14.25C19.0355 14.25 19.875 13.4105 19.875 12.375C19.875 11.3395 19.0355 10.5 18 10.5Z"
                                                    fill="#F491AF" />
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M18 33C9.71573 33 3 26.2843 3 18C3 9.71573 9.71573 3 18 3C26.2843 3 33 9.71573 33 18C33 26.2843 26.2843 33 18 33ZM18 6C11.3726 6 6 11.3726 6 18C6 24.6274 11.3726 30 18 30C24.6274 30 30 24.6274 30 18C30 11.3726 24.6274 6 18 6ZM18 16.125C19.0355 16.125 19.875 16.9645 19.875 18V24C19.875 25.0355 19.0355 25.875 18 25.875C16.9645 25.875 16.125 25.0355 16.125 24V18C16.125 16.9645 16.9645 16.125 18 16.125ZM18 10.5C16.9645 10.5 16.125 11.3395 16.125 12.375C16.125 13.4105 16.9645 14.25 18 14.25C19.0355 14.25 19.875 13.4105 19.875 12.375C19.875 11.3395 19.0355 10.5 18 10.5Z"
                                                    fill="url(#paint0_linear_105_825)" />
                                                <defs>
                                                    <linearGradient id="paint0_linear_105_825" x1="4.17978"
                                                        y1="3.92308" x2="29.447" y2="35.7472"
                                                        gradientUnits="userSpaceOnUse">
                                                        <stop stop-color="#6FD943" />
                                                        <stop offset="1" stop-color="#6FD943" />
                                                    </linearGradient>
                                                </defs>
                                            </svg>
                                        </span>
                                        <span>{!! isset($value['faq_questions']) ? $value['faq_questions'] : '' !!}</span>
                                    </a>
                                    <div class="acnav-list">
                                        <p>
                                            {!! isset($value['faq_answer']) ? $value['faq_answer'] : '' !!}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </section>
        @endif
    </div>
    <!-- wrapper end -->
    <div class="register-popup">
        <div class="register-popup-body">
            <div class="popup-header">
                <h5>{{ __('Register Form') }}</h5>
                <div class="close-register">
                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50"
                        fill="none">
                        <path
                            d="M27.7618 25.0008L49.4275 3.33503C50.1903 2.57224 50.1903 1.33552 49.4275 0.572826C48.6647 -0.189868 47.428 -0.189965 46.6653 0.572826L24.9995 22.2386L3.33381 0.572826C2.57102 -0.189965 1.3343 -0.189965 0.571605 0.572826C-0.191089 1.33562 -0.191186 2.57233 0.571605 3.33503L22.2373 25.0007L0.571605 46.6665C-0.191186 47.4293 -0.191186 48.666 0.571605 49.4287C0.952952 49.81 1.45285 50.0007 1.95275 50.0007C2.45266 50.0007 2.95246 49.81 3.3339 49.4287L24.9995 27.763L46.6652 49.4287C47.0465 49.81 47.5464 50.0007 48.0463 50.0007C48.5462 50.0007 49.046 49.81 49.4275 49.4287C50.1903 48.6659 50.1903 47.4292 49.4275 46.6665L27.7618 25.0008Z"
                            fill="white"></path>
                    </svg>
                </div>
            </div>
            <form method="POST" action="{{ route('register') }}" id="register-form">
                @csrf
                <div class="popup-content">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label class="form-label">{{ __('Name') }}</label>
                                <input id="name" type="text" placeholder="Enter name" class="form-control"
                                    name="name" value="" required="required" autocomplete="name" autofocus>
                            </div>
                        </div>
                        <input type="hidden" name = "type" value="plan" id="type">
                        <input type="hidden" name="plan" value="">
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label class="form-label">{{ __('WorkSpace Name') }}</label>
                                <input id="store_name" placeholder="Enter workspace" type="text" class="form-control"
                                    name="workspace" value="" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input id="email" placeholder="Enter email" type="email" class="form-control "
                                    name="email" value="" required="required">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label class="form-label">{{ __('Password') }}</label>
                                <input id="password" placeholder="Enter password" type="password" class="form-control"
                                    name="password" required="required">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label class="form-label">{{ __('Confirm password') }}</label>
                                <input id="password-confirm" placeholder="Enter confirm password" type="password"
                                    class="form-control" name="password_confirmation" required="required">
                                <small class="text-danger password-msg d-none">{{ __('Passwords do not match!') }}</small>
                            </div>
                        </div>
                        @stack('recaptcha_field')
                    </div>
                </div>
                <div class="popup-footer d-flex align-items-center justify-content-end">
                    <button type="button" class="btn-danger btn close-register">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-secondary register-form-btn">{{ __('Register') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        $(document).on("click", ".plan-period-switch", function() {

            if ($('.plan-period-switch').prop('checked') == true) {
                $(".per_year_price").removeClass("d-none");
                $(".per_month_price").addClass("d-none");
            } else {
                $(".per_month_price").removeClass("d-none");
                $(".per_year_price").addClass("d-none");
            }
        });
    </script>
    <script>
        $(document).on("click", ".user-register-btn", function() {
            var workspace_counter = $('.workspace_counter_input').val();
            var workspaceprice = $('.workspaceprice_input').val();

            var user_counter = $('.user_counter_input').val();
            var user_module = $('.user_module_input').val();
            var userprice = $('.userprice_input').val();
            var user_module_price = $('.user_module_price_input').val();

            var time_period = $('.time_period_input').val();

            var plan_id = $(this).data('id');
            $('input[name=plan]').val(plan_id);

            $('input[name=type]').val('plan');

            const formData = new FormData();
            formData.append("workspace_counter", workspace_counter);
            formData.append("workspaceprice", workspaceprice);

            formData.append("user_counter", user_counter);
            formData.append("user_module", user_module);
            formData.append("userprice", userprice);
            formData.append("user_module_price", user_module_price);
            formData.append("time_period", time_period);
            formData.append('_token', "{{ csrf_token() }}");

        });

        $(document).on('click',".user-trial",function(){
            var plan_id = $(this).data('id');
            $('input[name=plan]').val(plan_id);

            $('input[name=type]').val('trial');

            $('.register-popup').addClass('active');
        });
    </script>
    <script>
        $(document).on("click", ".register-form-btn", function() {
            var status = true;
            var name = $('#name').val();
            var store_name = $('#store_name').val();
            var email = $('#email').val();
            var password = $('#password').val();
            var type = $('#type').val();
            var plan_id = $('input[name=plan]').val();
            var password_confirm = $('#password-confirm').val();
            $('.password-msg').addClass('d-none');

            // Check for empty fields
            if (name.trim() === '' || store_name.trim() === '' || email.trim() === '' || password.trim() === '' ||
                type.trim() === '' || password_confirm.trim() === '' || plan_id.trim() === '') {
                alert('Please fill in all required fields.');
                status = false;
            }

            if (password != password_confirm) {
                $('.password-msg').removeClass('d-none');
                status = false;
            }
            if (status == true) {
                $('#register-form').submit();
            }
        });
    </script>
@endpush
