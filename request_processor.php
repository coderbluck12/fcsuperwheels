<?php
include_once('admin/inc/functions.php');

if(isset($_POST['request_button']))
{
	// Collecting and validating inputs
	$name = validate_input($_POST['name']);
	$email = validate_input($_POST['email']);
	$phone = validate_input($_POST['phone']);
	$others = validate_input($_POST['others']); // Any other details about the car request
	$time_created = date('Y-m-d H:i:s');
	
	// Prepare the SQL statement with named placeholders
	$stmt = $pdo->prepare("INSERT INTO car_request SET 
			name = :name,
			phone = :phone, 
			email = :email, 
			others = :others,
			time_created = :time_created
			");

	// Bind parameters to the statement
	$stmt->bindParam(':name', $name);
	$stmt->bindParam(':phone', $phone);
	$stmt->bindParam(':email', $email);
	$stmt->bindParam(':others', $others);
	$stmt->bindParam(':time_created', $time_created);

	// Execute the statement
	$stmt->execute();
	
	// Check if the insert was successful
	if ($stmt->rowCount() > 0) {
		// Admin email notification
		$admin_email = "info@fcsuperwheels.com"; // Admin email address
		$admin_subject = "New Car Request from $name";
		$admin_message = "You have received a new car request with the following details:\n\n";
		$admin_message .= "Name: $name\n";
		$admin_message .= "Email: $email\n";
		$admin_message .= "Phone: $phone\n";
		$admin_message .= "Additional Information: $others\n";
		$admin_message .= "Time of Request: $time_created\n";
		
		$admin_headers = "From: noreply@fcsuperwheels.com"; // Set the sender

		// Send email to admin
		mail($admin_email, $admin_subject, $admin_message, $admin_headers);

		// Requester confirmation email
		$requester_subject = "Car Request Received - Firstchoice Superwheels";
		$requester_message = "Dear $name,\n\n";
		$requester_message .= "Thank you for submitting your car request at Firstchoice Superwheels. We have received the following details:\n";
		$requester_message .= "Phone: $phone\n";
		$requester_message .= "Additional Information: $others\n\n";
		$requester_message .= "We will get in touch with you shortly to discuss the available options based on your request.\n\n";
		$requester_message .= "Best regards,\n";
		$requester_message .= "Firstchoice Superwheels Team\n";
		
		$requester_headers = "From: noreply@fcsuperwheels.com"; // Set the sender

		// Send confirmation email to the requester
		mail($email, $requester_subject, $requester_message, $requester_headers);

		// Redirect to confirmation page upon success
		redirect_to('index.php?success');
		exit();
	} else {
		// Redirect to failure page if insert fails
		redirect_to('index.php?fail');
		exit();
	}
}
else
{
	// Redirect if form was not submitted
	redirect_to('index.php');
}
?>
