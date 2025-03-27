<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../app/Models/CompanyCounter.php';

class CompanyCounterTest extends TestCase
{
    private $pdo;
    private $counter;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec("CREATE TABLE company_counter (count INTEGER)");
        $this->counter = new CompanyCounter($this->pdo);
    }

    public function testInitialCountIsZero(): void
    {
        $this->assertEquals(0, $this->counter->getCount());
    }

    public function testMultipleIncrements(): void
    {
        $this->counter->increment();
        $this->counter->increment();
        $this->assertEquals(2, $this->counter->getCount());
    }

    public function testResetAfterIncrement(): void
    {
        $this->counter->increment();
        $this->assertTrue($this->counter->reset());
        $this->assertEquals(0, $this->counter->getCount());
    }
}
