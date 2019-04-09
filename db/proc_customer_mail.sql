/*
	CALL customer_mail(1, 1);
*/
DROP PROCEDURE IF EXISTS customer_mail;
CREATE DEFINER=`digi5`@`%` PROCEDURE customer_mail(
	IN customer_id INT,
	IN exec BOOLEAN
)
    READS SQL DATA
    SQL SECURITY INVOKER
STAGE_01: BEGIN
	
	-- DECLARE EXIT HANDLER FOR SQLSTATE '42S22'
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		SELECT 0 AS proc_status_id, 'Erro ao solicitar os dados customer_mail, consulte o administrador da Api.' AS proc_status;
	END;
  
	-- Id não informado
	IF(customer_id IS NULL OR customer_id = '') THEN
		SELECT 0 AS proc_status_id, 'Erro ao solicitar os dados customer_mail, customer_id não informado' AS proc_status;
		LEAVE STAGE_01;
	END IF;
	
	-- Query para a listagem
	SET @query = CONCAT("
		SELECT
			apis_mail_types.id AS mail_types_id,
			apis_mail_types.name AS mail_types_name,
			cli_customer_mails.id AS mail_id,
			cli_customer_mails.mail mail_mail,
			apis_status.status_code AS mail_status_id,
			apis_status.name AS mail_status_name,
			cli_customer_mails.created
		FROM
			cli_customer_mails
				INNER JOIN
					apis_mail_types
						ON
							apis_mail_types.id = cli_customer_mails.mail_type_id
				INNER JOIN
					apis_status
						ON
							apis_status.status_origin_id = cli_customer_mails.status_origin_id
						AND
							apis_status.status_code = cli_customer_mails.status_code
		WHERE
			cli_customer_mails.customer_id = " , customer_id , "
		ORDER BY
			apis_mail_types.name
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