<?php

use App\Support\ClientIp;
use Illuminate\Http\Request;

test('it prefers the non-spoofable envoy header over the forwarded ip', function () {
    $request = Request::create('/', server: [
        'REMOTE_ADDR' => '203.0.113.9',
        'HTTP_X_FORWARDED_FOR' => '1.2.3.4',
        'HTTP_X_ENVOY_EXTERNAL_ADDRESS' => '198.51.100.7',
    ]);

    expect(ClientIp::resolve($request))->toBe('198.51.100.7');
});

test('it prefers the cloudflare header over the envoy edge ip', function () {
    // Behind Cloudflare the Envoy header only sees a Cloudflare edge IP; the
    // real visitor is in CF-Connecting-IP.
    $request = Request::create('/', server: [
        'REMOTE_ADDR' => '203.0.113.9',
        'HTTP_X_FORWARDED_FOR' => '1.2.3.4',
        'HTTP_CF_CONNECTING_IP' => '198.51.100.7',
        'HTTP_X_ENVOY_EXTERNAL_ADDRESS' => '172.68.0.1',
    ]);

    expect(ClientIp::resolve($request))->toBe('198.51.100.7');
});

test('it ignores a spoofed envoy header that is not a valid ip', function () {
    $request = Request::create('/', server: [
        'REMOTE_ADDR' => '203.0.113.9',
        'HTTP_X_ENVOY_EXTERNAL_ADDRESS' => 'not-an-ip',
    ]);

    expect(ClientIp::resolve($request))->toBe('203.0.113.9');
});

test('it falls back to the request ip when the envoy header is absent', function () {
    $request = Request::create('/', server: ['REMOTE_ADDR' => '203.0.113.9']);

    expect(ClientIp::resolve($request))->toBe('203.0.113.9');
});
