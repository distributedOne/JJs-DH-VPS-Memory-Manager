<?php

  require($system_path . "/libs/Smarty/Smarty.class.php");

  class Smarty_DH_VPS_MM extends Smarty {
  
     function __construct() {
       global $system_path;
          parent::__construct();
          $this->config_dir   = $system_path . '/libs/Smarty/configs/';
          $this->compile_dir  = $system_path . '/cache/';
          $this->cache_dir    = $system_path . '/cache/';
          $this->template_dir = $system_path . '/templates/' . TEMPLATE;
          $this->caching = Smarty::CACHING_OFF;
     }

  }

?>