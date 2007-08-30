<?php

/**
 * Testing Flag
 * Set to true to use canned data (no SOAP connection
 * will ever be made).
 */
define('SOAP_TEST_FLAG', true);

/**
 * AXP Testing Flag
 * Set to true to allow fake users to login
 * (No actual authentication to AXP)
 */
define('AXP_TEST_FLAG', true);

/**
 * Gender defines
 */
define("FEMALE", 0);
define("MALE",1);

define("TOOOLD", -3);
define("BADTUPLE", -2);
define("TOOEARLY", -1 );
define("TOOLATE", 0 );
define("STUDENT", 1 );
define("ADMIN", 2 );
define("BADCLASS", 3);

?>
