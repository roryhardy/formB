<?php
/*
 =================================================================================================================================================================
 Name        : FormBackend
 Author      : GneatGeek [Rory Cronin-Hardy]
 Version     : 1.6
 Description : FormBackend processes form input.  This file contains both the FormB and Line classes.
 Copyright   : You may use this code in your own projects as long as this copyright is left in place.
 All code is provided AS-IS. This code is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 © Rory Cronin-Hardy 2011
 Special Thanks	: Special thanks to [Steven Smith] for his help with testing and for programming tips and ideas!
 =================================================================================================================================================================
 */

require_once('config.inc');


class FormB{
	private $_errors, $_required, $_handle;

	/* Constructor Method
	 * Takes: Required field or array of required fields $requiredVars.
	 *        Mysql handle $mysqlHandle.  Used with checkIP and logIP methods.
	 */
	public function __construct($requiredVars="", $mysqlHandle=""){
		$this->_required = $requiredVars;
		$this->_handle   = $mysqlHandle; # Used for database stuff in the advanced section.
	}

	/* Is Set Not Empty Method
	 * Use:     Verify if a variable is set and is not empty.
	 * Takes:   Variable reference $var
	 * Returns: True if the variable is set AND not empty.
	 *            False otherwise.
	 */
	final public static function isSetNotEmpty(&$var){
		return(isset($var) && !empty($var));
	}

	/* Add Custom Error Method
	 * Takes:        Error Message $errorMessage
	 * Description:  Adds error message to $_errors array
	 *               Use this method for specialized/unique error handling
	 *               Note it will not allow you to add empty errors since they are meaningless.
	 */
	final public function addError($errorMessage){
		if(!empty($errorMessage))
			$this->_errors[] = $errorMessage;
	}

	/* Check Errors Method
	 * Returns: False if there are no errors and true if errors exist.
	 */
	final public function checkErrors(){
		return(!empty($this->_errors));
	}

	/* Process Required Fields Method
	 * NOTE: This method should only be called once!
	 * Use:  Verifies that all values in the $_required array are set and not empty
	 *       Will capitalize the first letter of each word for the form field.  Will also convert '_' to ' '
	 *       So 'first_name' will be printed as 'First Name'
	 * Uses: $_REQUEST since that is a combination of $_GET, $_POST, and $_COOKIE...
	 *       Don't mix and match GET/POST with the same field names or one will get overwritten by the
	 *         other as defined in php.ini.  Normally GPC -> Get, Post, Cookie.
	 */
	public function processReq(){
		if(empty($this->_required))
			throw new Exception('processReq() called with no requirements specified. No need to call this method.');
		if(is_array($this->_required)){
			foreach($this->_required as $v)
				$this->setErr($v);
		}else
			$this->setErr($this->_required);
	}

	private function setErr($e){ # Helper method for processReq()
		if(!empty($e) && !$this->isSetNotEmpty($_REQUEST[$e]))
			$this->_errors[] = sprintf("You left the <strong>%s</strong> field blank!", ucwords(str_replace("_", " ", $e)));
	}

	/* Print Errors Method
	 * It will print nicely formatted html by default.
	 * Prints a division with class="errors".  Outputs p tags for errors.  Style with CSS.
	 * Can be easily modified to format inline.
	 */
	public function printErrors(){
		if($this->checkErrors()){
			$error_c   = count($this->_errors);
			$error_msg = ($error_c > 1) ? 'Errors' : 'Error';
			echo('<div class="errors">' . "\n\t<h2>$error_c $error_msg Occurred</h2>\n");
			foreach($this->_errors as $e)
				echo("\t<p>" . stripslashes($e) . "</p>\n");
			echo("</div><br>\n");
		}
	}

	/* Print Text Method
	 * Use:   Set default text for a form to display.  Useful if user makes an error.
	 * 	      Don't make your user re-enter data on form submission failures.
	 *        Primary use -- <textarea>
	 * Takes: REQUEST variable name $requestVar
	 *        BOOLEAN $return to determine wether to return the string or to print it out.
	 * Uses:  $_REQUEST since that is a combination of $_GET, $_POST, and $_COOKIE...
	 *        sanitizeInput() FOR PROTECTION AGAINST XSS ATTACKS!
	 *          NOT REDUNDANT!  sanitizePOST/GET does NOT affect the REQUEST array!
	 * Do not mix and match GET/POST with the same field names or one will get overwritten by the
	 * other as defined in php.ini.  Normally GPC -> Get, Post, Cookie.
	 */
	public static function printTxt($requestVar, $return=FALSE){
		if(self::isSetNotEmpty($_REQUEST[$requestVar])){
			$string = self::sanitizeInput(stripslashes($_REQUEST[$requestVar]));
			if($return)
				return($string); # Useful if the call is made in an echo statement!
			else
				echo($string);
		}
	}

