<?php

/* Database info */
define('DB_HOST', getenv('DB_HOSTNAME') ? getenv('DB_HOSTNAME') : 'localhost');
define('DB_NAME', getenv('DB_NAME') ? getenv('DB_NAME') : '');
define('DB_USER', getenv('DB_USERNAME') ? getenv('DB_USERNAME') : '');
define('DB_PASS', getenv('DB_PASSWORD') ? getenv('DB_PASSWORD') : '');

/* Site name */
define('SITE_NAME','Timeclock');

/* Company name */
define('COMPANY_NAME', '');

/* Developer/Debug Mode */
define('DEV_DEBUG', '1');
