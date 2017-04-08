<?php	if( !defined( 'BILLING_MODULE' ) ) die( "Hacking attempt!" );
/*
=====================================================
 Billing
-----------------------------------------------------
 any-pay.org
-----------------------------------------------------
 This code is copyrighted
=====================================================
*/

Class PAYSYS
{
	var $doc = "https://any-pay.org";
	
	function Settings($config) 
	{
		$Form = array();

		if (empty($config['merchant_url']))
		{
			$Form[] = array("URL мерчанта", "URL для оплаты заказа (по умолчанию, https://any-pay.org/merchant)", "<input name=\"save_con[merchant_url]\" class=\"edit bk\" type=\"text\" value=\"https://any-pay.org/merchant\">" );
		}
		else
		{
			$Form[] = array("URL мерчанта", "URL для оплаты заказа (по умолчанию, https://any-pay.org/merchant)", "<input name=\"save_con[merchant_url]\" class=\"edit bk\" type=\"text\" value=\"" . $config['merchant_url'] ."\">" );
		}
		
		$Form[] = array("ID магазина", "ID магазина, зарегистрированного в AnyPAY", "<input name=\"save_con[merchant_id]\" class=\"edit bk\" type=\"text\" value=\"" . $config['merchant_id'] ."\">" );
		$Form[] = array("Секретный пароль", "Секретный пароль магазина, зарегистрированного в AnyPAY", "<input name=\"save_con[secret_key]\" class=\"edit bk\" type=\"text\" value=\"" . $config['secret_key'] ."\">" );
		$Form[] = array("Журнал", "Путь до файла для журнализации оплат (например, /anypay_orders.log)", "<input name=\"save_con[log_file]\" class=\"edit bk\" type=\"text\" value=\"" . $config['log_file'] ."\">" );
		$Form[] = array("IP фильтр", "Список доверенных ip адресов, можно указать маску", "<input name=\"save_con[ip_filter]\" class=\"edit bk\" type=\"text\" value=\"" . $config['ip_filter'] ."\">" );
		$Form[] = array("Email для ошибок", "Email для отправления ошибок оплаты", "<input name=\"save_con[email_error]\" class=\"edit bk\" type=\"text\" value=\"" . $config['email_error'] ."\">" );
		
		return $Form;
	}

	function form($id, $config, $invoice, $currency, $desc) 
	{
		$m_url = $config['merchant_url'];
		$m_shop = $config['merchant_id'];
		$m_orderid = $id;
		$m_amount = $invoice['invoice_pay'];
		$m_desc = 'Order #'.$id;

		return '
			<form method="post" id="paysys_form" action="' . $m_url . '">
				<input type="hidden" name="id" value="' . $m_shop . '">
				<input type="hidden" name="pay_id" value="' . $m_orderid . '">
				<input type="hidden" name="summ" value="' . $m_amount . '">
				<input type="hidden" name="desc" value="' . $m_desc . '">
				<input type="submit" class="bs_button" value="Оплатить" />
			</form>';
	}
	
	function check_id($DATA) 
	{
		return $DATA["pay_id"];
	}
	
	function check_ok($DATA) 
	{
		return $DATA['pay_id'] . '|success';
	}
	
	function check_out($DATA, $CONFIG, $INVOICE) 
	{
		$log_text = "--------------------------------------------------------\n".
			"operation id       " . $DATA["pay_id"] . "\n".
			"shop               " . $DATA["id"] . "\n".
			"amount             " . $DATA["summ"] . "\n".
			"currency           " . $DATA["curr"] . "\n".
			"description        " . $DATA["desc"] . "\n".
			"sign               " . $DATA["sign"] . "\n\n";

		$log_file = $CONFIG['log_file'];
		
		if (!empty($log_file))
		{
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . $log_file, $log_text, FILE_APPEND);
		}
		
		// проверка цифровой подписи и ip
		
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

		$hash = md5($CONFIG['merchant_id'].':'.$_REQUEST['summ'].':'.$_REQUEST['pay_id'].':'.$CONFIG['secret_key']);

		if ($DATA["sign"] != $hash)
		{
			$message .= " - не совпадают цифровые подписи\n";
			$err = true;
		}

		if ($err)
		{
			$to = $CONFIG['email_error'];

			if (!empty($to))
			{
				$message = "Не удалось провести платёж через систему any-pay по следующим причинам:\n\n" . $message . "\n" . $log_text;
				$headers = "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n" . 
				"Content-type: text/plain; charset=utf-8 \r\n";
				mail($to, 'Ошибка оплаты', $message, $headers);
			}
			
			return $DATA['pay_id'] . '|error';
		}
		else
		{
			return 200;
		}
	}
}

$Paysys = new PAYSYS;
?>