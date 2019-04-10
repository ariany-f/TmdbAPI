/*
	CALL customer_document(1, 1);
*/
DROP PROCEDURE IF EXISTS customer_document;
CREATE DEFINER=`digi5`@`%` PROCEDURE customer_document(
	IN customer_id INT,
	IN exec BOOLEAN
)
    READS SQL DATA
    SQL SECURITY INVOKER
STAGE_01: BEGIN
	
	-- DECLARE EXIT HANDLER FOR SQLSTATE '42S22'
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		SELECT 0 AS proc_status_id, 'Erro ao solicitar os dados customer_document, consulte o administrador da Api.' AS proc_status;
	END;
  
	-- Id não informado
	IF(customer_id IS NULL OR customer_id = '') THEN
		SELECT 0 AS proc_status_id, 'Erro ao solicitar os dados customer_document, customer_id não informado' AS proc_status;
		LEAVE STAGE_01;
	END IF;
	
	-- Query para a listagem
	SET @query = CONCAT("
		SELECT
			apis_document_types.id	AS document_type_id,
			apis_document_types.name AS document_type_name,
			apis_document_emitters.id AS document_emitter_id,
			apis_document_emitters.name AS document_emitter_name,
			cli_customer_documents.number AS document_number,
			cli_customer_documents.issue_date AS document_issue_date,
			apis_status.status_code AS document_status_id,
			apis_status.name AS document_status_name,
			cli_customer_documents.created
		FROM
			cli_customer_documents
				INNER JOIN
					apis_document_types
						ON
							apis_document_types.id = cli_customer_documents.document_type_id
				INNER JOIN
					apis_document_emitters
						ON
							apis_document_emitters.id = cli_customer_documents.document_emitter_id
				INNER JOIN
					apis_status
						ON
							apis_status.status_origin_id = cli_customer_documents.status_origin_id
						AND
							apis_status.status_code = cli_customer_documents.status_code
							
		WHERE
			cli_customer_documents.customer_id = " , customer_id , "
		ORDER BY
			apis_document_types.name
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