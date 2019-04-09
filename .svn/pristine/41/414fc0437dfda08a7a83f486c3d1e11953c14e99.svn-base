-- ------------------------------------------------------------------
-- registers_before_insert
-- Modifica insert se pago, registra data do pagamento
-- ------------------------------------------------------------------
DROP TRIGGER IF EXISTS `registers_before_insert`;
CREATE DEFINER=`digi5`@`%` TRIGGER `registers_before_insert`
	BEFORE INSERT ON `pay_registers`
	FOR EACH ROW
BEGIN
	IF(NEW.paid = 1) THEN
		SET NEW.paid_date=NOW();
	END IF;
END;

-- ------------------------------------------------------------------
-- registers_before_update
-- Modifica update se pago, registra data do pagamento
-- ------------------------------------------------------------------
DROP TRIGGER IF EXISTS `registers_before_update`;
CREATE DEFINER=`digi5`@`%` TRIGGER `registers_before_update`
	BEFORE UPDATE ON `pay_registers`
	FOR EACH ROW
BEGIN
	IF(NEW.paid = 1 AND OLD.paid_date IS NULL) THEN
		SET NEW.paid_date=NOW();
	END IF;
END;


-- ------------------------------------------------------------------
-- register_logs_after_insert
-- Registra log de pagamento depois do insert
-- ------------------------------------------------------------------
DROP TRIGGER IF EXISTS `register_logs_after_insert`;
CREATE DEFINER=`digi5`@`%` TRIGGER `register_logs_after_insert`
	AFTER INSERT ON `pay_registers`
	FOR EACH ROW
BEGIN
		INSERT INTO
			pay_register_logs
		SET
			register_id=NEW.id,
			paid=NEW.paid,
			status_code=NEW.status_code;  
END;

-- ------------------------------------------------------------------
-- register_logs_after_update
-- Registra log de pagamento depois do update
-- ------------------------------------------------------------------
DROP TRIGGER IF EXISTS `register_logs_after_update`;
CREATE DEFINER=`digi5`@`%` TRIGGER `register_logs_after_update`
	AFTER UPDATE ON `pay_registers`
	FOR EACH ROW
BEGIN
		IF(NEW.status_code != OLD.status_code) THEN
			INSERT INTO
				pay_register_logs
			SET
				register_id=NEW.id,
				paid=NEW.paid,
				status_code=NEW.status_code;
		END IF;
END;