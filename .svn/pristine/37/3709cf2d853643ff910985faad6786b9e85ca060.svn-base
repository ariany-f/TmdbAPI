SELECT
	`for_products`.`id`,
	`for_products`.`name`,
	`for_products`.`description`,
	`for_brands`.`company`,
	`for_brands`.`cnpj`,
	`for_products`.`disclaimer`,
	`for_products`.`susep_code`,
	`for_products`.`value`,
	`for_products`.`installments`,
	`for_products`.`valid_period_days`,
	`pay_methods`.`name`
FROM
	`for_brands`
		INNER JOIN
			`for_product_type_brands`
				ON
					`for_brands`.`id` = `for_product_type_brands`.`brand_id`
		INNER JOIN
			`for_product_types`
				ON
					`for_product_type_brands`.`product_type_id` = `for_product_types`.`id`
		INNER JOIN
			`for_products`
				ON
					`for_product_type_brands`.`id` = `for_products`.`product_type_brand_id`
		INNER JOIN
			`for_product_pay_methods`
				ON
					`for_products`.`id` = `for_product_pay_methods`.`product_id`
		INNER JOIN
			`pay_methods`
				ON
					`for_product_pay_methods`.`method_id` = `pay_methods`.`id`
WHERE
	`for_brands`.status_code = 2
AND
	`for_product_type_brands`.status_code = 2
AND
	`for_product_types`.status_code = 2
AND
	`for_products`.status_code = 2
AND
	`for_product_pay_methods`.status_code = 2
AND
	`pay_methods`.status_code = 2;