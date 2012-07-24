<?php

/**
 * DreamHost API Class
 *
 * @author JuanJose Galvez
 * @website http://www.gimmesoda.com/
 * @email jj@gimmesoda.com
 * @version 0.5
 * @package DreamHost API Class
 */

class DreamHost_API {
  
  public static $command;
  public static $extra_arguments;
  public static $enable_debug;

  private static $unique_id;
  private static $unique_prefix;
  private static $api_key;
  private static $api_url;
  private static $final_url;
  private static $final_results;
  
  private static $error_array;
  
  /**
   * The construct for this class sets the api_key, api_url, and unique_prefix
   * for use by the rest of the class functions.
   * 
   * @param string $key DreamHost API key
   * @param string $unique_prefix Prefix for set_unique_id function - Optional
   * @param string $format Output format - Optional
   * @param constant $api_url URL of DreamHost API - Optional
   * 
   * @return void
   */
  public function __construct($key, $unique_prefix = "", $api_url = "https://api.dreamhost.com/") {
    self::$api_key = $key;
    self::$api_url = $api_url;
    self::$unique_prefix = $unique_prefix;
  }
  
  /**
   * execute_command function
   * @return boolean
   * 
   * This function handles the calls for the generation of the unique ID, final generation of the
   * url, debugging output, the api call, and stores any API response in the self::$final_results
   * varible. This function is the real core of the class.
   */
  public function execute_command() {

    self::set_unique_id();
    self::generate_final_url();
    
    if(self::$enable_debug == true) {
      echo "DH API KEY: "; echo self::$api_key; echo "<br />\n";
      echo "DH API CMD: "; echo self::$command; echo "<br />\n";
      echo "DH API UID: "; echo self::$unique_id; echo "<br />\n";
      echo "DH API FINAL URL: "; echo self::$final_url; echo "<br />\n";
    }
    
    $api_results = self::call_api();
    /* @todo Handle curl errors more gracefully */
    if(!empty(self::$error_array['code'])) {
      echo "An error has been encountered while attempting to access the API: <br />\n";
      echo "Error Code: " . self::$error_array['code'] . "<br />\n";
      echo "Error Message: " . self::$error_array['message'] . "<br />\n";
      echo "Extra Information: <pre>"; print_r(self::$error_array['info']); "</pre><br />\n";
    }
    
    return self::parse_results($api_results);
    
  }
  
  function get_error_array() {
    return self::$error_array;
  }

  function parse_results($api_results) {
    $xml = @simplexml_load_string($api_results); //ugly suppresion
    if($xml === false) {
      return false;
    } else {
      if($xml->result[0] == "success") {
        self::$final_results = $xml;
        return true;
      } else {
        self::$final_results = $xml;
        return false;
      }
    }
  }

  /**
   * set_unique_id function
   *
   * @return void
   * 
   * Sets the unique ID of each API request.
   * Automatically called by execute_command();
   */
  private static function set_unique_id() {
    self::$unique_id = uniqid(self::$unique_prefix, true);
    return;
  }
  
  /**
   * generate_final_url function
   * 
   * @return void
   * 
   * Sets the final URL used by CURL to access the API.
   */
  private static function generate_final_url() {
    self::$final_url = self::$api_url . "?key=" . self::$api_key . "&cmd=" . self::$command . "&unique_id=" . self::$unique_id . "&format=xml";
    if(is_array(self::$extra_arguments)) {
      foreach(self::$extra_arguments as $array) {
        foreach($array as $key => $value) {
          self::$final_url .= "&" . $key . "=" . $value;
        }
      }
    }
  }
  
  public static function add_argument($key, $value) {

    if(is_array(self::$extra_arguments)) {
      $cnt = 0;
      $found = false;

      foreach(self::$extra_arguments as $array) {
        foreach($array as $curkey => $curvalue) {
          if($curkey == $key) {
            self::$extra_arguments[$cnt] = array($key=>$value);
            $found = true;
          }
          $cnt++;
        }
      }
      
      if($found == false) {
        if($value != null) {
          self::$extra_arguments[] = array($key => $value);
        }
      }
      
    } else {

      if($value != null) {
        self::$extra_arguments[] = array($key => $value);      
      }

    }

  }
  
