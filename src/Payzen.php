<?php

namespace Noweh\Payzen;

use Config;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Arr;

class Payzen
{
	private $_key;
	private $_url;
	private $_params;
	private $_nb_products = 0;

	/**
	 * Payzen constructor
	 */
	public function __construct()
	{
		$this->_key = Config::get("payzen.key");
		$this->_url = Config::get("payzen.url");
		$this->_params = Config::get("payzen.params");
		$this->set_params([
			'vads_site_id' => Config::get('payzen.site_id'),
			'vads_ctx_mode' => Config::get("payzen.env"),
			'vads_trans_date' => gmdate('YmdHis')
		]);
	}

	/**
	 * Magic method that allows you to use getters and setters on each payzen parameters
	 * Remember to not keep the 'vads' prefix in your accessor function name
	 * @param String $method name of the accessor
	 * @param array $args list of arguments
	 * @return Payzen
	 * @throws InvalidArgumentException
	 */
	public function __call(string $method, array $args): Payzen
	{
		if (function_exists($method)) {
			return call_user_func_array($method, $args);
		}
		if (preg_match("/get_(.*)/", $method, $matches)) {
			return $this->_params["vads_{$matches[1]}"];
		}
		if (preg_match("/set_(.*)/", $method, $matches)) {
			if (count($args) != 1) {
				throw new InvalidArgumentException($method . ' takes one argument.');
			}

			$this->_params["vads_{$matches[1]}"] = $args[0];
		}

        return $this;
	}

	/**
	 * Method to do massive assignment of parameters
	 * @param array $params associative array of Payzen parameters
	 * @return Payzen
	 */
	public function set_params(array $params)
	{
		$this->_params = array_merge($this->_params, $params);
		return $this;
	}

	/**
	 * Get all parameters
	 * @return array associative array of Payzen parameters
	 */
	public function get_params()
	{
		return $this->_params;
	}

	/**
	 * Generate payzen signature and add it to the parameters array
	 * @return Payzen
	 */
	public function set_signature()
	{
		//Initialization of the variable that will contain the string to encrypt
		$signature = "";

		//sorting fields alphabetically
		ksort($this->_params);
		foreach($this->_params as $name => $value){
			//Recovery of vads_ fields
			if (strpos($name, 'vads_') === 0) {
				//Concatenation with "+"
				$signature .= $value."+";
			}
		}
		//Adding the key at the end
		$signature .= $this->_key;

		//Encoding base64 encoded chain with SHA-256 algorithm
		$this->_params['signature'] = base64_encode(hash_hmac('sha256',$signature, $this->_key, true));

		return $this;
	}

	/**
	 * Return Payzen signature
	 * @return String Payzen signature
	 */
	public function get_signature()
	{
		return $this->_params['signature'];
	}

	/**
	 * Defines the total amount of the order. If you doesn't give the amount in parameter, it will be automaticly calculated
	 * by the sum of products you've got in your basket
	 * @param int $amount
	 * @return Payzen
	 */
	public function set_amount($amount = 0): Payzen
	{
		$this->_params['vads_amount'] = 0;
		if ($amount) {
			$this->_params['vads_amount'] = 100 * $amount;
		} else {
            Arr::where($this->_params, function ($value, $key) {
				if (preg_match("/vads_product_amount([0-9]+)/", $key, $match)) {
					$this->_params['vads_amount'] += $this->_params["vads_product_qty{$match[1]}"] * $value;
				}
			});
		}
		return $this;
	}

	/**
	 * Get total amount of the order
	 * @param boolean $decimal [optional] bool $decimal if true, you get a decimal otherwise you get standard Payzen amount format (int)
	 * @return float
	 */
	public function get_amount(bool $decimal = true): float
	{
		return $decimal ? $this->_params['vads_amount'] / 100 : $this->_params['vads_amount'];
	}

	/**
	 * Add a product to the order
	 * @param array $product $product , must have the following keys : 'label,amount,type,ref,qty'
	 * @return Payzen
	 */
	public function add_product(array $product): Payzen
	{
		$this->_params = array_merge($this->_params, [
			"vads_product_label{$this->_nb_products}" => $product["label"],
			"vads_product_amount{$this->_nb_products}" => $product["amount"] * 100,
			"vads_product_type{$this->_nb_products}" => $product["type"],
			"vads_product_ref{$this->_nb_products}" => $product["ref"],
			"vads_product_qty{$this->_nb_products}" => $product["qty"]
		]);
		$this->_params['vads_nb_products'] = $this->_nb_products += 1;
		return $this;
	}

	/**
	 * Return HTML Payzen form
	 * @param string $button html code of the submit button
	 * @return string
	 */
	public function get_form(string $button): string
	{
		$html_form = '<form method="post" action="' . $this->_url . '" accept-charset="UTF-8">';
		foreach ($this->_params as $key => $value) {
			$html_form .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
		}
		$html_form .= $button;
		$html_form .= "</form>";
		return $html_form;
	}

	/**
	 * Convert an input string to one compatible
	 * @param string $input
	 * @param string $allow n|a|an|ans
	 * @param int $length 1..255
	 * @param boolean $truncate allow returning truncated result if the transcoded $input length is over $length
	 * @return string
	 */
	public function ascii_transcode(string $input, string $allow, int $length, bool $truncate)
	{
		$ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $input);
		$ascii = str_replace (array("\r\n", "\n", "\r"), ' ', $ascii);
		switch($allow) {
			case 'a':
				$ascii = preg_replace("/[^A-Za-z \-]/", ' ', $ascii);
				break;
			case 'n':
				$ascii = preg_replace("/[^0-9]/", '', $ascii);
				break;
			case 'an':
				$ascii = preg_replace("/[^A-Za-z0-9 \-]/", ' ', $ascii);
				break;
			case 'ans':
				break;
			default:

		}
		$out = trim(preg_replace('/\s+/', ' ', $ascii));
		if (strlen($out) > $length && !$truncate) {
			return '';
		}

		return substr($out, 0 , $length);
	}
	
	/**
	 * Checking Payzen response signature
	 * @return bool
	 */
	public function isResponseSignatureValid(): bool
	{
		self::set_params(Arr::where(request()->all(), function($value, $key) {
			return strrpos($key, 'vads_', -5) !== false;
		}))->set_signature();
	
		return (request()->input('signature') && (self::get_signature() === request()->input('signature')));
	}
}
