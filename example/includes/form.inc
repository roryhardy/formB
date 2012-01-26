<?php
$size = 40; // Set a standard default size of your form here, easier to change field widths.
$tIDX = 0; // Setup to use an auto tab index order.  See any tabindex field!
$arr  = array("Was this form helpful?", "General Inquiry");
?>
<h2>This sample form serves to demonstrate the basic functionality of the FormB (Form Backend) class!</h2>
<form method="post" name="form" id="form"><!-- This form posts to itsself, no need to specify action -->
	<fieldset>
		<legend>Your information</legend>
		<p>First Name *<br>
			<input name="first_name"<?php // This method adds the space before value automatically FormB::printVal('first_name'); // Print out the already submitted data if it exists! ?> type="text" size="<?php echo($size); ?>" tabindex="<?php echo(++$tIDX); ?>">
		</p>
		<p>Last Name *<br>
			<input name="last_name"<?php FormB::printVal('last_name'); ?> type="text" size="<?php echo($size); ?>" tabindex="<?php echo(++$tIDX); ?>">
		</p>
		<p>
			<label for="email">Email Address *</label><br>
			<input name="email"<?php FormB::printVal('email'); ?> type="email" size="<?php echo($size); ?>" tabindex="<?php echo(++$tIDX); ?>">
			<!-- use type="email" for inline HTML5 email validation goodness -->
		</p>
	</fieldset>
	<fieldset>
		<legend>Your message</legend>
		<p>
			<select name="message_options" tabindex="<?php echo(++$tIDX); ?>">
				<?php formB::printOptions($arr, "Please choose a message option"); ?>
			</select>
		</p>
		<p>
			<label for="message">Message... *</label><br>
			<textarea name="message" id="message" cols="35" rows="6" tabindex="<?php echo(++$tIDX); ?>"><?php FormB::printTxt('message'); // use printTxt() since value="" is not necessary for text-areas! ?></textarea>
		</p>
		<input type="checkbox" name="copy" tabindex="<?php echo(++$tIDX); ?>" value="1"<?php echo((FormB::isSetNotEmpty($_POST['copy'])?' checked="checked"':'')); ?>> Send a copy of this email to yourself.
	</fieldset>
	<button type="submit" name="submit" tabindex="<?php echo(++$tIDX); ?>">Submit</button>
</form>