  /**
   * call_api function
   * 
   * @return string or false
   * 
   * Final step in accessing the API
   * CURL accesses the final URL and returns results
   * False is returned on failure.
   */
  private static function call_api() {
    
    $curl_handle = curl_init();
    
    curl_setopt($curl_handle, CURLOPT_URL, self::$final_url);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_HEADER, 0);
    
    $api_results = curl_exec($curl_handle);
    $request_info = curl_getinfo($curl_handle);

    if(curl_errno($curl_handle)) {
      $api_results = false;
      $error_code = curl_errno($curl_handle);
      $error_message = curl_error($curl_handle);
      self::add_error($request_info, $error_code, $error_message);      
    }
    
    curl_close($curl_handle);
    $curl_handle = null;

    return $api_results;
    
  }
  
  /**
   * add_error function
   * 
   * @return void
   * 
   * Sets an error into the $error_array which is checked by the 
   */
  private static function add_error($info, $errorCode, $errorMessage) {
    self::$error_array['info'] = $info;
    self::$error_array['code'] = $errorCode;
    self::$error_array['message'] = $errorMessage;
  }
  
  /**
   * get_final_results function
   * 
   * @return string
   * 
   * Public function which will grab the final results from the API
   */
  public static function get_final_results() {
    self::$final_results = self::xmlobj2array(self::$final_results);
    self::$final_results = self::$final_results['data'];
    return self::$final_results;
  }
  
  /**
   * Credit for xmlobj2array Function goes to:
   * sherwinterunez at yahoo dot com
   * http://www.php.net/manual/en/book.simplexml.php#99729
   */
  
  private static function xmlobj2array($obj, $level=0) {
    $items = array();
    
    if(!is_object($obj)) return $items;
    
    $child = (array)$obj;
    
    if(sizeof($child)>1) {
      foreach($child as $aa=>$bb) {
        if(is_array($bb)) {
          foreach($bb as $ee=>$ff) {
            if(!is_object($ff)) {
              $items[$aa][$ee] = $ff;
            } else
            if(get_class($ff)=='SimpleXMLElement') {
              $items[$aa][$ee] = self::xmlobj2array($ff,$level+1);
            }
          }
        } else
        if(!is_object($bb)) {
          $items[$aa] = $bb;
        } else
        if(get_class($bb)=='SimpleXMLElement') {
          $items[$aa] = self::xmlobj2array($bb,$level+1);
        }
      }
    } else
    if(sizeof($child)>0) {
      foreach($child as $aa=>$bb) {
        if(!is_array($bb)&&!is_object($bb)) {
          $items[$aa] = $bb;
        } else
        if(is_object($bb)) {
          $items[$aa] = self::xmlobj2array($bb,$level+1);
        } else {
          foreach($bb as $cc=>$dd) {
            if(!is_object($dd)) {
              $items[$obj->getName()][$cc] = $dd;
            } else
            if(get_class($dd)=='SimpleXMLElement') {
              $items[$obj->getName()][$cc] = self::xmlobj2array($dd,$level+1);
            }
          }
        }
      }
    }
    return $items;
  }


}

/**
 * class DreamHost_Account_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Account_commands
 * 
 * This class contains the Account Commands
 */
class DreamHost_Account_Commands extends DreamHost_API {

  public static function domain_usage() {
    parent::$command = "account-domain_usage";
    return parent::execute_command();
  }
  
  public static function status() {
    parent::$command = "account-status";
    return parent::execute_command();
  }
  
  public static function user_usage() {
    parent::$command = "account-user_usage";
    return parent::execute_command();
  }

}

/**
 * class DreamHost_Announcement_List_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Announcement_list_commands
 * 
 * This class contains the Announcement List Commands
 */
class DreamHost_Announcement_List_Commands extends DreamHost_API {  

  public static function list_lists() {
    parent::$command = "announcement_list-list_lists";
    return parent::execute_command();
  }
  
  public static function list_subscribers($listname, $domain) {
    parent::$command = "announcement_list-list_subscribers";
    parent::add_argument("listname", $listname);
    parent::add_argument("domain", $domain);
    return parent::execute_command();
  }
  
  public static function add_subscriber($listname, $domain, $email, $name) {
    parent::$command = "announcement_list-add_subscriber";
    parent::add_argument("listname", $listname);
    parent::add_argument("domain", $domain);
    parent::add_argument("email", $email);
    parent::add_argument("name", $name);
    return parent::execute_command();
  }
  
