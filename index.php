<?php
/*
   This is a simple PHP application written as a demonstration for 
   MB315. This connects to a MySQL database and modifies a "Customer"
   database table. 
*/

$dbh = new PDO('sqlite:customers.db');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

// Pre-prepare all of the SQL queries for this application
$ListSQL = $dbh->prepare("SELECT * FROM Customers");
$GetCustomerSQL = $dbh->prepare("SELECT * FROM Customers WHERE CustomerID=?");
$DeleteSQL = $dbh->prepare("DELETE FROM Customers WHERE CustomerID=?");
$InsertSQL = $dbh->prepare("INSERT INTO Customers (FirstName, LastName, PhoneNumber) VALUES (?, ?, ?)");
$UpdateSQL = $dbh->prepare("UPDATE Customers SET FirstName=?, LastName=?, PhoneNumber=? WHERE CustomerID=?");

echo "<html><body>";

// Delete a customer, this occurs when the user clicks the "delete" link
if( !empty($_REQUEST['delete']) && is_numeric($_REQUEST['delete']) ) 
{
	$DeleteSQL->execute(array($_REQUEST['delete']));	
	$DeleteSQL->closeCursor();
	echo "<p><div style='color: red'>Customer Deleted</div></p>";
}

// Add a new customer, this occurs when the user clicks "submit" on the form at the bottom of the page
if( !empty($_REQUEST['add']) && !empty($_REQUEST['FirstName']) &&
    !empty($_REQUEST['LastName']) && !empty($_REQUEST['PhoneNumber']) )
{
	$InsertSQL->execute(array($_REQUEST['FirstName'],$_REQUEST['LastName'],
	                       $_REQUEST['PhoneNumber']));	
	$InsertSQL->closeCursor();
	echo "<p><div style='color: red'>Customer Added</div></p>";
}

// Edit an existing Customer, this occurs when the user clicks "edit"
if( !empty($_REQUEST['edit']) && is_numeric($_REQUEST['edit']) ) {
	if( !empty($_REQUEST['submit']) ) {
		// Customer information was already edited, updated the DB
		$UpdateSQL->execute(array($_REQUEST['FirstName'],
		                          $_REQUEST['LastName'],
		                          $_REQUEST['PhoneNumber'],
		                          $_REQUEST['CustomerID']) );
		$UpdateSQL->closeCursor();
		echo "<p><div style='color: red'>Customer Updated</div></p>";
	} else {
		// Display the form to allow the editing of a customer
		$GetCustomerSQL->execute(array($_REQUEST['edit']));
		$Customer = $GetCustomerSQL->fetch(PDO::FETCH_ASSOC);
		$GetCustomerSQL->closeCursor();
		echo "<form method='POST' action='?'>";
		echo "<b>Edit Customer Record</b><br />";
		echo "FirstName: <input type='text' name='FirstName' value='".
		     $Customer['FirstName']."' /><br />";
		echo "LastName: <input type='text' name='LastName' value='".
		     $Customer['LastName']."' /><br />";
		echo "PhoneNumber: <input type='text' name='PhoneNumber' value='".
		     $Customer['PhoneNumber']."' /><br />";
		echo "<input type='hidden' name='CustomerID' value='".
		     $Customer['CustomerID']."' /><br />";
		echo "<input type='hidden' name='edit' value='1' />";
		echo "<input type='submit' name='submit' value='Submit' /></form>";
	}
}

// Display a complete list of all customers
$ListSQL->execute();
$Customers = $ListSQL->fetchAll(PDO::FETCH_ASSOC);
$ListSQL->closeCursor();

echo "<p /><table border='1'><tbody>";
echo "<tr><th>First Name</th><th>Last Name</th><th>Phone Number</th><th>&nbsp;</th></tr>";
// This is a "foreach" statement, that loops through all of the variables in the array "$Customers"
foreach( $Customers as $ThisCustomer ) {
	echo "<tr>";
	echo "<td>".$ThisCustomer['FirstName']."</td>";
	echo "<td>".$ThisCustomer['LastName']."</td>";
	echo "<td>".$ThisCustomer['PhoneNumber']."</td>";
	echo "<td><a href='?edit=".$ThisCustomer['CustomerID']."'>Edit</a>&nbsp;";
	echo "<a href='?delete=".$ThisCustomer['CustomerID']."'>Delete</a></td>";
	echo "</tr>";
}
echo "</tbody></table>";

// Display the form to allow the adding of a new customer
echo "<p />";
echo "<form method='POST' action='?'><b>Add New Customer Record</b><br />";
echo "FirstName: <input type='text' name='FirstName' /><br />";
echo "LastName: <input type='text' name='LastName' /><br />";
echo "PhoneNumber: <input type='text' name='PhoneNumber' /><br />";
echo "<input type='submit' name='add' value='Submit' /></form>";

echo "</html></body>";

?>
