@extends('layouts.main')

@section('page-title')
    {{ __('Manage Languages') }}
@endsection
@section('page-breadcrumb')
    {{ __('Languages') }}
@endsection
@section('page-action')
    <div class="d-flex">
        @if ($lang != 'en')
            <div class=" pb-0">
                <div class="form-check form-switch custom-switch-v1 mt-0">
                    <input type="hidden" name="disable_lang" value="off">
                    <input type="checkbox" class="form-check-input input-primary" name="disable_lang" data-bs-placement="top"
                        title="{{ __('Enable/Disable') }}" id="disable_lang" data-bs-toggle="tooltip"
                        {{ $langs->status == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="disable_lang"></label>
                </div>
            </div>
        @endif
        @if ($lang != (\Auth::user()->lang ?? 'en'))
            {{ Form::open(['route' => ['lang.destroy', $lang], 'class' => 'm-0']) }}
            @method('DELETE')
            <a href="#" class="btn btn-sm  bg-danger align-items-center bs-pass-para show_confirm me-2"
                data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"
                data-confirm-yes="delete-form-{{ $lang }}"><i class="ti ti-trash text-white"></i></a>
            {{ Form::close() }}
        @endif

        <a href="#" class="btn btn-sm btn-primary me-2" data-ajax-popup="true" data-size="md"
            data-title="{{ __('Import Lang Zip File') }}" data-url="{{ route('import.lang.json.upload') }}"
            data-bs-toggle="tooltip" data-bs-original-title="{{ __('Import') }}">
            <i class="ti ti-file-import"></i>
        </a>
        <a href="{{ route('export.lang.json') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Export') }}">
            <i class="ti ti-file-export"></i>
        </a>
    </div>
@endsection

@section('content')
    @php
        $modules = getshowModuleList();
        $module = Module_Alias_Name($module);
    @endphp
    <div class="row">
<div class="col-12">
        <div class="card align-middle p-3 language-card">
            <ul class="nav nav-pills pb-3" id="pills-tab" role="tablist">
                <li class="nav-item px-1">
                    <a class="nav-link text-capitalize  {{ $module == 'general' ? ' active' : '' }} "
                        href="{{ route('lang.index', [$lang]) }}">{{ __('General') }}</a>
                </li>
                @foreach ($modules as $item)
                    @php
                        $item = Module_Alias_Name($item);
                    @endphp
                    <li class="nav-item px-1">
                        <a class="nav-link text-capitalize  {{ $module == $item ? ' active' : '' }} "
                            href="{{ route('lang.index', [$lang, $item]) }}">{{ $item }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

        <div class="col-xl-2">
            <div class="card">
                <div class="card-body">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        @foreach ($languages as $key => $language)
                            <a href="{{ route('lang.index', [$key, $module]) }}"
                                class="nav-link my-1 font-weight-bold @if ($key == $lang) active @endif">
                                <i class="d-lg-none d-block mr-1"></i>
                                <span class="text-break">{{ Str::ucfirst($language) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-10">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-end filter-box gap-3">
                        <div class="btn-box">
                            <input type="text" id="letter" placeholder="{{ __('Enter a letter to filter') }}"
                                class="form-control">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button id="filter-btn" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                title="{{ __('Apply') }}"><i class="ti ti-search"></i></button>
                            <button id="reset-btn" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                title="{{ __('Reset') }}"><i class="ti ti-trash-off"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            @if ($module == 'general' || $module == '')
                <div class="card px-3">
                    <ul class="nav nav-pills nav-fill my-4 lang-tab">
                        <li class="nav-item">
                            <a data-href="#labels" class="nav-link active">{{ __('Labels') }}</a>
                        </li>

                        <li class="nav-item">
                            <a data-toggle="tab" data-href="#messages" class="nav-link">{{ __('Messages') }} </a>
                        </li>
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form method="post" action="{{ route('lang.store.data', [$lang, $module]) }}">
                        @csrf
                        <div class="tab-content">
                            <div class="tab-pane active" id="labels">
                                <div class="row" id="labels-container">
                                    @foreach ($arrLabel as $label => $value)
                                        <div class="col-lg-6 label-item">
                                            <div class="form-group mb-3">
                                                <label class="form-label text-dark">{{ $label }}</label>
                                                <input type="text" class="form-control"
                                                    name="label[{{ $label }}]" value="{{ $value }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @if ($module == 'general' || $module == '')
                                <div class="tab-pane" id="messages">
                                    @foreach ($arrMessage as $fileName => $fileValue)
                                        <div class="row">
                                            <div class="col-lg-12 label-item">
                                                <h6>{{ ucfirst($fileName) }}</h6>
                                            </div>
                                            @foreach ($fileValue as $label => $value)
                                                @if (is_array($value))
                                                    @foreach ($value as $label2 => $value2)
                                                        @if (is_array($value2))
                                                            @foreach ($value2 as $label3 => $value3)
                                                                @if (is_array($value3))
                                                                    @foreach ($value3 as $label4 => $value4)
                                                                        @if (is_array($value4))
                                                                            @foreach ($value4 as $label5 => $value5)
                                                                                <div class="col-lg-6 label-item">
                                                                                    <div class="form-group mb-3">
                                                                                        <label
                                                                                            class="form-label text-dark">{{ $fileName }}.{{ $label }}.{{ $label2 }}.{{ $label3 }}.{{ $label4 }}.{{ $label5 }}</label>
                                                                                        <input type="text"
                                                                                            class="form-control"
                                                                                            name="message[{{ $fileName }}][{{ $label }}][{{ $label2 }}][{{ $label3 }}][{{ $label4 }}][{{ $label5 }}]"
                                                                                            value="{{ $value5 }}">
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        @else
                                                                            <div class="col-lg-6 label-item">
                                                                                <div class="form-group mb-3">
                                                                                    <label
                                                                                        class="form-label text-dark">{{ $fileName }}.{{ $label }}.{{ $label2 }}.{{ $label3 }}.{{ $label4 }}</label>
                                                                                    <input type="text"
                                                                                        class="form-control"
                                                                                        name="message[{{ $fileName }}][{{ $label }}][{{ $label2 }}][{{ $label3 }}][{{ $label4 }}]"
                                                                                        value="{{ $value4 }}">
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                @else
                                                                    <div class="col-lg-6 label-item">
                                                                        <div class="form-group mb-3">
                                                                            <label
                                                                                class="form-label text-dark">{{ $fileName }}.{{ $label }}.{{ $label2 }}.{{ $label3 }}</label>
                                                                            <input type="text" class="form-control"
                                                                                name="message[{{ $fileName }}][{{ $label }}][{{ $label2 }}][{{ $label3 }}]"
                                                                                value="{{ $value3 }}">
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <div class="col-lg-6 label-item">
                                                                <div class="form-group mb-3">
                                                                    <label
                                                                        class="form-label text-dark">{{ $fileName }}.{{ $label }}.{{ $label2 }}</label>
                                                                    <input type="text" class="form-control"
                                                                        name="message[{{ $fileName }}][{{ $label }}][{{ $label2 }}]"
                                                                        value="{{ $value2 }}">
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <div class="col-lg-6 label-item">
                                                        <div class="form-group mb-3">
                                                            <label
                                                                class="form-label text-dark">{{ $fileName }}.{{ $label }}</label>
                                                            <input type="text" class="form-control"
                                                                name="message[{{ $fileName }}][{{ $label }}]"
                                                                value="{{ $value }}">
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-end">
                            <input type="submit" value="{{ __('Save Changes') }}"
                                class="btn btn-primary btn-block btn-submit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#filter-btn').on('click', function() {
                var letter = $('#letter').val().toLowerCase();

                if (!letter) {
                    toastrs('Error', 'Please enter at least one letter', 'error')
                    setTimeout(function() {
                        location.reload(true);
                    }, 1500);
                    return;
                }

                $('.label-item').each(function() {
                    var label = $(this).find('label').text().toLowerCase();
                    if (label.includes(letter)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#reset-btn').on('click', function() {
                location.reload();
            });
        });
    </script>

    <script>
        $(document).on('click', '.lang-tab .nav-link', function() {
            $('.lang-tab .nav-link').removeClass('active');
            $('.tab-pane').removeClass('active');
            $(this).addClass('active');
            var id = $('.lang-tab .nav-link.active').attr('data-href');
            $(id).addClass('active');
        });

        $(document).on('change', '#disable_lang', function() {
            var val = $(this).prop("checked");
            if (val == true) {
                var langMode = 1;
            } else {
                var langMode = 0;
            }
            $.ajax({
                type: 'POST',
                url: "{{ route('disablelanguage') }}",
                datType: 'json',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "mode": langMode,
                    "lang": "{{ $lang }}"
                },
                success: function(data) {
                    toastrs('Success', data.message, 'success')
                    setTimeout(function() {
                        location.reload(true);
                    }, 1500);
                }
            });
        });
    </script>
@endpush