	/* Print Value Method
	 * Use:      Set default value for a form to display.  Useful if user makes an error.
	 *             Don't make your user re-enter data on form submission failures.
	 * Takes:    REQUEST variable name $requestVar
	 *           BOOLEAN $return to determine wether to return the string or to print it out.
	 * REQUIRES: printTxt();
	 */
	public static function printVal($requestVar, $return=FALSE){
		if(self::isSetNotEmpty($_REQUEST[$requestVar])){
			$string = ' value="' . self::printTxt($requestVar, TRUE) . '"';
			if($return)
				return($string); # Useful if the call is made in an echo statement!
			else
				echo($string);
		}
	}

	/*
	 * Print Option Tags Method
	 * Use:   This method will create a complete drop down menu with the ability for it to auto-select
	 *            the last known selection in case the form has an error during submission.
	 *            Does NOT print out the <select></select> tags, but will create all the <option></option> tags.
	 *        Don't make your user reset info they already set.  Also can print out a default
	 *            non-selectable option for display and user guidance purposes.
	 * Takes: Name of the parent <select> tag $selectName
	 *        Array of options (can be numerical or associative) &$options
	 *        Non-Selectable default option text $defaultText.  Defaults to &nbsp;.
	 *            If you pass NULL or a blank string a default option will not be printed.
	 *        Wether or not to use the array key as the option value BOOLEAN $useKey
	 *            array("Value" => "Display to user") etc
	 */
	public static function printOptions($selectName, &$options, $defaultText="&nbsp;", $useKey=FALSE){
		$selected = (isset($_REQUEST[$selectName]) ? $_REQUEST[$selectName] : FALSE);
		if(!empty($defaultText)){
			printf("<option%s disabled=\"disabled\" value=\"\">$defaultText</option>\n",
				($selected === FALSE ? " selected=\"selected\"" : "") # Use strong compare in case a value of zero is passed.
			);
		}
		if($useKey){
			foreach($options as $key => $val){
				printf("<option value=\"%s\"%s>%s</option>\n",
					$key,
					($selected !== FALSE && $key == $selected ? " selected=\"selected\"" : ""),
					$val
				);
			}
		}else{
			foreach($options as $option){
				printf("<option%s>%s</option>",
					($selected !== FALSE && $option == $selected ? " selected=\"selected\"" : ""),
					$option
				);
			}
		}
	}

	/* Send Mail Method
	 * Sends an HTML email with a pre-defined format.
	 * Takes:  Email Recipient $emailRecipient or array of email recipients{
	 *           The formatting of this string must comply with RFC 2822.  See examples below.
	 *           user@example.com
	 *           user@example.com,anotheruser@example.com
	 *           User <user@example.com>
	 *           User <user@example.com>, Another User <anotheruser@example.com>
	 *           Pass in an array to send emails to individual email addresses for privacy.
	 *         }
	 *         Subject $subject
	 *         Email Template (external file) $template;  Can be HTML and/or PHP
	 *           See message.inc for an example.
	 *         Sender $sender
	 * THROWS: Exceptions if $emailRecipient, $subject, $template, or $sender is not set properly!
	 *         Exceptions if the message(s) are not sent properly.
	 */
	public function sendMail($emailRecipient, $subject, $template, $sender){
		if(empty($emailRecipient))
			throw new Exception('Email recipient(s) are required in method sendMail().');
		if(empty($subject))
			throw new Exception('The subject is required in method sendMail().');
		if(!is_file($template))
			throw new Exception('The HTML/PHP template file provided does not exist in method sendMail().');
		if(empty($sender))
			throw new Exception('Sender/From must be specified in method sendMail().');
		ob_start();
			include($template);
		$result   = ob_get_clean();
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: " . $sender . "\r\n";
		if(is_array($emailRecipient)){
			foreach($emailRecipient as $to_addr){
				if(!mail($to_addr, $subject, $result, $headers))
					throw new Exception('Email not sent properly in method sendMail().');
			}
		}else{
			if(!mail($emailRecipient, $subject, $result, $headers))
				throw new Exception('Email not sent properly in method sendMail().');
		}
	}

