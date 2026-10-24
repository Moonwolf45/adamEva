<?php

declare(strict_types=1);

namespace App\Service;

final class IpPrefixNormalizer
{
    /**
     * IPv4: первые 3 октета → "91.213.152"
     * IPv6: первые 3 группы → "2a02:2e0:3"
     *
     * Все IP в пределах этого префикса, как правило,
     * относятся к одному оператору / одному городу.
     */
    public function normalize(string $ip): string
    {
        if (str_contains($ip, ':')) {
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 3));
        }

        $parts = explode('.', $ip);
        return implode('.', array_slice($parts, 0, 3));
    }
}
