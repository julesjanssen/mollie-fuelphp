<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * FuelPHP Mollie package implementation.
 *
 * @author 		Jules Janssen
 * @version   	1.0
 * @package   	Fuel
 * @subpackage 	Mollie
 * @link 		http://julesj.nl
 */
namespace Mollie;

class Ideal{

	/**
	 * Cached instance of the configuration file
	 *
	 * @var   array
	 */
	protected static $configuration = array();

	// Seperation of configuration values into variables
	protected $_partner_id;
	protected $_payment_profile;
	protected $_testmode;

	private static $_api_host = 'https://secure.mollie.nl';
	private static $_api_port = 443;

	public static function forge($config = array()){

		\Config::load('mollie', true);
		static::$configuration = array_merge(\Config::get('mollie', array()), $config);

		$instance = new static();

		return $instance;

	}

	public function list_banks(){

		$params = array(
			'a'				=> 'banklist'
		);

		$response = $this->_do_request($params);
		$banks = $response['bank'];

		$list = array();
		if(isset($banks[0]) && is_array($banks[0])){

			foreach($banks as $value){

				$list[$value['bank_id']] = $value['bank_name'];

			}

		}else{

			$list[$banks['bank_id']] = $banks['bank_name'];

		}

		return $list;

	}

	public function create_payment($bank_id, $amount, $description){

		$return_url = \Arr::element(static::$configuration, 'return_url');
		$report_url = \Arr::element(static::$configuration, 'report_url');

		if(!$return_url)	$return_url = \Uri::current();
		if(!$report_url)	$report_url = \Uri::current();

		$params = array(
			'a'				=> 'fetch',
			'bank_id'		=> preg_replace('/([^0-9]+)/', '', $bank_id),
			'amount'		=> (int) $amount,
			'description'	=> \Str::truncate($description, 29),
			'returnurl'		=> $return_url,
			'reporturl'		=> $report_url,
		);

		$response = $this->_do_request($params);

		return $response;

	}

	public function check_payment($transaction_id){

		$params = array(
			'a'					=> 'check',
			'transaction_id'	=> $transaction_id,
		);

		$response = $this->_do_request($params);

		return $response;

	}

	private function _do_request($params = array(), $path = false){

		$params['partner_id'] 		= (int) static::$configuration['partner_id'];
		$params['partnerid'] 		= (int) static::$configuration['partner_id'];
		$params['testmode']			= (bool) \Arr::element(static::$configuration, 'testmode', false);

		$payment_profile = \Arr::element(static::$configuration, 'payment_profile');
		if(!empty($payment_profile)){
			$params['payment_profile']	= static::$configuration['payment_profile'];
		}

		if(!$path) $path = '/xml/ideal/';

		$ch = curl_init();

		if(!ini_get('safe_mode') && !ini_get('open_basedir')){
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		}

		curl_setopt($ch, CURLOPT_URL, self::$_api_host . $path);
		curl_setopt($ch, CURLOPT_PORT, self::$_api_port);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));

		$response = curl_exec($ch);
		$info = curl_getinfo($ch);

		if($info['http_code'] == 200){

			$body = \Format::factory($response, 'xml')->to_array();

		}

		return $body;

	}

	/**
	 * Constructor, called via static::forge() if configuration values are meant to be used
	 *
	 * @param   string
	 * @param   string
	 * @return  void
	 */
	public function __construct(){

		if(!in_array('ssl', stream_get_transports())){

			throw new \Fuel_Exception('No SSL support');

		}

	}


}