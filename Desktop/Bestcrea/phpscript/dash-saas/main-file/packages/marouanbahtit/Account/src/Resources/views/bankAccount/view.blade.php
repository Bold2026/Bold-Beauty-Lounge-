<div class="modal-body">
    <div class="row">
        <div class="col-lg-12 table-responsive">
            <table class="table modal-table">
                <tbody>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <td>{{ !empty($bankAccount->bank_name) ? $bankAccount->bank_name : '--' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Bank') }}</th>
                        <td>{{ !empty($bankAccount->holder_name) ? $bankAccount->holder_name : '--' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Account Number') }}</th>
                        <td>{{ !empty($bankAccount->account_number) ? $bankAccount->account_number : '--' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Contact Number') }}</th>
                        <td>{{ !empty($bankAccount->contact_number) ? $bankAccount->contact_number : '--' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Bank Branch') }}</th>
                        <td>{{ !empty($bankAccount->bank_branch) ? $bankAccount->bank_branch : '--' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('SWIFT') }}</th>
                        <td>{{ !empty($bankAccount->swift) ? $bankAccount->swift : '--' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Payment Gateway') }}</th>
                        <td>{{ !empty($bankAccount->payment_name) ? $bankAccount->payment_name : '--' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Bank Address') }}</th>
                        <td>{{ !empty($bankAccount->bank_address) ? $bankAccount->bank_address : '--' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
