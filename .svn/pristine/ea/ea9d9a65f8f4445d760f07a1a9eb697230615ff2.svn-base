SELECT
	`for_brands`.`id`,
	`for_brands`.`name`,
	`for_product_type_brands`.`id`,
	`for_product_types`.`id`,
	`for_product_types`.`name`,
	`for_products`.`id`,
	`for_products`.`name`
FROM
	`for_products`
		INNER JOIN
			`for_product_type_brands`
				ON
					`for_products`.`product_type_brand_id` = `for_product_type_brands`.`id`
		INNER JOIN
			`for_product_types`
				ON
					`for_product_type_brands`.`product_type_id` = `for_product_types`.`id`
		INNER JOIN
			`for_brands`
				ON
					`for_product_type_brands`.`brand_id` = `for_brands`.`id`
