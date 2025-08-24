@extends('layouts.auth')
@section('page-title')
    {{ __('Reset Password') }}
@endsection

@section('language-bar')
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
            <a class="dash-head-link dropdown-toggle btn" href  ="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ Str::upper($lang) }}
                </span>
            </a>
            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                @foreach (languages() as $key => $language)
                    <a href="{{ url('/forgot-password', $key) }}"
                        class="dropdown-item @if ($lang == $key) text-primary @endif">
                        <span>{{ Str::ucfirst($language) }}</span>
                    </a>
                @endforeach
            </div>
        </li>
    </div>
@endsection
@php
    $admin_settings = getAdminAllSetting();
@endphp

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="">
                <h2 class="mb-3 f-w-600">{{ __('Forgot Password') }}</h2>
                @if (session('status'))
                    <div class="alert alert-primary">
                        {{ session('status') }}
                    </div>
                @endif
                <p class="text-xs text-muted">{{ __('We will send a link to reset your password') }}</p>
            </div>
            <form method="POST" action="{{ route('password.email') }}" id="form_data" class="needs-validation" novalidate="">
                @csrf
                <div class="">
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">{{ __('Email') }}</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <span class="error invalid-email text-danger" role="alert">
                                <small>{{ $message }}</small>
                            </span>
                        @enderror
                    </div>
                    @stack('recaptcha_field')

                    <div class="d-grid">
                        <button class="btn btn-primary btn-submit btn-block mt-2">{{ __('Send Password Reset Link') }}
                        </button>
                    </div>
                    <p class="my-4 mb-0 text-center">{{ __('Or') }}
                        <a href="{{ route('login', $lang) }}"
                            class="my-4 text-primary">{{ __('Login') }}</a>{{ __(' With') }}
                    </p>
                </div>
            </form>
        </div>
    </div>
@endsection
