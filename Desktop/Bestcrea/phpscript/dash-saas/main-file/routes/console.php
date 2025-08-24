<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('pc', function () {

    $paths = [
        'storage/',
        'bootstrap/cache/',
        'public/',
        'packages/workdo/',
        'uploads/',
        'resources/lang/',
        '.env'
    ];

    foreach ($paths as $path) {
        $output = [];
        $resultCode = 0;

        exec("sudo chmod -R 777 $path", $output, $resultCode);

        if ($resultCode !== 0) {
            $this->error("Failed to change permissions for $path. Output: " . implode("\n", $output));
        } else {
            $this->info("Permissions changed successfully for $path");
        }
    }

    // Clear various caches
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('optimize:clear');

    $this->info('All caches cleared and permissions set!');
})->describe('Clear all types of caches and set file permissions');


