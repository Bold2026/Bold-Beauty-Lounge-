@extends('layouts.main')
@section('page-title')
    {{ __('Manage Purchase') }}
@endsection
@section('page-breadcrumb')
    {{ __('Purchase') }}
@endsection
@push('scripts')
    <script>
        $(document).on("click",".cp_link",function() {
            var value = $(this).attr('data-link');
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val(value).select();
                document.execCommand("copy");
                $temp.remove();
                toastrs('success', '{{__('Link Copy on Clipboard')}}', 'success')
        });
    </script>
@endpush
@section('page-action')
<div class="d-flex">
    @stack('addButtonHook')
    <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip"title="{{ __('List View') }}">
        <i class="ti ti-list text-white"></i>
    </a>
    @if(module_is_active('ProductService'))
    <a href="{{ route('category.index') }}"data-size="md"  class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip"data-title="{{__('Setup')}}" title="{{__('Setup')}}"><i class="ti ti-settings"></i></a>
        @permission('purchase create')
            <a href="{{ route('purchases.create',0) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    @endif
</div>
@endsection
@section('content')
<div class="row">
<div class="col-sm-12">
    <div class="row mb-4 project-wrp d-flex">
        @isset($purchases)
        @foreach ($purchases as $purchase)
                <div class="col-xxl-3 col-xl-4 col-sm-6 col-12 ">
                    <div class="project-card">
                        <div class="project-card-inner">
                            <div class="project-card-header d-flex justify-content-between">
                                @if($purchase->status == 0)
                                    <span class="purchase_status badge bg-info p-2 px-3">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                @elseif($purchase->status == 1)
                                    <span class="purchase_status badge bg-primary p-2 px-3">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                @elseif($purchase->status == 2)
                                    <span class="purchase_status badge bg-secondary p-2 px-3">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                @elseif($purchase->status == 3)
                                    <span class="purchase_status badge bg-warning p-2 px-3">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                @elseif($purchase->status == 4)
                                    <span class="purchase_status badge bg-success p-2 px-3">{{ __(App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                @endif

                                    <button type="button"
                                        class="btn btn-light dropdown-toggle d-flex align-items-center justify-content-center"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ti ti-dots-vertical text-black"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-end pointer">
                                        <a href="#"  data-link="{{route('purchases.link.copy', \Crypt::encrypt($purchase->id)) }}" class="dropdown-item cp_link" >
                                            <i class="ti ti-file me-1"></i> {{__('Click To Copy Purchase Link')}}
                                        </a>
                                        @permission('purchase show')
                                            <a href="{{ route('purchases.show',\Crypt::encrypt($purchase->id)) }}" data-size="md"class="dropdown-item" data-bs-toggle="tooltip"  data-title="{{__('Details')}}">
                                                <i class="ti ti-eye me-1"></i> {{__('View')}}
                                            </a>
                                        @endpermission
                                        @if($purchase->status != 4)
                                            @permission('purchase edit')
                                            <a href="{{ route('purchases.edit',\Crypt::encrypt($purchase->id)) }}" class="dropdown-item" data-bs-toggle="tooltip" data-title="{{__('Edit Purchase')}}"><i class="ti ti-pencil me-1"></i> {{__('Edit')}}</a>
                                            @endpermission

                                            @permission('purchase delete')
                                                {!! Form::open(['method' => 'DELETE', 'route' =>['purchases.destroy', $purchase->id]]) !!}
                                                    <a href="#!" class="text-danger dropdown-item show_confirm" data-bs-toggle="tooltip">
                                                        <i class="ti ti-trash me-1"></i> {{ __('Delete') }}
                                                    </a>
                                                {!! Form::close() !!}
                                            @endpermission
                                        @endif
                                </div>
                            </div>
                            <div class="project-card-content">
                                <div class="project-content-top">
                                    <div class="user-info  d-flex align-items-center">
                                        <a href="{{ route('purchases.show',\Crypt::encrypt($purchase->id)) }}">{{ App\Models\Purchase::purchaseNumberFormat($purchase->purchase_id) }}</a>
                                    </div>
                                    <div class="row align-items-center mt-3">
                                        <div class="col-6">
                                            <h6 class="mb-0 text-break">{{currency_format_with_sym($purchase->getTotal())}}</h6>
                                            <span class="text-sm text-muted">{{__('Total Amount')}}</span>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="mb-0 text-break">{{currency_format_with_sym($purchase->getDue())}}</h6>
                                            <span class="text-sm text-muted">{{__('Due Amount')}}</span>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mt-3">
                                        <div class="col-6">
                                            <h6 class="mb-0 text-break">{{currency_format_with_sym($purchase->getTotalTax())}}</h6>
                                            <span class="text-sm text-muted">{{__('Total Tax')}}</span>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="mb-0 text-break">{{company_date_formate($purchase->purchase_date)}}</h6>
                                            <span class="text-sm text-muted">{{__('Purchase Date')}}</span>
                                         </div>
                                    </div>
                                </div>
                                <div
                                    class="project-content-bottom d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2 user-image">
                                        @if (\Auth::user()->type != 'vendor')
                                            <div class="user-group pt-2">
                                                    <img alt="image" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    @if ($purchase->user != NUll) title="{{ $purchase->user->name }}" @else title="{{$purchase->vender_name}}"@endif
                                                        @if ($purchase->user != NUll) src="{{ get_file($purchase->user->avatar) }}" @else src="{{ get_file('uploads/users-avatar/avatar.png') }}" @endif
                                                        class="rounded-circle " width="25" height="25">
                                            </div>
                                        @endif
                                    </div>
                                    <div class="comment d-flex align-items-center gap-2">
                                        @permission('purchase show')
                                            <a class="btn btn-sm btn-warning" href="{{ route('purchases.show',\Crypt::encrypt($purchase->id)) }}" data-size="md"class="dropdown-item" data-bs-toggle="tooltip"  title="{{__('View')}}">
                                                <i class="ti ti-eye text-white"></i>
                                            </a>
                                        @endpermission
                                        @if($purchase->status != 4)
                                            @permission('purchase edit')
                                                <a class="btn btn-sm btn-info" href="{{ route('purchases.edit',\Crypt::encrypt($purchase->id)) }}" class="dropdown-item" data-bs-toggle="tooltip" title="{{__('Edit')}}"><i class="ti ti-pencil"></i></a>
                                            @endpermission
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endisset
        @auth('web')
            @permission('purchase create')
                <div class="col-xxl-3 col-xl-4 col-sm-6 col-12 ">
                    <div class="project-card-inner">
                        <a href="{{ route('purchases.create', 0) }}" class="btn-addnew-project " data-size="md"
                            data-title="{{ __('Create New Purchase') }}">
                            <div class="badge bg-primary proj-add-icon">
                                <i class="ti ti-plus"></i>
                            </div>
                            <h6 class="my-2 text-center">{{ __('New Purchase') }}</h6>
                            <p class="text-muted text-center">{{ __('Click here to add New Purchase') }}</p>
                        </a>
                    </div>
                </div>
            @endpermission
        @endauth
    </div>
</div>
{!! $purchases->links('vendor.pagination.global-pagination') !!}
</div>
@endsection


