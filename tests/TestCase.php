<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $db = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? null;

        if ($db === null && function_exists('env')) {
            $db = env('DB_DATABASE');
        }

        if ($db === 'ai_chat') {
            throw new \RuntimeException(
                "Tests aborted: configured to use production database."
            );
        }
    }
}
