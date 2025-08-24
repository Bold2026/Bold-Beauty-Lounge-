{{Form::model($bank_transfer_payment,array('route' => array('invoice.bank.request.update', $bank_transfer_payment->id), 'method' => 'POST')) }}
    <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-bordered ">
                <tr>
                    <th>{{__('Number')}}</th>
                    <td>{{$invoice_id}}</td>
                </tr>
                <tr role="row">
                    <th>{{ __('Order Id') }}</th>
                    <td>{{ $bank_transfer_payment->order_id }}</td>
                </tr>
                <tr>
                    <th>{{__('Status')}}</th>
                    <td>
                        <span class="badge bg-warning p-2 px-3 text-white">{{ucfirst($bank_transfer_payment->status)}}</span>
                    </td>
                </tr>
                <tr>
                    <th>{{__('Price')}}</th>
                    <td>{{ currency_format_with_sym($bank_transfer_payment->price)}}</td>
                </tr>
                <tr>
                    <th>{{__('Payment Type')}}</th>
                    <td>{{('Bank transfer')}}</td>
                </tr>
                <tr>
                    <th>{{ __('Payment Date') }}</th>
                    <td>{{ company_date_formate($bank_transfer_payment->created_at)}}</td>
                </tr>
                <tr>
                    <th>{{ __('Bank Detail') }}</th>
                    <td>{!! company_setting('bank_number') !!}</td>
                </tr>
                <tr>
                    <th>{{__('Attachment')}}</th>
                    <td class="d-flex">
                        @if (!empty($bank_transfer_payment->attachment) && (check_file($bank_transfer_payment->attachment)))
                            <div class="action-btn bg-primary me-2">
                                <a class="btn btn-sm align-items-center" href="{{ get_file($bank_transfer_payment->attachment) }}" download>
                                    <i class="ti ti-download text-white"></i>
                                </a>
                            </div>
                            <div class="action-btn bg-secondary me-2">
                                <a class="btn btn-sm align-items-center" href="{{ get_file($bank_transfer_payment->attachment) }}" target="_blank"  >
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
            <input type="submit" value="{{ __('Approved') }}" class="btn btn-success" name="status">
            <input type="submit" value="{{ __('Reject') }}" class="btn btn-danger" name="status">
        </div>
    @endif
{{ Form::close() }}
