ALTER TABLE  `%PREFIX%googleplay_sales` CHANGE  `sale_order_charged_date`  `sale_order_charged_date` DATETIME NOT NULL;

CREATE INDEX `idx_reports_report_issue` ON `%PREFIX%reports` (`report_issue`);