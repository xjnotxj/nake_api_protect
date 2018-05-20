<?php

class Nake_api_protect
{

    public $project_name;
    public $identity;
    public $frequency;
    public $persistence;

    public $redis_instance;

    public function __construct($options)
    {
        $this->project_name = isset($options['project_name']) ? $options['project_name'] : 'default';
        $this->identity = isset($options['identity']) ? $options['identity'] : 'session';
        $this->frequency = isset($options['frequency']) ? $options['frequency'] : [array("during" => -1, 'times' => -1)];
        $this->redis = isset($options['redis']) ? $options['redis'] : null;

        //init frequency
        $frequency = [];
        for ($i = 0; $i < sizeof($this->frequency); $i++) { 
            array_push($frequency,
                array(
                    "during" => $this->frequency[$i]["during"] + time(),
                    "times" => $this->frequency[$i]["times"],
                    "number" => 0,
                )
            );
        }

        //save
        if ($this->identity === "session") {
            session_start();
            if (!$_SESSION[$this->project_name]) {
                $_SESSION[$this->project_name] = $frequency;
            }
        } else if ($this->identity === "ip") {
            $this->redis_instance = new Redis();
            $this->redis_instance->connect($this->redis->address, $this->redis->port);
            $this->redis_instance->set($this->project_name . $this->_getClientIP(), $frequency);
        }
    }

    public function active()
    {

        $frequency = null;

        //fetch
        if ($this->identity === "session") {
            session_start();
            $frequency = $_SESSION[$this->project_name];
        } else if ($this->identity === "ip") {
            $this->redis_instance->get($this->project_name . $this->_getClientIP(), $frequency);
        }

        if (!$frequency || sizeof($frequency) <= 0) {
            die("[active] function error : This Nake_api_protect object has been destroyed. Please rebuild it.");
        }

        //update
        for ($i = 0; $i < sizeof($frequency); $i++) {
            if (time() < $frequency[$i]["during"]) {
                $frequency[$i]["number"] = $frequency[$i]["number"] + 1;
            }
        }

//        echo var_dump($frequency);

        //save
        if ($this->identity === "session") {
            session_start();
            $_SESSION[$this->project_name] = $frequency;
        } else if ($this->identity === "ip") {
            $this->redis_instance->set($this->project_name . "frequency", $frequency);
        }
    }

    public function valid()
    {
        $frequency = null;

        //fetch
        if ($this->identity === "session") {
            session_start();
            $frequency = $_SESSION[$this->project_name];
        } else if ($this->identity === "ip") {
            $this->redis_instance->get($this->project_name . $this->_getClientIP());
        }

        if (!$frequency || sizeof($frequency) <= 0) {
            die("[active] function error : This Nake_api_protect object has been destroyed. Please rebuild it.");
        }

        //update
        for ($i = 0; $i < sizeof($frequency); $i++) {
            if (time() < $frequency[$i]["during"] && $frequency[$i]["number"] > $frequency[$i]["times"]) {
                return false;
            }
        }

        return true;
    }

    public function clear()
    {
        $frequency = null;

        //fetch
        if ($this->identity === "session") {
            session_start();
            $frequency = $_SESSION[$this->project_name];
        } else if ($this->identity === "ip") {
            $this->redis_instance->get($this->project_name . $this->_getClientIP());
        }

        if (!$frequency || sizeof($frequency) <= 0) {
            die("[clear] function error : This Nake_api_protect object has been destroyed. Please rebuild it.");
        }

        //update
        for ($i = 0; $i < sizeof($frequency); $i++) {
            $frequency[$i]["number"] = 0;
        }

        //save
        if ($this->identity === "session") {
            session_start();
            $_SESSION[$this->project_name] = $frequency;
        } else if ($this->identity === "ip") {
            $this->redis_instance->set($this->project_name . $this->_getClientIP(), $frequency);
        }

        return true;
    }

    public function destory()
    {

        //waitting GC
        $this->project_name = null;
        $this->identity = null;
        $this->frequency = null;
        $this->persistence = null;

        //destroy
        if ($this->identity === "session") {
            unset($_SESSION[$this->project_name]);
//            session_destroy();
        } else if ($this->identity === "ip") {
            $this->redis_instance->flushAll();
        }
    }

    function _getClientIP()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
            $ip = getenv("REMOTE_ADDR");
        } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "unknown";
        }
        return $ip;
    }

}

?>