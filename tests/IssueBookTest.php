<?php
use PHPUnit\Framework\TestCase;

class IssueBookTest extends TestCase
{
    protected $dbh;

    protected function setUp(): void
    {
        // Create a mock database connection
        $this->dbh = $this->createMock(PDO::class);
    }

    public function testIssueBookSuccess()
    {
        // Arrange
        $studentId = 'STUDENT123';
        $bookId = 'BOOK456';
        $isIssued = 1;

        // Create a mock statement
        $stmt = $this->createMock(PDOStatement::class);
        $this->dbh->method('prepare')->willReturn($stmt);
        
        // Set up the behavior of the statement mock
        $stmt->method('bindParam')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $this->dbh->method('lastInsertId')->willReturn('1'); // Simulating a successful insert as a string

        // Act
        $sql = "INSERT INTO tblissuedbookdetails(StudentID, BookId) VALUES(:studentid, :bookid);
                UPDATE tblbooks SET isIssued=:isissued WHERE id=:bookid;";

        // Prepare and execute the statement
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
        $query->bindParam(':bookid', $bookId, PDO::PARAM_STR);
        $query->bindParam(':isissued', $isIssued, PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $this->dbh->lastInsertId();

        // Assert
        $this->assertEquals('1', $lastInsertId, "Book should be issued successfully.");
    }

    public function testIssueBookFailure()
    {
        // Arrange
        $studentId = 'STUDENT123';
        $bookId = 'BOOK456';
        $isIssued = 1;

        // Create a mock statement
        $stmt = $this->createMock(PDOStatement::class);
        $this->dbh->method('prepare')->willReturn($stmt);
        
        // Set up the behavior of the statement mock
        $stmt->method('bindParam')->willReturn(true);
        $stmt->method('execute')->willReturn(false); // Simulating a failure to execute

        // Act
        $sql = "INSERT INTO tblissuedbookdetails(StudentID, BookId) VALUES(:studentid, :bookid);
                UPDATE tblbooks SET isIssued=:isissued WHERE id=:bookid;";

        // Prepare and execute the statement
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
        $query->bindParam(':bookid', $bookId, PDO::PARAM_STR);
        $query->bindParam(':isissued', $isIssued, PDO::PARAM_STR);
        $result = $query->execute();

        // Assert
        $this->assertFalse($result, "Book issue should fail due to execution failure.");
    }
}
