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
define('CHECKOUT_FILE_TYPE_SALES', '1');
define('CHECKOUT_FILE_TYPE_EARNINGS', '2');

define('CHECKOUT_SALES_FILE_COL_COUNT', 17);
define('CHECKOUT_EARNINGS_FILE_COL_COUNT', 0);


class GoogleCheckoutCSVFileImporter {
	
	public static function checkUploadedFile($f) {
		$allowedTypes = array("text/comma-separated-values", "text/csv", "application/csv", "application/excel", "application/vnd.ms-excel", "application/vnd.msexcel", "text/anytext");
		$allowedExt = 'csv';
		
		$temp = explode(".", $f["name"]);
		$ext = strtolower(end($temp));
		
		if ($ext!='csv') {
			return 'Invalid file extension !';
		}

		if (!in_array($f['type'], $allowedTypes)) {
			return 'File type '.$f['type'].' is not supported !';
		}
		
		if ($f['size']>20000) {
			return 'File size must be under 20kB !';
		}
				
		return null;
	}
	
	public static function import($file, $type) {
		switch ($type) {
			case CHECKOUT_FILE_TYPE_SALES :
					return self::importSales($file);
				break;
			case CHECKOUT_FILE_TYPE_EARNINGS :
					return self::importEarnings($file);
				break;
		}
		
		return 'Unhandled file type !';
	}
	

	private static function importSales($file) {
		$error = array();
		$row = 0;
		
		if (($handle = fopen($file['tmp_name'], "r"))!==false) {
			$cols = array(
					SALE_ORDER_NUMBER,
					SALE_ORDER_CHARGED_DATE,
					SALE_ORDER_CHARGED_TIMESTAMP,
					SALE_FINANCIAL_STATUS,
					SALE_DEVICE_MODEL,
					SALE_PRODUCT_TITLE,
					SALE_PRODUCT_ID,
					SALE_PRODUCT_TYPE,
					SALE_SKU_ID,
					SALE_CURRENCY_CODE,
					SALE_ITEM_PRICE,
					SALE_TAXES_COLLECTED,
					SALE_CHARGED_AMOUNT,
					SALE_BUYER_CITY,
					SALE_BUYER_STATE,
					SALE_BUYER_POSTAL_CODE,
					SALE_BUYER_COUNTRY,
					SALE_APP_ID
				);
			
			
			while (($data=fgetcsv($handle, 1000, ","))!==false) {
				if ($row>0) {
					$rowCount = count($data);					
					if ($rowCount!=CHECKOUT_SALES_FILE_COL_COUNT) {
						$error[] = 'Row #'.$row.' has invalid column count '.$rowCount.'/'.CHECKOUT_SALES_FILE_COL_COUNT;
						
					} else {
						$values = array();
						for ($colIdx=0; $colIdx<$rowCount; ++$colIdx) { 
							$values[$cols[$colIdx]] = $data[$colIdx];
						}
					}

					$res = DbHelper::insertSale($values);
					if ($res!=null) {
						$error[] = 'Row #'.$row.' insertion failed : '.$res;
					}
				}
				
				++$row;
			}
			
			fclose($handle);
		}
		
		return $error;
	}
	
	private static function importEarnings($file) {
		return true;
	}

}
