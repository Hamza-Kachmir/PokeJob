<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../app/Models/Job.php';

class JobTest extends TestCase
{
    private $pdo;
    private $job;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));

        $this->pdo->exec("
            CREATE TABLE jobs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                company_name TEXT,
                status TEXT,
                contact_name TEXT,
                contact_phone TEXT,
                contact_mail TEXT,
                link_annonce TEXT,
                link_linkedin TEXT,
                date_applied TEXT,
                date_relance TEXT,
                notes_perso TEXT,
                company_website TEXT,
                type_candidature TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT
            )
        ");

        $this->job = new Job($this->pdo);
    }

    public function testAddAndGetJob(): void
    {
        $this->assertTrue($this->job->addJob(1, 'PokeJob', 'postule'));
        $jobs = $this->job->getUserJobs(1);
        $this->assertCount(1, $jobs);
        $this->assertEquals('PokeJob', $jobs[0]['company_name']);
    }

    public function testGetJobByIdAndDelete(): void
    {
        $this->job->addJob(1, 'PokeJob', 'postule');
        $id = $this->pdo->lastInsertId();
        $this->assertEquals($id, $this->job->getJobById($id, 1)['id']);
        $this->assertTrue($this->job->deleteJob($id, 1));
        $this->assertNull($this->job->getJobById($id, 1));
    }

    public function testUpdateJobStatus(): void
    {
        $this->job->addJob(1, 'PokeJob', 'postule');
        $id = $this->pdo->lastInsertId();

        $this->assertTrue($this->job->updateJobStatus($id, 1, 'entretien'));
        $fetched = $this->job->getJobById($id, 1);

        $this->assertEquals('entretien', $fetched['status']);
    }
}
