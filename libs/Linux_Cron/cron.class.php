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
    
        self::$cron_bin = trim(shell_exec('which crontab'));
        self::$cron_tmp = tempnam("/tmp", "cron_");

        $this->get_cron_value();

    }
    
    public function get_bin() {
        return self::$cron_bin;
    }
    
    public function get_tmp() {
        return self::$cron_tmp;
    }
    
    public function add_line($add) {

        $this->get_cron_value();
        $output = self::$cron_contents . "\n" . $add . "\n";

        if(file_put_contents(self::$cron_tmp, $output) === false) {
            return false;
        } else {
            exec(self::$cron_bin . " " . self::$cron_tmp);
        }

        return $output;

    }
    
    public function remove_line($remove) {

        $this->get_cron_value();
        $output = "";
        $lines = explode("\n", self::$cron_contents);

        foreach($lines as $line) {
            if($line != $remove) {
                $output .= $line . "\n";
            }
        }

        if(file_put_contents(self::$cron_tmp, $output) === false) {
            return false;
        } else {
            exec(self::$cron_bin . " -r " . self::$cron_tmp);
        }

        return $output;

    }
    
    public function find_line($check) {
        $this->get_cron_value();
        $output = "";
        $lines = explode("\n", self::$cron_contents);

        foreach($lines as $line) {
            if($line == $check) {
                return true;
            }
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