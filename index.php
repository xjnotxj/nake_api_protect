<?php

require dirname(__FILE__) . "/lib/config.php";

$mobile_param = isset($_GET['mobile']) ? trim($_GET['mobile']) : false;

//use
if (!$nake_api_protect->valid()) {
    echo var_dump($nake_api_protect->debug());
    echo "Your request is too frequent.";
    return;
}
$nake_api_protect->active();

echo var_dump($nake_api_protect->debug());

//database operate ……

echo $mobile_param;
return;
