<?php
// includes/config.php

// Set the default timezone
date_default_timezone_set('Africa/Kigali');

// App constants
define('APP_NAME', 'Mass Reader Scheduler');
define('BASE_URL', 'https://stbasile.ct.ws/');



// Email configuration
define('MAILHOST', 'smtp.gmail.com');
define('USERNAME', 'info.vaultcloud@gmail.com');
define('PASSWORD', 'cgrf qtes ldkg rtjb');
define('SEND_FROM', 'info.vaultcloud@gmail.com');
define('WEBSITE_NAME', "St. Basile Community Mass Readers Scheduler System");
define('PORT', 587);

//Google reCAPTACHA Verification Constants

define('SECRET_KEY','6Lf8ryorAAAAADHXbLqL9TXXAVL5nnX444XPH5yU');
define('SITE_KEY',value: '6Lf8ryorAAAAANKDcwYVp4KhkZAO-oL2gNAetasD');


//DB Constants

define('HOST','localhost');
define('USER','root');
define('PASS','');
define('DB_NAME','if0_38626920_stbasile_db');
?>
