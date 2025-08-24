    {{-- email setting --}}
    @php
    $get_setting = 'smtp';
    if(array_key_exists('email_setting', $settings))
        {
            $get_setting = $settings['email_setting'];
        }
    @endphp

    <div class="card" id="email-sidenav">
        <div class="email-setting-wrap ">
            {{ Form::open(['route' => ['email.setting.store'], 'id' => 'mail-form']) }}
            @method('post')
            @csrf
            <input type="hidden" class="email">
            <div class="card-header p-3">
                <h5 >{{ __('Email Settings') }}</h5>
            </div>
            <div class="card-body p-3 pb-0">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <div class="form-group col switch-width">
                           {{Form::label('email_setting',__('Email Setting'),array('class'=>'form-label')) }}

                           {{ Form::select('email_setting',$email_setting, isset($settings['email_setting']) ? $settings['email_setting'] : $get_setting, ['id' => 'email_setting','class'=>"form-control choices",'searchEnabled'=>'true']) }}
                        </div>
                     </div>
                </div>
                <div class="row">
                    <div class="col-12" id="getfields">
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between flex-wrap p-3 gap-2">
                <input type="hidden" name="custom_email" id="custom_email" value="{{ isset($settings['email_setting']) ? $settings['email_setting'] : $get_setting}}">
                <button type="button" data-url="{{ route('test.mail') }}" data-title="{{ __('Send Test Mail') }}"
                    class="btn btn-print-invoice  btn-primary  test-mail">{{ __('Send Test Mail') }}</button>

                <input class="btn btn-print-invoice  btn-primary " type="submit"
                    value="{{ __('Save Changes') }}">
            </div>
            {{ Form::close() }}
        </div>
    </div>

    <!--Email Notification Settings-->
    <div class="card" id="email-notification-sidenav">
        <div class="email-setting-wrap ">
            {{ Form::open(['route' => ['email.notification.setting.store'], 'id' => 'mail-notification-form']) }}
            @method('post')
            <div class="card-header p-3">
                <h5>{{ __('Email Notification Settings') }}</h5>
            </div>
            <div class="card-body p-3 pb-0">
                <ul class="nav nav-pills gap-2 mb-3" id="pills-tab" role="tablist">
                    @php
                        $active = 'active';
                    @endphp
                    @foreach ($email_notification_modules as $e_module)
                        @if (Laratrust::hasPermission($e_module . ' manage') ||
                                Laratrust::hasPermission(strtolower($e_module) . ' manage') ||
                                $e_module == 'general')
                            <li class="nav-item">
                                <a class="nav-link text-capitalize rounded-1 {{ $active }}"
                                    id="pills-{{ strtolower($e_module) }}-tab-email" data-bs-toggle="pill"
                                    href="#pills-{{ strtolower($e_module) }}-email" role="tab"
                                    aria-controls="pills-{{ strtolower($e_module) }}-email"
                                    aria-selected="true">{{ Module_Alias_Name($e_module) }}</a>
                            </li>
                            @php
                                $active = '';
                            @endphp
                        @endif
                    @endforeach
                </ul>
                <div class="tab-content " id="pills-tabContent">
                    @foreach ($email_notification_modules as $e_module)
                        <div class="tab-pane fade {{ $loop->index == 0 ? 'active' : '' }} show"
                            id="pills-{{ strtolower($e_module) }}-email" role="tabpanel"
                            aria-labelledby="pills-{{ strtolower($e_module) }}-tab-email">
                            <div class="row">
                                @foreach ($email_notify as $e_action)
                                    @if ($e_action->permissions == null || Laratrust::hasPermission($e_action->permissions))
                                        @if ($e_action->module == $e_module)
                                            <div class="col-lg-4 col-sm-6 col-12 mb-3">
                                                <div
                                                    class="rounded-1 card   list_colume_notifi p-3 h-100 mb-0">
                                                    <div class="card-body d-flex align-items-center justify-content-between gap-2 p-0">
                                                        <h6 class="mb-0">
                                                                <label for="{{ $e_action->action }}"
                                                                    class="form-label mb-0">{{ $e_action->action }}</label>
                                                            </h6>
                                                            <div class="form-check form-switch d-inline-block text-end">
                                                                <input type="hidden"
                                                                    name="mail_noti[{{ $e_action->action }}]"
                                                                    value="0" />
                                                                <input class="form-check-input"
                                                                    {{ isset($settings[$e_action->action]) && $settings[$e_action->action] == true ? 'checked' : '' }}
                                                                    id="mail_notificaation"
                                                                    name="mail_noti[{{ $e_action->action }}]"
                                                                    type="checkbox" value="1">
                                                            </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end p-3">
                <input class="btn btn-print-invoice  btn-primary" type="submit"
                    value="{{ __('Save Changes') }}">
            </div>
            {{ Form::close() }}
        </div>
    </div>

    <script>
        $(document).ready(function() {
        // Check if email_setting is already set
        var emailSetting = $('#email_setting').val();

        if (emailSetting) {
            $.ajax({
                url: '{{ route('get.emailfields') }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "emailsetting": emailSetting,
                },
                success: function(data) {
                    $('#getfields').empty();
                    $('#getfields').append(data.html)
                    $('.email').append(data.html)
                },
            });
        }

        // Initialize choices
        choices();
    });
    </script>
    <script>
        $(document).on('change', '#email_setting', function() {
            var emailsetting = $(this).val();
            $.ajax({
                url: '{{ route('get.emailfields') }}',
                type: 'POST',

                data: {
                    "_token": "{{ csrf_token() }}",
                    "emailsetting": emailsetting,
                },
                success: function(data) {
                    $('#getfields').empty();
                    $('#getfields').append(data.html)
                    $('.email').append(data.html)
                },

            });
        });
    </script>

