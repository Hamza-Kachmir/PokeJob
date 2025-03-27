<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../app/Models/Contact.php';

class ContactTest extends TestCase
{
    private $pdo;
    private $contact;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec("
            CREATE TABLE contacts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                first_name TEXT,
                last_name TEXT,
                email TEXT,
                subject TEXT,
                message TEXT
            )
        ");
        $this->contact = new Contact($this->pdo);
    }

    public function testAddContact(): void
    {
        $this->assertTrue($this->contact->addContact(1, 'Toto', 'Test', 'toto@pokejob.com', 'Sujet', 'Message'));
        $count = (int)$this->pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
        $this->assertEquals(1, $count);
    }
}
