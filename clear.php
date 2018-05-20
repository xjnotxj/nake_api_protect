<?php

require dirname(__FILE__) . "/nake_api_protect.php";

$mobile_param = isset($_GET['mobile']) ? trim($_GET['mobile']) : false;

//init nake_api_protect
$options = array(
    'project_name' => 'mobile_project',
    'identity' => 'session', 
);

$nake_api_protect = new Nake_api_protect($options); //创建实例对象

//use
if($nake_api_protect->clear()){
    echo "ok.";
    return;
}else{
    echo "fail.";
    return;
}
 
?>