	/* Lazy Mail Method
	 * Sends a basic array dump of all set POST elements.
	 * Takes:    Email Recipient $emailRecipient or array of email recipients{
	 *             The formatting of this string must comply with RFC 2822.  See examples below.
	 *             user@example.com
	 *             user@example.com,anotheruser@example.com
	 *             User <user@example.com>
	 *             User <user@example.com>, Another User <anotheruser@example.com>
	 *             Pass in an array to send emails to individual email addresses for privacy.
	 *           }
	 *           Subject $subject
	 *           Sender $sender
	 * Requires: Form to be sent via the POST method.
	 *             Do not use $_REQUEST as it causes a security issue with array dumps.
	 * THROWS:   Exceptions if $emailRecipient, $subject, or $sender is not set properly!
	 *           Exceptions if the message(s) are not sent properly.
	 * POST variables should be well named if this method is to be used.
	 */
	public function lazyMail($emailRecipient, $subject, $sender){
		if(empty($emailRecipient))
			throw new Exception('Email recipient(s) are required in method lazyMail().');
		if(empty($subject))
			throw new Exception('The subject is required in method lazyMail().');
		if(empty($sender))
			throw new Exception('Sender/From must be specified in method lazyMail().');
		foreach($_POST as $key => $val){
			if(!empty($val))
				$message .= ucwords(str_replace("_"," ",$key)) . ": "."\n\t" . stripslashes($val) . "\n\n";
		}
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: " . $sender . "\r\n";
		if(is_array($emailRecipient)){
			foreach($emailRecipient as $toAddr){
				if(!mail($toAddr, $subject, $message, $headers))
					throw new Exception('Email not sent properly in method lazyMail().');
			}
		}else{
			if(!mail($emailRecipient, $subject, $message, "From: " . $sender))
				throw new Exception('Email not sent properly in method lazyMail().');
		}
	}

