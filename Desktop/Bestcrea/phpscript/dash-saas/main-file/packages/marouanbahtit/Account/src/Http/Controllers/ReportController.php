<?php

namespace Workdo\Account\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\Purchase;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\BankAccount;
use Workdo\Account\Entities\Bill;
use Workdo\Account\Entities\BillPayment;
use Workdo\Account\Entities\BillProduct;
use Workdo\Account\Entities\Customer;
use Workdo\Account\Entities\Payment;
use Workdo\Account\Entities\Revenue;
use Workdo\Account\Entities\StockReport;
use Workdo\Account\Entities\Vender;
use Workdo\Hrm\Entities\PaySlip;
use Workdo\Training\Entities\Training;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function accountStatement(Request $request)
    {
        if (Auth::user()->isAbleTo('report statement manage')) {

            $filter['account'] = __('All');
            $filter['type'] = __('Revenue');
            $reportData['revenues'] = '';
            $reportData['payments'] = '';
            $reportData['revenueAccounts'] = '';
            $reportData['paymentAccounts'] = '';

            $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');

            $types = [
                'revenue' => __('Revenue'),
                'payment' => __('Payment'),
            ];

            if (!isset($request->type)) {
                $request->type = 'payment';
            }

            if ($request->type == 'revenue' || !isset($request->type)) {

                $revenueAccounts = Revenue::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'revenues.account_id', '=', 'bank_accounts.id')->groupBy('revenues.account_id')->selectRaw('sum(amount) as total')->where('revenues.created_by', '=', creatorId());

                $revenues = Revenue::where('revenues.created_by', '=', creatorId())->orderBy('id', 'desc');
            }

            if ($request->type == 'payment') {
                $paymentAccounts = Payment::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'payments.account_id', '=', 'bank_accounts.id')->groupBy('payments.account_id')->selectRaw('sum(amount) as total')->where('payments.workspace', '=', getActiveWorkSpace());
                $payments = Payment::where('payments.workspace', '=', getActiveWorkSpace())->orderBy('id', 'desc');
            }

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-m'));
                $end = strtotime(date('Y-m', strtotime("-5 month")));
            }
            $currentdate = $start;
            while ($currentdate <= $end) {
                $data['month'] = date('m', $currentdate);
                $data['year'] = date('Y', $currentdate);

                if ($request->type == 'payment') {
                    $paymentAccounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('payments.workspace', '=', getActiveWorkSpace());
                        }
                    );
                }
                if ($request->type == 'revenue') {
                    $revenueAccounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('revenues.workspace', '=', getActiveWorkSpace());
                        }
                    );
                }

                $currentdate = strtotime('+1 month', $currentdate);
            }
            if (!empty($request->account)) {
                if ($request->type == 'revenue') {
                    $revenues->where('account_id', $request->account);
                    $revenues->where('revenues.workspace', '=', getActiveWorkSpace());
                    $revenueAccounts->where('account_id', $request->account);
                    $revenueAccounts->where('revenues.workspace', '=', getActiveWorkSpace());
                }

                if ($request->type == 'payment') {
                    $payments->where('account_id', $request->account);
                    $payments->where('payments.workspace', '=', getActiveWorkSpace());

                    $paymentAccounts->where('account_id', $request->account);
                    $paymentAccounts->where('payments.workspace', '=', getActiveWorkSpace());
                }

                $bankAccount = BankAccount::find($request->account);
                $filter['account'] = !empty($bankAccount) ? $bankAccount->holder_name . ' - ' . $bankAccount->bank_name : '';
                if ($bankAccount->holder_name == 'Cash') {
                    $filter['account'] = 'Cash';
                }
            }

            if ($request->type == 'revenue') {
                $reportData['revenues'] = $revenues->get();

                $revenueAccounts->where('revenues.workspace', '=', getActiveWorkSpace());
                $reportData['revenueAccounts'] = $revenueAccounts->get();
                $filter['type'] = __('Revenue');
            }

            if ($request->type == 'payment') {
                $reportData['payments'] = $payments->get();

                $paymentAccounts->where('payments.workspace', '=', getActiveWorkSpace());
                $reportData['paymentAccounts'] = $paymentAccounts->get();
                $filter['type'] = __('Payment');
            }

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange'] = date('M-Y', $end);

            return view('account::report.statement_report', compact('reportData', 'account', 'types', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function incomeSummary(Request $request)
    {

        if (\Auth::user()->isAbleTo('report income manage')) {
            $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');

            $customer = Customer::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
            $category = [];
            if (module_is_active('ProductService')) {
                $category = \Workdo\ProductService\Entities\Category::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->where('type', '=', 1)->get()->pluck('name', 'id');
            }

            $data['monthList'] = $month = $this->yearMonth();
            $data['yearList'] = $this->yearList();
            $filter['category'] = __('All');
            $filter['customer'] = __('All');

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            // ------------------------------REVENUE INCOME-----------------------------------
            $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')->leftjoin('categories', 'revenues.category_id', '=', 'categories.id')->where('categories.type', '=', 1);
            $incomes->where('revenues.workspace', '=', getActiveWorkSpace());
            $incomes->whereRAW('YEAR(date) =?', [$year]);
            if (!empty($request->category)) {
                $incomes->where('category_id', '=', $request->category);
                $cat = [];
                if (module_is_active('ProductService')) {
                    $cat = \Workdo\ProductService\Entities\Category::find($request->category);
                }
                $filter['category'] = !empty($cat) ? $cat->name : '';
            }

            if (!empty($request->customer)) {
                $incomes->where('customer_id', '=', $request->customer);
                $cust = Customer::find($request->customer);
                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }
            $incomes->groupBy('month', 'year', 'category_id');
            $incomes = $incomes->get();

            $tmpArray = [];
            foreach ($incomes as $income) {
                $tmpArray[$income->category_id][$income->month] = $income->amount;
            }
            $array = [];
            foreach ($tmpArray as $cat_id => $record) {
                $tmp = [];
                $tmp['category'] = [];
                if (module_is_active('ProductService')) {
                    $tmp['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $tmp['data'] = [];
                for ($i = 1; $i <= 12; $i++) {
                    $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
                }
                $array[] = $tmp;
            }

            $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $incomesData->where('revenues.workspace', '=', getActiveWorkSpace());
            $incomesData->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $incomesData->where('category_id', '=', $request->category);
            }
            if (!empty($request->customer)) {
                $incomesData->where('customer_id', '=', $request->customer);
            }
            $incomesData->groupBy('month', 'year');
            $incomesData = $incomesData->get();

            $incomeArr = [];
            foreach ($incomesData as $k => $incomeData) {
                $incomeArr[$incomeData->month] = $incomeData->amount;
            }
            for ($i = 1; $i <= 12; $i++) {
                $incomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
            }

            //---------------------------INVOICE INCOME-----------------------------------------------

            $invoices = Invoice::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0)->where('invoice_module', '!=', 'taskly');
            $invoices->whereRAW('YEAR(send_date) =?', [$year]);
            if (!empty($request->customer)) {
                $invoices->where('user_id', '=', $cust->user_id);
            }

            if (!empty($request->category)) {
                $invoices->where('category_id', '=', $request->category);
            }

            $invoices = $invoices->get();
            $invoiceTmpArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
            }

            $invoiceArray = [];
            foreach ($invoiceTmpArray as $cat_id => $record) {
                $invoice = [];
                $invoice['category'] = [];
                if (module_is_active('ProductService')) {
                    $invoice['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->where('type', '1')->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->where('type', '1')->first()->name : '';
                }
                $invoice['data'] = [];
                for ($i = 1; $i <= 12; $i++) {
                    $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $invoiceArray[] = $invoice;
            }

            $invoiceTotalArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            for ($i = 1; $i <= 12; $i++) {
                $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $chartIncomeArr = array_map(
                function () {
                    return array_sum(func_get_args());
                }, $incomeTotal, $invoiceTotal
            );
            $data['chartIncomeArr'] = $chartIncomeArr;
            $data['incomeArr'] = $array;
            $data['invoiceArray'] = $invoiceArray;
            $data['account'] = $account;
            $data['customer'] = $customer;
            $data['category'] = $category;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange'] = 'Dec-' . $year;
            return view('account::report.income_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
    public function expenseSummary(Request $request)
    {

        if (Auth::user()->isAbleTo('report expense manage')) {
            $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');

            $vendor = Vender::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');

            $category = [];
            if (module_is_active('ProductService')) {
                $category = \Workdo\ProductService\Entities\Category::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->where('type', '=', 2)->get()->pluck('name', 'id');
            }

            $data['monthList'] = $month = $this->yearMonth();
            $data['yearList'] = $this->yearList();
            $filter['category'] = __('All');
            $filter['vendor'] = __('All');

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            //   -----------------------------------------PAYMENT EXPENSE ------------------------------------------------------------
            $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')->leftjoin('categories', 'payments.category_id', '=', 'categories.id')->where('categories.type', '=', 2);
            $expenses->where('payments.workspace', '=', getActiveWorkSpace());
            $expenses->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $expenses->where('category_id', '=', $request->category);
                $cat = [];
                if (module_is_active('ProductService')) {
                    $cat = \Workdo\ProductService\Entities\Category::find($request->category);
                }
                $filter['category'] = !empty($cat) ? $cat->name : '';
            }
            if (!empty($request->vendor)) {
                $expenses->where('vendor_id', '=', $request->vendor);

                $vend = Vender::find($request->vendor);
                $filter['vendor'] = !empty($vend) ? $vend->name : '';
            }

            $expenses->groupBy('month', 'year', 'category_id');
            $expenses = $expenses->get();
            $tmpArray = [];
            foreach ($expenses as $expense) {
                $tmpArray[$expense->category_id][$expense->month] = $expense->amount;
            }
            $array = [];
            foreach ($tmpArray as $cat_id => $record) {
                $tmp = [];
                $tmp['category'] = [];
                if (module_is_active('ProductService')) {
                    $tmp['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $tmp['data'] = [];
                for ($i = 1; $i <= 12; $i++) {
                    $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
                }
                $array[] = $tmp;
            }
            $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $expensesData->where('payments.workspace', '=', getActiveWorkSpace());
            $expensesData->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $expensesData->where('category_id', '=', $request->category);
            }
            if (!empty($request->vendor)) {
                $expensesData->where('vendor_id', '=', $request->vendor);
            }
            $expensesData->groupBy('month', 'year');
            $expensesData = $expensesData->get();

            $expenseArr = [];
            foreach ($expensesData as $k => $expenseData) {
                $expenseArr[$expenseData->month] = $expenseData->amount;
            }
            for ($i = 1; $i <= 12; $i++) {
                $expenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
            }

            //     ------------------------------------BILL EXPENSE----------------------------------------------------

            $bills = Bill::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0);
            $bills->whereRAW('YEAR(send_date) =?', [$year]);

            if (!empty($request->vendor)) {
                $bills->where('vendor_id', '=', $request->vendor);
            }

            if (!empty($request->category)) {
                $bills->where('category_id', '=', $request->category);
            }
            $bills = $bills->get();
            $billTmpArray = [];
            foreach ($bills as $bill) {
                $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
            }

            $billArray = [];
            foreach ($billTmpArray as $cat_id => $record) {

                $bill = [];
                $bill['category'] = [];
                if (module_is_active('ProductService')) {
                    $bill['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $bill['data'] = [];
                for ($i = 1; $i <= 12; $i++) {

                    $bill['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $billArray[] = $bill;
            }
            $billTotalArray = [];
            foreach ($bills as $bill) {
                $billTotalArray[$bill->month][] = $bill->getTotal();
            }
            for ($i = 1; $i <= 12; $i++) {
                $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
            }

            //     ------------------------------------Purchase EXPENSE----------------------------------------------------

            $purchases = Purchase::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,purchase_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0);
            $purchases->whereRAW('YEAR(send_date) =?', [$year]);

            if (!empty($request->vendor)) {
                $purchases->where('vender_id', '=', $request->vendor);
            }

            if (!empty($request->category)) {
                $purchases->where('category_id', '=', $request->category);
            }
            $purchases = $purchases->get();
            $purchaseTmpArray = [];
            foreach ($purchases as $purchase) {
                $purchaseTmpArray[$purchase->category_id][$purchase->month][] = $purchase->getTotal();
            }

            $purchaseArray = [];
            foreach ($purchaseTmpArray as $cat_id => $record) {

                $purchase = [];
                $purchase['category'] = [];
                if (module_is_active('ProductService')) {
                    $purchase['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $purchase['data'] = [];
                for ($i = 1; $i <= 12; $i++) {

                    $purchase['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $purchaseArray[] = $purchase;
            }
            $purchaseTotalArray = [];
            foreach ($purchases as $purchase) {
                $purchaseTotalArray[$purchase->month][] = $purchase->getTotal();
            }
            for ($i = 1; $i <= 12; $i++) {
                $purchaseTotal[] = array_key_exists($i, $purchaseTotalArray) ? array_sum($purchaseTotalArray[$i]) : 0;
            }

            $salTotal = [];
            if (module_is_active('Hrm') && empty($request->vendor)) {
                // ---------------------------------------------Employee Salary EXPENSE----------------------------------------------

                $employees = PaySlip::selectRaw('SUM(net_payble) as total_salary, SUBSTRING(salary_month, 1, 4) as year, SUBSTRING(salary_month, 6, 2) as month')
                    ->where('salary_month', 'like', $year . '-%')
                    ->where('status', 1)
                    ->groupBy(DB::raw('SUBSTRING(salary_month, 1, 4), SUBSTRING(salary_month, 6, 2)'));

                $employees = $employees->get();

                $employeess = [];
                foreach ($employees as $employee) {
                    $employeess[$employee->month][] = $employee->total_salary;
                }
                $salTotal = [];
                for ($i = 1; $i <= 12; $i++) {
                    // Assuming $employee->02 and $employee->03 represent different months
                    $propertyName = str_pad($i, 2, "0", STR_PAD_LEFT); // Ensure two-digit representation
                    $salTotal[] = array_key_exists($propertyName, $employeess) ? array_sum($employeess[$propertyName]) : 0;
                }
            } else {
                for ($i = 1; $i <= 12; $i++) {
                    $salTotal[] = 0;
                }
            }

            $TrainingCostTotal = [];
            if (module_is_active('Training') && empty($request->vendor)) {
                // ------------------------------------------------ Training Cost -------------------------------------------------

                $trainings = Training::selectRaw('SUM(training_cost) as total_cost, SUBSTRING(start_date, 1, 4) as year, SUBSTRING(start_date, 6, 2) as month')
                    ->where('start_date', 'like', $year . '-%')
                    ->where('status', 2)
                    ->where('account_type', '!=', 'null')
                    ->groupBy(DB::raw('SUBSTRING(start_date, 1, 4), SUBSTRING(start_date, 6, 2)'));

                $trainings = $trainings->get();

                $trainingss = [];
                foreach ($trainings as $training) {
                    $trainingss[$training->month][] = $training->total_cost;
                }

                $TrainingCostTotal = [];
                for ($i = 1; $i <= 12; $i++) {
                    $propertyName = str_pad($i, 2, "0", STR_PAD_LEFT);
                    $TrainingCostTotal[] = array_key_exists($propertyName, $trainingss) ? array_sum($trainingss[$propertyName]) : 0;
                }
            } else {
                for ($i = 1; $i <= 12; $i++) {
                    $TrainingCostTotal[] = 0;
                }
            }

            $chartExpenseArr = array_map(
                function () {
                    return array_sum(func_get_args());
                }, $expenseTotal, $billTotal, $salTotal, $TrainingCostTotal, $purchaseTotal
            );

            $data['chartExpenseArr'] = $chartExpenseArr;
            $data['expenseArr'] = $array;
            $data['billArray'] = $billArray;
            $data['purchaseArray'] = $purchaseArray;
            $data['account'] = $account;
            $data['vendor'] = $vendor;
            $data['category'] = $category;
            if (module_is_active('Hrm')) {
                $data['EmpSalary'] = $salTotal;
            }
            if (module_is_active('Training')) {
                $data['TrainingCost'] = $TrainingCostTotal;
            }

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange'] = 'Dec-' . $year;

            return view('account::report.expense_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
    public function incomeVsExpenseSummary(Request $request)
    {
        if (Auth::user()->isAbleTo('report income vs expense manage')) {
            $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');
            $vendor = Vender::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
            $customer = Customer::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
            $category = [];
            if (module_is_active('ProductService')) {
                $category = \Workdo\ProductService\Entities\Category::where('workspace_id', '=', getActiveWorkSpace())->whereIn('type', [1, 2])->get()->pluck('name', 'id');
            }
            $data['monthList'] = $month = $this->yearMonth();
            $data['yearList'] = $this->yearList();

            $filter['category'] = __('All');
            $filter['customer'] = __('All');
            $filter['vendor'] = __('All');

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            // ------------------------------TOTAL PAYMENT EXPENSE-----------------------------------------------------------
            $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $expensesData->where('payments.workspace', '=', getActiveWorkSpace());
            $expensesData->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $expensesData->where('category_id', '=', $request->category);
                $cat = [];
                if (module_is_active('ProductService')) {
                    $cat = \Workdo\ProductService\Entities\Category::find($request->category);
                }
                $filter['category'] = !empty($cat) ? $cat->name : '';

            }
            if (!empty($request->vendor)) {
                $expensesData->where('vendor_id', '=', $request->vendor);

                $vend = Vender::find($request->vendor);
                $filter['vendor'] = !empty($vend) ? $vend->name : '';
            }
            $expensesData->groupBy('month', 'year');
            $expensesData = $expensesData->get();

            $expenseArr = [];
            foreach ($expensesData as $k => $expenseData) {
                $expenseArr[$expenseData->month] = $expenseData->amount;
            }

            // ------------------------------TOTAL BILL EXPENSE-----------------------------------------------------------

            $bills = Bill::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0);
            $bills->whereRAW('YEAR(send_date) =?', [$year]);

            if (!empty($request->vendor)) {
                $bills->where('vendor_id', '=', $request->vendor);

            }

            if (!empty($request->category)) {
                $bills->where('category_id', '=', $request->category);
            }

            $bills = $bills->get();
            $billTmpArray = [];
            foreach ($bills as $bill) {
                $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
            }
            $billArray = [];
            foreach ($billTmpArray as $cat_id => $record) {
                $bill = [];
                $bill['category'] = [];
                if (module_is_active('ProductService')) {
                    $bill['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $bill['data'] = [];
                for ($i = 1; $i <= 12; $i++) {

                    $bill['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $billArray[] = $bill;
            }

            $billTotalArray = [];
            foreach ($bills as $bill) {
                $billTotalArray[$bill->month][] = $bill->getTotal();
            }

            // ------------------------------TOTAL Purchase EXPENSE-----------------------------------------------------------

            $purchases = Purchase::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,purchase_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0);

            $purchases->whereRAW('YEAR(send_date) =?', [$year]);

            if (!empty($request->vendor)) {
                $purchases->where('vender_id', '=', $request->vendor);

            }

            if (!empty($request->category)) {
                $purchases->where('category_id', '=', $request->category);
            }

            $purchases = $purchases->get();
            $purchaseTmpArray = [];
            foreach ($purchases as $purchase) {
                $purchaseTmpArray[$purchase->category_id][$purchase->month][] = $purchase->getTotal();
            }
            $purchaseArray = [];
            foreach ($purchaseTmpArray as $cat_id => $record) {
                $purchase = [];
                $purchase['category'] = [];
                if (module_is_active('ProductService')) {
                    $purchase['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $purchase['data'] = [];
                for ($i = 1; $i <= 12; $i++) {

                    $purchase['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $purchaseArray[] = $purchase;
            }

            $purchaseTotalArray = [];
            foreach ($purchases as $purchase) {
                $purchaseTotalArray[$purchase->month][] = $purchase->getTotal();
            }

            // -----------------------------------------------TOTAL EMPLOYEE EXPENSE--------------------------------------------------------
            if (module_is_active('Hrm')) {

                $employees = PaySlip::selectRaw('SUM(net_payble) as total_salary, SUBSTRING(salary_month, 1, 4) as year, SUBSTRING(salary_month, 6, 2) as month')
                    ->where('salary_month', 'like', $year . '-%')
                    ->where('status', 1)
                    ->groupBy(DB::raw('SUBSTRING(salary_month, 1, 4), SUBSTRING(salary_month, 6, 2)'));

                $employees = $employees->get();

                $employeess = [];
                foreach ($employees as $employee) {
                    $employeess[$employee->month][] = $employee->total_salary;
                }
            }

            // ---------------------------------------------TOTAL TRAINING COST------------------------------------------------------
            if (module_is_active('Training')) {
                $trainings = Training::selectRaw('SUM(training_cost) as total_cost, SUBSTRING(start_date, 1, 4) as year, SUBSTRING(start_date, 6, 2) as month')
                    ->where('start_date', 'like', $year . '-%')
                    ->where('status', 2)
                    ->where('account_type', '!=', 'null')
                    ->groupBy(DB::raw('SUBSTRING(start_date, 1, 4), SUBSTRING(start_date, 6, 2)'));

                $trainings = $trainings->get();

                $trainingss = [];
                foreach ($trainings as $training) {
                    $trainingss[$training->month][] = $training->total_cost;
                }
            }

            // ------------------------------TOTAL REVENUE INCOME-----------------------------------------------------------

            $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $incomesData->where('revenues.workspace', '=', getActiveWorkSpace());
            $incomesData->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $incomesData->where('category_id', '=', $request->category);
            }
            if (!empty($request->customer)) {
                $incomesData->where('customer_id', '=', $request->customer);
                $cust = Customer::find($request->customer);

                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }
            $incomesData->groupBy('month', 'year');
            $incomesData = $incomesData->get();
            $incomeArr = [];
            foreach ($incomesData as $k => $incomeData) {
                $incomeArr[$incomeData->month] = $incomeData->amount;
            }

            // ------------------------------TOTAL INVOICE INCOME-----------------------------------------------------------
            $invoices = Invoice::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0)->where('invoice_module', '!=', 'taskly');

            $invoices->whereRAW('YEAR(send_date) =?', [$year]);
            if (!empty($request->customer)) {
                $invoices->where('user_id', '=', $cust->user_id);
            }
            if (!empty($request->category)) {
                $invoices->where('category_id', '=', $request->category);
            }
            $invoices = $invoices->get();
            $invoiceTmpArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
            }

            $invoiceArray = [];
            foreach ($invoiceTmpArray as $cat_id => $record) {

                $invoice = [];
                $invoice['category'] = [];
                if (module_is_active('ProductService')) {
                    $invoice['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $invoice['data'] = [];
                for ($i = 1; $i <= 12; $i++) {

                    $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $invoiceArray[] = $invoice;
            }

            $invoiceTotalArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            //        ----------------------------------------------------------------------------------------------------

            for ($i = 1; $i <= 12; $i++) {
                $paymentExpenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
                $billExpenseTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
                $purchaseExpenseTotal[] = array_key_exists($i, $purchaseTotalArray) ? array_sum($purchaseTotalArray[$i]) : 0;
                if (module_is_active('Hrm') && empty($request->vendor) && empty($request->customer)) {
                    $propertyName = str_pad($i, 2, "0", STR_PAD_LEFT); // Ensure two-digit representation
                    $salTotal[] = array_key_exists($propertyName, $employeess) ? array_sum($employeess[$propertyName]) : 0;
                } else {
                    $salTotal[] = 0;
                }
                if (module_is_active('Training') && empty($request->vendor) && empty($request->customer)) {
                    $propertyName = str_pad($i, 2, "0", STR_PAD_LEFT); // Ensure two-digit representation
                    $TrainingCostTotal[] = array_key_exists($propertyName, $trainingss) ? array_sum($trainingss[$propertyName]) : 0;
                } else {
                    $TrainingCostTotal[] = 0;
                }
                $RevenueIncomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
                $invoiceIncomeTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $totalIncome = array_map(
                function () {
                    return array_sum(func_get_args());
                }, $RevenueIncomeTotal, $invoiceIncomeTotal
            );

            $totalExpense = array_map(
                function () {
                    return array_sum(func_get_args());
                }, $paymentExpenseTotal, $billExpenseTotal, $salTotal, $TrainingCostTotal, $purchaseExpenseTotal
            );

            $profit = [];
            $keys = array_keys($totalIncome + $totalExpense);
            foreach ($keys as $v) {
                $profit[$v] = (empty($totalIncome[$v]) ? 0 : $totalIncome[$v]) - (empty($totalExpense[$v]) ? 0 : $totalExpense[$v]);
            }

            $data['paymentExpenseTotal'] = $paymentExpenseTotal;
            $data['billExpenseTotal'] = $billExpenseTotal;
            $data['purchaseExpenseTotal'] = $purchaseExpenseTotal;
            $data['revenueIncomeTotal'] = $RevenueIncomeTotal;
            $data['invoiceIncomeTotal'] = $invoiceIncomeTotal;
            $data['profit'] = $profit;
            $data['account'] = $account;
            $data['vendor'] = $vendor;
            $data['customer'] = $customer;
            $data['category'] = $category;
            $data['EmpSalary'] = $salTotal;
            $data['TrainingCost'] = $TrainingCostTotal;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange'] = 'Dec-' . $year;
            return view('account::report.income_vs_expense_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
    public function taxSummary(Request $request)
    {
        if (module_is_active('ProductService')) {
            if (Auth::user()->isAbleTo('report tax manage')) {
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList'] = $this->yearList();
                $data['taxList'] = $taxList = \Workdo\ProductService\Entities\Tax::where('workspace_id', getActiveWorkSpace())->get();

                if (isset($request->year)) {
                    $year = $request->year;
                } else {
                    $year = date('Y');
                }

                $data['currentYear'] = $year;

                $invoiceProducts = InvoiceProduct::selectRaw('invoice_products.* ,MONTH(invoice_products.created_at) as month,YEAR(invoice_products.created_at) as year')->leftjoin('product_services', 'invoice_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(invoice_products.created_at) =?', [$year])->where('product_services.workspace_id', '=', getActiveWorkSpace())->get();
                $incomeTaxesData = [];
                foreach ($invoiceProducts as $invoiceProduct) {
                    $incomeTax = [];
                    $incomeTaxes = AccountUtility::tax($invoiceProduct->tax);

                    foreach ($incomeTaxes as $taxe) {
                        $taxDataPrice = AccountUtility::taxRate(!empty($taxe) ? ($taxe['rate']) : 0, $invoiceProduct->price, $invoiceProduct->quantity);
                        $incomeTax[!empty($taxe) ? ($taxe['name']) : ''] = $taxDataPrice;
                    }
                    $incomeTaxesData[$invoiceProduct->month][] = $incomeTax;
                }

                $income = [];
                foreach ($incomeTaxesData as $month => $incomeTaxx) {
                    $incomeTaxRecord = [];
                    foreach ($incomeTaxx as $k => $record) {
                        foreach ($record as $incomeTaxName => $incomeTaxAmount) {
                            if (array_key_exists($incomeTaxName, $incomeTaxRecord)) {
                                $incomeTaxRecord[$incomeTaxName] += $incomeTaxAmount;
                            } else {
                                $incomeTaxRecord[$incomeTaxName] = $incomeTaxAmount;
                            }
                        }
                        $income['data'][$month] = $incomeTaxRecord;
                    }

                }

                foreach ($income as $incomeMonth => $incomeTaxData) {
                    $incomeData = [];
                    for ($i = 1; $i <= 12; $i++) {
                        $incomeData[$i] = array_key_exists($i, $incomeTaxData) ? $incomeTaxData[$i] : 0;
                    }

                }

                $incomes = [];
                if (isset($incomeData) && !empty($incomeData)) {
                    foreach ($taxList as $taxArr) {
                        foreach ($incomeData as $month => $tax) {
                            if ($tax != 0) {
                                if (isset($tax[$taxArr->name])) {
                                    $incomes[$taxArr->name][$month] = $tax[$taxArr->name];
                                } else {
                                    $incomes[$taxArr->name][$month] = 0;
                                }
                            } else {
                                $incomes[$taxArr->name][$month] = 0;
                            }
                        }
                    }
                }
                $billProducts = BillProduct::selectRaw('bill_products.* ,MONTH(bill_products.created_at) as month,YEAR(bill_products.created_at) as year')->leftjoin('product_services', 'bill_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(bill_products.created_at) =?', [$year])->where('product_services.workspace_id', '=', getActiveWorkSpace())->get();
                $expenseTaxesData = [];
                foreach ($billProducts as $billProduct) {
                    if($billProduct->tax != 0)
                    {
                        $billTax = [];
                        $billTaxes = AccountUtility::tax($billProduct->tax);
                        foreach ($billTaxes as $taxe) {
                            $taxDataPrice = AccountUtility::taxRate($taxe['rate'], $billProduct->price, $billProduct->quantity);
                            $billTax[$taxe['name']] = $taxDataPrice;
                        }
                        $expenseTaxesData[$billProduct->month][] = $billTax;
                    }
                }

                $bill = [];
                foreach ($expenseTaxesData as $month => $billTaxx) {
                    $billTaxRecord = [];
                    foreach ($billTaxx as $k => $record) {
                        foreach ($record as $billTaxName => $billTaxAmount) {
                            if (array_key_exists($billTaxName, $billTaxRecord)) {
                                $billTaxRecord[$billTaxName] += $billTaxAmount;
                            } else {
                                $billTaxRecord[$billTaxName] = $billTaxAmount;
                            }
                        }
                        $bill['data'][$month] = $billTaxRecord;
                    }

                }

                foreach ($bill as $billMonth => $billTaxData) {
                    $billData = [];
                    for ($i = 1; $i <= 12; $i++) {
                        $billData[$i] = array_key_exists($i, $billTaxData) ? $billTaxData[$i] : 0;
                    }

                }
                $expenses = [];
                if (isset($billData) && !empty($billData)) {

                    foreach ($taxList as $taxArr) {
                        foreach ($billData as $month => $tax) {
                            if ($tax != 0) {
                                if (isset($tax[$taxArr->name])) {
                                    $expenses[$taxArr->name][$month] = $tax[$taxArr->name];
                                } else {
                                    $expenses[$taxArr->name][$month] = 0;
                                }
                            } else {
                                $expenses[$taxArr->name][$month] = 0;
                            }
                        }

                    }
                }
                $data['expenses'] = $expenses;
                $data['incomes'] = $incomes;

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange'] = 'Dec-' . $year;

                return view('account::report.tax_summary', compact('filter'), $data);
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->route('home')->with('error', __('Please Enable Product & Service Module'));
        }

    }
    public function profitLossSummary(Request $request)
    {

        if (Auth::user()->isAbleTo('report loss & profit  manage')) {
            $data['month'] = [
                'Jan-Mar',
                'Apr-Jun',
                'Jul-Sep',
                'Oct-Dec',
                'Total',
            ];
            $data['monthList'] = $month = $this->yearMonth();
            $data['yearList'] = $this->yearList();
            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            // -------------------------------REVENUE INCOME-------------------------------------------------
            $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
            $incomes->where('workspace', '=', getActiveWorkSpace());
            $incomes->whereRAW('YEAR(date) =?', [$year]);
            $incomes->groupBy('month', 'year', 'category_id');
            $incomes = $incomes->get();
            $tmpIncomeArray = [];
            foreach ($incomes as $income) {
                $tmpIncomeArray[$income->category_id][$income->month] = $income->amount;
            }

            $incomeCatAmount_1 = $incomeCatAmount_2 = $incomeCatAmount_3 = $incomeCatAmount_4 = 0;
            $revenueIncomeArray = array();
            foreach ($tmpIncomeArray as $cat_id => $record) {

                $tmp = [];
                $tmp['category'] = [];
                if (module_is_active('ProductService')) {
                    $tmp['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $sumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $sumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
                }

                $month_1 = array_slice($sumData, 0, 3);
                $month_2 = array_slice($sumData, 3, 3);
                $month_3 = array_slice($sumData, 6, 3);
                $month_4 = array_slice($sumData, 9, 3);

                $incomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $incomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $incomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $incomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $incomeData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );

                $incomeCatAmount_1 += $sum_1;
                $incomeCatAmount_2 += $sum_2;
                $incomeCatAmount_3 += $sum_3;
                $incomeCatAmount_4 += $sum_4;

                $data['month'] = array_keys($incomeData);
                $tmp['amount'] = array_values($incomeData);

                $revenueIncomeArray[] = $tmp;

            }

            $data['incomeCatAmount'] = $incomeCatAmount = [
                $incomeCatAmount_1,
                $incomeCatAmount_2,
                $incomeCatAmount_3,
                $incomeCatAmount_4,
                array_sum(
                    array(
                        $incomeCatAmount_1,
                        $incomeCatAmount_2,
                        $incomeCatAmount_3,
                        $incomeCatAmount_4,
                    )
                ),
            ];

            $data['revenueIncomeArray'] = $revenueIncomeArray;

            //-----------------------INVOICE INCOME---------------------------------------------

            $invoices = Invoice::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0)->where('invoice_module', '!=', 'taskly');
            $invoices->whereRAW('YEAR(send_date) =?', [$year]);
            if (!empty($request->customer)) {
                $invoices->where('customer_id', '=', $request->customer);
            }
            $invoices = $invoices->get();

            $invoiceTmpArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
            }

            $invoiceCatAmount_1 = $invoiceCatAmount_2 = $invoiceCatAmount_3 = $invoiceCatAmount_4 = 0;
            $invoiceIncomeArray = array();
            foreach ($invoiceTmpArray as $cat_id => $record) {

                $invoiceTmp = [];
                $invoiceTmp['category'] = [];
                if (module_is_active('ProductService')) {
                    $invoiceTmp['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $invoiceSumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $invoiceSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;

                }

                $month_1 = array_slice($invoiceSumData, 0, 3);
                $month_2 = array_slice($invoiceSumData, 3, 3);
                $month_3 = array_slice($invoiceSumData, 6, 3);
                $month_4 = array_slice($invoiceSumData, 9, 3);
                $invoiceIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $invoiceIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $invoiceIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $invoiceIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $invoiceIncomeData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );
                $invoiceCatAmount_1 += $sum_1;
                $invoiceCatAmount_2 += $sum_2;
                $invoiceCatAmount_3 += $sum_3;
                $invoiceCatAmount_4 += $sum_4;

                $invoiceTmp['amount'] = array_values($invoiceIncomeData);

                $invoiceIncomeArray[] = $invoiceTmp;

            }

            $data['invoiceIncomeCatAmount'] = $invoiceIncomeCatAmount = [
                $invoiceCatAmount_1,
                $invoiceCatAmount_2,
                $invoiceCatAmount_3,
                $invoiceCatAmount_4,
                array_sum(
                    array(
                        $invoiceCatAmount_1,
                        $invoiceCatAmount_2,
                        $invoiceCatAmount_3,
                        $invoiceCatAmount_4,
                    )
                ),
            ];

            $data['invoiceIncomeArray'] = $invoiceIncomeArray;

            $data['totalIncome'] = $totalIncome = array_map(
                function () {
                    return array_sum(func_get_args());
                }, $invoiceIncomeCatAmount, $incomeCatAmount
            );

            //---------------------------------PAYMENT EXPENSE-----------------------------------

            $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
            $expenses->where('workspace', '=', getActiveWorkSpace());
            $expenses->whereRAW('YEAR(date) =?', [$year]);
            $expenses->groupBy('month', 'year', 'category_id');
            $expenses = $expenses->get();

            $tmpExpenseArray = [];
            foreach ($expenses as $expense) {
                $tmpExpenseArray[$expense->category_id][$expense->month] = $expense->amount;
            }

            $expenseArray = [];
            $expenseCatAmount_1 = $expenseCatAmount_2 = $expenseCatAmount_3 = $expenseCatAmount_4 = 0;
            foreach ($tmpExpenseArray as $cat_id => $record) {
                $tmp = [];
                $tmp['category'] = [];
                if (module_is_active('ProductService')) {
                    $tmp['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $expenseSumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $expenseSumData[] = array_key_exists($i, $record) ? $record[$i] : 0;

                }

                $month_1 = array_slice($expenseSumData, 0, 3);
                $month_2 = array_slice($expenseSumData, 3, 3);
                $month_3 = array_slice($expenseSumData, 6, 3);
                $month_4 = array_slice($expenseSumData, 9, 3);

                $expenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $expenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $expenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $expenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $expenseData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );

                $expenseCatAmount_1 += $sum_1;
                $expenseCatAmount_2 += $sum_2;
                $expenseCatAmount_3 += $sum_3;
                $expenseCatAmount_4 += $sum_4;

                $data['month'] = array_keys($expenseData);
                $tmp['amount'] = array_values($expenseData);

                $expenseArray[] = $tmp;

            }

            $data['expenseCatAmount'] = $expenseCatAmount = [
                $expenseCatAmount_1,
                $expenseCatAmount_2,
                $expenseCatAmount_3,
                $expenseCatAmount_4,
                array_sum(
                    array(
                        $expenseCatAmount_1,
                        $expenseCatAmount_2,
                        $expenseCatAmount_3,
                        $expenseCatAmount_4,
                    )
                ),
            ];
            $data['expenseArray'] = $expenseArray;

            //    ----------------------------EXPENSE BILL-----------------------------------------------------------------------

            $bills = Bill::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0);
            $bills->whereRAW('YEAR(send_date) =?', [$year]);
            if (!empty($request->customer)) {
                $bills->where('vendor_id', '=', $request->vendor);
            }
            $bills = $bills->get();
            $billTmpArray = [];
            foreach ($bills as $bill) {
                $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
            }

            $billExpenseArray = [];
            $billExpenseCatAmount_1 = $billExpenseCatAmount_2 = $billExpenseCatAmount_3 = $billExpenseCatAmount_4 = 0;
            foreach ($billTmpArray as $cat_id => $record) {
                $billTmp = [];
                $billTmp['category'] = [];
                if (module_is_active('ProductService')) {
                    $billTmp['category'] = !empty(\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first())?\Workdo\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                }
                $billExpensSumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $billExpensSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }

                $month_1 = array_slice($billExpensSumData, 0, 3);
                $month_2 = array_slice($billExpensSumData, 3, 3);
                $month_3 = array_slice($billExpensSumData, 6, 3);
                $month_4 = array_slice($billExpensSumData, 9, 3);

                $billExpenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $billExpenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $billExpenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $billExpenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $billExpenseData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );

                $billExpenseCatAmount_1 += $sum_1;
                $billExpenseCatAmount_2 += $sum_2;
                $billExpenseCatAmount_3 += $sum_3;
                $billExpenseCatAmount_4 += $sum_4;

                $data['month'] = array_keys($billExpenseData);
                $billTmp['amount'] = array_values($billExpenseData);

                $billExpenseArray[] = $billTmp;

            }

            $data['billExpenseCatAmount'] = $billExpenseCatAmount = [
                $billExpenseCatAmount_1,
                $billExpenseCatAmount_2,
                $billExpenseCatAmount_3,
                $billExpenseCatAmount_4,
                array_sum(
                    array(
                        $billExpenseCatAmount_1,
                        $billExpenseCatAmount_2,
                        $billExpenseCatAmount_3,
                        $billExpenseCatAmount_4,
                    )
                ),
            ];

            $data['billExpenseArray'] = $billExpenseArray;

            $salExpenseCatAmount = [];
            if (module_is_active('Hrm')) {
                // ---------------------------------------------EXPENSE EMPLOYEE SALARY-----------------------------------------------
                $employees = PaySlip::selectRaw('SUM(net_payble) as total_salary, SUBSTRING(salary_month, 1, 4) as year, SUBSTRING(salary_month, 6, 2) as month')
                    ->where('salary_month', 'like', $year . '-%')
                    ->where('status', 1)
                    ->groupBy(DB::raw('SUBSTRING(salary_month, 1, 4), SUBSTRING(salary_month, 6, 2)'));

                $employees = $employees->get();

                $employeess = [];
                $salExpenseCatAmount_1 = $salExpenseCatAmount_2 = $salExpenseCatAmount_3 = $salExpenseCatAmount_4 = 0;
                foreach ($employees as $employee) {
                    $employeess[$employee->month][] = $employee->total_salary;
                }
                $salTotal = [];
                for ($i = 1; $i <= 12; $i++) {
                    // Assuming $employee->02 and $employee->03 represent different months
                    $propertyName = str_pad($i, 2, "0", STR_PAD_LEFT); // Ensure two-digit representation
                    $salTotal[] = array_key_exists($propertyName, $employeess) ? array_sum($employeess[$propertyName]) : 0;
                }

                $month_1 = array_slice($salTotal, 0, 3);
                $month_2 = array_slice($salTotal, 3, 3);
                $month_3 = array_slice($salTotal, 6, 3);
                $month_4 = array_slice($salTotal, 9, 3);

                $salExpenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $salExpenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $salExpenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $salExpenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $salExpenseData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );

                $salExpenseCatAmount_1 += $sum_1;
                $salExpenseCatAmount_2 += $sum_2;
                $salExpenseCatAmount_3 += $sum_3;
                $salExpenseCatAmount_4 += $sum_4;

                $data['month'] = array_keys($salExpenseData);
                $salTmp['amount'] = array_values($salExpenseData);

                $salExpenseArray[] = $salTmp;

                $data['salExpenseCatAmount'] = $salExpenseCatAmount = [
                    $salExpenseCatAmount_1,
                    $salExpenseCatAmount_2,
                    $salExpenseCatAmount_3,
                    $salExpenseCatAmount_4,
                    array_sum(
                        array(
                            $salExpenseCatAmount_1,
                            $salExpenseCatAmount_2,
                            $salExpenseCatAmount_3,
                            $salExpenseCatAmount_4,
                        )
                    ),
                ];
                $data['salExpenseArray'] = $salExpenseArray;
            }

            $TrainingCostExpenseAmount = [];
            if (module_is_active('Training')) {
                // ---------------------------------------------EXPENSE TRAINING COST-----------------------------------------------
                $trainings = Training::selectRaw('SUM(training_cost) as total_cost, SUBSTRING(start_date, 1, 4) as year, SUBSTRING(start_date, 6, 2) as month')
                    ->where('start_date', 'like', $year . '-%')
                    ->where('status', 2)
                    ->where('account_type', '!=', 'null')
                    ->groupBy(DB::raw('SUBSTRING(start_date, 1, 4), SUBSTRING(start_date, 6, 2)'));

                $trainings = $trainings->get();

                $trainingss = [];
                $TrainingCostExpenseAmount_1 = $TrainingCostExpenseAmount_2 = $TrainingCostExpenseAmount_3 = $TrainingCostExpenseAmount_4 = 0;
                foreach ($trainings as $training) {
                    $trainingss[$training->month][] = $training->total_cost;
                }
                $TrainingCostTotal = [];
                for ($i = 1; $i <= 12; $i++) {
                    // Assuming $employee->02 and $employee->03 represent different months
                    $propertyName = str_pad($i, 2, "0", STR_PAD_LEFT); // Ensure two-digit representation
                    $TrainingCostTotal[] = array_key_exists($propertyName, $trainingss) ? array_sum($trainingss[$propertyName]) : 0;
                }

                $month_1 = array_slice($TrainingCostTotal, 0, 3);
                $month_2 = array_slice($TrainingCostTotal, 3, 3);
                $month_3 = array_slice($TrainingCostTotal, 6, 3);
                $month_4 = array_slice($TrainingCostTotal, 9, 3);

                $TrainingCostExpenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $TrainingCostExpenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $TrainingCostExpenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $TrainingCostExpenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $TrainingCostExpenseData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );

                $TrainingCostExpenseAmount_1 += $sum_1;
                $TrainingCostExpenseAmount_2 += $sum_2;
                $TrainingCostExpenseAmount_3 += $sum_3;
                $TrainingCostExpenseAmount_4 += $sum_4;

                $data['month'] = array_keys($TrainingCostExpenseData);
                $TrainingCostTmp['amount'] = array_values($TrainingCostExpenseData);

                $TrainingCostExpenseArray[] = $TrainingCostTmp;

                $data['TrainingCostExpenseAmount'] = $TrainingCostExpenseAmount = [
                    $TrainingCostExpenseAmount_1,
                    $TrainingCostExpenseAmount_2,
                    $TrainingCostExpenseAmount_3,
                    $TrainingCostExpenseAmount_4,
                    array_sum(
                        array(
                            $TrainingCostExpenseAmount_1,
                            $TrainingCostExpenseAmount_2,
                            $TrainingCostExpenseAmount_3,
                            $TrainingCostExpenseAmount_4,
                        )
                    ),
                ];
                $data['TrainingCostExpenseArray'] = $TrainingCostExpenseArray;
            }

            $data['totalExpense'] = $totalExpense = array_map(
                function () {
                    return array_sum(func_get_args());
                }, $billExpenseCatAmount, $expenseCatAmount, $salExpenseCatAmount, $TrainingCostExpenseAmount
            );

            foreach ($totalIncome as $k => $income) {
                $netProfit[] = $income - $totalExpense[$k];
            }
            $data['netProfitArray'] = $netProfit;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange'] = 'Dec-' . $year;

            return view('account::report.profit_loss_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
    public function invoiceSummary(Request $request)
    {
        if (Auth::user()->isAbleTo('report invoice manage')) {
            $filter['customer'] = __('All');
            $filter['status'] = __('All');

            $customer = Customer::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');

            $status = Invoice::$statues;

            $invoices = Invoice::selectRaw('invoices.*,MONTH(send_date) as month,YEAR(send_date) as year');

            if ($request->status != '') {
                $invoices->where('status', $request->status);

                $filter['status'] = Invoice::$statues[$request->status];
            } else {
                $invoices->where('status', '!=', 0);
            }

            $invoices->where('workspace', '=', getActiveWorkSpace());

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end = strtotime(date('Y-12'));
            }

            $invoices->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange'] = date('M-Y', $end);

            if (!empty($request->customer)) {
                $cust = Customer::find($request->customer);
                $invoices->where('user_id', $cust->user_id);

                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }
            $invoices = $invoices->get();

            $totalInvoice = 0;
            $totalDueInvoice = 0;
            $invoiceTotalArray = [];
            foreach ($invoices as $invoice) {
                $totalInvoice += $invoice->getTotal();
                $totalDueInvoice += $invoice->getDue();

                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            $totalPaidInvoice = $totalInvoice - $totalDueInvoice;

            for ($i = 1; $i <= 12; $i++) {
                $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $monthList = $month = $this->yearMonth();
            return view('account::report.invoice_report', compact('invoices', 'customer', 'status', 'totalInvoice', 'totalDueInvoice', 'totalPaidInvoice', 'invoiceTotal', 'monthList', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function billSummary(Request $request)
    {
        if (Auth::user()->isAbleTo('report bill manage')) {
            $filter['vendor'] = __('All');
            $filter['status'] = __('All');

            $vendor = Vender::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');

            // Bill
            $status = Bill::$statues;

            $bills = Bill::selectRaw('bills.*,MONTH(send_date) as month,YEAR(send_date) as year')->addselect('bills.*', 'vendors.name as vendor_name', 'categories.name as categories_name', 'bill_payments.date as lastpayment_date')->join('vendors', 'bills.vendor_id', '=', 'vendors.id')->join('categories', 'bills.category_id', '=', 'categories.id')->join('bill_payments', 'bills.bill_id', '=', 'bill_payments.bill_id')->where('bill_module', '!=', 'taskly')->groupBy('bills.id', 'vendors.name', 'categories.name');

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end = strtotime(date('Y-12'));
            }
            $bills->where('bills.send_date', '>=', date('Y-m-01', $start))->where('bills.send_date', '<=', date('Y-m-t', $end));

            // purchase
            $status = Purchase::$statues;

            $purchases = Purchase::selectRaw('purchases.*,MONTH(send_date) as month,YEAR(send_date) as year')->addselect('purchases.*', 'categories.name as categories_name', 'purchase_payments.date as lastpayment_date')->join('categories', 'purchases.category_id', '=', 'categories.id')->join('purchase_payments', 'purchases.id', '=', 'purchase_payments.purchase_id')->where('purchase_module', '!=', 'taskly');

            $purchases->where('purchases.send_date', '>=', date('Y-m-01', $start))->where('purchases.purchase_date', '<=', date('Y-m-t', $end));

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange'] = date('M-Y', $end);

            if (!empty($request->vendor)) {
                $bills->where('bills.vendor_id', $request->vendor);
                $vend = Vender::find($request->vendor);

                // purchase
                $purchases->where('purchases.vender_id', $request->vendor);

                $filter['vendor'] = !empty($vend) ? $vend->name : '';
            }

            if ($request->status != '') {
                $bills->where('bills.status', '=', $request->status);

                $filter['status'] = Bill::$statues[$request->status];

                // purchase
                $purchases->where('purchases.status', '=', $request->status);
            } else {
                $bills->where('status', '!=', 0);

                // purchase
                $purchases->where('status', '!=', 0);
            }

            $bills->where('bills.workspace', '=', getActiveWorkSpace());
            $bills = $bills->get();

            $purchases = $purchases->where('purchases.workspace', '=', getActiveWorkSpace())->get();

            $totalBill = 0;
            $totalDueBill = 0;
            $billTotalArray = [];
            foreach ($bills as $bill) {
                $totalBill += $bill->getTotal();
                $totalDueBill += $bill->getDue();

                $billTotalArray[$bill->month][] = $bill->getTotal();
            }
            $totalPaidBill = $totalBill - $totalDueBill;
            for ($i = 1; $i <= 12; $i++) {
                $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
            }

            $monthList = $month = $this->yearMonth();

            // purchase
            $totalPurchase = 0;
            $totalDuePurchase = 0;
            $purchaseTotalArray = [];
            foreach ($purchases as $purchase) {
                $totalPurchase += $purchase->getTotal();
                $totalDuePurchase += $purchase->getDue();

                $purchaseTotalArray[$purchase->month][] = $purchase->getTotal();
            }
            $totalPaidPurchase = $totalPurchase - $totalDuePurchase;

            for ($i = 1; $i <= 12; $i++) {
                $purchaseTotal[] = array_key_exists($i, $purchaseTotalArray) ? array_sum($purchaseTotalArray[$i]) : 0;
            }

            return view('account::report.bill_report', compact('bills', 'vendor', 'status', 'totalBill', 'totalDueBill', 'totalPaidBill', 'billTotal', 'monthList', 'filter', 'purchases', 'purchaseTotal'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function productStock(Request $request)
    {
        if (Auth::user()->isAbleTo('report stock manage')) {
            $stocks = StockReport::where('workspace', '=', getActiveWorkSpace())->leftjoin('product_services', 'stock_reports.product_id', '=', 'product_services.id')->select('stock_reports.*', 'product_services.name as name')->get();
            return view('account::report.product_stock_report', compact('stocks'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function yearMonth()
    {

        $month[] = __('January');
        $month[] = __('February');
        $month[] = __('March');
        $month[] = __('April');
        $month[] = __('May');
        $month[] = __('June');
        $month[] = __('July');
        $month[] = __('August');
        $month[] = __('September');
        $month[] = __('October');
        $month[] = __('November');
        $month[] = __('December');

        return $month;
    }

    public function yearList()
    {
        $starting_year = date('Y', strtotime('-5 year'));
        $ending_year = date('Y');

        foreach (range($ending_year, $starting_year) as $year) {
            $years[$year] = $year;
        }

        return $years;
    }

    public function cashflow(Request $request)
    {
        $yearList = $this->yearList();
        $monthList = $this->yearMonth();
        if (isset($request->year)) {
            $year = $request->year;
        } else {
            $year = date('Y');
        }

        //  ----------------------------------- Invoice   ------------------------------------------- //

        $invoices = InvoicePayment::selectRaw('
                MONTH(invoice_payments.date) as month,
                YEAR(invoice_payments.date) as year,
                invoice_payments.account_id,
                invoice_payments.amount,
                invoice_payments.invoice_id,
                invoice_payments.id
            ')
            ->whereRaw('YEAR(invoice_payments.date) = ?', [$year])
            ->join('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
            ->where('invoices.created_by', creatorId())
            ->where('invoices.workspace', '=', getActiveWorkSpace())
            ->get();

        $invoiceTmpArray = [];
        foreach ($invoices as $invoice) {
            $invoiceTmpArray[$invoice->month][] = $invoice->amount;
        }
        for ($i = 1; $i <= 12; $i++) {
            $invoiceTotal[] = array_key_exists($i, $invoiceTmpArray) ? array_sum($invoiceTmpArray[$i]) : 0;
        }
        //  ----------------------------------- Revenue   ------------------------------------------- //

        $revenues = Revenue::selectRaw('MONTH(date) as month,YEAR(date) as year,category_id,amount,account_id,id');
        $revenues->whereRAW('YEAR(date) =?', [$year])->where('created_by', creatorId())->where('workspace', '=', getActiveWorkSpace());
        $revenues = $revenues->get();
        $RevenueTmpArray = [];
        foreach ($revenues as $revenue) {
            $RevenueTmpArray[$revenue->month][] = $revenue->amount;
        }
        for ($i = 1; $i <= 12; $i++) {
            $RevenueTotal[] = array_key_exists($i, $RevenueTmpArray) ? array_sum($RevenueTmpArray[$i]) : 0;
        }

        //  ----------------------------------- Total Income  ------------------------------------------- //

        $chartIncomeArr = array_map(
            function () {
                return array_sum(func_get_args());
            }, $invoiceTotal, $RevenueTotal
        );

        //  ----------------------------------- payment ------------------------------------------- //
        $payments = Payment::selectRaw('MONTH(date) as month,YEAR(date) as year,category_id,amount,account_id,id');
        $payments->whereRAW('YEAR(date) =?', [$year])->where('created_by', creatorId())->where('workspace', '=', getActiveWorkSpace());
        $payments = $payments->get();

        $paymentTmpArray = [];
        foreach ($payments as $payment) {
            $paymentTmpArray[$payment->month][] = $payment->amount;
        }
        for ($i = 1; $i <= 12; $i++) {
            $paymentTotal[] = array_key_exists($i, $paymentTmpArray) ? array_sum($paymentTmpArray[$i]) : 0;
        }

        //  ----------------------------------- PaySlip ------------------------------------------- //
        $paySlips = PaySlip::selectRaw('MONTH(updated_at) as month,YEAR(updated_at) as year,employee_id,net_payble,id');
        $paySlips->whereRAW('YEAR(updated_at) =?', [$year])->where('created_by', creatorId())->where('status', '1')->where('workspace', '=', getActiveWorkSpace());
        $paySlips = $paySlips->get();

        $paySlipTmpArray = [];
        foreach ($paySlips as $paySlip) {
            $paySlipTmpArray[$paySlip->month][] = $paySlip->net_payble;
        }
        for ($i = 1; $i <= 12; $i++) {
            $paySlipTotal[] = array_key_exists($i, $paySlipTmpArray) ? array_sum($paySlipTmpArray[$i]) : 0;
        }

        //  ----------------------------------- Bill ------------------------------------------- //
        $bills = BillPayment::selectRaw('
            MONTH(bill_payments.date) as month,
            YEAR(bill_payments.date) as year,
            bill_payments.account_id,
            bill_payments.amount,
            bill_payments.bill_id,
            bill_payments.id
        ')
            ->whereRaw('YEAR(bill_payments.date) = ?', [$year])
            ->join('bills', 'bill_payments.bill_id', '=', 'bills.id')
            ->where('bills.created_by', creatorId())
            ->where('bills.workspace', '=', getActiveWorkSpace())
            ->get();

        $billTmpArray = [];
        foreach ($bills as $bill) {
            $billTmpArray[$bill->month][] = $bill->amount;
        }
        for ($i = 1; $i <= 12; $i++) {
            $billTotal[] = array_key_exists($i, $billTmpArray) ? array_sum($billTmpArray[$i]) : 0;
        }

        //  ----------------------------------- total expense ------------------------------------------- //

        $chartExpenseArr = array_map(
            function () {
                return array_sum(func_get_args());
            }, $paySlipTotal, $paymentTotal, $billTotal
        );

        //  ----------------------------------- Net Profit ------------------------------------------- //

        $netProfit = [];
        $keys = array_keys($chartIncomeArr + $chartExpenseArr);
        foreach ($keys as $v) {
            $netProfit[$v] = (empty($chartIncomeArr[$v]) ? 0 : $chartIncomeArr[$v]) - (empty($chartExpenseArr[$v]) ? 0 : $chartExpenseArr[$v]);
        }

        $netProfitArray = $netProfit;

        $filter['startDateRange'] = 'Jan-' . $year;
        $filter['endDateRange'] = 'Dec-' . $year;

        return view('account::report.cash_flow', compact('yearList', 'filter', 'monthList', 'invoiceTotal', 'RevenueTotal', 'chartIncomeArr', 'paymentTotal', 'paySlipTotal', 'billTotal', 'chartExpenseArr', 'netProfitArray'));

    }

    public function quarterlycashflow(Request $request)
    {
        $yearList = $this->yearList();
        if (isset($request->year)) {
            $year = $request->year;
        } else {
            $year = date('Y');
        }
        $four_month = [
            'Jan-Mar',
            'Apr-Jun',
            'Jul-Sep',
            'Oct-Dec',
            'Total',
        ];
        $monthList = $month = $this->yearMonth();

        // -------------------------------- invoice data ---------------------- //

        $invoices = InvoicePayment::selectRaw('
            MONTH(invoice_payments.date) as month,
            YEAR(invoice_payments.date) as year,
            invoice_payments.account_id,
            invoice_payments.amount,
            invoice_payments.invoice_id,
            invoice_payments.id
        ')
            ->whereRaw('YEAR(invoice_payments.date) = ?', [$year])
            ->join('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
            ->where('invoices.created_by', creatorId())
            ->where('invoices.workspace', '=', getActiveWorkSpace())
            ->get();

        $invoiceTmpArray = [];
        foreach ($invoices as $invoice) {

            $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->amount;
        }
        $invoiceIncomeArray = [0, 0, 0, 0, 0];
        if (!empty($invoiceTmpArray)) {
            $invoiceCatAmount_1 = $invoiceCatAmount_2 = $invoiceCatAmount_3 = $invoiceCatAmount_4 = 0;
            $invoiceIncomeArray = array();
            foreach ($invoiceTmpArray as $cat_id => $record) {
                $invoiceSumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $invoiceSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;

                }
                $month_1 = array_slice($invoiceSumData, 0, 3);
                $month_2 = array_slice($invoiceSumData, 3, 3);
                $month_3 = array_slice($invoiceSumData, 6, 3);
                $month_4 = array_slice($invoiceSumData, 9, 3);
                $invoiceIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $invoiceIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $invoiceIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $invoiceIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $invoiceIncomeData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );
                $invoiceCatAmount_1 += $sum_1;
                $invoiceCatAmount_2 += $sum_2;
                $invoiceCatAmount_3 += $sum_3;
                $invoiceCatAmount_4 += $sum_4;

                $invoiceTmp = array_values($invoiceIncomeData);

                $invoiceIncomeArray = $invoiceTmp;

            }

        }
        $invoiceTotal = $invoiceIncomeArray;

        //  ----------------------------------- Revenue   ------------------------------------------- //

        $revenues = Revenue::selectRaw('MONTH(date) as month,YEAR(date) as year,category_id,amount,account_id,id');
        $revenues->whereRAW('YEAR(date) =?', [$year])->where('created_by', creatorId())->where('workspace', '=', getActiveWorkSpace());
        $revenues = $revenues->get();

        $revenueTmpArray = [];
        foreach ($revenues as $revenue) {

            $revenueTmpArray[$revenue->category_id][$revenue->month][] = $revenue->amount;
        }

        $revenueIncomeArray = [0, 0, 0, 0, 0];
        if (!empty($revenueTmpArray)) {
            $revenueCatAmount_1 = $revenueCatAmount_2 = $revenueCatAmount_3 = $revenueCatAmount_4 = 0;

            $revenueIncomeArray = array();
            foreach ($revenueTmpArray as $cat_id => $record) {

                $revenueSumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $revenueSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;

                }

                $month_1 = array_slice($revenueSumData, 0, 3);
                $month_2 = array_slice($revenueSumData, 3, 3);
                $month_3 = array_slice($revenueSumData, 6, 3);
                $month_4 = array_slice($revenueSumData, 9, 3);
                $revenueIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $revenueIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $revenueIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $revenueIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $revenueIncomeData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );
                $revenueCatAmount_1 += $sum_1;
                $revenueCatAmount_2 += $sum_2;
                $revenueCatAmount_3 += $sum_3;
                $revenueCatAmount_4 += $sum_4;

                $revenueTmp = array_values($revenueIncomeData);

                $revenueIncomeArray = $revenueTmp;

            }
        }

        $RevenueTotal = $revenueIncomeArray;

        //  ----------------------------------- Income  ------------------------------------------- //

        $chartIncomeArr = array_map(
            function () {
                return array_sum(func_get_args());
            }, $invoiceTotal, $RevenueTotal
        );

        //  ----------------------------------- payment  ------------------------------------------- //

        $payments = Payment::selectRaw('MONTH(date) as month,YEAR(date) as year,category_id,amount,account_id,id');
        $payments->whereRAW('YEAR(date) =?', [$year])->where('created_by', creatorId())->where('workspace', '=', getActiveWorkSpace());
        $payments = $payments->get();

        $paymentTmpArray = [];
        foreach ($payments as $payment) {

            $paymentTmpArray[$payment->category_id][$payment->month][] = $payment->amount;
        }
        $paymentIncomeArray = [0, 0, 0, 0, 0];
        if (!empty($paymentTmpArray)) {
            $paymentCatAmount_1 = $paymentCatAmount_2 = $paymentCatAmount_3 = $paymentCatAmount_4 = 0;

            $paymentIncomeArray = array();
            foreach ($paymentTmpArray as $cat_id => $record) {

                $paymentSumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $paymentSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;

                }

                $month_1 = array_slice($paymentSumData, 0, 3);
                $month_2 = array_slice($paymentSumData, 3, 3);
                $month_3 = array_slice($paymentSumData, 6, 3);
                $month_4 = array_slice($paymentSumData, 9, 3);
                $paymentIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $paymentIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $paymentIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $paymentIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $paymentIncomeData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );
                $paymentCatAmount_1 += $sum_1;
                $paymentCatAmount_2 += $sum_2;
                $paymentCatAmount_3 += $sum_3;
                $paymentCatAmount_4 += $sum_4;

                $paymentTmp = array_values($paymentIncomeData);

                $paymentIncomeArray = $paymentTmp;

            }
        }
        $paymentTotal = $paymentIncomeArray;

        //  ----------------------------------- PaySlip  ------------------------------------------- //

        $paySlips = PaySlip::selectRaw('MONTH(updated_at) as month,YEAR(updated_at) as year,employee_id,net_payble,id');
        $paySlips->whereRAW('YEAR(updated_at) =?', [$year])->where('created_by', creatorId())->where('status', '1')->where('workspace', '=', getActiveWorkSpace());
        $paySlips = $paySlips->get();

        $paySlipTmpArray = [];
        foreach ($paySlips as $paySlip) {
            $paySlipTmpArray[$paySlip->category_id][$paySlip->month][] = $paySlip->net_payble;
        }

        $paySlipIncomeArray = [0, 0, 0, 0, 0];
        if (!empty($paySlipTmpArray)) {
            $paySlipCatAmount_1 = $paySlipCatAmount_2 = $paySlipCatAmount_3 = $paySlipCatAmount_4 = 0;

            $paySlipIncomeArray = array();
            foreach ($paySlipTmpArray as $cat_id => $record) {

                $paySlipSumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $paySlipSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;

                }

                $month_1 = array_slice($paySlipSumData, 0, 3);
                $month_2 = array_slice($paySlipSumData, 3, 3);
                $month_3 = array_slice($paySlipSumData, 6, 3);
                $month_4 = array_slice($paySlipSumData, 9, 3);
                $paySlipIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $paySlipIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $paySlipIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $paySlipIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $paySlipIncomeData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );
                $paySlipCatAmount_1 += $sum_1;
                $paySlipCatAmount_2 += $sum_2;
                $paySlipCatAmount_3 += $sum_3;
                $paySlipCatAmount_4 += $sum_4;

                $paySlipTmp = array_values($paySlipIncomeData);

                $paySlipIncomeArray = $paySlipTmp;

            }
        }
        $paySlipTotal = $paySlipIncomeArray;

        //  ----------------------------------- Bill  ------------------------------------------- //

        $bills = BillPayment::selectRaw('
            MONTH(bill_payments.date) as month,
            YEAR(bill_payments.date) as year,
            bill_payments.account_id,
            bill_payments.amount,
            bill_payments.bill_id,
            bill_payments.id
        ')
            ->whereRaw('YEAR(bill_payments.date) = ?', [$year])
            ->join('bills', 'bill_payments.bill_id', '=', 'bills.id')
            ->where('bills.created_by', creatorId())
            ->where('bills.workspace', '=', getActiveWorkSpace())
            ->get();
        $billTmpArray = [];
        foreach ($bills as $bill) {
            $billTmpArray[$bill->account_id][$bill->month][] = $bill->amount;
        }

        $billIncomeArray = [0, 0, 0, 0, 0];
        if (!empty($billTmpArray)) {
            $billCatAmount_1 = $billCatAmount_2 = $billCatAmount_3 = $billCatAmount_4 = 0;

            $billIncomeArray = array();

            foreach ($billTmpArray as $cat_id => $record) {

                $billSumData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $billSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;

                }

                $month_1 = array_slice($billSumData, 0, 3);
                $month_2 = array_slice($billSumData, 3, 3);
                $month_3 = array_slice($billSumData, 6, 3);
                $month_4 = array_slice($billSumData, 9, 3);
                $billIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $billIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $billIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $billIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $billIncomeData[__('Total')] = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );
                $billCatAmount_1 += $sum_1;
                $billCatAmount_2 += $sum_2;
                $billCatAmount_3 += $sum_3;
                $billCatAmount_4 += $sum_4;

                $billTmp = array_values($billIncomeData);

                $billIncomeArray = $billTmp;

            }
        }
        $billTotal = $billIncomeArray;

        //  ----------------------------------- Total expence  ------------------------------------------- //

        $chartExpenseArr = array_map(
            function () {
                return array_sum(func_get_args());
            }, $paySlipTotal, $paymentTotal, $billTotal
        );

        //  ----------------------------------- Net Profit ------------------------------------------- //

        $netProfit = [];
        $keys = array_keys($chartIncomeArr + $chartExpenseArr);
        foreach ($keys as $v) {
            $netProfit[$v] = (empty($chartIncomeArr[$v]) ? 0 : $chartIncomeArr[$v]) - (empty($chartExpenseArr[$v]) ? 0 : $chartExpenseArr[$v]);
        }

        $netProfitArray = $netProfit;

        $filter['startDateRange'] = 'Jan-' . $year;
        $filter['endDateRange'] = 'Dec-' . $year;

        return view('account::report.quarterly_cash_flow', compact('yearList', 'filter', 'monthList', 'invoiceTotal', 'four_month', 'RevenueTotal', 'chartIncomeArr', 'paymentTotal', 'paySlipTotal', 'billTotal', 'chartExpenseArr', 'netProfitArray'));

    }
}
