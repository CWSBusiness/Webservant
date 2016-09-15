<?php

//// Constant Definitions

define("COMPANY_NAME", "COMPSA Web Services");              // the company name, displayed when internal codes are used

// Server database
define("DB_HOST", "localhost");                             // database server
define("DB_NAME", "webservant");                            // the database name
define("DB_USER", "root");                             // the database username
define("DB_PASS", "");                         // the database password

define("DOMAIN", "http://admin.compsawebservices.com");     // the site's domain

define("ADMIN_EMAIL", "brandon@bloch.ca");		            // the default email address for receiving mail
define("MAILING_EMAIL", "mail@compsawebservices.com");      // the default email address for sending mail

define("MAX_IDLE_TIME", 10800);						        // destroy the session after this many seconds of inactivity. 1500 = 25 minutes

define("MAX_FILESIZE", 1048576);   // 1 MB                  // the maximum size to allow for file uploads, in bytes

define("USERNAME_MIN_LENGTH", 6);                           // the minimum number of characters for a username
define("PASSWORD_MIN_LENGTH", 8);                           // the minimum number of characters for a password

define("BETA_MESSAGE", false);                              // show the beta message