<?php
//\\/\/\/\/\/\/\\\\\\//// CONFIGURATION \\/\/\////\/\/\/\//\\\\/\/\/\/
// The following is for the advanced section and is for connecting to the database.
// Only uncomment these lines if you know what you are doing
createConfig('FORM_CHKERR', 'A fatal error has occured.');
createConfig('FORM_LOGERR', 'A fatal error has occured.');

//	THE BELOW DEFINITIONS ARE FOR USE WITH THE handleIP method!
//  If you use this method, you should store this include file outside the public_html root.
/*
 createConfig('HANDLEIP_DBHOST','INSERT DATABASE HOST HERE');
 createConfig('HANDLEIP_DBNAME','INSERT DATABASE NAME HERE');
 createConfig('HANDLEIP_DBUSER','INSERT DATABASE USER HERE');
 createConfig('HANDLEIP_DBPASS','INSERT DATASE PASSWORD HERE');
 createConfig('HANDLEIP_TABLE','INSERT TABLE NAME HERE');
 createConfig('HANDLEIP_URLOMIT','INSERT OMIT URL STRING HERE');
 createConfig('HANDLEIP_NUMATTEMPTS','INSERT MAX FORM ATTEMPTS HERE');
 */


//\\/\/\/\/\/\/\\\\\\// END-CONFIGURATION \\/\/\////\/\/\/\//\\\\/\/\/

/* Function for defining constants for the configuration
 * Takes:	Name of constant $name
 * 			Constant's value $val
 * Checks that the constant is not already defined then defines it.
 */
function createConfig($name, $val){
	if(!defined($name))
		define($name, $val);
}

function exception_handler($exception) {// Default Exception Catching function
  echo "Uncaught exception: " , $exception->getMessage(), "\n";
}

set_exception_handler('exception_handler'); //Set the default exception handler.
?>