  public static function remove_subscriber($listname, $domain, $email) {
    parent::$command = "announcement_list-remove_subscriber";
    parent::add_argument("listname", $listname);
    parent::add_argument("domain", $domain);
    parent::add_argument("email", $email);
    return parent::execute_command();
  }
  
  public static function post_announcement($listname, $domain, $subject, $message, $name, $stamp, $charset, $type, $duplicate_ok) {
    parent::$command = "announcement_list-post_announcement";
    parent::add_argument("listname", $listname);
    parent::add_argument("domain", $domain);
    parent::add_argument("subject", $subject);
    parent::add_argument("message", $message);
    parent::add_argument("name", $name);
    parent::add_argument("stamp", $stamp);
    parent::add_argument("charset", $charset);
    parent::add_argument("type", $type);
    parent::add_argument("duplicate_ok", $duplicate_ok);
    return parent::execute_command();
  }

}

/**
 * class DreamHost_API_Meta_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Api_commands
 * 
 * This class contains the API Meta Commands
 */
class DreamHost_API_Meta_Commands extends DreamHost_API {

  public static function list_accessible_cmds() {
    parent::$command = "api-list_accessible_cmds";
    return parent::execute_command();
  }
  
  public static function list_keys() {
    parent::$command = "api-list_keys";
    return parent::execute_command();
  }

}

/**
 * class DreamHost_DNS_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Dns_commands
 * 
 * This class contains the DNS Commands
 */
class DreamHost_DNS_Commands extends DreamHost_API {  

  public static function list_records() {
    parent::$command = "dns-list_records";
    return parent::execute_command();
  }
  
  public static function add_record($record, $type, $value, $comment) {
    parent::$command = "dns-add_record";
    parent::add_argument("record", $record);
    parent::add_argument("type", $type);
    parent::add_argument("value", $value);
    parent::add_argument("comment", $comment);
    return parent::execute_command();
  }
  
  public static function remove_record($record, $type, $value) {
    parent::$command = "dns-remove_record";
    parent::add_argument("record", $record);
    parent::add_argument("type", $type);
    parent::add_argument("value", $value);
    return parent::execute_command();
  }

}

/**
 * class DreamHost_Domain_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Domain_commands
 * 
 * This class contains the Domain Commands
 */
class DreamHost_Domain_Commands extends DreamHost_API {

  public static function list_domains() {
    parent::$command = "domain-list_domains";
    return parent::execute_command();
  }
  
  public static function list_registrations() {
    parent::$command = "domain-list_registrations";
    return parent::execute_command();
  } 

}  

/**
 * class DreamHost_VPS_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Dreamhost_ps_commands
 * 
 * This class contains the VPS Commands
 */
class DreamHost_VPS_Commands extends DreamHost_API {

  public static function add_ps($account_id, $type, $movedata) {
    parent::$command = "dreamhost_ps-add_ps";
    parent::add_argument("account_id", $account_id);
    parent::add_argument("type", $type);
    parent::add_argument("movedata", $movedata);
    return parent::execute_command();
  }
  
  public static function removed_ps($ps) {
    parent::$command = "dreamhost_ps-removed_ps";
    parent::add_argument("ps", $ps);
    return parent::execute_command();
  }
  
  public static function list_pending_ps() {
    parent::$command = "dreamhost_ps-list_pending_ps";
    return parent::execute_command();
  }
  
  public static function remove_pending_ps() {
    parent::$command = "dreamhost_ps-remove_pending_ps";
    return parent::execute_command();
  }
  
  public static function list_ps() {
    parent::$command = "dreamhost_ps-list_ps";
    return parent::execute_command();
  }
  
  public static function list_settings($ps) {
    parent::$command = "dreamhost_ps-list_settings";
    parent::add_argument("ps", $ps);
    return parent::execute_command();
  }
  
  public static function set_settings($ps, $apache2_enabled = null, $comment = null, $courier_enabled = null, $lighttpd_enabled = null, $modphp_selected = null, $php_cache_xcache = null, $proftpd_enabled = null) {
    parent::$command = "dreamhost_ps-set_settings";
    parent::add_argument("ps", $ps);
    parent::add_argument("apache2_enabled", $apache2_enabled);
    parent::add_argument("comment", $comment);
    parent::add_argument("courier_enabled", $courier_enabled);
    parent::add_argument("lighttpd_enabled", $lighttpd_enabled);
    parent::add_argument("modphp_selected", $modphp_selected);
    parent::add_argument("php_cache_xcache", $php_cache_xcache);
    parent::add_argument("proftpd_enabled", $proftpd_enabled);
    return parent::execute_command();
  }
  
