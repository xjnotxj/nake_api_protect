<?php

require dirname(__FILE__) . "/nake_api_protect.php";

$mobile_param = isset($_GET['mobile']) ? trim($_GET['mobile']) : false;

//init nake_api_protect
$options = array(
    'project_name' => 'mobile_project',
    'identity' => 'session',
    'frequency' =>
        [
            array("during" => 1 * 60, 'times' => 1),
            array("during" => 60 * 60, 'times' => 3),
        ],
);

$nake_api_protect = new Nake_api_protect($options); //创建实例对象

//use
if(!$nake_api_protect->valid()){
    echo "Your request is too frequent.";
    return;
}
$nake_api_protect->active();

//database operate ……

echo $mobile_param;
return;
 


?>