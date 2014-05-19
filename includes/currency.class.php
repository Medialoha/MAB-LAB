<?php defined('DIRECT_ACCESS_CHECK') or die('DIRECT ACCESS NOT ALLOWED');
/**
 * Copyright (c) 2013 EIRL DEVAUX J. - Medialoha.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the GNU Public License v3.0
 * which accompanies this distribution, and is available at
 * http://www.gnu.org/licenses/gpl.html
 *
 * Contributors:
 *     EIRL DEVAUX J. - Medialoha - initial API and implementation
 */
define ('CURRENCY_EXCHANGE_RATES_URL', 'http://finance.yahoo.com/d/quotes.csv?f=l1&s=');


class Currency {

	public static $currency_codes = array(
			'AED', 'ANG', 'ARS', 'AUD', 'BGN', 'BHD', 'BND', 'BOB', 'BRL', 'BWP', 'CAD', 'CHF',
			'CLP', 'CNY', 'COP', 'CRC', 'CZK', 'DKK', 'DOP', 'DZD', 'EEK', 'EGP', 'EUR', 'FJD',
			'GBP', 'HKD', 'HNL', 'HRK', 'HUF', 'IDR', 'ILS', 'INR', 'JMD', 'JOD', 'JPY', 'KES',
			'KRW', 'KWD', 'KYD', 'KZT', 'LBP', 'LKR', 'LTL', 'LVL', 'MAD', 'MDL', 'MKD', 'MUR',
			'MXN', 'MYR', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PEN', 'PGK', 'PHP',
			'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'SAR', 'SCR', 'SEK', 'SGD', 'SKK',
			'SLL', 'SVC', 'THB', 'TND', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU',
			'UZS', 'VND', 'YER', 'ZAR', 'ZMK',
	);
		
	private static $currency_rates = array();
	
	private $currency_code;
	
	
	// CONSTRUCTOR
	public function Currency($currency_code) {
		$this->setCurrencyCode($currency_code);
		
	}
	
	
	public function setCurrencyCode($code) {
		if (in_array($code, self::$currency_codes)) {
			$this->currency_code = $code;
			
			if (!isset($this->currency_rates[$code]))
				$this->currency_rates[$code] = array();
				
		} else { $this->currency_code = 'EUR'; }		
	}
	
	public function getCurrencyCode() {
		return $this->currency_code;
	}
	
	public function format($value, $showCurrencyCode=true) {
		return ($showCurrencyCode?$this->currency_code.' ':'').round($value, 2);
	}

	public function formatValue($value, $currencyCode) {
		return ($currencyCode!=null?$currencyCode.' ':'').round($value, 2);
	}
	
	public function convert($value, $from) {
		return $this->convertTo($value, $from, $this->currency_code);
	}
		
	public function convertTo($value, $from, $to) {
		if (!isset(self::$currency_rates[$from][$to])) {
			// get rate
			$handle = fopen(CURRENCY_EXCHANGE_RATES_URL.$from.$to.'=X', 'r');
			
			if ($handle) {
				$res = fgetcsv($handle);
				
				self::$currency_rates[$from][$to] = $res[0];
				
				fclose($handle);
				
			} else { return false; }
		}
		
		return $value*self::$currency_rates[$from][$to];
		
// 		$googleUrl = "http://www.google.com/finance/converter?a=".urlencode($value)."&from=".urlencode($from)."&to=".urlencode($to);
	
// 		$res = explode("<span class=bld>", file_get_contents($googleUrl));
// 		$res = explode("</span>",$res[1]);
	
// 		return preg_replace("/[^0-9\.]/", null, $res[0]);
	}
	
}