  public static function list_size_history($ps) {
    parent::$command = "dreamhost_ps-list_size_history";
    parent::add_argument("ps", $ps);    
    return parent::execute_command();
  }
  
  public static function set_size($ps, $size) {
    parent::$command = "dreamhost_ps-set_size";
    parent::add_argument("ps", $ps);
    parent::add_argument("size", $size);
    return parent::execute_command();
  }
  
  public static function list_reboot_history($ps) {
    parent::$command = "dreamhost_ps-list_reboot_history";
    parent::add_argument("ps", $ps);
    return parent::execute_command();
  }
  
  public static function reboot($ps) {
    parent::$command = "dreamhost_ps-ps_reboot";
    parent::add_argument("ps", $ps);
    return parent::execute_command();
  }
  
  public static function list_usage($ps) {
    parent::$command = "dreamhost_ps-list_usage";
    parent::add_argument("ps", $ps);
    return parent::execute_command();
  }
  
  public static function list_images() {
    parent::$command = "dreamhost_ps-list_images";
    return parent::execute_command();
  }

}

/**
 * class DreamHost_Jabber_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Jabber_commands
 * 
 * This class contains the Jabber Commands
 */
class DreamHost_Jabber_Commands extends DreamHost_API {  
  
  public static function list_users() {
    parent::$command = "jabber-list-users";
    return parent::execute_command();
  }
  
  public static function list_users_no_pw() {
    parent::$command = "jabber-list_users_no_pw";
    return parent::execute_command();
  }
  
  public static function list_valid_domains() {
    parent::$command = "jabber-list_valid_domains";
    return parent::execute_command();
  }
  
  public static function add_user($username, $domain, $password) {
    parent::$command = "jabber-add_user";
    parent::add_argument("username", $username);
    parent::add_argument("domain", $domain);
    parent::add_argument("password", $password);
    return parent::execute_command();
  }
  
  public static function remove_user($username, $domain) {
    parent::$command = "jabber-remove_user";
    parent::add_argument("username", $username);
    parent::add_argument("domain", $domain);
    return parent::execute_command();
  }
  
  public static function reactivate_user($username, $domain) {
    parent::$command = "jabber-reactivate_user";
    parent::add_argument("username", $username);
    parent::add_argument("domain", $domain);
    return parent::execute_command();
  }
  
  public static function deactivate_user($username, $domain) {
    parent::$command = "jabber-deactivate_user";
    parent::add_argument("username", $username);
    parent::add_argument("domain", $domain);
    return parent::execute_command();
  }

}

/**
 * class DreamHost_Mail_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Mail_commands
 * 
 * This class contains the Mail Commands
 */
class DreamHost_Mail_Commands extends DreamHost_API {

  public static function list_filters() {
    parent::$command = "mail-list_filters";
    return parent::execute_command();
  }
  
  public static function add_filter($address, $filter_on, $filter, $action, $action_value, $contains, $stop, $rank) {
    parent::$command = "mail-add_filter";
    parent::add_argument("address", $address);
    parent::add_argument("filter_on", $filter_on);
    parent::add_argument("filter", $filter);
    parent::add_argument("action", $action);
    parent::add_argument("action_value", $action_value);
    parent::add_argument("contains", $contains);
    parent::add_argument("stop", $stop);
    parent::add_argument("rank", $rank);
    return parent::execute_command();
  }

  public static function remove_filter($address, $filter_on, $filter, $action, $action_name, $contains, $stop, $rank) {
    parent::$command = "mail-remove_filter";
    parent::add_argument("address", $address);
    parent::add_argument("filter_on", $filter_on);
    parent::add_argument("filter", $filter);
    parent::add_argument("action", $action);
    parent::add_argument("action_value", $action_value);
    parent::add_argument("contains", $contains);
    parent::add_argument("stop", $stop);
    parent::add_argument("rank", $rank);
    return parent::execute_command();
  }

}

