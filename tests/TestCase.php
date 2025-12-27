<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $testingDb = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $productionDb = self::getProductionDatabaseName();

        if ($productionDb && $testingDb === $productionDb) {
            throw new \RuntimeException(
                sprintf("FATAL: Tests are configured to run against the production database '%s'. Aborting.", $testingDb)
            );
        }
    }

    protected static function getProductionDatabaseName(): ?string
    {
        $envPath = __DIR__ . '/../.env';

        if (!file_exists($envPath)) {
            return null;
        }

        $content = file_get_contents($envPath);

        if (preg_match('/^DB_DATABASE\s*=\s*(.*)$/m', $content, $matches)) {
            return trim(trim($matches[1]), '"\'');
        }

        return null;
    }
}
