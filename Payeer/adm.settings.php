<?php	if( !defined( 'BILLING_MODULE' ) ) die( "Hacking attempt!" );
/*
=====================================================
 Billing
-----------------------------------------------------
 info@payeer.com
-----------------------------------------------------
 This code is copyrighted
=====================================================
*/

Class PAYSYS
{
	var $doc = "https://payeer.com";
	
	function Settings($config) 
	{
		$Form = array();

		if (empty($config['merchant_url']))
		{
			$Form[] = array("URL мерчанта", "URL для оплаты заказа (по умолчанию, https://payeer.com/merchant/)", "<input name=\"save_con[merchant_url]\" class=\"edit bk\" type=\"text\" value=\"https://payeer.com/merchant/\">" );
		}
		else
		{
			$Form[] = array("URL мерчанта", "URL для оплаты заказа (по умолчанию, https://payeer.com/merchant/)", "<input name=\"save_con[merchant_url]\" class=\"edit bk\" type=\"text\" value=\"" . $config['merchant_url'] ."\">" );
		}
		
		$Form[] = array("Идентификатор магазина", "Идентификатор магазина, зарегистрированного в Payeer", "<input name=\"save_con[merchant_id]\" class=\"edit bk\" type=\"text\" value=\"" . $config['merchant_id'] ."\">" );
		$Form[] = array("Секретный ключ", "Секретный ключ магазина, зарегистрированного в Payeer", "<input name=\"save_con[secret_key]\" class=\"edit bk\" type=\"text\" value=\"" . $config['secret_key'] ."\">" );
		$Form[] = array("Журнал", "Путь до файла для журнализации оплат (например, /payeer_orders.log)", "<input name=\"save_con[log_file]\" class=\"edit bk\" type=\"text\" value=\"" . $config['log_file'] ."\">" );
		$Form[] = array("IP фильтр", "Список доверенных ip адресов, можно указать маску", "<input name=\"save_con[ip_filter]\" class=\"edit bk\" type=\"text\" value=\"" . $config['ip_filter'] ."\">" );
		$Form[] = array("Email для ошибок", "Email для отправления ошибок оплаты", "<input name=\"save_con[email_error]\" class=\"edit bk\" type=\"text\" value=\"" . $config['email_error'] ."\">" );
		
		return $Form;
	}

	function form($id, $config, $invoice, $currency, $desc) 
	{
		$m_url = $config['merchant_url'];
		$m_shop = $config['merchant_id'];
		$m_orderid = $id;
		$m_amount = number_format($invoice['invoice_pay'], 2, '.', '');
		$m_curr = strtoupper($config['currency']);
		$m_curr = ($m_curr == 'RUR') ? 'RUB' : $m_curr;
		$m_desc = base64_encode($desc);
		$m_key = $config['secret_key'];
		$m_lang = 'ru';
		
		$arHash = array(
			$m_shop,
			$m_orderid,
			$m_amount,
			$m_curr,
			$m_desc,
			$m_key
		);
		
		$sign = strtoupper(hash('sha256', implode(':', $arHash)));

		return '
			<form method="post" id="paysys_form" action="' . $m_url . '">
				<input type="hidden" name="m_shop" value="' . $m_shop . '">
				<input type="hidden" name="m_orderid" value="' . $m_orderid . '">
				<input type="hidden" name="m_amount" value="' . $m_amount . '">
				<input type="hidden" name="m_curr" value="' . $m_curr . '">
				<input type="hidden" name="m_desc" value="' . $m_desc . '">
				<input type="hidden" name="m_sign" value="' . $sign . '">
				<input type="hidden" name="lang" value="' . $m_lang . '">
				<input type="submit" name="process" class="bs_button" value="Оплатить" />
			</form>';
	}
	
	function check_id($DATA) 
	{
		return $DATA["m_orderid"];
	}
	
	function check_ok($DATA) 
	{
		return $DATA['m_orderid'] . '|success';
	}
	
	function check_out($DATA, $CONFIG, $INVOICE) 
	{
		$log_text = "--------------------------------------------------------\n".
			"operation id       " . $DATA["m_operation_id"] . "\n".
			"operation ps       " . $DATA["m_operation_ps"] . "\n".
			"operation date     " . $DATA["m_operation_date"] . "\n".
			"operation pay date " . $DATA["m_operation_pay_date"] . "\n".
			"shop               " . $DATA["m_shop"] . "\n".
			"order id           " . $DATA['m_orderid'] . "\n".
			"amount             " . $DATA["m_amount"] . "\n".
			"currency           " . $DATA["m_curr"] . "\n".
			"description        " . base64_decode($DATA["m_desc"]) . "\n".
			"status             " . $DATA["m_status"] . "\n".
			"sign               " . $DATA["m_sign"] . "\n\n";

		$log_file = $CONFIG['log_file'];
		
		if (!empty($log_file))
		{
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . $log_file, $log_text, FILE_APPEND);
		}
		
		// проверка цифровой подписи и ip

		$sign_hash = strtoupper(hash('sha256', implode(":", array(
			$DATA['m_operation_id'],
			$DATA['m_operation_ps'],
			$DATA['m_operation_date'],
			$DATA['m_operation_pay_date'],
			$DATA['m_shop'],
			$DATA['m_orderid'],
			$DATA['m_amount'],
			$DATA['m_curr'],
			$DATA['m_desc'],
			$DATA['m_status'],
			$CONFIG['secret_key']
		))));
		
		$valid_ip = true;
		$sIP = str_replace(' ', '', $CONFIG['ip_filter']);
		
		if (!empty($sIP))
		{
			$arrIP = explode('.', $_SERVER['REMOTE_ADDR']);
			if (!preg_match('/(^|,)(' . $arrIP[0] . '|\*{1})(\.)' .
			'(' . $arrIP[1] . '|\*{1})(\.)' .
			'(' . $arrIP[2] . '|\*{1})(\.)' .
			'(' . $arrIP[3] . '|\*{1})($|,)/', $sIP))
			{
				$valid_ip = false;
			}
		}
		
		if (!$valid_ip)
		{
			$message .= " - IP-адрес сервера не является доверенным\n" . 
			"   доверенные IP: " . $sIP . "\n" .
			"   IP текущего сервера: " . $_SERVER['REMOTE_ADDR'] . "\n";
			$err = true;
		}

		if ($DATA["m_sign"] != $sign_hash)
		{
			$message .= " - не совпадают цифровые подписи\n";
			$err = true;
		}
		
		if ($DATA['m_status'] != 'success')
		{
			$message .= " - статус платежа не является success\n";
			$err = true;
		}

		if ($err)
		{
			$to = $CONFIG['email_error'];

			if (!empty($to))
			{
				$message = "Не удалось провести платёж через систему Payeer по следующим причинам:\n\n" . $message . "\n" . $log_text;
				$headers = "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n" . 
				"Content-type: text/plain; charset=utf-8 \r\n";
				mail($to, 'Ошибка оплаты', $message, $headers);
			}
			
			return $DATA['m_orderid'] . '|error';
		}
		else
		{
			return 200;
		}
	}
}

$Paysys = new PAYSYS;
?>