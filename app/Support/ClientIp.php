<?php

namespace App\Support;

use Illuminate\Http\Request;

final class ClientIp
{
    /**
     * Headers that carry the real client IP, most-trusted first.
     *
     * The app trusts proxies for scheme/port detection, which makes the
     * left-most `X-Forwarded-For` entry (and therefore `Request::ip()`)
     * client-controlled and trivially spoofable to defeat IP throttling. These
     * headers are instead set by the edge proxy and overwrite anything the
     * client sends:
     *
     * - `CF-Connecting-IP`: the real visitor when Cloudflare proxies the
     *   traffic (Cloudflare → Railway). Preferred, because behind Cloudflare
     *   the Envoy header below only sees a Cloudflare edge IP.
     * - `X-Envoy-External-Address`: set by Railway's edge Envoy. This is the
     *   real client when Cloudflare is DNS-only (grey-clouded) or absent.
     *
     * @var list<string>
     */
    private const TRUSTED_IP_HEADERS = [
        'CF-Connecting-IP',
        'X-Envoy-External-Address',
    ];

    /**
     * Resolve the real client IP for rate-limiting purposes, falling back to
     * Laravel's resolved IP for local/other environments.
     *
     * @see README.md ("Proxy, IP do cliente e rate limiting") for the proxy
     *      assumptions this relies on — notably that the origin should only be
     *      reachable through the edge proxy for these headers to be trustworthy.
     */
    public static function resolve(Request $request): string
    {
        foreach (self::TRUSTED_IP_HEADERS as $header) {
            $value = $request->headers->get($header);

            if (is_string($value) && filter_var($value, FILTER_VALIDATE_IP) !== false) {
                return $value;
            }
        }

        return $request->ip() ?? '0.0.0.0';
    }
}
