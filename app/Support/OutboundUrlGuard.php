<?php

namespace App\Support;

use InvalidArgumentException;

final class OutboundUrlGuard
{
    /**
     * Ensure a URL is safe to fetch server-side.
     *
     * Rejects anything that is not an absolute http/https URL, or whose host
     * resolves to a private, reserved, loopback or link-local address. This
     * blocks SSRF against cloud metadata endpoints (169.254.169.254), loopback
     * services and internal network hosts.
     *
     * @throws InvalidArgumentException when the URL is unsafe.
     */
    public static function assertSafe(string $url): void
    {
        $parts = parse_url($url);

        $scheme = strtolower($parts['scheme'] ?? '');

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Somente URLs http ou https são permitidas.');
        }

        // parse_url keeps the brackets around IPv6 literals (e.g. "[::1]").
        $host = trim($parts['host'] ?? '', '[]');

        if ($host === '') {
            throw new InvalidArgumentException('A URL informada não possui um host válido.');
        }

        foreach (self::resolveIps($host) as $ip) {
            if (! self::isPublicIp($ip)) {
                throw new InvalidArgumentException('O host informado aponta para um endereço de rede interno.');
            }
        }
    }

    /**
     * Resolve a host to the IP addresses it points at. A host that fails to
     * resolve yields an empty list — the subsequent HTTP request will fail on
     * its own, and there is nothing internal to protect against.
     *
     * @return list<string>
     */
    private static function resolveIps(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return [$host];
        }

        $ips = gethostbynamel($host) ?: [];

        foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $record) {
            if (isset($record['ipv6']) && is_string($record['ipv6'])) {
                $ips[] = $record['ipv6'];
            }
        }

        return $ips;
    }

    private static function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) !== false;
    }
}
