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
        $this->frequency = isset($options['frequency']) ? $options['frequency'] : [array("during" => 0, 'times' => 0)];
        $this->redis = isset($options['redis']) ? $options['redis'] : null;

        //check param
        if (!is_string($this->project_name)) {
            throw new InvalidArgumentException("[__construct] function error : project_name param must be string.");
        }
        if (!is_string($this->identity) || !($this->identity === "session" || $this->identity === "ip")) {
            throw new InvalidArgumentException("[__construct] function error : identity param must be string or invalid format.");
        }

        if (!is_array($this->frequency) || sizeof($this->frequency) <= 0) {
            throw new InvalidArgumentException("[__construct] function error : frequency param must be array.");
        }

        if ($this->identity === "ip") {
            if (!$this->redis) {
                throw new InvalidArgumentException("[__construct] function error : redis param must be array.");
            }

            //check param
            if (!is_string($this->redis["address"])) {
                throw new InvalidArgumentException("[__construct] function error : redis's address param must be greater than or equal to 0.");
            }
            if (!is_integer($this->redis["port"])) {
                throw new InvalidArgumentException("[__construct] function error : redis's port param must be greater than or equal to 0.");
            }
        }

        //init frequency
        $frequency = [];
        for ($i = 0; $i < sizeof($this->frequency); $i++) {

            //check param
            if (!is_integer($this->frequency[$i]["during"])) {
                throw new InvalidArgumentException("[__construct] function error : frequency's during param must be integer.");
            }
            if (!is_integer($this->frequency[$i]["times"])) {
                throw new InvalidArgumentException("[__construct] function error : frequency's times param must be integer.");
            }

            if ($this->frequency[$i]["during"] < 0) {
                throw new InvalidArgumentException("[__construct] function error : frequency's during param must be greater than or equal to 0.");
            }
            if ($this->frequency[$i]["times"] < 0) {
                throw new InvalidArgumentException("[__construct] function error : frequency's times param must be greater than or equal to 0.");
            }

            array_push($frequency,
                array(
                    "during_origin" => $this->frequency[$i]["during"],
                    "during" => $this->frequency[$i]["during"] + time(),
                    "times" => $this->frequency[$i]["times"],
                    "number" => 0,
                )
            );
        }

        //save
        if ($this->identity === "session") {
            @session_start();
            if (!$_SESSION[$this->project_name]) {
                $_SESSION[$this->project_name] = $frequency;
            }
        } else if ($this->identity === "ip") {
            $this->redis_instance = new Redis();
            $this->redis_instance->connect($this->redis["address"], $this->redis["port"]);
            if (!$this->redis_instance) {
                throw new RuntimeException("[__construct] function error : create redis instance fail.");
            }
            if (!$this->redis_instance->get($this->project_name . $this->_getClientIP())) {
                $this->redis_instance->set($this->project_name . $this->_getClientIP(), serialize($frequency));
            }

        }
    }

    public function active()
    {

        //fetch
        $frequency = $this->_fetchDate();

        if (!$frequency || sizeof($frequency) <= 0) {
            throw new RuntimeException("[active] function error : This Nake_api_protect object has been destroyed. Please rebuild it.");
        }

        //updateDate
        $frequency = $this->_autoUpdateDate($frequency);

        //update
        for ($i = 0; $i < sizeof($frequency); $i++) {
            if (time() < $frequency[$i]["during"]) {
                $frequency[$i]["number"] = $frequency[$i]["number"] + 1;
            }
        }

        //save
        $this->_saveDate($frequency);
    }

    public function valid()
    {

        //fetch
        $frequency = $this->_fetchDate();

        if (!$frequency || sizeof($frequency) <= 0) {
            throw new RuntimeException("[active] function error : This Nake_api_protect object has been destroyed. Please rebuild it.");
        }

        //updateDate
        $frequency = $this->_autoUpdateDate($frequency);

        //check
        for ($i = 0; $i < sizeof($frequency); $i++) {
            if (time() < $frequency[$i]["during"] && $frequency[$i]["number"] >= $frequency[$i]["times"]) {
                return false;
            }
        }

        return true;
    }

    public function debug()
    {
        //fetch
        $frequency = $this->_fetchDate();

        if (!$frequency || sizeof($frequency) <= 0) {
            throw new RuntimeException("[clear] function error : This Nake_api_protect object has been destroyed. Please rebuild it.");
        }

        return $frequency;
    }

    public function clear()
    {
        //fetch
        $frequency = $this->_fetchDate();

        if (!$frequency || sizeof($frequency) <= 0) {
            throw new RuntimeException("[clear] function error : This Nake_api_protect object has been destroyed. Please rebuild it.");
        }

        //update
        for ($i = 0; $i < sizeof($frequency); $i++) {
            $frequency[$i]["number"] = 0;
        }

        //save
        $this->_saveDate($frequency);

    }

    public function destory()
    {
        //destroy
        if ($this->identity === "session") {
            unset($_SESSION[$this->project_name]);
//            session_destroy();
        } else if ($this->identity === "ip") {
            $this->redis_instance->flushAll();
        }

        //waitting GC
        $this->project_name = null;
        $this->identity = null;
        $this->frequency = null;
        $this->persistence = null;

    }

    private function _fetchDate()
    {
        if ($this->identity === "session") {
            @session_start();
            $frequency = $_SESSION[$this->project_name];
        } else if ($this->identity === "ip") {
            $frequency = unserialize($this->redis_instance->get($this->project_name . $this->_getClientIP()));
        }

        return $frequency;
    }

    private function _saveDate($frequency)
    {
        if ($this->identity === "session") {
            @session_start();
            $_SESSION[$this->project_name] = $frequency;
        } else if ($this->identity === "ip") {
            $this->redis_instance->set($this->project_name . $this->_getClientIP(), serialize($frequency));
        }
    }

    private function _autoUpdateDate($frequency)
    {
        for ($i = 0; $i < sizeof($frequency); $i++) {
            if (time() >= $frequency[$i]["during"]) {
                $frequency[$i]["number"] = 0;
                $frequency[$i]["during"] = $frequency[$i]["during"] + $frequency[$i]["during_origin"];
            }
        }
        return $frequency;
    }

    private function _getClientIP()
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
