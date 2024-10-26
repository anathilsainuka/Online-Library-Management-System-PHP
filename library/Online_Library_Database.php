<?php

// Database connection settings
$host = 'localhost';  // Database host
$username = 'root';   // Database username
$password = '';       // Database password
$database = 'library'; // Database name

// Create a connection
$conn = new mysqli($host, $username, $password);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it does not exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists.\n";
} else {
    echo "Error creating database: " . $conn->error;
}

// Select the database
$conn->select_db($database);

// Admin Table
$sql = "CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100),
    AdminEmail VARCHAR(120),
    UserName VARCHAR(100) NOT NULL,
    Password VARCHAR(100) NOT NULL,
    updationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table admin created successfully.\n";
} else {
    echo "Error creating admin table: " . $conn->error;
}

// Authors Table
$sql = "CREATE TABLE IF NOT EXISTS tblauthors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    AuthorName VARCHAR(159),
    creationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table tblauthors created successfully.\n";
} else {
    echo "Error creating tblauthors table: " . $conn->error;
}

// Books Table
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
if ($conn->query($sql) === TRUE) {
    echo "Table tblbooks created successfully.\n";
} else {
    echo "Error creating tblbooks table: " . $conn->error;
}

// Category Table
$sql = "CREATE TABLE IF NOT EXISTS tblcategory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(150),
    Status TINYINT(1),
    CreationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table tblcategory created successfully.\n";
} else {
    echo "Error creating tblcategory table: " . $conn->error;
}

// Issued Book Details Table
$sql = "CREATE TABLE IF NOT EXISTS tblissuedbookdetails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    BookId INT,
    StudentID VARCHAR(150),
    IssuesDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ReturnDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    RetrunStatus TINYINT(1),
    fine INT,
    FOREIGN KEY (BookId) REFERENCES tblbooks(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Table tblissuedbookdetails created successfully.\n";
} else {
    echo "Error creating tblissuedbookdetails table: " . $conn->error;
}

// Students Table
$sql = "CREATE TABLE IF NOT EXISTS tblstudents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    StudentId VARCHAR(100) UNIQUE,
    FullName VARCHAR(120),
    EmailId VARCHAR(120),
    MobileNumber CHAR(11),
    Password VARCHAR(120),
    Status TINYINT(1),
    RegDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table tblstudents created successfully.\n";
} else {
    echo "Error creating tblstudents table: " . $conn->error;
}

// Close the connection
$conn->close();
?>