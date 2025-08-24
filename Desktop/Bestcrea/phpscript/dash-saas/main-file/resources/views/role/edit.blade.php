@extends('layouts.main')
@section('page-title')
    {{ __('Edit Role') }}
@endsection
@section('page-breadcrumb')
    {{ __('Edit Role') }}
@endsection
@section('content')
    <div class="mt-4">
        {{ Form::model($role, ['route' => ['roles.update', $role->id], 'method' => 'PUT','class'=>'needs-validation','novalidate']) }}
        <div class="row ">
            <!-- Sidebar -->
            <div class="col-xl-3 col-12">
                <div class="card">
                    <div class="card-body pt-1">
                        <div class="">
                            {{ Form::label('name', __('Name'), ['class' => 'col-form-label']) }}<x-required></x-required>
                            @if (in_array($role->name, \App\Models\User::$not_edit_role))
                                {{ Form::text('role_name', $role->name, ['class' => 'form-control', 'disabled' => 'disabled', 'placeholder' => __('Enter Role Name')]) }}
                                {{ Form::hidden('name', $role->name, ['class' => 'form-control']) }}
                            @else
                                {{ Form::text('name', null, ['class' => 'form-control','required'=>'required','placeholder' => __('Enter Role Name')]) }}
                            @endif
                            @error('name')
                                <small class="invalid-name" role="alert">
                                    <strong class="text-danger">{{ $message }}</strong>
                                </small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card sticky-top roles-sidebar">
                    <div class="list-group rounded" id="pills-tab" role="tablist">
                        @foreach ($modules as $module)
                            @if (module_is_active($module) || $module == 'General')
                                <button
                                    class="nav-link p-3 d-flex align-items-center justify-content-between w-100 text-capitalize text-black text-start gap-2 border-0 {{ $loop->index == 0 ? 'active' : '' }}"
                                    id="pills-{{ strtolower($module) }}-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-{{ strtolower($module) }}" type="button">
                                    {{ Module_Alias_Name($module) }}
                                    <i class="ti ti-chevron-right"></i>
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @if (!empty($permissions))
                <!-- Main Content -->
                <div class="col-xl-9 col-12 setting-menu-div roles-menu mb-4">
                    @foreach ($modules as $module)
                        <div class="card tab-pane h-100 mb-0 fade {{ $loop->index == 0 ? 'show active' : '' }}"
                            id="pills-{{ strtolower($module) }}" role="tabpanel"
                            aria-labelledby="pills-{{ strtolower($module) }}-tab">
                            <div class="card-header p-3">
                                <h5>{{ Module_Alias_Name($module) }}</h5>
                            </div>
                            <div class="card-body  p-3">
                                <div class="tab-content" id="pills-tabContent">
                                    @if (module_is_active($module) || $module == 'General')
                                        <div>
                                            <!-- Tab Content -->
                                            <input type="checkbox" class="form-check-input pointer"
                                                name="checkall-{{ strtolower($module) }}"
                                                id="checkall-{{ strtolower($module) }}"
                                                onclick="Checkall('{{ strtolower($module) }}')">
                                            <small class="text-muted mx-2">
                                                {{ Form::label('checkall-' . strtolower($module), 'Assign ' . Module_Alias_Name($module) . ' Permission to Roles', ['class' => 'form-check-label pointer']) }}
                                            </small>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-roles mb-0 mt-3">
                                                    <thead>
                                                        <tr>
                                                            <th class="bg-primary"></th>
                                                            <th class="bg-primary text-white">{{ __('Module') }}</th>
                                                            <th class="bg-primary text-white">{{ __('Permissions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $permissions = get_permission_by_module($module);
                                                            $m_permissions = array_column($permissions->toArray(), 'name');
                                                            $module_list = [];
                                                            foreach ($m_permissions as $key => $value) {
                                                                array_push($module_list, strtok($value, ' '));
                                                            }
                                                            $module_list = array_unique($module_list);
                                                        @endphp
                                                        @foreach ($module_list as $key => $list)
                                                            <tr>
                                                                <td>
                                                                    <input type="checkbox"
                                                                        class="form-check-input ischeck pointer"
                                                                        onclick="CheckModule('module_checkbox_{{ $key }}_{{ $list }}')"
                                                                        id="module_checkbox_{{ $key }}_{{ $list }}">
                                                                </td>
                                                                <td>
                                                                    {{ Form::label('module_checkbox_' . $key . '_' . $list, str_replace('_', ' ', $list), ['class' => 'form-check-label pointer', 'style' => 'word-break: break-word;']) }}
                                                                </td>
                                                                <td
                                                                    class="module_checkbox_{{ $key }}_{{ $list }} ps-4">
                                                                    <div class="row">
                                                                        @foreach ($permissions as $key => $prermission)
                                                                            @php
                                                                                $check = strtok($prermission->name, ' ');
                                                                                $name = str_replace(
                                                                                    $check,
                                                                                    '',
                                                                                    $prermission->name,
                                                                                );
                                                                            @endphp
                                                                            @if ($list == $check)
                                                                                <div class="col-xl-3 col-sm-6 form-check mb-2">
                                                                                    {{ Form::checkbox('permissions[]', $prermission->id,$role->permission,['class' => 'form-check-input pointer checkbox-' . strtolower($module), 'id' => 'permission_' . $key . '_' . $prermission->id]) }}
                                                                                    {{ Form::label('permission_' . $key . '_' . $prermission->id, $name, ['class' => 'form-check-label pointer', 'style' => 'white-space: normal; word-break: break-word;']) }}
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer p-3 d-flex justify-content-end gap-2">
                                <input type="button" value="{{ __('Cancel') }}" onclick="location.href = '{{ route('roles.index') }}';"
                                    class="btn btn-secondary text-white" data-bs-dismiss="modal">
                                <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
        {{ Form::close() }}
    </div>
@endsection
<script>
    function Checkall(module = null) {
        console.log(module);

        var ischecked = $("#checkall-" + module).prop('checked');
        if (ischecked == true) {
            $('.checkbox-' + module).prop('checked', true);
        } else {
            $('.checkbox-' + module).prop('checked', false);
        }
    }
</script>
<script type="text/javascript">
    function CheckModule(cl = null) {
        var ischecked = $("#" + cl).prop('checked');
        if (ischecked == true) {
            $('.' + cl).find("input[type=checkbox]").prop('checked', true);
        } else {
            $('.' + cl).find("input[type=checkbox]").prop('checked', false);
        }
    }
</script>
