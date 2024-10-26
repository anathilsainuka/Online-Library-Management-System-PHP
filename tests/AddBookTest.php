<?php
use PHPUnit\Framework\TestCase;

class AddBookTest extends TestCase
{
    protected $dbh;
    protected $addBook; // Placeholder for the class that handles adding a book (you'll need to define this class).

    protected function setUp(): void
    {
        // Mock the PDO class
        $this->dbh = $this->createMock(PDO::class);

        // Initialize your book adding class (assuming it's named AddBook)
        // You'll need to create this class if you haven't already
        $this->addBook = new AddBook($this->dbh); // Pass the mocked PDO instance
    }

    public function testAddBookSuccess()
    {
        // Arrange
        $_POST['add'] = true;
        $_POST['bookname'] = 'Test Book';
        $_POST['category'] = 1;
        $_POST['author'] = 1;
        $_POST['isbn'] = '978-3-16-148410-0';
        $_POST['price'] = 19.99;
        $_FILES['bookpic']['name'] = 'testbook.jpg';
        $_FILES['bookpic']['tmp_name'] = 'path/to/tmp/file';

        // Mock the behavior for file upload (mocking move_uploaded_file is tricky, focus on logic instead)
        // Mock the statement and bind parameters
        $stmt = $this->createMock(PDOStatement::class);
        $this->dbh->method('prepare')->willReturn($stmt);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $this->dbh->method('lastInsertId')->willReturn(1); // Simulating a successful insert

        // Act
        // This part assumes you have a method in your AddBook class to handle the addition.
        $result = $this->addBook->addBook(); // Replace with your actual method call

        // Assert
        $this->assertEquals('Book Listed successfully', $result);
    }

    public function testAddBookInvalidFileType()
    {
        // Arrange
        $_POST['add'] = true;
        $_POST['bookname'] = 'Test Book';
        $_POST['category'] = 1;
        $_POST['author'] = 1;
        $_POST['isbn'] = '978-3-16-148410-0';
        $_POST['price'] = 19.99;
        $_FILES['bookpic']['name'] = 'testbook.txt'; // Invalid file type
        $_FILES['bookpic']['tmp_name'] = 'path/to/tmp/file';

        // Act
        $result = $this->addBook->addBook(); // Replace with your actual method call

        // Assert
        $this->assertEquals('Invalid format. Only jpg / jpeg/ png /gif format allowed', $result);
    }

    public function testAddBookDatabaseFailure()
    {
        // Arrange
        $_POST['add'] = true;
        $_POST['bookname'] = 'Test Book';
        $_POST['category'] = 1;
        $_POST['author'] = 1;
        $_POST['isbn'] = '978-3-16-148410-0';
        $_POST['price'] = 19.99;
        $_FILES['bookpic']['name'] = 'testbook.jpg';
        $_FILES['bookpic']['tmp_name'] = 'path/to/tmp/file';

        // Mock the statement and force failure
        $stmt = $this->createMock(PDOStatement::class);
        $this->dbh->method('prepare')->willReturn($stmt);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->method('execute')->willReturn(false); // Simulating a failed insert

        // Act
        $result = $this->addBook->addBook(); // Replace with your actual method call

        // Assert
        $this->assertEquals('Something went wrong. Please try again', $result);
    }
}
