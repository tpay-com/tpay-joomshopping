INSERT IGNORE INTO `#__jshopping_payment_method`
SET
  `payment_id`            = '10001',
	`payment_code`          = 'tpay',
	`payment_class`         = 'pm_tpay',
	`scriptname`            = 'pm_tpay',
	`payment_publish`       = 0,
	`payment_ordering`      = 0,
  `payment_params`        = "seller_id=\nseller_secret=\nproxy=\ntransaction_end_status=6\ntransaction_pending_status=1\ntransaction_failed_status=3",
	`payment_type`          = 2,
	`price`                 = 0.00,
	`price_type`            = 1,
	`tax_id`                = -1,
	`show_descr_in_email`   = 0,
	`name_en-GB`            = 'Tpay.com online payments'
;