	/* Validate Email Method
	 * Takes: Email address reference $email
	 *        True/False to check the MX record to validate the domain of the email $chkDNS
	 *          Defaults to true as this is a good thing in most cases.
	 * Validates both the format of the email and checks the MX records to verify
	 * that the domain of the email address exists.
	 * Sets an error if the email address supplied is invalid.
	 */
	final public function validEmail(&$email, $chkDNS=TRUE){
		$isValid = TRUE;
		$atIndex = strrpos($email, "@");
		if(is_bool($atIndex) && !$atIndex)
			$isValid = FALSE;
		else{
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if($localLen < 1 || $localLen > 64){ # local part length exceeded
				$isValid = FALSE;
			}elseif($domainLen < 1 || $domainLen > 255){ # domain part length exceeded
				$isValid = FALSE;
			}elseif($local[0] == '.' || $local[$localLen-1] == '.'){ # local part starts or ends with '.'
				$isValid = FALSE;
			}elseif(preg_match('/[.]{2,}/', $local)){ # local part has two consecutive dots
				$isValid = FALSE;
			}elseif(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)){ # character not valid in domain part
				$isValid = FALSE;
			}elseif(preg_match('/[.]{2,}/', $domain)){ # domain part has two consecutive dots
				$isValid = FALSE;
			}elseif(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))){
				# character not valid in local part unless local part is quoted
				if(!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local)))
					$isValid = FALSE;
			}
			if($chkDNS){ # Make sure the user want's to do this check since it can be time consuming if a lot of requests are made.
				if($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))){# domain not found in DNS
					$isValid = FALSE;
				}
			}
		}
		if(!$isValid)
			$this->_errors[] = "The <strong>Email Address</strong> you supplied was invalid!";
	}

	/* Sanitize Input Method
	 * Takes: String $string
	 * Sanitizes user input by converting all applicable characters to HTML entities.
	 * This is a simple yet effective way of removing XSS attack potential from web forms.
	 * This method is NOT sufficient for CMS use where HTML will be reposted/executed!  Regex is necessary in that case!
	 */
	static private function sanitizeInput($string){
		return htmlentities($string);
	}

	/* Sanitize Array Method
	 * Takes:    Array $arr
	 * Requires: sanitizeInput()
	 * Sanitizes user input via sanitizeInput();
	 * This method handles arrays to be sanitized and recursively calls itself if there are sub-arrays found.
	 */
	final static public function sanitizeArray($arr){
		foreach($arr as $key=>$value){
			if(is_array($value))
				$arr[$key] = self::sanitizeArray($value); # Recursive Call
			else
				$arr[$key] = self::sanitizeInput($value); # Base Case
		}
		return($arr);
	}

	/* Sanitize Object Method
	 * Takes:    Object $object
	 * Requires: sanitizeInput(), sanitizeArray()
	 * Sanitizes user input via sanitizeInput().
	 * Determines whether the object passed is an array or not and handles the data accordingly.
	 * General Sanitation Method!
	 */
	final static public function sanitizeObject($ob){
		if(is_array($ob))
			return(self::sanitizeArray($ob));
		else
			return self::sanitizeInput($ob);
	}

	/* Sanitize POST Method
	 * Requires: sanitizeArray()
	 * Sanitizes the entire POST array.
	 */
	final static public function sanitizePOST(){
		$_POST = self::sanitizeArray($_POST);
	}

	/* Sanitize GET Method
	 * Requires: sanitizeArray()
	 * Sanitizes the entire GET array.
	 */
	final static public function sanitizeGET(){
		$_GET = self::sanitizeArray($_GET);
	}

	/* Check It Box Function
	 * Use:     Checks if a check box is checked or not when using an array of checkboxes.
	 * Takes:   Var $string, the string you want to verify with.
	 *          Array Pointer $checkBoxArray, the checkbox array.
	 * Returns:	Either an empty string or the checked=checked string
	 */
	public static function checkItBox($string, &$checkBoxArray){
		$c='';
		if(!empty($checkBoxArray)){
			if(in_array($string, $checkBoxArray))
				$c = 'checked="checked"';
		}
		return $c;
	}

	/* /\/\////\\////\/\/ ADVANCED USER SECTION \\/\///\\\/\/\/\//\/\/\/
	 * This section assumes you have access to a MySQL database and know how to connect to it.
	 */

	/* Check user IP method
	 * Takes: The defined database table $tbl
	 *        Constant in the URL to be ommited $urlOmitString
	 *        # of allowed tries $allowableNumAttempts which defaults to 3.
	 * Uses:  The connection handler $this->_handle.
	 * Use this method instead of handleIP if you already have a mysql connection.
	 * Checks if the user has exceeded the allowed number of attempts in the
	 * last 30 minutes to correctly fill out your form.
	 * SEE Proposed SQL Structure in default.sql!
	 * WARNING! By using this method, you may be seriously restricting access to your forms by using this method if
	 * 	several people are using proxies or are behing a NAT router.
	 * If the user exceedes, add an error to the error array.
	 */
	public function checkIP($tbl, $urlOmitString="", $allowableNumAttempts=3){
		if(!is_resource($this->_handle))
			throw new Exception("Handle provided was not a valid MySQL resource in method checkIP().");
		$q = sprintf("SELECT `attempts` FROM %s\n WHERE `page`='%s'\nAND `remote_addr`='%s'\nAND `date`>NOW()- INTERVAL 30 MINUTE LIMIT 1",
				$tbl, # Database Table
				$this->sanitizeUrlString($_SERVER['PHP_SELF'], $urlOmitString), # Sanitized URL of the current page
				ip2long($_SERVER['REMOTE_ADDR']) # 32bit integer representation of IPV4 Address.
			 );
		if($result = mysql_query($q, $this->_handle)){
			if(mysql_num_rows($result) > 0 && mysql_result($result, 0) >= $allowableNumAttempts)
				$this->_errors[] = "You have tried to submit this form too many times.  Please try again in <strong>30 minutes</strong>.";
			else
				$this->logIP($tbl, $urlOmitString);
		}else $this->_errors[] = FORM_CHKERR;
	}

	/* Log user's IP Method
	 * Takes: The defined database table $tbl
	 *        Constant in the URL to be ommited $urlOmitString
	 * Uses:  The connection handler $this->_handle
	 * If the user submits or attemps to submit, log their IP address along with a timestamp and what form page they are on.
	 * Add something like name or email for added security.  A captcha (recaptcha) is another good (probably better) tool.
	 */
	private function logIP($tbl, $urlOmitString){
		$q = sprintf("INSERT INTO %s\n(`page`,`remote_addr`)\nVALUES(\n'%s',\n%s)\nON DUPLICATE KEY UPDATE `attempts`="
				. "IF((`date`>NOW()- INTERVAL 30 MINUTE),(`attempts`+1),1),`date`=NOW();",
				$tbl,
				mysql_real_escape_string($this->sanitizeUrlString($_SERVER['PHP_SELF'], $urlOmitString)),
				ip2long($_SERVER['REMOTE_ADDR'])
			 );
		if(!mysql_query($q, $this->_handle))
			$this->_errors[] = FORM_LOGERR;
	}

	/* Internal IP Handling method.
	 * Checks AND logs user IPs.
	 * Connects to DB and disconects upon completion.
	 * Uses Constants defined in the config section
	 */
	public function handleIP(){
		$this->verConstant("HANDLEIP_DBHOST");
		$this->verConstant("HANDLEIP_DBNAME");
		$this->verConstant("HANDLEIP_DBUSER");
		$this->verConstant("HANDLEIP_DBPASS");
		$this->verConstant("HANDLEIP_TABLE");
		$this->verConstant("HANDLEIP_URLOMIT");
		$this->verConstant("HANDLEIP_NUMATTEMPTS");
		$this->connect2DB(); # Open the Connection
		$this->checkIP(HANDLEIP_TABLE, HANDLEIP_URLOMIT, TRUE, HANDLEIP_NUMATTEMPTS); # Do stuff
		$this->closeDB(); # Close the Connection
	}

	# Helper method for verifying constants in handleIP method.
	private static function verConstant($e){
		if(!defined($e))
			throw new Exception('Constant $e not defined in method handleIP().');
	}
	# Helper method for stripping out a constant part of the site url.
	private static function sanitizeUrlString($string,$needle){
		return substr($string, strlen($needle));
	}

	/* Connect to Database Method
	 * Uses Constants defined in the config section
	 * Throws:	Standard SQL connection errors
	 */
	private function connect2DB(){
		if(!$this->_handle = mysql_connect(HANDLEIP_DBHOST, HANDLEIP_DBUSER, HANDLEIP_DBPASS))
			throw new Exception('Error connecting to database server in method connect2DB().');
		if(!mysql_select_db(HANDLEIP_DBNAME, $this->_handle))
			throw new Exception('Error selecting database: ' . HANDLEIP_DBNAME . ' in method connect2DB().');
	}

	/* Close Database Method
	 * Uses $this->_handle
	 * Throws: Error if mysql_close fails.
	 */
	private function closeDB(){
		if(!mysql_close($this->_handle))
			throw new Exception('Error disconnecting from database server in method closeDB().');
	}

	// ///\/\\\\///\\///\/\\\//\ END ADVANCED SECTION!!! \\/\\\///\\/\/\/\\\\////\\//\\\//\/\/\/\/\\\//\

	/* Get Var Method
	 * Takes:   Class variable name $var
	 * Returns: Class variable of $var
	 */
	final public function getVar($var){
		return $this->$var;
	}
}


