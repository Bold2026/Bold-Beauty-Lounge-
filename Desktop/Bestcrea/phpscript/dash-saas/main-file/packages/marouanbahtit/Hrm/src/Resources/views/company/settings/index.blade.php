@permission('hrm manage')
    <div class="card" id="hrm-sidenav">
        {{ Form::open(['route' => 'hrm.setting.store', 'id' => 'hrm_setting_store']) }}
        <div class="card-header p-3">
            <h5 class="">{{ __('HRM Settings') }}</h5>
        </div>
        <div class="card-body pb-0 p-3">
            <div class="row ">
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label for="employee_prefix" class="form-label">{{ __('Employee Prefix') }}</label>
                        <input type="text" name="employee_prefix" class="form-control"
                            placeholder="{{ __('Employee Prefix') }}"
                            value="{{ !empty($settings['employee_prefix']) ? $settings['employee_prefix'] : '#EMP000' }}"
                            id="employee_prefix">
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label for="company_start_time" class="form-label">{{ __('Company Start Time') }}</label>
                        <input type="time" name="company_start_time" class="form-control"
                            value="{{ !empty($settings['company_start_time']) ? $settings['company_start_time'] : '09:00' }}"
                            id="company_start_time">
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label for="company_end_time" class="form-label">{{ __('Company End Time') }}</label>
                        <input type="time" name="company_end_time" class="form-control"
                            value="{{ !empty($settings['company_end_time']) ? $settings['company_end_time'] : '18:00' }}"
                            id="company_end_time">
                    </div>
                </div>

                @if (Auth::user()->isAbleTo('ip restrict manage'))
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="form-group col mb-0">
                                    <div class=" form-switch p-0">
                                        {{ Form::label('ip_restrict', __('IP Restrict'), ['class' => ' form-label mb-0']) }}
                                        <div class=" float-end">
                                            <input type="checkbox" class="form-check-input" id="ip_restrict"
                                                name="ip_restrict"
                                                {{ isset($settings['ip_restrict']) && $settings['ip_restrict'] == 'on' ? 'checked' : '' }} />
                                            <label class="form-check-label form-label" for="ip_restrict"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                {{ Form::close() }}
            </div>
            <div class="text-xs mt-1">
                {{ __('Manage ') }}
                <a href="{{ route('joiningletter.index') }}"><b>{{ __('joining letter,') }}</b></a><a href="{{ route('experiencecertificate.index') }}"><b>{{ __(' certificate of experience letter and') }}</b></a><a href="{{ route('hrmnoc.index') }}"><b>{{ __(' no objection certificate letter') }}</b></a>{{ __(' here') }}
            </div>
        </div>
        <div class="card-footer text-end  p-3">
            <input class="btn btn-print-invoice  btn-primary  hrm_setting_btn" type="button"
                value="{{ __('Save Changes') }}">
        </div>
    </div>

    <div class="ip_restrict_div {{ !empty($settings['ip_restrict']) && $settings['ip_restrict'] != 'on' ? ' d-none ' : '' }}"
        id="ip_restrict">
        <div class="card">
            @if (Auth::user()->isAbleTo('ip restrict create'))
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between p-3 row-gap">
                    <h5>{{ __('IP Restriction Settings') }}</h5>
                    <a data-url="{{ route('iprestrict.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                        data-bs-original-title="{{ __('Create New IP') }}" data-bs-placement="top" data-size="md"
                        data-ajax-popup="true" data-title="{{ __('Create New IP') }}">
                        <i class="ti ti-plus"></i>
                    </a>
                </div>
            @endif
            <div class="table-border-style">
                <div class="card-body p-3" style="max-height: 290px; overflow:auto">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th class="w-75"> {{ __('IP') }}</th>
                                    <th width="200px"> {{ 'Action' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ips as $ip)
                                    <tr class="Action">
                                        <td class="sorting_1">{{ $ip->ip }}</td>
                                        <td class="">
                                            @permission('ip restrict edit')
                                                <div class="action-btn me-2">
                                                    <a class="mx-3 btn bg-info btn-sm  align-items-center"
                                                        data-url="{{ route('iprestrict.edit', $ip->id) }}" data-size="md"
                                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"
                                                        data-bs-placement="top" data-ajax-popup="true"
                                                        data-title="{{ __('Edit IP') }}" class="edit-icon"
                                                        data-original-title="{{ __('Edit') }}"><i
                                                            class="ti ti-pencil text-white"></i></a>
                                                </div>
                                            @endpermission
                                            @permission('ip restrict delete')
                                                <div class="action-btn">
                                                    {{ Form::open(['method' => 'DELETE', 'route' => ['iprestrict.destroy', $ip->id], 'class' => 'm-0']) }}

                                                    <a class="mx-3 btn bg-danger btn-sm  align-items-center bs-pass-para show_confirm"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete" data-confirm="{{ __('Are You Sure?') }}"
                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="delete-form-{{ $ip->id }}"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                    {{ Form::close() }}
                                                </div>
                                            @endpermission
                                        </td>
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
@endpermission



<link rel="stylesheet" href="{{ asset('packages/workdo/Hrm/src/Resources/assets/css/custom.css') }}">
<script src="{{ asset('js/custom.js') }}"></script>
<script>
    $(".hrm_setting_btn").click(function() {
        $("#hrm_setting_store").submit();
    });
</script>
<script>
    $(document).on('change', '#ip_restrict', function() {
        if ($(this).is(':checked')) {
            $('.ip_restrict_div').removeClass('d-none');

        } else {
            $('.ip_restrict_div').addClass('d-none');

        }
    });
</script>
