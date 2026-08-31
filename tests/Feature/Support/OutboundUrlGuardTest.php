<?php

use App\Support\OutboundUrlGuard;

test('it allows public http and https urls', function (string $url) {
    OutboundUrlGuard::assertSafe($url);
})->with([
    'https://example.com/projeto',
    'http://example.com',
    'https://8.8.8.8/status',
])->throwsNoExceptions();

test('it rejects non-http schemes', function (string $url) {
    OutboundUrlGuard::assertSafe($url);
})->with([
    'ftp://example.com/file',
    'file:///etc/passwd',
    'gopher://example.com',
    'javascript:alert(1)',
])->throws(InvalidArgumentException::class);

test('it rejects hosts that resolve to internal addresses', function (string $url) {
    OutboundUrlGuard::assertSafe($url);
})->with([
    'http://127.0.0.1/',
    'http://localhost/',
    'http://169.254.169.254/latest/meta-data/',
    'http://10.0.0.5/',
    'http://192.168.1.1/',
    'http://[::1]/',
])->throws(InvalidArgumentException::class);
