<?php

require dirname(__FILE__) . "/lib/config.php";

//use
try {
    $nake_api_protect->destory();
} catch (InvalidArgumentException $e) {
    throw new InvalidArgumentException($e);
} catch (RuntimeException $e) {
    throw new RuntimeException($e);
} catch (Exception $e) {
    throw new Exception($e);
}

echo "ok";
