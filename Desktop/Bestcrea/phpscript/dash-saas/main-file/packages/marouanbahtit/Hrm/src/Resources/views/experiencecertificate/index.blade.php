@extends('layouts.main')
@section('page-title')
    {{ __('Certificate of Experience Settings') }}
@endsection
@section('page-breadcrumb')
{{ __('Certificate of Experience Settings') }}
@endsection
@section('page-action')
@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endsection
@section('content')
<div class="row">
    <div class="col-sm-3">
        @include('hrm::layouts.hrm_setup')
    </div>
    <div class="col-sm-9">
        <div class="" id="experience-certificate-settings">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5>{{ __('Certificate of Experience Settings') }}</h5>
                    <div class="d-flex justify-content-end drp-languages">
                        @if (module_is_active('AIAssistant'))
                            @include('aiassistant::ai.generate_ai_btn', [
                                'template_module' => 'experience certificate settings',
                                'module' => 'Hrm',
                            ])
                        @endif
                        <ul class="list-unstyled mb-0 m-2">
                            <li class="dropdown dash-h-item drp-language" style="margin-left: 10px;">
                                <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                                    role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage1">
                                    <span class="drp-text hide-mob text-primary">

                                        {{ Str::upper($explang) }}
                                    </span>
                                    <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                </a>
                                <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                    aria-labelledby="dropdownLanguage1">
                                    @foreach (languages() as $key => $explangs)
                                        <a href="{{ route('experiencecertificate.index', ['explangs' => $key]) }}"
                                            class="dropdown-item {{ $key == $explang ? 'text-primary' : '' }}">{{ Str::ucfirst($explangs) }}</a>
                                    @endforeach
                                </div>
                            </li>

                        </ul>
                    </div>

                </div>
                <div class="card-body ">
                    <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-header card-body">
                                <div class="row text-xs">
                                    <div class="row">
                                        <p class="col-4">{{ __('App Name') }} : <span
                                            class="pull-right text-primary">{app_name}</span></p>
                                        <p class="col-4">{{ __('Company Name') }} : <span
                                                class="pull-right text-primary">{company_name}</span></p>
                                        <p class="col-4">{{ __('Employee Name') }} : <span
                                                class="pull-right text-primary">{employee_name}</span></p>
                                        <p class="col-4">{{ __('Date of Issuance') }} : <span
                                                class="pull-right text-primary">{date}</span></p>
                                        <p class="col-4">{{ __('Designation') }} : <span
                                                class="pull-right text-primary">{designation}</span></p>
                                        <p class="col-4">{{ __('Start Date') }} : <span
                                                class="pull-right text-primary">{start_date}</span></p>
                                        <p class="col-4">{{ __('Branch') }} : <span
                                                class="pull-right text-primary">{branch}</span></p>
                                        <p class="col-4">{{ __('Start Time') }} : <span
                                                class="pull-end text-primary">{start_time}</span></p>
                                        <p class="col-4">{{ __('End Time') }} : <span
                                                class="pull-right text-primary">{end_time}</span></p>
                                        <p class="col-4">{{ __('Number of Hours') }} : <span
                                                class="pull-right text-primary">{total_hours}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style ">

                    {{ Form::open(['route' => ['experiencecertificate.update', $explang], 'method' => 'post']) }}
                    <div class="form-group col-12">
                        {{ Form::label('experience_content', __(' Format'), ['class' => 'form-label text-dark']) }}
                        <textarea name="experience_content"
                            class="form-control summernote  {{ !empty($errors->first('experience_content')) ? 'is-invalid' : '' }}" required
                            id="experience_content">{!! isset($curr_exp_cetificate_Lang->content) ? $curr_exp_cetificate_Lang->content : '' !!}</textarea>
                    </div>

                    <div class="text-end">

                        {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
                    </div>

                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
