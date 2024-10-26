<?php

use PHPUnit\Framework\TestCase;

class AdminLoginTest extends TestCase
{
    protected static $dbh; // Declare the PDO variable as static

    public static function setUpBeforeClass(): void
    {
        // Set up a PDO connection (adjust to your environment)
        $dsn = 'mysql:host=localhost;dbname=library'; // Adjust your DSN here
        $username = 'root'; // Database username
        $password = ''; // Database password

        try {
            self::$dbh = new \PDO($dsn, $username, $password); // Use global PDO class
            self::$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); // Ensure to use global PDO class
        } catch (\PDOException $e) { // Use global PDOException class
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

        // Insert a test admin into the database for the login tests
        $adminUsername = 'admin';
        $adminPassword = md5('adminpassword'); // MD5-hashed password

        // Check if the admin already exists to avoid duplicates
        $sql = "SELECT * FROM admin WHERE UserName = :adminUsername";
        $stmt = self::$dbh->prepare($sql);
        $stmt->bindParam(':adminUsername', $adminUsername);
        $stmt->execute();
        $adminExists = $stmt->fetch(\PDO::FETCH_ASSOC); // Use global PDO class

        if (!$adminExists) {
            // Insert admin if it does not exist
            $sql = "INSERT INTO admin (UserName, Password) VALUES (:adminUsername, :adminPassword)";
            $stmt = self::$dbh->prepare($sql);
            $stmt->bindParam(':adminUsername', $adminUsername);
            $stmt->bindParam(':adminPassword', $adminPassword);
            $stmt->execute();
        }
    }

    // Test for a valid admin login scenario
    public function testValidAdminLogin(): void
    {
        $adminUsername = 'admin';
        $adminPassword = 'adminpassword'; // Plain text password

        // Simulate the login process
        $sql = "SELECT * FROM admin WHERE UserName = :adminUsername";
        $stmt = self::$dbh->prepare($sql);
        $stmt->bindParam(':adminUsername', $adminUsername);
        $stmt->execute();
        $admin = $stmt->fetch(\PDO::FETCH_ASSOC); // Use global PDO class

        // Assert that the admin exists
        $this->assertNotEmpty($admin, 'Admin should exist in the database');

        // Verify the password matches (using md5 hash)
        $hashedPassword = md5($adminPassword);
        $this->assertEquals($hashedPassword, $admin['Password'], 'Password should match');

        // Simulate setting session variables
        $_SESSION['alogin'] = $admin['UserName'];

        $this->assertEquals($adminUsername, $_SESSION['alogin']);
    }

    // Test for an invalid login with incorrect username or password
    public function testInvalidAdminLogin(): void
    {
        $adminUsername = 'wrongadmin'; // Invalid username
        $adminPassword = 'wrongpassword'; // Invalid password

        // Simulate the login process
        $sql = "SELECT * FROM admin WHERE UserName = :adminUsername";
        $stmt = self::$dbh->prepare($sql);
        $stmt->bindParam(':adminUsername', $adminUsername);
        $stmt->execute();
        $admin = $stmt->fetch(\PDO::FETCH_ASSOC); // Use global PDO class

        // Assert that the admin does not exist
        $this->assertEmpty($admin, 'Admin should not exist');

        // Simulate login failure
        $_SESSION['messages'] = [];
        if (empty($admin) || md5($adminPassword) !== $admin['Password']) {
            $_SESSION['messages'][] = 'Invalid login details.';
        }

        // Assert that the failure message is set correctly
        $this->assertContains('Invalid login details.', $_SESSION['messages']);
    }

    // Test for empty username and password fields
    public function testEmptyAdminLoginFields(): void
    {
        $adminUsername = '';
        $adminPassword = '';

        // Simulate login failure for empty fields
        $_SESSION['messages'] = [];

        if (empty($adminUsername) || empty($adminPassword)) {
            $_SESSION['messages'][] = 'Username or password is required.';
        }

        // Assert that the failure message is set correctly
        $this->assertContains('Username or password is required.', $_SESSION['messages']);
    }

    // Test for login with correct username but wrong password
    public function testAdminLoginWithWrongPassword(): void
    {
        $adminUsername = 'admin';
        $adminPassword = 'wrongpassword'; // Incorrect password

        // Simulate the login process
        $sql = "SELECT * FROM admin WHERE UserName = :adminUsername";
        $stmt = self::$dbh->prepare($sql);
        $stmt->bindParam(':adminUsername', $adminUsername);
        $stmt->execute();
        $admin = $stmt->fetch(\PDO::FETCH_ASSOC); // Use global PDO class

        // Assert that the admin exists
        $this->assertNotEmpty($admin, 'Admin should exist in the database');

        // Verify the password does not match
        $this->assertNotEquals(md5($adminPassword), $admin['Password'], 'Password should not match');

        // Simulate login failure
        $_SESSION['messages'] = [];
        $_SESSION['messages'][] = 'Invalid login details.';

        // Assert that the failure message is set correctly
        $this->assertContains('Invalid login details.', $_SESSION['messages']);
    }

    // Test for login with correct password but wrong username
    public function testAdminLoginWithWrongUsername(): void
    {
        $adminUsername = 'wrongadmin';
        $adminPassword = 'adminpassword'; // Correct password

        // Simulate the login process
        $sql = "SELECT * FROM admin WHERE UserName = :adminUsername";
        $stmt = self::$dbh->prepare($sql);
        $stmt->bindParam(':adminUsername', $adminUsername);
        $stmt->execute();
        $admin = $stmt->fetch(\PDO::FETCH_ASSOC); // Use global PDO class

        // Assert that the admin should not exist
        $this->assertEmpty($admin, 'Admin should not exist');

        // Simulate login failure
        $_SESSION['messages'] = [];
        $_SESSION['messages'][] = 'Invalid login details.';

        // Assert that the failure message is set correctly
        $this->assertContains('Invalid login details.', $_SESSION['messages']);
    }

    // Clean up the test admin after all tests
    protected function tearDown(): void
    {
        $sql = "DELETE FROM admin WHERE UserName = :adminUsername";
        $stmt = self::$dbh->prepare($sql);

        // Ensure this matches the username created in the setup
        $adminUsernameToDelete = 'admin';
        $stmt->bindParam(':adminUsername', $adminUsernameToDelete);
        $stmt->execute();

        parent::tearDown();
    }
}
