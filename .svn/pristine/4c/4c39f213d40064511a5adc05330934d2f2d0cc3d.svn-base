/*
	CALL customer_address(1, 1);
*/
DROP PROCEDURE IF EXISTS customer_address;
CREATE DEFINER=`digi5`@`%` PROCEDURE customer_address(
	IN customer_id INT,
	IN exec BOOLEAN
)
    READS SQL DATA
    SQL SECURITY INVOKER
STAGE_01: BEGIN
	
	-- DECLARE EXIT HANDLER FOR SQLSTATE '42S22'
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		SELECT 0 AS proc_status_id, 'Erro ao solicitar os dados customer_address, consulte o administrador da Api.' AS proc_status;
	END;
  
	-- Id não informado
	IF(customer_id IS NULL OR customer_id = '') THEN
		SELECT 0 AS proc_status_id, 'Erro ao solicitar os dados customer_address, customer_id não informado' AS proc_status;
		LEAVE STAGE_01;
	END IF;
	
	-- Query para a listagem
	SET @query = CONCAT("
		SELECT
			apis_address_types.id AS address_types_id,
			apis_address_types.name AS address_types_name,
			apis_address.id AS address_id,
			apis_address.public_place AS address_public_place,
			apis_address.number AS address_number,
			apis_address.complement AS address_complement,
			apis_address.neighborhood AS address_neighborhood,
			apis_address.city AS address_city,
			apis_address.state AS address_state,
			apis_address.country AS address_country,
			apis_address.zip AS address_zip,
			apis_address.lat AS address_lat,
			apis_address.lng AS address_lng,
			apis_status.status_code AS address_status_id,
			apis_status.name AS address_status_name,
			apis_address.created
		FROM
			apis_address
				INNER JOIN
					apis_address_types
						ON
							apis_address_types.id = apis_address.address_type_id
				INNER JOIN
					apis_status
						ON
							apis_status.status_origin_id = apis_address.status_origin_id
						AND
							apis_status.status_code = apis_address.status_code
		WHERE
			apis_address.address_base_id = 1
		AND
			apis_address.address_ref_id = " , customer_id , "
		ORDER BY
			apis_address_types.name
	");

	-- Executa a query ou a retorna
	IF(exec) THEN
		PREPARE statement FROM @query;
		EXECUTE statement;
		DEALLOCATE PREPARE statement;
	ELSE
		SELECT @query;
	END IF;
END;