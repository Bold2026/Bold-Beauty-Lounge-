@if($productService == [])
<div class="form-group col-md-6">
    {{ Form::label('sale_chartaccount_id', __('Income Account'),['class'=>'form-label']) }}<x-required></x-required>
    <select name="sale_chartaccount_id" class="form-control" required>
        <option value="">Select Chart of Account</option>
        @foreach ($incomeChartAccounts as $typeName => $subtypes)
            <optgroup label="{{ $typeName }}">
                @foreach ($subtypes as $subtypeId => $subtypeData)
                    <option disabled style="color: #000; font-weight: bold;">{{ $subtypeData['account_name'] }}</option>
                    @foreach ($subtypeData['chart_of_accounts'] as $chartOfAccount)
                        <option value="{{ $chartOfAccount['id'] }}">
                            &nbsp;&nbsp;&nbsp;{{ $chartOfAccount['account_name'] }}
                        </option>
                        @foreach ($subtypeData['subAccounts'] as $subAccount)
                            @if ($chartOfAccount['id'] == $subAccount['parent'])
                            <option value="{{ $subAccount['id'] }}" class="ms-5"> &nbsp; &nbsp;&nbsp;&nbsp; {{' - '. $subAccount['account_name'] }}</option>
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>

<div class="form-group col-md-6">
    {{ Form::label('expense_chartaccount_id', __('Expense Account'),['class'=>'form-label']) }}<x-required></x-required>
    <select name="expense_chartaccount_id" class="form-control" required>
        <option value="">Select Chart of Account</option>
        @foreach ($expenseChartAccounts as $typeName => $subtypes)
            <optgroup label="{{ $typeName }}">
                @foreach ($subtypes as $subtypeId => $subtypeData)
                    <option disabled style="color: #000; font-weight: bold;">{{ $subtypeData['account_name'] }}</option>
                    @foreach ($subtypeData['chart_of_accounts'] as $chartOfAccount)
                        <option value="{{ $chartOfAccount['id'] }}">
                            &nbsp;&nbsp;&nbsp;{{ $chartOfAccount['account_name'] }}
                        </option>
                        @foreach ($subtypeData['subAccounts'] as $subAccount)
                            @if ($chartOfAccount['id'] == $subAccount['parent'])
                            <option value="{{ $subAccount['id'] }}" class="ms-5"> &nbsp; &nbsp;&nbsp;&nbsp; {{' - '. $subAccount['account_name'] }}</option>
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>

@else
<div class="form-group col-md-6">
    {{ Form::label('sale_chartaccount_id', __('Income Account'),['class'=>'form-label']) }}<x-required></x-required>
    <select name="sale_chartaccount_id" class="form-control" required>
        <option value="">Select Chart of Account</option>
        @foreach ($incomeChartAccounts as $typeName => $subtypes)
            <optgroup label="{{ $typeName }}">
                @foreach ($subtypes as $subtypeId => $subtypeData)
                    <option disabled style="color: #000; font-weight: bold;">{{ $subtypeData['account_name'] }}</option>
                    @foreach ($subtypeData['chart_of_accounts'] as $chartOfAccount)
                        <option value="{{ $chartOfAccount['id'] }}" {{ $productService->sale_chartaccount_id == $chartOfAccount['id'] ? 'selected' : ''}}>
                            &nbsp;&nbsp;&nbsp;{{ $chartOfAccount['account_name'] }}
                        </option>
                        @foreach ($subtypeData['subAccounts'] as $subAccount)
                            @if ($chartOfAccount['id'] == $subAccount['parent'])
                            <option value="{{ $subAccount['id'] }}" class="ms-5" {{ $productService->sale_chartaccount_id == $subAccount['id'] ? 'selected' : ''}}> &nbsp; &nbsp;&nbsp;&nbsp; {{' - '. $subAccount['account_name'] }}</option>
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>

<div class="form-group col-md-6">
    {{ Form::label('expense_chartaccount_id', __('Expense Account'),['class'=>'form-label']) }}<x-required></x-required>
    <select name="expense_chartaccount_id" class="form-control" required>
        <option value="">Select Chart of Account</option>
        @foreach ($expenseChartAccounts as $typeName => $subtypes)
            <optgroup label="{{ $typeName }}">
                @foreach ($subtypes as $subtypeId => $subtypeData)
                    <option disabled style="color: #000; font-weight: bold;">{{ $subtypeData['account_name'] }}</option>
                    @foreach ($subtypeData['chart_of_accounts'] as $chartOfAccount)
                        <option value="{{ $chartOfAccount['id'] }}" {{ $productService->expense_chartaccount_id == $chartOfAccount['id'] ? 'selected' : ''}}>
                            &nbsp;&nbsp;&nbsp;{{ $chartOfAccount['account_name'] }}
                        </option>
                        @foreach ($subtypeData['subAccounts'] as $subAccount)
                            @if ($chartOfAccount['id'] == $subAccount['parent'])
                            <option value="{{ $subAccount['id'] }}" class="ms-5" {{ $productService->expense_chartaccount_id == $subAccount['id'] ? 'selected' : ''}}> &nbsp; &nbsp;&nbsp;&nbsp; {{' - '. $subAccount['account_name'] }}</option>
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>

@endif
