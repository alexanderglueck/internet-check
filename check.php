<?php

require('vendor/autoload.php');

$internetCheck = new Check\InternetCheck();
$internetCheck->run();