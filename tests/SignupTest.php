<?php

use PHPUnit\Framework\TestCase;

class SignupTest extends TestCase
{
    protected $dbh;
    protected $testStudentIdFile = 'studentid.txt';

    protected function setUp(): void
    {
        // Set up a mock database connection or use SQLite for testing
        $this->dbh = new PDO('sqlite::memory:');
        $this->dbh->exec("CREATE TABLE tblstudents (
            StudentId INTEGER PRIMARY KEY,
            FullName TEXT,
            MobileNumber TEXT,
            EmailId TEXT,
            Password TEXT,
            Status INTEGER
        )");

        // Create a test file for StudentId
        file_put_contents($this->testStudentIdFile, 0);
    }

    protected function tearDown(): void
    {
        // Clean up the database and test files
        $this->dbh = null;
        unlink($this->testStudentIdFile);
    }

    public function testValidInput()
    {
        // Simulate a signup process
        $_POST['fullanme'] = 'John Doe';
        $_POST['mobileno'] = '1234567890';
        $_POST['email'] = 'johndoe@example.com';
        $_POST['password'] = 'password';
        $_POST['confirmpassword'] = 'password';

        $this->assertTrue($this->valid()); // Assuming valid() is a method you will implement
    }

    public function testDatabaseInsertion()
    {
        // Simulate a signup process with valid data
        $StudentId = file_get_contents($this->testStudentIdFile);
        $StudentId++;

        // Prepare data
        $fname = 'John Doe';
        $mobileno = '1234567890';
        $email = 'johndoe@example.com';
        $password = md5('password');
        $status = 1;

        // Execute the SQL insert
        $sql = "INSERT INTO tblstudents (StudentId, FullName, MobileNumber, EmailId, Password, Status) VALUES (:StudentId, :fname, :mobileno, :email, :password, :status)";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':StudentId', $StudentId, PDO::PARAM_STR);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->execute();

        // Assert that the student was inserted
        $this->assertEquals(1, $query->rowCount());

        // Verify the record exists
        $result = $this->dbh->query("SELECT * FROM tblstudents WHERE EmailId = 'johndoe@example.com'")->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($result);
        $this->assertEquals($fname, $result['FullName']);
        $this->assertEquals($mobileno, $result['MobileNumber']);
    }

    public function testStudentIdIncrement()
    {
        // Simulate reading and writing student ID
        $hits = file($this->testStudentIdFile);
        $hits[0]++;
        file_put_contents($this->testStudentIdFile, $hits[0]);

        // Verify the student ID is incremented
        $this->assertEquals(1, (int)$hits[0]);
    }
    
    // You can add more tests as needed for edge cases
}
