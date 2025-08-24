@extends('layouts.main')
@section('page-title')
    {{ __('Manage Roles') }}
@endsection
@section('page-breadcrumb')
    {{ __('Roles') }}
@endsection
@section('page-action')
    @permission('roles create')
        <div>
            <a href="{{ route('roles.create') }}" class="btn btn-sm btn-primary"
                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        </div>
    @endpermission
@endsection
@push('css')
@endpush
@section('content')
    <div class="row">
        @foreach ($roles as $role)
        @php
                $permissions = $role->permissions()->get();
        @endphp
        <div class="col-xl-4 col-sm-6 mb-4">
            <div class="card h-100 mb-0">
                <div class="roles-content-top h-100 p-3 border-1 border-bottom">
                    <div class="roles-title btn btn-primary mb-3">{{$role->name}}</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($permissions->take(9) as $permission)
                            @if (module_is_active($permission->module) || $permission->module == 'General')
                                <span class="badge p-2  px-3 text-black">{{$permission->name}}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="roles-content-bottom p-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center flex-wrap gap-2 roles-image">
                        @foreach ($role->users->take(5) as $users)
                        <img alt="image" data-bs-toggle="tooltip" data-bs-placement="top" title=""
                            src="{{get_file($users->avatar)}}"
                            class="border-1 border border-white rounded-circle" data-bs-original-title="{{$users->name}}"
                            aria-label="{{$users->name}}">
                        @endforeach
                        <span class="">{{ __('Members')}}</span>
                    </div>
                    <div class="d-flex align-items-center">

                        @permission('roles edit')
                        <div class="action-btn me-2">
                            <a href="{{ route('roles.edit',$role->id) }}" class="btn btn-sm  align-items-center bg-info me-2" data-size="xl"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{__('Edit')}}" aria-describedby="tooltip434956"
                                > <span class="text-white"> <i
                                        class="ti ti-pencil"></i></span></a>
                        </div>
                        @endpermission
                        @if (!in_array($role->name,\App\Models\User::$not_edit_role))
                            @permission('roles delete')
                                <div class="action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Delete" aria-describedby="tooltip434956">
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['roles.destroy', $role->id],'id'=>'delete-form-'.$role->id]) !!}

                                <a type="submit" class="mx-2 btn btn-sm align-items-center show_confirm bg-danger" data-toggle="tooltip" title="" data-original-title="{{__('Delete')}}">
                                        <i class="ti ti-trash text-white"></i>
                                    </a>
                                    {!! Form::close() !!}
                                </div>
                            @endpermission
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        {!! $roles->links('vendor.pagination.global-pagination') !!}

    </div>

@endsection
@push('scripts')
    <script>
        function Checkall(module = null) {
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
@endpush
