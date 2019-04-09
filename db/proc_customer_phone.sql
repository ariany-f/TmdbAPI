/*
	CALL customer_phone(1, 1);
*/
DROP PROCEDURE IF EXISTS customer_phone;
CREATE DEFINER=`digi5`@`%` PROCEDURE customer_phone(
	IN customer_id INT,
	IN exec BOOLEAN
)
    READS SQL DATA
    SQL SECURITY INVOKER
STAGE_01: BEGIN
	
	-- DECLARE EXIT HANDLER FOR SQLSTATE '42S22'
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		SELECT 0 AS proc_status_id, 'Erro ao solicitar os dados proc_customer_phones, consulte o administrador da Api.' AS proc_status;
	END;
  
	-- Id não informado
	IF(customer_id IS NULL OR customer_id = '') THEN
		SELECT 0 AS proc_status_id, 'Erro ao solicitar os dados proc_customer_phones, customer_id não informado' AS proc_status;
		LEAVE STAGE_01;
	END IF;
	
	-- Query para a listagem
	SET @query = CONCAT("
		SELECT
			apis_phone_types.id AS phone_type_id,
			apis_phone_types.name AS phone_type_name,
			cli_customer_phones.id AS phone_id,
			cli_customer_phones.phone AS phone_number,
			apis_status.status_code AS phone_status_id,
			apis_status.name AS phone_status_name,
			cli_customer_phones.created
		FROM
			cli_customer_phones
				INNER JOIN
					apis_phone_types
						ON
							apis_phone_types.id = cli_customer_phones.phone_type_id
				INNER JOIN
					apis_status
						ON
							apis_status.status_code = cli_customer_phones.status_code
						AND
							apis_status.status_origin_id = cli_customer_phones.status_origin_id
		WHERE
			cli_customer_phones.customer_id = " , customer_id , "
		ORDER BY
			apis_phone_types.name
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