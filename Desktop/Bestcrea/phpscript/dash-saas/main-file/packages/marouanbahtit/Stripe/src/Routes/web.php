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
use MarouanBahtit\Stripe\Http\Controllers\StripeController;

Route::group(['middleware' => ['web', 'auth', 'verified','PlanModuleCheck:Stripe']], function () {
    Route::prefix('stripe')->group(function() {
        Route::post('/setting/store', [StripeController::class,'setting'])->name('stripe.setting.store');
    });
});
Route::middleware(['web'])->group(function ()
{
    Route::prefix('stripe')->group(function() {
        Route::post('/plan/company/payment', [StripeController::class,'planPayWithStripe'])->name('plan.pay.with.stripe')->middleware(['auth']);
        Route::get('/plan/company/status', [StripeController::class,'planGetStripeStatus'])->name('plan.get.payment.status')->middleware(['auth']);
    });
    Route::post('/invoice-pay-with-stripe', [StripeController::class, 'invoicePayWithStripe'])->name('invoice.pay.with.stripe');
    Route::get('/stripe/invoice/{invoice_id}/{type}', [StripeController::class, 'getInvoicePaymentStatus'])->name('invoice.stripe');

    Route::post('course/stripe/{slug?}', [StripeController::class,'coursePayWithStripe'])->name('course.pay.with.stripe');
    Route::get('course/stripe/{slug?}', [StripeController::class, 'getCoursePaymentStatus'])->name('course.stripe');

    Route::post('/stripe/{slug?}', [StripeController::class,'contentPayWithStripe'])->name('content.pay.with.stripe');
    Route::get('content/stripe/{slug?}', [StripeController::class, 'getContentPaymentStatus'])->name('content.stripe');

    Route::prefix('hotel/{slug}')->group(function() {
        Route::post('customer/stripe', [StripeController::class,'BookinginvoicePayWithStripe'])->name('booking.stripe.post');
    });
    Route::get('/invoice/stripe/{invoice_id}/{type}', [StripeController::class, 'getBookingInvoicePaymentStatus'])->name('booking.stripe');

    Route::prefix('stripe')->group(function() {
        Route::post('/property/tenant/payment', [StripeController::class,'propertyPayWithStripe'])->name('property.pay.with.stripe')->middleware(['auth']);
        Route::get('/property/tenant/status', [StripeController::class,'propertyGetStripeStatus'])->name('property.get.payment.status.stripe')->middleware(['auth']);
    });

    Route::any('vehicle-booking/stripe/{slug}/{lang?}', [StripeController::class, 'vehicleBookingWithStripe'])->name('vehicle.booking.with.stripe');
    Route::get('vehicle-booking/stripe/status/{slug}/{lang?}', [StripeController::class, 'vehicleBookingStatus'])->name('vehicle.booking.status.stripe');

    Route::post('/memberplan-pay-with-stripe', [StripeController::class, 'memberplanPayWithStripe'])->name('memberplan.pay.with.stripe');
    Route::get('/stripe/invoice/{membershipplan_id}', [StripeController::class, 'getMemberPlanPaymentStatus'])->name('memberplan.stripe');

    Route::post('/beauty-spa-pay-with-stripe/{slug?}', [StripeController::class,'BeautySpaPayWithStripe'])->name('beauty.spa.pay.with.stripe');
    Route::get('/beauty-spa/stripe/{slug?}', [StripeController::class, 'getBeautySpaPaymentStatus'])->name('beauty.spa.stripe');

    Route::post('/bookings-pay-with-stripe/{slug?}', [StripeController::class,'BookingsPayWithStripe'])->name('bookings.pay.with.stripe');
    Route::get('/bookings/stripe/{slug?}', [StripeController::class, 'getBookingsPaymentStatus'])->name('bookings.stripe');
    Route::post('/movie-show-booking-pay-with-stripe/{slug?}', [StripeController::class,'MovieShowBookingPayWithStripe'])->name('movie.show.booking.pay.with.stripe');
    Route::get('/movie-show-booking-system/stripe/{slug?}', [StripeController::class, 'getMovieShowBookingPaymentStatus'])->name('movie.show.booking.stripe');

    Route::post('{slug}/parking-pay-with-stripe/{lang?}', [StripeController::class,'parkingPayWithStripe'])->name('parking.pay.with.stripe');
    Route::get('{slug}/parking/stripe/{id}/{amount}/{lang?}', [StripeController::class, 'getParkingPaymentStatus'])->name('parking.stripe');


    Route::post('/event-show-booking-pay-with-stripe/{slug?}', [StripeController::class,'EventShowBookingPayWithStripe'])->name('event.show.booking.pay.with.stripe');
    Route::get('/event-show-booking-system/stripe/{slug?}', [StripeController::class, 'getEventShowBookingPaymentStatus'])->name('event.show.booking.stripe');

    Route::post('/facilities-pay-with-stripe/{slug?}', [StripeController::class,'FacilitiesPayWithStripe'])->name('facilities.pay.with.stripe');
    Route::get('/facilities/stripe/{slug?}', [StripeController::class, 'getFacilitiesPaymentStatus'])->name('facilities.stripe');


    Route::post('/property-booking-pay-with-stripe/{slug?}', [StripeController::class, 'PropertyBookingPayWithStripe'])->name('property.booking.pay.with.stripe');
    Route::get('/property-booking/stripe/{slug?}', [StripeController::class,'GetPropertyBookingPaymentStatus'])->name('property.booking.stripe');


    Route::post('/vcard/enable-stripe/{id?}', [StripeController::class, 'VcardEnableStripe'])->name('vcard.enable.stripe');
    Route::any('vcard-pay-with-stripe/{id}', [StripeController::class, 'VcardPayWithStripe'])->name('vcard.pay.with.stripe');
    Route::any('stripe-get-vcard-payment/', [StripeController::class, 'VcardGetStripePaymentStatus'])->name('card.vcard.stripe');

    Route::post('/coworking-pay/payment/with-stripe/{slug?}', [StripeController::class, 'CoworkingPayWithStripe'])->name('coworking.pay.payment.with.stripe');
    Route::get('/coworking-space-booking/stripe/{slug?}', [StripeController::class, 'getCoworkingPaymentStatus'])->name('coworking.booking.stripe');

    Route::post('/water-park-pay-with-stripe/{slug?}', [StripeController::class,'WaterParkPayWithStripe'])->name('water.park.pay.with.stripe');
    Route::get('/water-park/stripe/{slug?}', [StripeController::class, 'getWaterParkPaymentStatus'])->name('water.park.stripe');

    // Sports Club And Ground Booking Routes
    Route::post('/sports-club-pay-with-stripe/{slug?}', [StripeController::class, 'SportsAndClubPayWithStripe'])->name('sports.club.pay.with.stripe');
    Route::get('/sports-club/stripe/{slug?}', [StripeController::class, 'getSportsAndClubPaymentStatus'])->name('sports.club.stripe');

    // Sports Club And Ground Membership Plan Routes
    Route::post('/sports-club-plan-pay-with-stripe/{slug?}', [StripeController::class, 'SportsAndClubPlanPayWithStripe'])->name('sports.club.plan.pay.with.stripe');
    Route::get('/sports-club-plan/stripe/{slug?}', [StripeController::class, 'getSportsAndClubPlanPaymentStatus'])->name('get.sports.club.plan.pay.status');

    //Boutique And Desingner Studio
    Route::post('/boutique-designert-studio-pay-with-stripe/{slug?}', [StripeController::class, 'BoutiquePayWithStripe'])->name('boutique.pay.payment.with.stripe');
    Route::get('/boutique-designer-booking/stripe/{slug?}', [StripeController::class, 'getStripeBoutiquePaymentStatus'])->name('boutique.booking.stripe');

    //Influencer Marketing
    Route::post('/brand-deposit-pay-with-stripe/{slug?}', [StripeController::class, 'BrandDepositPayWithStripe'])->name('brand.deposit.pay.payment.with.stripe');
    Route::get('/brand-deposit/stripe/{slug?}', [StripeController::class, 'getStripeBrandDepositPaymentStatus'])->name('brand.deposit.stripe.status');

    // NGO Donation Payment
    Route::post('/ngo-donation-pay-with-stripe/{slug?}', [StripeController::class, 'NGODonationPayWithStripe'])->name('ngo.donation.pay.with.stripe');
    Route::get('/ngo-donation/stripe/{slug?}', [StripeController::class, 'getNGODonationPaymentStatus'])->name('ngo.donation.stripe.status');

    // Art Showcase Art Work Payment Routes
    Route::post('/art-work-pay-with-stripe/{slug?}', [StripeController::class, 'artWorkPayWithStripe'])->name('artwork.pay.with.stripe');
    Route::get('/art-work/stripe/{slug?}', [StripeController::class, 'getArtWorkPaymentStatus'])->name('get.artwork.pay.status');
});
