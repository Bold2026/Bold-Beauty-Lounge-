<?php

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

use Illuminate\Support\Facades\Route;
use Workdo\Paypal\Http\Controllers\PaypalController;

Route::group(['middleware' => ['web', 'auth', 'verified', 'PlanModuleCheck:Paypal']], function () {
    Route::prefix('paypal')->group(function () {
        Route::post('/setting/store', [PaypalController::class, 'setting'])->name('paypal.setting.store');
    });
});
Route::middleware(['web'])->group(function () {
    Route::post('plan-pay-with/paypal', [PaypalController::class, 'planPayWithPaypal'])->name('plan.pay.with.paypal')->middleware(['auth']);
    Route::get('plan-get-paypal-status/{plan_id}', [PaypalController::class, 'planGetPaypalStatus'])->name('plan.get.paypal.status')->middleware(['auth']);
    Route::get('/invoice/paypal/{invoice_id}/{amount}/{type}', [PaypalController::class, 'getInvoicePaymentStatus'])->name('invoice.paypal');

    Route::post('course-pay-with-paypal/{slug?}', [PaypalController::class, 'coursePayWithPaypal'])->name('course.pay.with.paypal');
    Route::get('{id}/course-get-payment-status{slug?}', [PaypalController::class, 'GetCoursePaymentStatus'])->name('course.paypal');

    Route::post('content-pay-with-paypal/{slug?}', [PaypalController::class, 'contentPayWithPaypal'])->name('content.pay.with.paypal');
    Route::get('{id}/content-get-payment-status{slug?}', [PaypalController::class, 'GetContentPaymentStatus'])->name('content.paypal');

    Route::prefix('hotel/{slug}')->group(function () {
        Route::post('pay-with/paypal', [PaypalController::class, 'BookingPayWithPaypal'])->name('pay.with.paypal');
        Route::get('{amount}/get-payment-paypal-status/{couponid}', [PaypalController::class, 'GetBookingPaypalPaymentStatus'])->name('booking.get.payment.status.paypal');
    });
    Route::post('/invoice-pay-with/paypal', [PaypalController::class, 'invoicePayWithPaypal'])->name('invoice.pay.with.paypal');

    Route::prefix('paypal')->group(function () {
        Route::post('/property/tenant/payment', [PaypalController::class, 'propertyPayWithPaypal'])->name('property.pay.with.paypal')->middleware(['auth']);
        Route::get('/property/tenant/status', [PaypalController::class, 'propertyGetPaypalStatus'])->name('property.get.paypal.status')->middleware(['auth']);
    });

    Route::any('vehicle-booking-payment/paypal/status', [PaypalController::class, 'vehicleBookingStatus'])->name('vehicle.booking.paypal.status');
    Route::any('vehicle-booking/paypal/{slug}/{lang?}', [PaypalController::class, 'vehicleBookingWithPaypal'])->name('vehicle.booking.with.paypal');

    Route::post('/memberplan-pay-with-paypal', [PaypalController::class, 'memberplanPayWithpaypal'])->name('memberplan.pay.with.paypal');
    Route::get('/paypal/invoice/{membershipplan_id}/{amount}', [PaypalController::class, 'getMemberPlanPaymentStatus'])->name('memberplan.paypal');

    Route::get('/beauty-spa-payment-paypal-status/{slug?}', [PaypalController::class, 'GetPaypalBeautySpaPaymentStatus'])->name('beauty.spa.paypal.status');
    Route::post('/beauty-spa-pay-with-paypal/{slug?}', [PaypalController::class, 'BeautySpaPayWithPaypal'])->name('beauty.spa.pay.with.paypal');

    Route::post('/bookings-pay-with-paypal/{slug?}', [PaypalController::class, 'BookingsPayWithPaypal'])->name('bookings.pay.with.paypal');
    Route::get('/bookings-payment-status/{slug?}', [PaypalController::class, 'GetBookingsPaymentStatus'])->name('bookings.paypal');
    Route::get('/movie-show-booking-payment-status/{slug?}', [PaypalController::class, 'GetMovieShowBookingPaymentStatus'])->name('movie.show.booking.paypal');
    Route::post('/movie-show-booking-pay-with-paypal/{slug?}', [PaypalController::class, 'MovieShowBookingPayWithPaypal'])->name('movie.show.booking.pay.with.paypal');

    Route::post('{slug}/parking-pay-with-paypal/{lang?}', [PaypalController::class, 'parkingPayWithPaypal'])->name('parking.pay.with.paypal');
    Route::get('{slug}/parking-payment-status/{id}/{lang?}', [PaypalController::class, 'GetParkingPaymentStatus'])->name('parking.paypal');


    Route::post('/event-show-booking-pay-with-paypal/{slug?}', [PaypalController::class, 'EventShowBookingPayWithPaypal'])->name('event.show.booking.pay.with.paypal');
    Route::get('/event-show-booking-payment-paypal-status/{slug?}', [PaypalController::class, 'GetEventShowBookingPaymentStatus'])->name('event.show.booking.paypal');

    Route::post('/facilities-pay-with-paypal/{slug?}', [PaypalController::class, 'FacilitiesPayWithPaypal'])->name('facilities.pay.with.paypal');
    Route::get('/facilities-payment-status/{slug?}', [PaypalController::class, 'GetFacilitiesPaymentStatus'])->name('facilities.paypal');

    Route::post('/property-booking-pay-with-paypal/{slug?}', [PaypalController::class, 'PropertyBookingPayWithPaypal'])->name('property.booking.pay.with.paypal');
    Route::get('/property-booking-payment-status/{slug?}', [PaypalController::class, 'GetPropertyBookingPaymentStatus'])->name('property.booking.paypal');

    Route::post('/vcard/enable-paypal/{id?}', [PaypalController::class, 'VcardEnablePaypal'])->name('vcard.enable.paypal');
    Route::any('vcard-pay-with-paypal/{id}', [PaypalController::class, 'VcardPayWithPaypal'])->name('vcard.pay.with.paypal');
    Route::any('paypal-get-vcard-payment/{id}', [PaypalController::class, 'VcardGetPaymentStatus'])->name('card.status.vcard.paypal');

    Route::post('/coworking-pay-with-paypal/{slug?}', [PaypalController::class, 'CoworkingPayWithPaypal'])->name('coworking.pay.with.paypal');
    Route::get('/coworking-space-booking/paypal/{slug?}', [PaypalController::class, 'getCoworkingPaymentStatus'])->name('coworking.booking.paypal');

    Route::post('/water-park-pay-with-paypal/{slug?}', [PaypalController::class, 'WaterParkPayWithPaypal'])->name('water.park.pay.with.paypal');
    Route::get('/water-park-payment-paypal-status/{slug?}', [PaypalController::class, 'GetPaypalWaterParkPaymentStatus'])->name('water.park.paypal.status');

    // Sports Club And Ground Booking Routes
    Route::post('/sports-club-pay-with-paypal/{slug?}', [PaypalController::class, 'SportsAndClubPayWithPaypal'])->name('sports.club.pay.with.paypal');
    Route::get('/sports-club-payment-paypal-status/{slug?}', [PaypalController::class, 'GetSportsAndClubPaymentStatus'])->name('sports.club.paypal.status');

    // Sports Club And Ground Membership Plan Routes
    Route::post('/sports-club-plan-pay-with-paypal/{slug?}', [PaypalController::class, 'SportsAndClubPlanPayWithPaypal'])->name('sports.club.plan.pay.with.paypal');
    Route::get('/sports-club-plan-payment-paypal-status/{slug?}', [PaypalController::class, 'GetSportsAndClubPlanPaymentStatus'])->name('sports.club.paypal.plan.status');

    //Boutique And Desingner Studio
    Route::post('/boutique-designert-studio-pay-with-paypal/{slug?}', [PaypalController::class, 'BoutiquePayWithPaypal'])->name('boutique.pay.payment.with.paypal');
    Route::get('/boutique-designer-booking/paypal/{slug?}', [PaypalController::class, 'getPaypalBoutiquePaymentStatus'])->name('boutique.booking.paypal');

     //Infleuncer MArketing Barnd Deposit Payment 
     Route::post('/brand-deposit-pay-with-paypal/{slug?}', [PaypalController::class, 'BrandDepositPayWithPaypal'])->name('brand.deposit.pay.payment.with.paypal');
     Route::get('/brand-deposit/paypal/{slug?}', [PaypalController::class, 'getPaypalBrandDepositPaymentStatus'])->name('brand.deposit.paypal.status');

    // NGO Donation Payment 
    Route::post('/ngo-donation-pay-with-paypal/{slug?}', [PaypalController::class, 'NGODonationPayWithPaypal'])->name('ngo.donation.pay.with.paypal');
    Route::get('/ngo-donation/paypal/{slug?}', [PaypalController::class, 'getPaypalNGODonationPaymentStatus'])->name('ngo.donation.paypal.status');
     
    // Art Showcase Art Work Payment Routes
    Route::post('/art-work-pay-with-paypal/{slug?}', [PaypalController::class, 'artWorkPayWithPaypal'])->name('artwork.pay.with.paypal');
    Route::get('/art-work/paypal/{slug?}', [PaypalController::class, 'getArtWorkPaymentStatus'])->name('get.artwork.paypal.status');
});
