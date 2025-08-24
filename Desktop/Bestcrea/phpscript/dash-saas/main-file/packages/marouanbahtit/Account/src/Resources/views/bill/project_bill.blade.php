
@extends('layouts.main')
@section('page-title')
{{ __('Manage Bill') }}
@endsection
@section('page-breadcrumb')
    {{ __('Bill') }}
@endsection

@section('page-action')
@endsection
@section('script-page')
@push('scripts')
<script>
$(document).ready(function() {
    $('.cp_link').on('click', function() {
        var value = $(this).attr('data-link');
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(value).select();
        document.execCommand("copy");
        $temp.remove();
        toastrs('Success', '{{ __('Link Copy on Clipboard') }}', 'success')
    });
});
</script>
@endpush
@endsection
@section('content')
<div id="retainer-settings" class="row">
    <div class="col-md-3">
        @include('taskly::layouts.finance_tab')
    </div>
    <div class="col-md-9">
        <div class="card retainer">
            <div class="card-header">
                <div class="row">
                    <div class="col-11">
                        <h5 class="">
                            {{ __('Bill') }}
                        </h5>
                    </div>
                    @permission('bill create')
                        <div class=" col-1 text-end">
                            <a href="{{ route('bills.create', ['cid' => 0,'type' => 'project', 'project_id' => $project->id ,'redirect_route' =>route('projects.bill', $project->id)]) }}
                                    " class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('Create') }}">
                                <i class="ti ti-plus"></i>
                            </a>
                        </div>
                    @endpermission
                </div>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table mb-0 pc-dt-simple" id="assets">
                        <thead>
                            <tr>
                                <th> {{ __('Bill') }}</th>
                                @if (!\Auth::user()->type != 'vendor')
                                    <th> {{ __('Vendor') }}</th>
                                @endif
                                <th> {{ __('Account Type') }}</th>
                                <th> {{ __('Bill Date') }}</th>
                                <th> {{ __('Due Date') }}</th>
                                <th>{{ __('Due Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                @if (Laratrust::hasPermission('bill edit') ||
                                        Laratrust::hasPermission('bill delete') ||
                                        Laratrust::hasPermission('bill show') ||
                                        Laratrust::hasPermission('bill duplicate'))
                                    <th width="10%"> {{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bills as $bill)
                                <tr class="font-style">
                                    <td class="Id">
                                        @permission('bill show')
                                            <a href="{{ route('bill.show', \Crypt::encrypt($bill->id)) }}"
                                                class="btn btn-outline-primary">{{ Workdo\Account\Entities\Bill::billNumberFormat($bill->bill_id) }}</a>
                                        @else
                                            <a
                                                class="btn btn-outline-primary">{{ Workdo\Account\Entities\Bill::billNumberFormat($bill->bill_id) }}</a>
                                @endif
                                </td>

                                @if (!\Auth::user()->type != 'vendor')
                                    <td> {{ !empty($bill->vendor_name) ? $bill->vendor_name : '' }}</td>
                                @endif
                                <td>{{ $bill->account_type }}</td>
                                <td>{{ company_date_formate($bill->bill_date) }}</td>
                                <td>
                                    @if ($bill->due_date < date('Y-m-d'))
                                        <p class="text-danger">
                                            {{ company_date_formate($bill->due_date) }}</p>
                                    @else
                                        {{ company_date_formate($bill->due_date) }}
                                    @endif
                                </td>
                                <td>{{ currency_format_with_sym($bill->getDue()) }}</td>
                                <td>
                                    @if ($bill->status == 0)
                                        <span
                                            class="badge bg-info p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                    @elseif($bill->status == 1)
                                        <span
                                            class="badge bg-primary p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                    @elseif($bill->status == 2)
                                        <span
                                            class="badge bg-secondary p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                    @elseif($bill->status == 3)
                                        <span
                                            class="badge bg-warning p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                    @elseif($bill->status == 4)
                                        <span
                                            class="badge bg-success p-2 px-3 bill_status">{{ __(Workdo\Account\Entities\Bill::$statues[$bill->status]) }}</span>
                                    @endif
                                </td>
                                @if (Laratrust::hasPermission('bill edit') ||
                                        Laratrust::hasPermission('bill delete') ||
                                        Laratrust::hasPermission('bill show') ||
                                        Laratrust::hasPermission('bill duplicate'))
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn  me-2">
                                                <a href="#" class="btn btn-sm bg-primary  align-items-center cp_link"
                                                    data-link="{{ route('pay.billpay', \Illuminate\Support\Facades\Crypt::encrypt($bill->id)) }}"
                                                    data-bs-toggle="tooltip" title="{{ __('Copy') }}"
                                                    data-original-title="{{ __('Click to copy Bill link') }}">
                                                    <i class="ti ti-file text-white"></i>
                                                </a>
                                            </div>
                                            @permission('bill duplicate')
                                                <div class="action-btn  me-2">
                                                    {!! Form::open([
                                                        'method' => 'get',
                                                        'route' => ['bill.duplicate', $bill->id],
                                                        'id' => 'duplicate-form-' . $bill->id,
                                                    ]) !!}
                                                    <a class="mx-3 btn btn-sm bg-secondary align-items-center bs-pass-para show_confirm"
                                                        data-bs-toggle="tooltip" title=""
                                                        data-bs-original-title="{{ __('Duplicate') }}" aria-label="Delete"
                                                        data-text="{{ __('You want to confirm duplicate this invoice. Press Yes to continue or Cancel to go back') }}"
                                                        data-confirm-yes="duplicate-form-{{ $bill->id }}">
                                                        <i class="ti ti-copy text-white text-white"></i>
                                                    </a>
                                                    {{ Form::close() }}
                                                </div>
                                            @endpermission
                                            @permission('bill show')
                                                <div class="action-btn  me-2">
                                                    <a href="{{ route('bill.show', \Crypt::encrypt($bill->id)) }}"
                                                        class="mx-3 btn bg-warning btn-sm align-items-center" data-bs-toggle="tooltip"
                                                        title="{{ __('Show') }}" data-original-title="{{ __('Detail') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                            @endpermission
                                            @if (module_is_active('ProductService') && $bill->bill_module == 'taskly'
                                                    ? module_is_active('Taskly')
                                                    : module_is_active('Account'))
                                                @permission('bill edit')
                                                    <div class="action-btn  me-2">
                                                        <a href="{{ route('bill.edit', \Crypt::encrypt($bill->id)) }}"
                                                            class="mx-3 btn bg-info btn-sm align-items-center" data-bs-toggle="tooltip"
                                                            title="Edit" data-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endpermission
                                            @endif
                                            @permission('bill delete')
                                                <div class="action-btn">
                                                    {{ Form::open(['route' => ['bill.destroy', $bill->id], 'class' => 'm-0']) }}
                                                    @method('DELETE')
                                                    <a class="mx-3 btn bg-danger btn-sm  align-items-center bs-pass-para show_confirm"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete" data-confirm="{{ __('Are You Sure?') }}"
                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="delete-form-{{ $bill->id }}"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                    {{ Form::close() }}
                                                </div>
                                            @endpermission
                                        </span>
                                    </td>
                                @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

