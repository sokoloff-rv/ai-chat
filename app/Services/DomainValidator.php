<?php

namespace App\Services;

class DomainValidator
{
    /**
     * Проверяет, разрешён ли домен.
     */
    public function isAllowed(?array $allowedDomains, ?string $referer): bool
    {
        if (empty($allowedDomains)) {
            return true;
        }

        $host = $this->extractHost($referer);

        if (!$host) {
            return false;
        }

        return $this->isDomainInList($host, $allowedDomains);
    }

    /**
     * Извлекает хост из URL.
     */
    private function extractHost(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        return parse_url($url, PHP_URL_HOST);
    }

    /**
     * Проверяет, находится ли домен в списке разрешённых.
     */
    private function isDomainInList(string $host, array $allowedDomains): bool
    {
        foreach ($allowedDomains as $domain) {
            $domain = trim($domain);

            if (empty($domain)) {
                continue;
            }

            if ($this->matchesDomain($host, $domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверяет совпадение домена или поддомена.
     */
    private function matchesDomain(string $host, string $domain): bool
    {
        return $host === $domain || str_ends_with($host, '.' . $domain);
    }
}
