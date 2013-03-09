<?php

/**
 * Cron through PHP
 *
 * @author JuanJose Galvez
 * @website http://www.gimmesoda.com/
 * @email jj@gimmesoda.com
 * @version 0.1
 */

class Gimme_Cron {

    public static $cron_bin;
    public static $cron_tmp;
    public static $cron_contents;

    public function __construct() {
        self::$cron_bin = "/usr/bin/crontab";
        self::$cron_tmp = tempnam("/tmp", "cron_");
        $this->get_cron_value();
    }
    
    public function get_bin() {
        return self::$cron_bin;
    }
    
    public function get_tmp() {
        return self::$cron_tmp;
    }
    
    public function add_line($add) { #add should be an array of lines to add
        $this->get_cron_value();
        foreach ($add as $line) {
            $output .= self::$cron_contents . "\n" . $line . "\n";
        }
        if(file_put_contents(self::$cron_tmp, $output) === false) {
            return false;
        } else {
            exec(self::$cron_bin . " " . self::$cron_tmp);
        }
        return $output;
    }
    
    public function remove_line($remove) { #remove should be an array
        $this->get_cron_value();
        $output = "";
        $lines = explode("\n", self::$cron_contents);
        $found = 0;
        foreach($lines as $line) {
            if($line != $remove[$found]) {
                $output .= $line . "\n";
            } else {
                $found++;
            }
        }
        if($found > 0) {
            if(file_put_contents(self::$cron_tmp, $output) === false) {
                return false;
            } else {
                exec(self::$cron_bin . " " . self::$cron_tmp);
            }
        }
        return $output;
    }
    
    public function find_line($check) { #check should be an array!
        $this->get_cron_value();
        $output = "";
        $lines = explode("\n", self::$cron_contents);
        $found = 0;
        foreach($lines as $line) {
            if($line == $check[$found]) {
                $found++;
            }
        }
        if($found === count($check)) {
            return true;
        }

        return false;
    }

    public function empty_cron() {
        shell_exec(self::$cron_bin . ' -r');
    }
    
    private function get_cron_value() {
        self::$cron_contents = trim(shell_exec(self::$cron_bin . ' -l'));
        return self::$cron_contents;
    }
    
    public function __destruct() {
        unlink(self::$cron_tmp);
    }

}

?>