<?php

use Illuminate\Support\Facades\Route;

test('the forwarded host header is not trusted', function () {
    Route::get('/_test/host', fn (): string => request()->getHost());

    $this->get('/_test/host', ['X-Forwarded-Host' => 'evil.example.com'])
        ->assertOk()
        ->assertSee('localhost')
        ->assertDontSee('evil.example.com');
});

test('the forwarded proto header is still trusted for https detection', function () {
    Route::get('/_test/secure', fn (): string => request()->isSecure() ? 'secure' : 'insecure');

    $this->get('/_test/secure', ['X-Forwarded-Proto' => 'https'])
        ->assertOk()
        ->assertSee('secure');
});
