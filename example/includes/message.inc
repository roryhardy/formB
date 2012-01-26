<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>E-mail</title>
</head>
<body style='font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px'>
<?php
// Utilize the Line class here.  Note the <p> in the first print call!!!
print(new Line("<p>Name: ",$_POST['first_name']." ".$_POST['last_name'],1));
print(new Line("Email: ",$_POST['email'],0)); // Last Line call, end the <p> tag.
print('<p>Message:<br><strong>'.nl2br(stripslashes($_POST['message']))."</strong></p>");
?>
</body>
</html>