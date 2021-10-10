<?php

//sets up database, table, and returns mysqli connection
function getConnection() {
	$servername = "localhost";
	$username = "testUser";
	$password = "password";
	$databaseName = 'test_database';

	// Create connection
	$connection = new mysqli($servername, $username, $password);
	// Check connection
	if ($connection->connect_error) {
	  die("Connection failed: " . $connection->connect_error);
	}

	// Create database if it doesn't already exist
	$sql = "CREATE DATABASE IF NOT EXISTS test_database";
	if (! $connection->query($sql) === TRUE) {
		echo json_encode([
			"statusMessage" => "Error sending message"
		]);

		exit;
	}

	//verify valid connection before attempting to create table
	if ($connection instanceof \mysqli) {
		mysqli_select_db($connection, 'test_database');

		$createTableStatement = $connection->query(
				"CREATE TABLE IF NOT EXISTS sent_messages (
				ID int NOT NULL AUTO_INCREMENT,
				PRIMARY KEY(ID),
				sender_name VARCHAR(50) NOT NULL,
				email_address VARCHAR(50) NOT NULL,
				phone_number VARCHAR(50),
				message LONGTEXT,
				sent_time DATETIME DEFAULT CURRENT_TIMESTAMP)"
		);


		if (! $createTableStatement === TRUE) {
			echo json_encode([
				"statusMessage" => "Error sending message"
			]);

			exit;
		}
	}

	return $connection;
}

//Begin main
$statusMessage = "";

if (isset($_POST)) {
	/* verify all required fields are set
	 * just in case something goes terribly wrong on the front end
	 */
	if (! isset($_POST['name'])) {
		$statusMessage = "Name cannot be empty";
	} else if (! isset($_POST['email'])) {
		$statusMessage = "Email cannot be empty";
	} else if (! isset($_POST['message'])) {
		$statusMessage = "Message cannot be empty";
	}

	$missingField = $statusMessage ? true : false;

	if (! $missingField) {

		$connection = getConnection();

		if ($connection instanceof \mysqli) {
			$phoneNumber = $_POST["phone"] ?? null;

			//prepare insert statement
			$insert = $connection->prepare(
				'INSERT INTO sent_messages
				(sender_name, email_address, phone_number, message)
				VALUES (?, ?, ?, ?)'
			);

			//bind parameters based on data passed in from post
			$insert->bind_param(
				"ssss",
				$_POST["name"],
				$_POST["email"],
				$phoneNumber,
				$_POST["message"]
			);

			//insert
			$insert->execute();

			//close connection after insert
			$connection->close();

			/* use php mail function to send email
			 * this assumes that user/tester has configured php mail function correctly
			 */
			mail("guy-smiley@example.com", "message test", $_POST["message"]);

			$statusMessage = "Message sent successfully";
		} else {
			$statusMessage = "Error getting connection to database";
		}
	}
} else {
	$statusMessage = "Error submitting form";

}

//echo message to be displayed in alert
echo json_encode([
	"statusMessage" => $statusMessage
]);
