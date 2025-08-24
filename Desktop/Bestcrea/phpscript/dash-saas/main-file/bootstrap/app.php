<?php

use App\Http\Middleware\CustomApiAuth;
use App\Http\Middleware\DomainCheck;
use App\Http\Middleware\SetLang;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'domain-check' => DomainCheck::class,
            'jwt.api.auth' => CustomApiAuth::class,
            'PlanModuleCheck' => \App\Http\Middleware\PlanModuleCheck::class,
        ]);
        // Append middleware to the 'web' group
        $middleware->appendToGroup('web', SetLang::class);
        // Exclude specific routes from CSRF protection
        $middleware->validateCsrfTokens(
            except: ['invoice/paytm/*',
                    'plan-get-paytm-status',
                    'invoice/mollie/*',
                    '/plan/iyzipay/*',
                    '/invoice/iyzipay/*',
                    '/plan/aamarpay/*',
                    '/invoice/aamarpay/*',
                    '/course/iyzipay/*',
                    '*course/mercado*',
                    '/course/aamarpay/*',
                    '/course/paytm*',
                    '/roombooking/iyzipay/*',
                    '/roombooking/aamarpay/*',
                    '*/invoice/pay/with/aamarpay',
                    '/plan/paytab/*',
                    'plan-get-phonepe-status/*',
                    '/invoice/phonepe/*',
                    'course/phonepe/*',
                    '*/get-payment-status/*',
                    '*/cinetpay/*',
                    'plan-easebuzz-payment-notify*',
                    '/invoice/easebuzz/*',
                    '/course/easebuzz*',
                    'plan-get-powertranz-status',
                    '/invoice-powertranz-status/*',
                    '/property-booking-pay-with-stripe/*',
                    '/beauty-spa-pay-with-instamojo/*',
                    '/beauty-spa-payment-status/*',
                    '/bookings-pay-with-instamojo/*',
                    '/bookings/instamojo/*',
                    '/invoice-pay-with-instamojo',
                    '/instamojo/invoice/*',
                    '/bookings-pay-with-paiementpro/*',
                    '/invoice-pay-with/paiementpro/',
                    '/sslcommerz/*',
                    ] // Add your routes here
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
