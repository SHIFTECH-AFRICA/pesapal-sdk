<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about-example', function (): void {
    $this->info('Pesapal Laravel SDK runnable example.');
})->purpose('Describe this example application');
