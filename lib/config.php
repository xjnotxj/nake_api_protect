<?php

require dirname(__FILE__) . "/nake_api_protect.php";

//init nake_api_protect
$nake_api_protect_options = array(
    'project_name' => 'mobile_project',
    'identity' => 'ip',
    'frequency' =>
    [
        array("during" => 1 * 60, 'times' => 3),
    ],
    'redis' => [
        "address" => "127.0.0.1",
        "port" => 6379,
    ],
);

try {
    $nake_api_protect = new Nake_api_protect($nake_api_protect_options); //创建实例对象
} catch (InvalidArgumentException $e) {
    throw new InvalidArgumentException($e);
} catch (RuntimeException $e) {
    throw new RuntimeException($e);
} catch (Exception $e) {
    throw new Exception($e);
}
