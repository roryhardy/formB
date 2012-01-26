<?php
require_once('../FormBackend.php'); // Include the FormB and Line classes.
$req = array('first_name','last_name','message', 'message_options'); // Array of required data fields
$form = new FormB($req); // Construct a Form Backend system $form.
if($form->isSetNotEmpty($_POST)){ // Verify the user has attempted to submit at least once before doing any handling.
	$to[] = "INSERT EMAIL HERE"; // Put your email address into the $to array.
	$form->processReq(); // Verify all specified required fields $req are NOT empty.
	$form->validEmail($_POST['email']); // Validate the email address.
	if(!$form->checkErrors()){ // Procede to send mail if there are no errors!
		$form->sanitizePOST(); // Sanitize the entire POST array to prevent XSS attacks!
		if($form->isSetNotEmpty($_POST['copy']))
			$to[] = $_POST['email']; // The user wants a copy, add their email to the $to array.
		try{ // Attempt to send the message(s).
			// sendMail method, utilizes HTML template message.inc
			$form->sendMail($to,"SUBJECT",'./includes/message.inc',$_POST['first_name']." ".$_POST['last_name'].'<'.$_POST['email'].'>');
			// lazyMail method, does not utilize an HTML email template and sends the message as plain text.
			//$form->lazyMail($to,"SUBJECT",$_POST['first_name']." ".$_POST['last_name'].'<'.$_POST['email'].'>');
		}catch (Exception $e){ // If a fatal error arises, capture it cleanly and output via the built in error system.  Don't use die();
			$form->addError("Unable to send message, please try again.<br>Caught Exception: ".$e->getMessage());
		}
	}
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Sample Form</title>
	<style type="text/css">
		div.errors{ 
			border:2px solid #7f1007;
			border-left-color:#e92516;
			border-top-color:#e92516;
			padding-left:5px;
		}
		div.errors p{
			color:#eb460d;
			font-size:18px;
		}
		div.errors strong{
			color:#a73007;
		}
	</style>
</head>
<body>
<?php
if($form->isSetNotEmpty($_POST) && !$form->checkErrors()){
	include('./includes/success.inc'); // Submit successful, show a confirmation page.
}else{
	$form->printErrors(); // If there are erros, show them to the user.
	include('./includes/form.inc'); // Include the form.
}	
?>
</body>
</html>