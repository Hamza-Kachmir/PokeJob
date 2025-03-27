<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../app/Models/User.php';

class UserTest extends TestCase
{
    private $pdo;
    private $user;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                first_name TEXT,
                last_name TEXT,
                email TEXT UNIQUE,
                password TEXT,
                photo TEXT
            )
        ");
        $this->user = new User($this->pdo);
    }

    public function testAddAndGetUser(): void
    {
        $this->assertTrue($this->user->addUser('Toto', 'Test', 'toto@pokejob.com', 'password'));
        $fetched = $this->user->getUserByEmail('toto@pokejob.com');
        $this->assertEquals('Toto', $fetched['first_name']);
    }

    public function testGetUserByEmailNotFound(): void
    {
        $this->assertFalse($this->user->getUserByEmail('absent@pokejob.com'));
    }

    public function testUpdateEmailAndPassword(): void
    {
        $this->user->addUser('Toto', 'Test', 'toto@pokejob.com', 'password');
        $id = $this->pdo->lastInsertId();

        $this->assertTrue(
            $this->user->updateEmailPasswordAndPhoto($id, 'toto_updated@pokejob.com', 'newpassword', null)
        );

        $updated = $this->user->getUserByEmail('toto_updated@pokejob.com');
        $this->assertEquals('toto_updated@pokejob.com', $updated['email']);
    }

    public function testDeleteUser(): void
    {
        $this->user->addUser('Toto', 'Test', 'toto@pokejob.com', 'password');
        $id = $this->pdo->lastInsertId();
        $this->assertTrue($this->user->delete($id));
        $this->assertFalse($this->user->getUserByEmail('toto@pokejob.com'));
    }

    public function testEmailExistsForAnotherUser(): void
    {
        $this->user->addUser('Toto', 'Test', 'toto@pokejob.com', 'password');
        $id = $this->pdo->lastInsertId();
        $this->assertTrue($this->user->emailExistsForAnotherUser('toto@pokejob.com', $id + 1));
        $this->assertFalse($this->user->emailExistsForAnotherUser('toto@pokejob.com', $id));
    }
}
