@extends('layouts.main')
@section('page-title')
    {{ __('Manage Proposal') }}
@endsection
@section('page-breadcrumb')
    {{ __('Proposal') }}
@endsection
@section('page-action')
    <div class="d-flex">
        @stack('addButtonHook')
        @if (module_is_active('ProductService') && (module_is_active('Account') || module_is_active('Taskly')))
            @permission('category create')
                <a href="{{ route('category.index') }}"data-size="md" class="btn btn-sm btn-primary me-2"
                    data-bs-toggle="tooltip"data-title="{{ __('Setup') }}" title="{{ __('Setup') }}"><i
                        class="ti ti-settings"></i></a>
            @endpermission
            @permission('proposal manage')
                <a href="{{ route('proposal.index') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('List View') }}"
                    class="btn btn-sm btn-primary btn-icon me-2">
                    <i class="ti ti-list"></i>
                </a>
                <a href="{{ route('proposal.stats.view') }}" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Quick Stats') }}" class="btn btn-sm btn-primary btn-icon me-2">
                    <i class="ti ti-filter"></i>
                </a>
            @endpermission
            @permission('proposal create')
                <a href="{{ route('proposal.create', 0) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i>
                </a>
            @endpermission
        @endif
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['proposal.grid.view'], 'method' => 'GET', 'id' => 'frm_submit']) }}
                    <div class="row d-flex align-items-center justify-content-end">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('issue_date', __('Date'), ['class' => 'text-type']) }}
                                {{ Form::text('issue_date', isset($_GET['issue_date']) ? $_GET['issue_date'] : null, ['class' => 'form-control flatpickr-to-input', 'placeholder' => 'Select Date']) }}
                            </div>
                        </div>
                        @if (\Auth::user()->type != 'client')
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('customer', __('Customer'), ['class' => 'text-type']) }}
                                    {{ Form::select('customer', $customer, isset($_GET['customer']) ? $_GET['customer'] : '', ['class' => 'form-control', 'placeholder' => 'Select Client']) }}
                                </div>
                            </div>
                        @endif
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('status', __('Status'), ['class' => 'text-type']) }}
                                {{ Form::select('status', ['' => 'Select Status'] + $status, isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end mt-4 d-flex">
                            <a class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                id="applyfilter" data-original-title="{{ __('apply') }}">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{ route('proposal.grid.view') }}" class="btn btn-sm btn-danger"
                                data-bs-toggle="tooltip" title="{{ __('Reset') }}" id="clearfilter"
                                data-original-title="{{ __('Reset') }}">
                                <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="row mb-4 project-wrp d-flex">
                @isset($proposals)
                @foreach ($proposals as $proposal)
                        <div class="col-xxl-3 col-xl-4 col-sm-6 col-12 ">
                            <div class="project-card">
                                <div class="project-card-inner">
                                    <div class="project-card-header d-flex justify-content-between">

                                        @if ($proposal->status == 0)
                                        <span
                                            class="badge fix_badge bg-primary p-2 px-3">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                    @elseif($proposal->status == 1)
                                        <span
                                            class="badge fix_badge bg-info p-2 px-3">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                    @elseif($proposal->status == 2)
                                        <span
                                            class="badge fix_badge bg-secondary p-2 px-3">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                    @elseif($proposal->status == 3)
                                        <span
                                            class="badge fix_badge bg-warning p-2 px-3">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                    @elseif($proposal->status == 4)
                                        <span
                                            class="badge fix_badge bg-danger p-2 px-3">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                    @endif
                                        <button type="button"
                                            class="btn btn-light dropdown-toggle d-flex align-items-center justify-content-center"
                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="ti ti-dots-vertical text-black"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-end pointer">
                                            <a href="#"
                                            data-link="{{ route('pay.proposalpay', \Illuminate\Support\Facades\Crypt::encrypt($proposal->id)) }}"
                                            class="dropdown-item cp_link">
                                            <i class="ti ti-file"></i> {{ __('Click To Copy Proposal Link') }}
                                        </a>
                                        @if (module_is_active('Retainer'))
                                            @include('retainer::setting.convert_retainer', [
                                                'proposal' => $proposal,
                                                'type' => 'grid',
                                            ])
                                        @endif
                                        @if ($proposal->is_convert == 0 && $proposal->is_convert_retainer == 0)
                                            @permission('proposal convert invoice')
                                                {!! Form::open([
                                                    'method' => 'get',
                                                    'route' => ['proposal.convert', $proposal->id],
                                                    'id' => 'proposal-form-' . $proposal->id,
                                                ]) !!}
                                                <a href="#!" class="show_confirm dropdown-item"
                                                    data-confirm-yes="proposal-form-{{ $proposal->id }}">
                                                    <i class="ti ti-exchange me-1"></i>{{ __('Convert to Invoice') }}
                                                </a>
                                                {{ Form::close() }}
                                            @endpermission
                                        @elseif($proposal->is_convert == 1)
                                            @permission('invoice show')
                                                <a href="{{ route('invoice.show', \Crypt::encrypt($proposal->converted_invoice_id)) }}"
                                                    class="dropdown-item">
                                                    <i class="ti ti-eye me-1"></i>{{ __('View Invoice') }}
                                                </a>
                                            @endpermission
                                        @endif
                                        @permission('proposal duplicate')
                                            {!! Form::open([
                                                'method' => 'get',
                                                'route' => ['proposal.duplicate', $proposal->id],
                                                'id' => 'duplicate-form-' . $proposal->id,
                                            ]) !!}
                                            <a href="#!" class="show_confirm dropdown-item"
                                                data-text="{{ __('You want to confirm duplicate this proposal. Press Yes to continue or Cancel to go back') }}"
                                                data-confirm-yes="duplicate-form-{{ $proposal->id }}">
                                                <i class="ti ti-copy me-1"></i> {{ __('Duplicate') }}
                                            </a>
                                            {!! Form::close() !!}
                                        @endpermission
                                        @permission('proposal show')
                                            <a href="{{ route('proposal.show', \Crypt::encrypt($proposal->id)) }}"
                                                class="dropdown-item" data-toggle="tooltip"
                                                data-original-title="{{ __('View') }}">
                                                <i class="ti ti-eye me-1"></i> {{ __('View') }}
                                            </a>
                                        @endpermission
                                        @if (module_is_active('ProductService') &&
                                                ($proposal->proposal_module == 'taskly' ? module_is_active('Taskly') : module_is_active('Account')))
                                            @permission('proposal edit')
                                                <a href="{{ route('proposal.edit', \Crypt::encrypt($proposal->id)) }}"
                                                    class="dropdown-item">
                                                    <i class="ti ti-pencil me-1"></i> {{ __('Edit') }}
                                                </a>
                                            @endpermission
                                        @endif
                                        @permission('proposal delete')
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['proposal.destroy', $proposal->id]]) !!}
                                            <a href="#!" class="show_confirm text-danger dropdown-item">
                                                <i class="ti ti-trash me-1"></i> {{ __('Delete') }}
                                            </a>
                                            {!! Form::close() !!}
                                        @endpermission

                                        </div>
                                    </div>
                                    <div class="project-card-content">
                                        <div class="project-content-top">
                                            <div class="user-info  d-flex align-items-center">
                                                @if (Laratrust::hasPermission('proposal show'))
                                                <a
                                                        href="{{ route('proposal.show', \Crypt::encrypt($proposal->id)) }}">{{ \App\Models\Proposal::proposalNumberFormat($proposal->proposal_id) }}</a>
                                                @else
                                                    <a
                                                        href="#">{{ \App\Models\Proposal::proposalNumberFormat($proposal->proposal_id) }}</a>
                                                @endif
                                            </div>
                                            <div class="row align-items-center mt-3">
                                                <div class="col-6">
                                                    <h6 class="mb-0 text-break">{{ currency_format_with_sym($proposal->getTotal()) }}</h6>
                                                    <span class="text-sm text-muted">{{ __('Total Amount') }}</span>
                                                </div>
                                                <div class="col-6">
                                                    <h6 class="mb-0 text-break">{{ currency_format_with_sym($proposal->getTotalTax()) }}
                                                    </h6>
                                                    <span class="text-sm text-muted">{{ __('Total Tax') }}</span>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mt-3">
                                                <div class="col-6 text-break">
                                                    <h6 class="mb-0">{{ company_date_formate($proposal->issue_date) }}</h6>
                                                    <span class="text-sm text-muted">{{ __('Issue Date') }}</span>
                                                </div>
                                                <div class="col-6 text-break">
                                                    <h6 class="mb-0">
                                                        {{ !empty($proposal->send_date) ? company_date_formate($proposal->send_date) : '-' }}
                                                    </h6>
                                                    <span class="text-sm text-muted">{{ __('Send Date') }}</span>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="project-content-bottom d-flex align-items-center justify-content-between gap-2">
                                            <div class="d-flex align-items-center gap-2 user-image">
                                                @if (\Auth::user()->type != 'Client')
                                                    @if (!empty($proposal->customer))
                                                        <div class="user-group pt-2">
                                                            <img alt="image" data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ $proposal->customer->name }}"
                                                                @if ($proposal->customer->avatar) src="{{ get_file($proposal->customer->avatar) }}" @else src="{{ get_file('avatar.png') }}" @endif
                                                                class="rounded-circle " width="25" height="25">
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="comment d-flex align-items-center gap-2">
                                                @permission('proposal show')
                                                <a class="btn btn-sm btn-warning" data-bs-toggle="tooltip"
                                                   href="{{ route('proposal.show', \Crypt::encrypt($proposal->id)) }}"
                                                   data-bs-original-title="{{ __('View') }}">
                                                   <i class="ti ti-eye  text-white"></i>
                                               </a>
                                               @endpermission
                                              @permission('proposal duplicate')
                                                    {!! Form::open([
                                                        'method' => 'get',
                                                        'route' => ['proposal.duplicate', $proposal->id],
                                                        'id' => 'duplicate-form-' . $proposal->id,
                                                    ]) !!}
                                                  <a href="#"
                                                      class="btn btn-sm bg-secondary  show_confirm"
                                                      data-bs-toggle="tooltip" title=""
                                                      data-bs-original-title="{{ __('Duplicate') }}"
                                                      aria-label="Delete"
                                                      data-text="{{ __('You want to confirm duplicate this proposal. Press Yes to continue or Cancel to go back') }}"
                                                      data-confirm-yes="duplicate-form-{{ $proposal->id }}">
                                                      <i class="ti ti-copy text-white"></i>
                                                  </a>
                                                  {{ Form::close() }}
                                          @endpermission
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endisset
                @auth('web')
                    @permission('proposal create')
                        <div class="col-xxl-3 col-xl-4 col-sm-6 col-12">
                            <div class="project-card-inner">
                                <a href="{{ route('proposal.create', 0) }}" class="btn-addnew-project "  data-size="md"
                                    data-title="{{ __('Create New Proposal') }}">
                                    <div class="badge bg-primary proj-add-icon">
                                        <i class="ti ti-plus"></i>
                                    </div>
                                    <h6 class="my-2 text-center">{{ __('New Proposal') }}</h6>
                                    <p class="text-muted text-center">{{ __('Click here to add New Proposal') }}</p>
                                </a>
                            </div>
                        </div>
                    @endpermission
                @endauth
            </div>
            {!! $proposals->links('vendor.pagination.global-pagination') !!}

        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).on("click", ".cp_link", function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('success', '{{ __('Link Copy on Clipboard') }}', 'success')
        });
    </script>
@endpush
