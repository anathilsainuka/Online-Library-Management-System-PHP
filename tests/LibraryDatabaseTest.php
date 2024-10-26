<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class LibraryDatabaseTest extends TestCase
{
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'library';
    private $conn;

    protected function setUp(): void
    {
        // Create a connection
        $this->conn = new mysqli($this->host, $this->username, $this->password);

        // Check the connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        // Create database if it does not exist
        $sql = "CREATE DATABASE IF NOT EXISTS $this->database";
        $this->conn->query($sql);

        // Select the database
        $this->conn->select_db($this->database);
    }

    protected function tearDown(): void
    {
        // Close the database connection after each test
        $this->conn->close();
    }

    // Test database connection
    public function testDatabaseConnection()
    {
        $this->assertFalse($this->conn->connect_error, "Database connection should be successful");
    }

    // Test database creation
    public function testDatabaseCreation()
    {
        // Check if the database now exists
        $dbSelected = $this->conn->select_db($this->database);
        $this->assertTrue($dbSelected, "Database should exist after creation");
    }

    // Test Admin table creation
    public function testAdminTableCreation()
    {
        // Admin Table creation SQL
        $sql = "CREATE TABLE IF NOT EXISTS admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            FullName VARCHAR(100),
            AdminEmail VARCHAR(120),
            UserName VARCHAR(100) NOT NULL,
            Password VARCHAR(100) NOT NULL,
            updationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        // Execute the query
        $this->assertTrue($this->conn->query($sql), "Admin table should be created successfully");

        // Verify the table exists
        $result = $this->conn->query("SHOW TABLES LIKE 'admin'");
        $this->assertEquals(1, $result->num_rows, "Admin table should exist");
    }

    // Test Books table creation
    public function testBooksTableCreation()
    {
        // Books Table creation SQL
        $sql = "CREATE TABLE IF NOT EXISTS tblbooks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            BookName VARCHAR(255),
            CatId INT,
            AuthorId INT,
            ISBNNumber VARCHAR(25),
            BookPrice DECIMAL(10,2),
            bookImage VARCHAR(250) NOT NULL,
            isIssued TINYINT(1),
            RegDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UpdationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (AuthorId) REFERENCES tblauthors(id) ON DELETE CASCADE
        )";

        // Execute the query
        $this->assertTrue($this->conn->query($sql), "Books table should be created successfully");

        // Verify the table exists
        $result = $this->conn->query("SHOW TABLES LIKE 'tblbooks'");
        $this->assertEquals(1, $result->num_rows, "Books table should exist");
    }

    // Test Insert Data into Admin table
    public function testInsertAdminData()
    {
        // Insert data into admin table
        $sql = "INSERT INTO admin (FullName, AdminEmail, UserName, Password) 
                VALUES ('John Doe', 'john@example.com', 'admin', 'password123')";

        // Execute the query
        $this->assertTrue($this->conn->query($sql), "Admin data should be inserted successfully");

        // Verify the data was inserted
        $result = $this->conn->query("SELECT * FROM admin WHERE UserName='admin'");
        $this->assertEquals(1, $result->num_rows, "There should be one admin record with the username 'admin'");
    }

    // Test foreign key constraint
    public function testForeignKeyConstraint()
    {
        // Insert an author
        $sql = "INSERT INTO tblauthors (AuthorName) VALUES ('Author Name')";
        $this->assertTrue($this->conn->query($sql), "Author should be inserted");

        // Get the inserted author ID
        $authorId = $this->conn->insert_id;

        // Insert a book linked to the author
        $sql = "INSERT INTO tblbooks (BookName, AuthorId) VALUES ('Book Title', $authorId)";
        $this->assertTrue($this->conn->query($sql), "Book should be inserted linked to the author");

        // Delete the author and ensure the book is also deleted due to ON DELETE CASCADE
        $sql = "DELETE FROM tblauthors WHERE id = $authorId";
        $this->assertTrue($this->conn->query($sql), "Author should be deleted");

        // Verify the book was deleted
        $result = $this->conn->query("SELECT * FROM tblbooks WHERE AuthorId = $authorId");
        $this->assertEquals(0, $result->num_rows, "Book should be deleted when the author is deleted");
    }
}