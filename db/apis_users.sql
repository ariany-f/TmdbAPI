-- ApisDig
SET @email = "ariany.ferreira@vidaclass.com.br";
SET @senha = "AgoraVai201809";

SELECT

	MD5(
			CONCAT(
				"Digi59ea5066fd980ff24ac7009cd3Vida8a72a3ec17e888Class58b68097004a3f3580837e8dbApis",
				@email,
				@senha
			)
	);	