/**
 * class DreamHost_MySQL_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Mysql_commands
 * 
 * This class contains the MySQL Commands
 */
class DreamHost_MySQL_Commands extends DreamHost_API {

  public static function list_dbs() {
    parent::$command = "mysql-list_dbs";
    return parent::execute_command();
  }
  
  public static function list_hostnames() {
    parent::$command = "mysql-list_hostnames";
    return parent::execute_command();
  }
  
  public static function add_hostnames($hostname) {
    parent::$command = "mysql-add_hostnames";
    parent::add_argument("hostname", $hostname);
    return parent::execute_command();
  }
  
  public static function remove_hostnames($hostname) {
    parent::$command = "mysql-remove_hostnames";
    parent::add_argument("hostname", $hostname);
    return parent::execute_command();
  }

  public static function list_users() {
    parent::$command = "mysql-list_users";
    return parent::execute_command();
  }
  
  public static function add_user($db, $user, $password, $select = null, $insert = null, $update = null, $delete = null, $create = null, $drop = null, $index = null, $alter = null, $hostnames = null) {
    parent::$command = "mysql-add_user";
    parent::add_argument("db", $db);
    parent::add_argument("user", $user);
    parent::add_argument("password", $password);
    parent::add_argument("select", $select);
    parent::add_argument("insert", $insert);
    parent::add_argument("update", $update);
    parent::add_argument("delete", $delete);
    parent::add_argument("create", $create);
    parent::add_argument("drop", $drop);
    parent::add_argument("index", $index);
    parent::add_argument("alter", $alter);
    parent::add_argument("hostnames", $hostnames);
    return parent::execute_command();
  }

  public static function remove_user($db, $user, $select = null, $insert = null, $update = null, $delete = null, $create = null, $drop = null, $index = null, $alter = null) {
    parent::$command = "mysql-remove_user";
    parent::add_argument("db", $db);
    parent::add_argument("user", $user);
    parent::add_argument("password", $password);
    parent::add_argument("select", $select);
    parent::add_argument("insert", $insert);
    parent::add_argument("update", $update);
    parent::add_argument("delete", $delete);
    parent::add_argument("create", $create);
    parent::add_argument("drop", $drop);
    parent::add_argument("index", $index);
    parent::add_argument("alter", $alter);
    return parent::execute_command();
  }

}

/**
 * class DreamHost_OneClick_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Oneclick_commands
 * 
 * This class contains the OneClick Commands
 */
class DreamHost_OneClick_Commands extends DreamHost_API {  

  public static function list_easy() {
    parent::$command = "oneclick-list_easy";
    return parent::execute_command();
  }
  
  public static function list_advanced() {
    parent::$command = "oneclick-list_advanced";
    return parent::execute_command();
  }
  
  public static function install_easy($domain, $type, $title, $email) {
    parent::$command = "oneclick-install_easy";
    parent::add_argument("domain", $domain);
    parent::add_argument("type", $type);
    parent::add_argument("title", $title);
    parent::add_argument("email", $email);
    return parent::execute_command();
  }
  
  public static function install_advanced($url, $type, $database) {
    parent::$command = "oneclick-install_advanced";
    parent::add_argument("url", $url);
    parent::add_argument("type", $type);
    parent::add_argument("database", $database);
    return parent::execute_command();
  }
  
  public static function upgrade($url) {
    parent::$command = "oneclick-upgrade";
    parent::add_argument("url", $url);
    return parent::execute_command();
  }
  
  public static function upgrade_all($type) {
    parent::$command = "oneclick-upgrade_all";
    parent::add_argument("type", $type);
    return parent::execute_command();
  }
  
  public static function list_settings($domain) {
    parent::$command = "oneclick-list_settings";
    parent::add_argument("domain", $domain);
    return parent::execute_command();
  }
  
  public static function set_settings($domain, $setting, $value) {
    parent::$command = "oneclick-set_settings";
    parent::add_argument("domain", $domain);
    parent::add_argument("setting", $setting);
    parent::add_argument("value", $value);
    return parent::execute_command();
  }
  
  public static function destroy_easy($domain) {
    parent::$command = "oneclick-destroy_easy";
    parent::add_argument("domain", $domain);
    return parent::execute_command();
  }
  
