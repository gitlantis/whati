<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2020                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2020
 * $Id$
 *
 */

/**
 * SMS Provider for Whati.
 *
 * Partly copied and mixed and matched from MySmsMantra, RingCentral and Clickatell.
 */

require __DIR__ . '/Parser.php';

class org_civicrm_sms_whati extends CRM_SMS_Provider
{

  /**
   * api type to use to send a message
   * @var	string
   */
  protected $_apiType = 'http';

  /**
   * provider details
   * @var	string
   */
  protected $_providerInfo = array();

  /**
   * Whati API Server Session ID
   *
   * @var string
   */
  protected $_sessionID = NULL;

  /**
   * Curl handle resource id
   *
   */
  protected $_ch;


  public $_apiURL = "https://app-server.wati.io/api/v1/sendTemplateMessage";



  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var object
   * @static
   */
  static private $_singleton = array();

  /**
   * Constructor
   *
   * Create and auth a Whati session.
   *
   * @return void
   */
  function __construct($provider = array(), $skipAuth = FALSE)
  {
    // Adjust for old civi versions which pass in numeric value.

    $this->_apiType = CRM_Utils_Array::value('api_type', $provider, 'http');
    $this->_providerInfo = $provider;

    if ($skipAuth) {
      return TRUE;
    }
    // first create the curl handle

    /**
     * Reuse the curl handle
     */
    $this->_ch = curl_init();
    if (!$this->_ch || !is_resource($this->_ch)) {
      return PEAR::raiseError('Cannot initialise a new curl handle.');
    }

    curl_setopt($this->_ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($this->_ch, CURLOPT_VERBOSE, 1);
    curl_setopt($this->_ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($this->_ch, CURLOPT_COOKIEJAR, "/dev/null");
    curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($this->_ch, CURLOPT_USERAGENT, 'CiviCRM - http://civicrm.org/');
  }

  /**
   * singleton function used to manage this object
   *
   * @return object
   * @static
   *
   */
  static function &singleton($providerParams = array(), $force = FALSE)
  {
    $providerID = CRM_Utils_Array::value('provider_id', $providerParams);
    $skipAuth   = $providerID ? FALSE : TRUE;
    $cacheKey   = (int) $providerID;

    if (!isset(self::$_singleton[$cacheKey]) || $force) {
      $provider = array();
      if ($providerID) {
        $provider = CRM_SMS_BAO_Provider::getProviderInfo($providerID);
      }
      self::$_singleton[$cacheKey] = new org_civicrm_sms_whati($provider, $skipAuth);
    }
    return self::$_singleton[$cacheKey];
  }

  /**
   * Send an SMS Message via the Whati API Server
   *
   * @param array the message with a recipients/message
   *
   * @return mixed true on sucess or PEAR_Error object
   * @access public
   */
  function send($recipients, $header, $message, $jobID = NULL)
  {
    
    $url = $this->_providerInfo['api_url'];
    $token = $this->_providerInfo['api_params']['token'];

    try{

      foreach(array($recipients) as $recipt)
      {
        $number = preg_replace('/[^0-9]/', '', $recipt);

        $this->_ch = curl_init();


        $parse = new Parser();
        $result = $parse->get_string_between($message, "[[", "]]");

        $whati = new WhatiTemplate();

        $whati->template_name = $result[0]??' ';
        $whati->broadcast_name = $result[1]??'octopus8';

        $result = $parse->get_string_between($message, "{{", "}}");
        $template[] = new TemplateParameters();

        $i=0;
        foreach($result as $res){
          $temp = new TemplateParameters();
          $str = explode('===', $res);
          $temp->name=$str[0]??' ';
          
          $temp->value=$str[1]??' ';

          $template[$i]=$temp;
          $i++;
        }
        
        $whati->parameters = $template;

        $url = $url.'?whatsappNumber='.$number;

        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json-patch+json",
        "Accept: application/json",
        "Authorization: Bearer ".$token
      ));

      //Civi::log()->debug(json_encode($whati));
      curl_setopt($this->_ch, CURLOPT_POSTFIELDS, json_encode($whati));

      //added to curl command to close the inteface once the message submitted
      curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($this->_ch, CURLOPT_TIMEOUT, 200);    

      //execute the curl commande
      $response = curl_exec($this->_ch);
      
      //Civi::log()->debug($response);
      curl_close($this->_ch);

      }
      return $token;

    } catch (Exception $e) {
      $errMsg = $e->getMessage();
      return PEAR::raiseError($errMsg);
    }

  }

  
}
