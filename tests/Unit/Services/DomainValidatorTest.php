<?php

namespace Tests\Unit\Services;

use App\Services\DomainValidator;
use Tests\TestCase;

class DomainValidatorTest extends TestCase
{
    private DomainValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new DomainValidator();
    }

    public function test_allows_all_domains_when_list_is_empty(): void
    {
        $result = $this->validator->isAllowed(null, 'https://any-domain.com');

        $this->assertTrue($result);
    }

    public function test_allows_all_domains_when_list_is_empty_array(): void
    {
        $result = $this->validator->isAllowed([], 'https://any-domain.com');

        $this->assertTrue($result);
    }

    public function test_denies_when_referer_is_missing(): void
    {
        $result = $this->validator->isAllowed(['example.com'], null);

        $this->assertFalse($result);
    }

    public function test_allows_exact_domain_match(): void
    {
        $result = $this->validator->isAllowed(
            ['example.com'],
            'https://example.com/page'
        );

        $this->assertTrue($result);
    }

    public function test_allows_subdomain(): void
    {
        $result = $this->validator->isAllowed(
            ['example.com'],
            'https://sub.example.com/page'
        );

        $this->assertTrue($result);
    }

    public function test_allows_deep_subdomain(): void
    {
        $result = $this->validator->isAllowed(
            ['example.com'],
            'https://deep.sub.example.com/page'
        );

        $this->assertTrue($result);
    }

    public function test_denies_different_domain(): void
    {
        $result = $this->validator->isAllowed(
            ['example.com'],
            'https://evil.com/page'
        );

        $this->assertFalse($result);
    }

    public function test_denies_similar_but_different_domain(): void
    {
        $result = $this->validator->isAllowed(
            ['example.com'],
            'https://notexample.com/page'
        );

        $this->assertFalse($result);
    }

    public function test_allows_multiple_domains(): void
    {
        $allowedDomains = ['example.com', 'test.com', 'demo.org'];

        $this->assertTrue($this->validator->isAllowed($allowedDomains, 'https://example.com'));
        $this->assertTrue($this->validator->isAllowed($allowedDomains, 'https://test.com'));
        $this->assertTrue($this->validator->isAllowed($allowedDomains, 'https://demo.org'));
        $this->assertFalse($this->validator->isAllowed($allowedDomains, 'https://evil.com'));
    }

    public function test_handles_domains_with_whitespace(): void
    {
        $result = $this->validator->isAllowed(
            ['  example.com  ', 'test.com'],
            'https://example.com/page'
        );

        $this->assertTrue($result);
    }

    public function test_ignores_empty_domains_in_list(): void
    {
        $result = $this->validator->isAllowed(
            ['example.com', '', '  ', 'test.com'],
            'https://test.com/page'
        );

        $this->assertTrue($result);
    }

    public function test_works_with_different_protocols(): void
    {
        $allowedDomains = ['example.com'];

        $this->assertTrue($this->validator->isAllowed($allowedDomains, 'http://example.com'));
        $this->assertTrue($this->validator->isAllowed($allowedDomains, 'https://example.com'));
    }

    public function test_works_with_ports(): void
    {
        $result = $this->validator->isAllowed(
            ['example.com'],
            'https://example.com:8080/page'
        );

        $this->assertTrue($result);
    }

    public function test_denies_invalid_url(): void
    {
        $result = $this->validator->isAllowed(
            ['example.com'],
            'not-a-valid-url'
        );

        $this->assertFalse($result);
    }
}