  public static function destroy_advanced($url, $deletefiles) {
    parent::$command = "oneclick-destroy_advanced";
    parent::add_argument("url", $url);
    parent::add_argument("deletefiles", $deletefiles);
    return parent::execute_command();
  }

}

/**
 * class DreamHost_Rewards_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Rewards_commands
 * 
 * This class contains the Rewards Commands
 */
class DreamHost_Rewards_Commands extends DreamHost_API {

  public static function add_promo_code($code, $description, $bonus_domregs, $bonus_ips, $discount_month, $discount_1year, $discount_2year) {
    parent::$command = "rewards-add_promo_code";
    parent::add_argument("code", $code);
    parent::add_argument("description", $description);
    parent::add_argument("bonus_domregs", $bonus_domregs);
    parent::add_argument("bonus_ips", $bonus_ips);
    parent::add_argument("discount_month", $discount_month);
    parent::add_argument("discount_1year", $discount_1year);
    parent::add_argument("discount_2year", $discount_2year);
    return parent::execute_command();
  }
  
  public static function remove_promo_code($code) {
    parent::$command = "rewards-remove_promo_code";
    parent::add_argument("code", $code);
    return parent::execute_command();
  }
  
  public static function enable_promo_code($code) {
    parent::$command = "rewards-enable_promo_code";
    parent::add_argument("code", $code);
    return parent::execute_command();
  }
  
  public static function disable_promo_code($code) {
    parent::$command = "rewards-disable_promo_code";
    parent::add_argument("code", $code);
    return parent::execute_command();
  }
  
  public static function list_promo_codes() {
    parent::$command = "rewards-list_promo_codes";
    return parent::execute_command();
  }
  
  public static function promo_details($code) {
    parent::$command = "rewards-promo_details";
    parent::add_argument("code", $code);
    return parent::execute_command();
  }
  
  public static function referral_summary($period) {
    parent::$command = "rewards-referral_summary";
    parent::add_argument("period", $period);
    return parent::execute_command();
  }
  
  public static function referral_log($period) {
    parent::$command = "rewards-referral_log";
    parent::add_argument("period", $period);
    return parent::execute_command();
  }

}

/**
 * class DreamHost_Service_Control_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/Services_commands
 * 
 * This class contains the Service Control Commands
 */
class DreamHost_Service_Control_Commands extends DreamHost_API {
  
  public static function progress($token) {
    parent::$command = "services-progress";
    parent::add_argument("token", $token);
    return parent::execute_command();
  }
  
  public static function flvencoder($url, $dim = null, $snap = null, $ab = null, $ar = null, $batch = null, $quiet = null, $noemail = null) {
    parent::$command = "services-flvencoder";
    parent::add_argument("url", $url);
    parent::add_argument("dim", $dim);
    parent::add_argument("snap", $snap);
    parent::add_argument("ab", $ab);
    parent::add_argument("ar", $ar);
    parent::add_argument("batch", $batch);
    parent::add_argument("quiet", $quiet);
    parent::add_argument("noemail", $noemail);
    return parent::execute_command();
  }

}

/**
 * class DreamHost_User_Commands
 * 
 * @see DreamHost_API
 * @link http://wiki.dreamhost.com/API/User_commands
 * 
 * This class contains the User Commands
 */
class DreamHost_User_Commands extends DreamHost_API {
  
  public static function add_user($type, $username, $gecos, $server, $shell_type, $password = null, $enhanced_security = null, $billing_cycle = null) {
    parent::$command = "user-add_user";
    parent::add_argument("type", $type);
    parent::add_argument("username", $username);
    parent::add_argument("gecos", $gecos);
    parent::add_argument("server", $server);
    parent::add_argument("shell_type", $shell_type);
    parent::add_argument("password", $password);
    parent::add_argument("enhanced_security", $enhanced_security);
    parent::add_argument("billing_cycle", $billing_cycle);
    return parent::execute_command();
  }
  
  public static function list_users() {
    parent::$command = "user-list_users";
    return parent::execute_command();
  }
  
  public static function list_users_no_pw() {
    parent::$command = "user-list_users_no_pw";
    return parent::execute_command();
  }
  
  public static function remove_user($username, $type = null, $remove_all = null) {
    parent::$command = "user-remove_user";
    parent::add_argument("username", $username);
    parent::add_argument("type", $type);
    parent::add_argument("remove_all", $remove_all);
    return parent::execute_command();
  }
  
}
?>