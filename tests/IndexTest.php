<?php

use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    protected static $dbh; // Declare the PDO variable as static

    public static function setUpBeforeClass(): void
    {
        // Set up a PDO connection (adjust to your environment)
        $dsn = 'mysql:host=localhost;dbname=library'; // Adjust your DSN here
        $username = 'root'; // Database username
        $password = ''; // Database password

        try {
            self::$dbh = new \PDO($dsn, $username, $password);
            self::$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Start the session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Insert a test user into the database for the login tests
        $email = 'user@example.com';
        $password = md5('userpassword'); // MD5-hashed password

        // Check if the user already exists to avoid duplicates
        $sql = "SELECT * FROM tblstudents WHERE EmailId = :email";
        $stmt = self::$dbh->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $userExists = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$userExists) {
            // Insert user if it does not exist
            $sql = "INSERT INTO tblstudents (EmailId, Password, Status) VALUES (:email, :password, 1)";
            $stmt = self::$dbh->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
        }
    }

    public function testValidUserLogin(): void
    {
        $email = 'user@example.com';
        $password = 'userpassword'; // Plain text password

        // Simulate the login process
        $sql = "SELECT * FROM tblstudents WHERE EmailId = :email AND Password = :password";
        $stmt = self::$dbh->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', md5($password)); // Hash the password
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Assert that the user exists
        $this->assertNotEmpty($user, 'User should exist in the database');

        // Simulate setting session variables
        $_SESSION['login'] = $user['EmailId'] ?? null; // Safely set session variable
        $_SESSION['stdid'] = $user['StudentId'] ?? null;

        // Assert session variables are set correctly
        $this->assertEquals($email, $_SESSION['login']);
        $this->assertEquals($user['StudentId'], $_SESSION['stdid']);
    }

    public function testBlockedAccount(): void
    {
        // Start output buffering
        ob_start();

        // Insert a blocked user
        $email = 'blockeduser@example.com';
        $password = md5('blockedpassword'); // MD5-hashed password

        // Check if the user already exists to avoid duplicates
        $sql = "INSERT INTO tblstudents (EmailId, Password, Status) VALUES (:email, :password, 0)";
        $stmt = self::$dbh->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        // Simulate the login process
        $sql = "SELECT * FROM tblstudents WHERE EmailId = :email AND Password = :password";
        $stmt = self::$dbh->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Assert that the user exists
        $this->assertNotEmpty($user, 'Blocked user should exist in the database');

        // Simulate account check
        if ($user['Status'] == 0) {
            $_SESSION['messages'][] = 'Your Account Has been blocked. Please contact admin.';
        }

        // Assert that the message is set
        $this->assertContains('Your Account Has been blocked. Please contact admin.', $_SESSION['messages']);

        // Clear output buffer
        ob_end_clean();
    }

    public function testInvalidUserLogin(): void
{
    $email = 'wronguser@example.com'; // Invalid email
    $password = 'wrongpassword'; // Invalid password

    // Simulate the login process
    $sql = "SELECT * FROM tblstudents WHERE EmailId = :email AND Password = :password";
    $stmt = self::$dbh->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', md5($password)); // Hash the password
    $stmt->execute();
    $user = $stmt->fetch(\PDO::FETCH_ASSOC); // Use global PDO class

    // Assert that the user does not exist
    $this->assertEmpty($user, 'User should not exist');

    // Simulate login failure
    $_SESSION['messages'] = []; // Ensure session messages are initialized

    // Check if the user variable is empty
    if (empty($user)) {
        $_SESSION['messages'][] = 'Invalid login details.';
    }

    // Assert that the failure message is set correctly
    $this->assertContains('Invalid login details.', $_SESSION['messages']);
}


    public function testEmptyUserLoginFields(): void
    {
        // Start output buffering
        ob_start();

        $email = '';
        $password = '';

        // Simulate login failure for empty fields
        $_SESSION['messages'] = [];

        if (empty($email) || empty($password)) {
            $_SESSION['messages'][] = 'Email or password is required.';
        }

        // Assert that the failure message is set correctly
        $this->assertContains('Email or password is required.', $_SESSION['messages']);

        // Clear output buffer
        ob_end_clean();
    }

    protected function tearDown(): void
    {
        $sql = "DELETE FROM tblstudents WHERE EmailId = :email";
        $stmt = self::$dbh->prepare($sql);

        // Ensure this matches the email created in the setup
        $emailToDelete = 'user@example.com';
        $stmt->bindParam(':email', $emailToDelete);
        $stmt->execute();

        parent::tearDown();
    }
}
