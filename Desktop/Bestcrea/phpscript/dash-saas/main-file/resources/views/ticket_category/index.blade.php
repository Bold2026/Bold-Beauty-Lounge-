@extends('layouts.main')
@section('page-title')
    {{ __('Manage Category') }}
@endsection
@section('page-breadcrumb')
    {{ __('Category') }}
@endsection

@section('page-action')
    <div>
        @permission('helpdesk ticketcategory create')
            <a data-url="{{ route('helpdeskticket-category.create') }}" data-ajax-popup="true"
                data-bs-toggle="tooltip" title="{{ __('Create') }}" title="{{ __('Create') }}"
                data-title="{{ __('Create Category') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>

@endsection
@push('scripts')
    <script src="{{ asset('assets/js/jscolor.js') }}"></script>
@endpush
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="helpdesk-ticketcategory">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col">{{ __('Color') }}</th>
                                    @if (Laratrust::hasPermission('helpdesk ticketcategory edit') || Laratrust::hasPermission('helpdesk ticketcategory delete'))
                                        <th class="text-end">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $index => $category)
                                    <tr>
                                        <th scope="row">{{ ++$index }}</th>
                                        <td>{{ $category->name }}</td>
                                        <td><span class="badge"
                                                style="background: {{ $category->color }}">&nbsp;&nbsp;&nbsp;</span></td>
                                        @if (Laratrust::hasPermission('helpdesk ticketcategory edit') || Laratrust::hasPermission('helpdesk ticketcategory delete'))
                                            <td>
                                                <span class="float-end">
                                                    @permission('helpdesk ticketcategory edit')
                                                        <div class="action-btn me-2">
                                                            <a class="mx-3 btn btn-sm align-items-center bg-info"
                                                                data-url="{{ route('helpdeskticket-category.edit', $category->id) }}"
                                                                data-ajax-popup="true"
                                                                data-title="{{ __('Edit Product Category') }}"
                                                                data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                                data-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('helpdesk ticketcategory delete')
                                                        <div class="action-btn  ">
                                                            <form method="POST"
                                                                action="{{ route('helpdeskticket-category.destroy', $category->id) }}"
                                                                id="user-form-{{ $category->id }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input name="_method" type="hidden" value="DELETE">
                                                                <button type="button"
                                                                    class="mx-3 btn btn-sm  align-items-center show_confirm bg-danger"
                                                                    data-bs-toggle="tooltip" title='Delete'>
                                                                    <span class="text-white"> <i class="ti ti-trash"></i></span>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endpermission
                                                </span>
                                            </td>
                                        @endif
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
@endsection
