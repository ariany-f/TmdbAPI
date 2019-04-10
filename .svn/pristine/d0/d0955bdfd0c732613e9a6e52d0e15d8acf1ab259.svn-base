DROP PROCEDURE IF EXISTS search_occupation;
CREATE DEFINER=`digi5`@`%` PROCEDURE search_occupation(
	IN company_id INT,
	IN termo VARCHAR(255),
	IN exec BOOLEAN
)
    READS SQL DATA
    SQL SECURITY INVOKER
STAGE_01: BEGIN

	-- DECLARE EXIT HANDLER FOR SQLSTATE '42S22'
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		SELECT 0 AS proc_status_id, 'Erro ao solicitar os dados, consulte o administrador da Api' AS proc_status;
	END;

	-- Acerto de parametros
	SET @termo = CONCAT(termo, " ");
	SET @name = "%";

	-- Filtro termo, anula matriz e filial
	IF(@termo <> '') THEN
		WHILE (LOCATE(' ', @termo) > 0) DO
			SET @STR = SUBSTRING(@termo, 1, LOCATE(' ',@termo)-1);
			SET @termo = SUBSTRING(@termo, LOCATE(' ', @termo) + 1);
			SET @name = CONCAT(@name, @STR, '%');
		END WHILE;
		SET @termo = CONCAT("`apis_occupations`.`name` LIKE '" , @name , "'");
	ELSE
		SELECT 0 AS proc_status_id, 'Termo não pode ser vazio' AS proc_status;
		SET @termo = TRUE;
	END IF;

	SET @query = CONCAT("
		SELECT
			`apis_occupations`.`id`,
			`apis_occupations`.`name`
		FROM
			`apis_occupations`
		WHERE
			" , @termo , "
		AND
			apis_occupations.status_code = 2
		ORDER BY
			`apis_occupations`.`name`
		LIMIT
			10
	");

	IF(exec) THEN
		PREPARE statement FROM @query;
		EXECUTE statement;
		DEALLOCATE PREPARE statement;
	ELSE
		SELECT @query;
	END IF;
END;