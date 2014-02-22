<?php
define('TBL_NAME_PREFIX', '%PREFIX%');

define('TBL_PREFIX', $mGlobalCfg['tbl.prefix']);


define('TBL_APPLICATIONS', TBL_PREFIX.'applications');
define('APP_ID', 'app_id');
define('APP_NAME', 'app_name');
define('APP_PACKAGE', 'app_package');


define('TBL_INCREMENTS', TBL_PREFIX.'increments');
define('INC_VALUE', 'inc');


define('TBL_ISSUES', TBL_PREFIX.'issues');
define('ISSUE_ID', 'issue_id');
define('ISSUE_KEY', 'issue_key');
define('ISSUE_DATETIME', 'issue_datetime');
define('ISSUE_CAUSE', 'issue_cause');
define('ISSUE_STATE', 'issue_state');
define('ISSUE_PRIORITY', 'issue_priority');
define('ISSUE_APP_ID', 'issue_app_id');
define('ISSUE_MILESTONE_ID', 'issue_milestone_id');
define('ISSUE_COMMENT', 'issue_comment');
define('ISSUE_TARGET_VERSION_NAME', '');
define('ISSUE_TARGET_VERSION_CODE', '');


define('TBL_LOGS', TBL_PREFIX.'logs');
define('LOG_TIMESTAMP', 'log_timestamp');


define('TBL_REPORTS', TBL_PREFIX.'reports');
define('REPORT_ID', 'report_id');
define('REPORT_KEY', 'report_key');
define('REPORT_CRASH_DATE', 'user_crash_date');
define('REPORT_PACKAGE_NAME', 'package_name');
define('REPORT_VERSION_NAME', 'app_version_name');
define('REPORT_VERSION_CODE', 'app_version_code');
define('REPORT_ANDROID_VERSION', 'android_version');
define('REPORT_PHONE_MODEL', 'phone_model');
define('REPORT_BRAND', 'brand');
define('REPORT_PRODUCT', 'product');
define('REPORT_USER_COMMENT', 'user_comment');
define('REPORT_STACK_TRACE', 'stack_trace');
define('REPORT_LOGCAT', 'logcat');
define('REPORT_DEVICE_ID', 'device_id');
define('REPORT_INSTALLATION_ID', 'installation_id');
define('REPORT_IS_SILENT', 'is_silent');

define('REPORT_STATE', 'report_state');		 // 1 new => 2 viewed => 3 closed => 0 archived
define('REPORT_ISSUE', 'report_issue');


define('TBL_USERS', TBL_PREFIX.'users');
define('USER_ID', 'user_id');
define('USER_NAME', 'user_name');
define('USER_PASSWORD', 'user_password');
define('USER_EMAIL', 'user_email');


define('TBL_MILESTONES', TBL_PREFIX.'milestones');
define('MILE_ID', 'mile_id');
define('MILE_APP_ID', 'mile_app_id');
define('MILE_NAME', 'mile_name');
define('MILE_DUEDATE', 'mile_duedate');
define('MILE_DESC', 'mile_description');


define('TBL_SALES', TBL_PREFIX.'googleplay_sales');
// CSV file columns :
define('SALE_ORDER_NUMBER', 'sale_order_number');
define('SALE_ORDER_CHARGED_DATE', 'sale_order_charged_date');
define('SALE_ORDER_CHARGED_TIMESTAMP', 'sale_charged_timestamp');
define('SALE_FINANCIAL_STATUS', 'sale_financial_status');
define('SALE_DEVICE_MODEL', 'sale_device_model');
define('SALE_PRODUCT_TITLE', 'sale_product_title');
define('SALE_PRODUCT_ID', 'sale_product_id');
define('SALE_PRODUCT_TYPE', 'sale_product_type');
define('SALE_SKU_ID', 'sale_sku_id');
define('SALE_CURRENCY_CODE', 'sale_currency_code');
define('SALE_ITEM_PRICE', 'sale_item_price');
define('SALE_TAXES_COLLECTED', 'sale_taxes_collected');
define('SALE_CHARGED_AMOUNT', 'sale_charged_amount');
define('SALE_BUYER_CITY', 'sale_buyer_city');
define('SALE_BUYER_STATE', 'sale_buyer_state');
define('SALE_BUYER_POSTAL_CODE', 'sale_buyer_postal_code');
define('SALE_BUYER_COUNTRY', 'sale_buyer_country');
// extra columns :
define('SALE_APP_ID', 'sale_app_id');


define('TBL_EARNINGS', TBL_PREFIX.'googleplay_earnings');
// CSV file columns :

// extra columns :
define('EARN_APP_ID', 'earn_app_id');
