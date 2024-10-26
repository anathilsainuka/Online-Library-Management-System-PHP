<?php

use PHPUnit\Framework\TestCase;

class ChangePasswordTest extends TestCase
{
    protected $dbh;

    protected function setUp(): void
    {
        // Mock the PDO class
        $this->dbh = $this->createMock(PDO::class);
    }

    public function testValidPasswordChange()
    {
        // Simulating user input
        $_POST['password'] = md5('current_password'); // Simulated current password
        $_POST['newpassword'] = md5('new_password');  // Simulated new password
        $_POST['confirmpassword'] = md5('new_password'); // Simulated confirm password
        $_SESSION['login'] = 'user@example.com'; // Simulated user email

        // Mock the password SELECT query
        $stmtSelect = $this->createMock(PDOStatement::class);
        
        // Set expectations for the SELECT query
        $stmtSelect->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmtSelect->expects($this->once())
            ->method('fetchAll')
            ->willReturn([new stdClass()]); // Simulating a returned record

        // Set up the prepare method to return the select statement
        $this->dbh->method('prepare')
            ->with($this->equalTo('SELECT Password FROM tblstudents WHERE EmailId=:email and Password=:password'))
            ->willReturn($stmtSelect);

        // Mock the password UPDATE query
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtUpdate->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Set up the prepare method to return the update statement when called for updating
        $this->dbh->method('prepare')
            ->with($this->equalTo('UPDATE tblstudents SET Password=:newpassword WHERE EmailId=:email'))
            ->willReturn($stmtUpdate);

        // Call the function that changes the password
        $result = $this->changePassword();

        // Assert that the password change was successful
        $this->assertEquals("Your Password successfully changed", $result);
    }


    public function testInvalidCurrentPassword()
    {
        $_POST['password'] = md5('wrong_password');
        $_POST['newpassword'] = md5('new_password');
        $_POST['confirmpassword'] = md5('new_password');
        $_SESSION['login'] = 'user@example.com';

        // Mock the password SELECT query
        $stmtSelect = $this->createMock(PDOStatement::class);
        $stmtSelect->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmtSelect->expects($this->once())
            ->method('rowCount')
            ->willReturn(0); // Simulating no records found

        $this->dbh->method('prepare')
            ->willReturn($stmtSelect);

        // Call the function that changes the password
        $result = $this->changePassword();

        // Assert that the error message is returned correctly
        $this->assertEquals("Your current password is wrong", $result);
    }

    // Function to simulate password change logic
    private function changePassword()
    {
        if (strlen($_SESSION['login']) == 0) {
            header('location:index.php');
            return;
        }

        $password = md5($_POST['password']);
        $newpassword = md5($_POST['newpassword']);
        $email = $_SESSION['login'];

        $sql = "SELECT Password FROM tblstudents WHERE EmailId=:email and Password=:password";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() > 0) {
            $con = "UPDATE tblstudents SET Password=:newpassword WHERE EmailId=:email";
            $chngpwd1 = $this->dbh->prepare($con);
            $chngpwd1->bindParam(':email', $email, PDO::PARAM_STR);
            $chngpwd1->bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
            $chngpwd1->execute();
            return "Your Password successfully changed";
        } else {
            return "Your current password is wrong";
        }
    }
}
