SELECT
	pay_card_brands.id,
	pay_card_brands.name,
	pay_card_brands.name_view
FROM
	for_products
		INNER JOIN
			for_product_pay_methods
				ON
					for_products.id = for_product_pay_methods.product_id
		INNER JOIN
			pay_gateway_methods
				ON
					for_product_pay_methods.method_id = pay_gateway_methods.method_id
		INNER JOIN
			pay_gateway_card_brands
				ON
					pay_gateway_methods.gateway_id = pay_gateway_card_brands.gateway_id
		INNER JOIN
			pay_card_brands
				ON
					pay_gateway_card_brands.card_brand_id = pay_card_brands.id
WHERE
	for_products.id = 1
AND
	pay_card_brands.name = 'elo'
AND
	pay_gateway_methods.gateway_id IN (1, 2)