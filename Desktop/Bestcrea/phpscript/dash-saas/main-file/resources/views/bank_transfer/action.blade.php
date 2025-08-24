{{ Form::model($bank_transfer_payment, ['route' => ['bank-transfer-request.update', $bank_transfer_payment->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-bordered ">
                <tr role="row">
                    <th>{{ __('Order Id') }}</th>
                    <td>{{ $bank_transfer_payment->order_id }}</td>
                </tr>
                <tr>
                    <th>{{__('status')}}</th>
                    <td>
                        @if($bank_transfer_payment->status == 'Approved')
                            <span class="badge bg-success p-2 px-3 text-white">{{ucfirst($bank_transfer_payment->status)}}</span>
                        @elseif($bank_transfer_payment->status == 'Pending')
                            <span class="badge bg-warning p-2 px-3 text-white">{{ucfirst($bank_transfer_payment->status)}}</span>
                        @else
                            <span class="badge bg-danger p-2 px-3 text-white">{{ucfirst($bank_transfer_payment->status)}}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Appplied On') }}</th>
                    <td>{{ company_datetime_formate($bank_transfer_payment->created_at)}}</td>
                </tr>
                <tr>
                    <th>{{__('Name')}}</th>
                    <td>{{ !empty($bank_transfer_payment->User) ? $bank_transfer_payment->User->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{__('Price')}}</th>
                    <td>{{ super_currency_format_with_sym($bank_transfer_payment->price) }}</td>
                </tr>
                <tr>
                    <th>{{__('Request')}}</th>
                    @php
                        $requests = json_decode($bank_transfer_payment->request);
                        $modules = explode(',',$requests->user_module_input);

                    @endphp

                    <td>
                            <p><span class="text-primary">{{ __('Workspace: ')}}</span>{{ $requests->workspace_counter_input }}</p>
                            <p><span class="text-primary">{{ __('Users: ')}}</span>{{ $requests->user_counter_input }}</p>
                            <p><span class="text-primary">{{ __('Time Period: ')}}</span>{{ $requests->time_period }}</p>
                            <div class="">
                                <span class="text-primary">{{ __('Add-on: ')}}</span>
                                @foreach ($modules as $module)
                                    @if($module)
                                        <a href="{{ route('software.details',$module) }}" target="_new" class="btn btn-sm btn-warning me-2">{{ $module}}</a>
                                    @endif
                                @endforeach
                            </div>
                    </td>
                </tr>
                <tr>
                    <th>{{__('Attachment')}}</th>
                    <td>
                        @if (!empty($bank_transfer_payment->attachment) && (check_file($bank_transfer_payment->attachment)))
                            <div class="action-btn me-2">
                                <a class="mx-3 btn btn-sm align-items-center bg-primary" href="{{ get_file($bank_transfer_payment->attachment) }}" download>
                                    <i class="ti ti-download text-white"></i>
                                </a>
                            </div>
                            <div class="action-btn">
                                <a class="mx-3 btn btn-sm align-items-center bg-secondary" href="{{ get_file($bank_transfer_payment->attachment) }}" target="_blank"  >
                                    <i class="ti ti-crosshair text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Preview') }}"></i>
                                </a>
                            </div>
                        @else
                            {{ __('Not Found')}}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @if ($bank_transfer_payment->status == 'Pending')
        <div class="modal-footer">
            <a href=""></a>
            <input type="submit" value="{{ 'Reject' }}" class="btn btn-danger" name="status">
            <input type="submit" value="{{ 'Approved' }}" class="btn btn-success" name="status">
        </div>
    @endif
{{ Form::close() }}
