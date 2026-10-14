<?php

declare(strict_types=1);

namespace Tests\Support;

use Lego\Database\Database;
use PHPUnit\Framework\TestCase;

/**
 * Base for every test under tests/Feature/Database: each test runs inside a
 * transaction on the shared test database and is rolled back afterwards, so
 * tests never see each other's data. Without a database the test is skipped
 * in setUp (see testDatabase() in tests/Pest.php).
 */
abstract class DatabaseTestCase extends TestCase
{
    private ?Database $database = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = \testDatabase();
        $this->database->connection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $connection = $this->database?->connection();
        while ($connection !== null && $connection->transactionLevel() > 0) {
            $connection->rollBack();
        }
        $this->database = null;

        parent::tearDown();
    }
}