/* Line Class
 * Please note: This class will be removed from future versions!
 * Takes: The prefix to the data (IE. Phone: [for a phone number]) $prefix
 *        The submission data that goes with the given prefix (IE. 555-555-1212 [for a phone number] $data
 *        Whether to end the p tag or not $state
 * Usage, print data lines with proper formatting.
 * It has an auto print method, an example call is
 * print(new line("Name: ",$_POST['first_name']." ".$_POST['last_name],1);
 */
class Line{
	private $_pre, $_data, $_line, $_s;

	public function __construct($prefix, $data, $state){
		$this->_pre  = $prefix;
		$this->_data = stripslashes($data); # Could sanitize here instead of post array etc
		$this->_s    = $state;
		$this->_line = ($state ? "" : "</p>");
		$this->verify(); # If you want to pass without checking just call sWrap() instead.
	}

	private function verify(){ # Verifies the data being sent through has at least some info in it.
		if(!empty($this->_data))
			$this->sWrap();
	}

	private function sWrap(){ # Wraps the content to be printed into nice clean formatting.
		$this->_line = $this->_pre . "<strong>" . $this->_data . "</strong>" . ($this->_s ? "<br />" : "</p>");
	}

	public function __toString(){ # Auto prints the end result.
		return $this->_line;
	}
}
?>