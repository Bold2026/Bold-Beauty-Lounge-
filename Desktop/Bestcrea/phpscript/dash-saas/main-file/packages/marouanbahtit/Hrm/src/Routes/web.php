<?php

use Illuminate\Support\Facades\Route;
use Workdo\Hrm\Http\Controllers\AllowanceController;
use Workdo\Hrm\Http\Controllers\AllowanceOptionController;
use Workdo\Hrm\Http\Controllers\AllowanceTaxController;
use Workdo\Hrm\Http\Controllers\AnnouncementController;
use Workdo\Hrm\Http\Controllers\AttendanceController;
use Workdo\Hrm\Http\Controllers\AwardController;
use Workdo\Hrm\Http\Controllers\AwardTypeController;
use Workdo\Hrm\Http\Controllers\BranchController;
use Workdo\Hrm\Http\Controllers\CommissionController;
use Workdo\Hrm\Http\Controllers\CompanyContributionController;
use Workdo\Hrm\Http\Controllers\CompanyPolicyController;
use Workdo\Hrm\Http\Controllers\ComplaintController;
use Workdo\Hrm\Http\Controllers\DeductionOptionController;
use Workdo\Hrm\Http\Controllers\DepartmentController;
use Workdo\Hrm\Http\Controllers\DesignationController;
use Workdo\Hrm\Http\Controllers\DocumentController;
use Workdo\Hrm\Http\Controllers\DocumentTypeController;
use Workdo\Hrm\Http\Controllers\EmployeeController;
use Workdo\Hrm\Http\Controllers\EventController;
use Workdo\Hrm\Http\Controllers\HolidayController;
use Workdo\Hrm\Http\Controllers\HrmController;
use Workdo\Hrm\Http\Controllers\IpRestrictController;
use Workdo\Hrm\Http\Controllers\LeaveController;
use Workdo\Hrm\Http\Controllers\LeaveTypeController;
use Workdo\Hrm\Http\Controllers\LoanController;
use Workdo\Hrm\Http\Controllers\LoanOptionController;
use Workdo\Hrm\Http\Controllers\OtherPaymentController;
use Workdo\Hrm\Http\Controllers\OvertimeController;
use Workdo\Hrm\Http\Controllers\PaySlipController;
use Workdo\Hrm\Http\Controllers\PayslipTypeController;
use Workdo\Hrm\Http\Controllers\PromotionController;
use Workdo\Hrm\Http\Controllers\ReportController;
use Workdo\Hrm\Http\Controllers\ResignationController;
use Workdo\Hrm\Http\Controllers\SaturationDeductionController;
use Workdo\Hrm\Http\Controllers\SetSalaryController;
use Workdo\Hrm\Http\Controllers\TaxBracketController;
use Workdo\Hrm\Http\Controllers\TaxRebateController;
use Workdo\Hrm\Http\Controllers\TaxThresholdController;
use Workdo\Hrm\Http\Controllers\TerminationController;
use Workdo\Hrm\Http\Controllers\TerminationTypeController;
use Workdo\Hrm\Http\Controllers\TransferController;
use Workdo\Hrm\Http\Controllers\TravelController;
use Workdo\Hrm\Http\Controllers\WarningController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['web', 'auth', 'verified','PlanModuleCheck:Hrm']], function () {
    Route::prefix('hrm')->group(function () {
        Route::get('/', [HrmController::class, 'index']);
    });
    Route::get('dashboard/hrm', [HrmController::class, 'index'])->name('hrm.dashboard');
    Route::resource('document', DocumentController::class);
    Route::get('/document/{id}/description', [DocumentController::class,'description'])->name('document.description');

    Route::resource('document-type', DocumentTypeController::class);
    // Attendance
    Route::resource('attendance', AttendanceController::class);
    Route::get('bulkattendance', [AttendanceController::class, 'BulkAttendance'])->name('attendance.bulkattendances');
    Route::post('bulkattendance', [AttendanceController::class, 'BulkAttendanceData'])->name('attendance.bulkattendance');
    Route::post('attendance/attendance', [AttendanceController::class, 'attendance'])->name('attendance.attendance');

    // Attendance import

    Route::get('attendance/import/export', [AttendanceController::class, 'fileImportExport'])->name('attendance.file.import');
    Route::post('attendance/import', [AttendanceController::class, 'fileImport'])->name('attendance.import');
    Route::get('attendance/import/modal', [AttendanceController::class, 'fileImportModal'])->name('attendance.import.modal');
    Route::post('attendance/data/import/', [AttendanceController::class, 'AttendanceImportdata'])->name('attendance.import.data');


    // branch
    Route::resource('branch', BranchController::class);
    Route::get('branchnameedit', [BranchController::class, 'BranchNameEdit'])->name('branchname.edit');
    Route::post('branch-settings', [BranchController::class, 'saveBranchName'])->name('branchname.update');
    // department
    Route::resource('department', DepartmentController::class);
    Route::get('departmentnameedit', [DepartmentController::class, 'DepartmentNameEdit'])->name('departmentname.edit');
    Route::post('department-settings', [DepartmentController::class, 'saveDepartmentName'])->name('departmentname.update');
    // designation
    Route::resource('designation', DesignationController::class);
    Route::get('designationnameedit', [DesignationController::class, 'DesignationNameEdit'])->name('designationname.edit');
    Route::post('designation-settings', [DesignationController::class, 'saveDesignationName'])->name('designationname.update');
    // employee
    Route::resource('employee', EmployeeController::class);
    Route::get('employee-grid', [EmployeeController::class, 'grid'])->name('employee.grid');

    Route::post('hrm/employee/getdepartment', [EmployeeController::class, 'getDepartment'])->name('hrm.employee.getdepartment');
    Route::post('hrm/employee/getdesignation', [EmployeeController::class, 'getdDesignation'])->name('hrm.employee.getdesignation');

    //employee import
    Route::get('employee/import/export', [EmployeeController::class, 'fileImportExport'])->name('employee.file.import')->middleware(['auth']);
    Route::post('employee/import', [EmployeeController::class, 'fileImport'])->name('employee.import')->middleware(['auth']);
    Route::get('employee/import/modal', [EmployeeController::class, 'fileImportModal'])->name('employee.import.modal')->middleware(['auth']);
    Route::post('employee/data/import/', [EmployeeController::class, 'employeeImportdata'])->name('employee.import.data')->middleware(['auth']);

    // settig in hrm
    Route::post('hrm/setting/store', [HrmController::class, 'setting'])->name('hrm.setting.store')->middleware(['auth']);
    Route::resource('company-policy', CompanyPolicyController::class);
    Route::get('/company-policy/{id}/description', [CompanyPolicyController::class,'description'])->name('company-policy.description');

    Route::resource('iprestrict', IpRestrictController::class);

    // Leave and Leave type
    Route::resource('leavetype', LeaveTypeController::class);
    Route::get('leave/{id}/action', [LeaveController::class, 'action'])->name('leave.action');
    Route::post('leave/changeaction', [LeaveController::class, 'changeaction'])->name('leave.changeaction');
    Route::post('leave/jsoncount', [LeaveController::class, 'jsoncount'])->name('leave.jsoncount');
    Route::resource('leave', LeaveController::class);
    Route::get('/leave/{id}/description', [LeaveController::class,'description'])->name('leave.description');


    // award
    Route::resource('awardtype', AwardTypeController::class);
    Route::resource('award', AwardController::class);
    Route::get('/award/{id}/description', [AwardController::class,'description'])->name('award.description');

    // transfer
    Route::resource('transfer', TransferController::class);
    Route::get('/transfer/{id}/description', [TransferController::class,'description'])->name('transfer.description');

    // Resignation
    Route::resource('resignation', ResignationController::class);
    Route::get('/resignation/{id}/description', [ResignationController::class,'description'])->name('resignation.description');

    // Travel || Trip
    Route::resource('trip', TravelController::class);
    Route::get('/trip/{id}/description', [TravelController::class,'description'])->name('trip.description');
    
    // Promotion
    Route::resource('promotion', PromotionController::class);
    Route::get('/promotion/{id}/description', [PromotionController::class,'description'])->name('promotion.description');

    //complaint
    Route::resource('complaint', ComplaintController::class);
    Route::get('/complaint/{id}/description', [ComplaintController::class,'description'])->name('complaint.description');

    //warning
    Route::resource('warning', WarningController::class);
    Route::get('/warning/{id}/description', [WarningController::class,'description'])->name('warning.description');

    // tax bracket
    Route::resource('taxbracket', TaxBracketController::class);

    // tax rebate
    Route::resource('taxrebate', TaxRebateController::class);

    // tax threshold
    Route::resource('taxthreshold', TaxThresholdController::class);

    // allowance tax
    Route::resource('allowancetax', AllowanceTaxController::class);

    // Termination and Terminationtype

    Route::resource('terminationtype', TerminationTypeController::class);

    Route::get('termination/{id}/description', [TerminationController::class, 'description'])->name('termination.description');

    Route::resource('termination', TerminationController::class);

    // Announcement
    Route::post('announcement/getemployee', [AnnouncementController::class, 'getemployee'])->name('announcement.getemployee');
    Route::resource('announcement', AnnouncementController::class);
    Route::get('/announcement/{id}/description', [AnnouncementController::class,'description'])->name('announcement.description');

    // Holiday
    Route::get('holiday/calender', [HolidayController::class, 'calender'])->name('holiday.calender');
    Route::resource('holiday', HolidayController::class);

    // Holiday import
    Route::get('holiday/import/export', [HolidayController::class, 'fileImportExport'])->name('holiday.file.import')->middleware(['auth']);
    Route::post('holiday/import', [HolidayController::class, 'fileImport'])->name('holiday.import')->middleware(['auth']);
    Route::get('holiday/import/modal', [HolidayController::class, 'fileImportModal'])->name('holiday.import.modal')->middleware(['auth']);
    Route::post('holiday/data/import/', [HolidayController::class, 'holidayImportdata'])->name('holiday.import.data')->middleware(['auth']);

    // Report
    Route::get('report/monthly/attendance', [ReportController::class, 'monthlyAttendance'])->name('report.monthly.attendance');
    Route::post('report/getdepartment', [ReportController::class, 'getdepartment'])->name('report.getdepartment');
    Route::post('report/getemployee', [ReportController::class, 'getemployee'])->name('report.getemployee');
    Route::get('report/leave', [ReportController::class, 'leave'])->name('report.leave');
    Route::get('employee/{id}/leave/{status}/{type}/{month}/{year}', [ReportController::class, 'employeeLeave'])->name('report.employee.leave');
    Route::get('report/payroll', [ReportController::class, 'Payroll'])->name('report.payroll');
    //payslip type
    Route::resource('payslip-type', PayslipTypeController::class);
    //allowance option
    Route::resource('allowanceoption', AllowanceOptionController::class);
    // loan option
    Route::resource('loanoption', LoanOptionController::class);
    //deduction option
    Route::resource('deductionoption', DeductionOptionController::class);
    // Payroll
    Route::resource('setsalary', SetSalaryController::class);
    Route::get('employee/salary/{eid}', [SetSalaryController::class, 'employeeBasicSalary'])->name('employee.basic.salary');
    Route::post('employee/update/salary/{id}', [SetSalaryController::class, 'employeeUpdateSalary'])->name('employee.salary.update');
    // Allowance
    Route::resource('allowance', AllowanceController::class);
    Route::get('allowances/create/{eid}', [AllowanceController::class, 'allowanceCreate'])->name('allowances.create');
    // commissions
    Route::get('commissions/create/{eid}', [CommissionController::class, 'commissionCreate'])->name('commissions.create');
    Route::resource('commission', CommissionController::class);
    // loan
    Route::get('loans/create/{eid}', [LoanController::class, 'loanCreate'])->name('loans.create');
    Route::resource('loan', LoanController::class);
    // saturationdeduction
    Route::get('saturationdeductions/create/{eid}', [SaturationDeductionController::class, 'saturationdeductionCreate'])->name('saturationdeductions.create');
    Route::resource('saturationdeduction', SaturationDeductionController::class);
    // otherpayment
    Route::get('otherpayments/create/{eid}', [OtherPaymentController::class, 'otherpaymentCreate'])->name('otherpayments.create');
    Route::resource('otherpayment', OtherPaymentController::class);
    // companycontribution
    Route::get('companycontribution/create/{eid}', [CompanyContributionController::class, 'companycontributionCreate'])->name('companycontributions.create');
    Route::resource('companycontribution', CompanyContributionController::class);
    // overtime
    Route::get('overtimes/create/{eid}', [OvertimeController::class, 'overtimeCreate'])->name('overtimes.create');
    Route::resource('overtime', OvertimeController::class);
    // Payslip
    Route::resource('payslip', PaySlipController::class);
    Route::post('payslip/search_json', [PaySlipController::class, 'search_json'])->name('payslip.search_json');
    Route::get('payslip/delete/{id}', [PaySlipController::class, 'destroy'])->name('payslip.delete');
    Route::get('payslip/pdf/{id}/{m}', [PaySlipController::class, 'pdf'])->name('payslip.pdf');
    Route::get('payslip/payslipPdf/{id}', [PaySlipController::class, 'payslipPdf'])->name('payslip.payslipPdf');
    Route::get('payslip/paysalary/{id}/{date}', [PaySlipController::class, 'paysalary'])->name('payslip.paysalary');
    Route::get('payslip/send/{id}/{m}', [PaySlipController::class, 'send'])->name('payslip.send');
    Route::get('payslip/editemployee/{id}', [PaySlipController::class, 'editemployee'])->name('payslip.editemployee');

    Route::post('payslip/editemployee/{id}', [PaySlipController::class, 'updateEmployee'])->name('payslip.updateemployee');

    //Event
    Route::get('event/data/{id}', [EventController::class, 'showData'])->name('eventsshow');
    Route::post('event/getdepartment', [EventController::class, 'getdepartment'])->name('event.getdepartment');
    Route::post('event/getemployee', [EventController::class, 'getemployee'])->name('event.getemployee');
    Route::resource('event', EventController::class);
    // //joining Letter
    Route::get('joiningletter/index', [HrmController::class, 'joiningletterindex'])->name('joiningletter.index');
    Route::post('setting/joiningletter/{lang?}', [HrmController::class, 'joiningletterupdate'])->name('joiningletter.update');
    Route::get('employee/pdf/{id}', [EmployeeController::class, 'joiningletterPdf'])->name('joiningletter.download.pdf');
    Route::get('employee/doc/{id}', [EmployeeController::class, 'joiningletterDoc'])->name('joininglatter.download.doc');

    // //Experience Certificate
    Route::get('experiencecertificate/index', [HrmController::class, 'experiencecertificateindex'])->name('experiencecertificate.index');

    Route::post('setting/exp/{lang?}', [HrmController::class, 'experienceCertificateupdate'])->name('experiencecertificate.update');
    Route::get('employee/exppdf/{id}', [EmployeeController::class, 'ExpCertificatePdf'])->name('exp.download.pdf');
    Route::get('employee/expdoc/{id}', [EmployeeController::class, 'ExpCertificateDoc'])->name('exp.download.doc');

    // //NOC
    Route::get('hrmnoc/index', [HrmController::class, 'hrmnocindex'])->name('hrmnoc.index');

    Route::post('setting/noc/{lang?}', [HrmController::class, 'NOCupdate'])->name('noc.update');
    Route::get('employee/nocpdf/{id}', [EmployeeController::class, 'NocPdf'])->name('noc.download.pdf');
    Route::get('employee/nocdoc/{id}', [EmployeeController::class, 'NocDoc'])->name('noc.download.doc');
});
