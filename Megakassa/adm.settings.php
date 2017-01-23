<?php	if( !defined( 'BILLING_MODULE' ) ) die( "Hacking attempt!" );


Class PAYSYS 
{
	var $doc = "";

	function Settings( $config ) 
	{
		$Form = array();
	
		$Form[] = array("ID Магазина:", "Из настроек магазина в дичном кабинете megakassa.ru", "<input name=\"save_con[shop_id]\" class=\"edit bk\" type=\"text\" value=\"" . $config['shop_id'] ."\">" );
		$Form[] = array("Секретный ключ:", "Из настроек магазина в дичном кабинете megakassa.ru", "<input name=\"save_con[secret_key]\" class=\"edit bk\" type=\"text\" value=\"" . $config['secret_key'] ."\">" );
		$Form[] = array("Валюта оплаты:", "Используется на сайте megakassa.ru", "<select name=\"save_con[server_currency]\" class=\"uniform\"><option value=\"RUB\" " . ( $config['server_currency'] == 'RUB' ? "selected" : "" ) . ">RUB</option><option value=\"USD\" " . ( $config['server_currency'] == "USD" ? "selected" : "" ) . ">USD</option><option value=\"EUR\" " . ( $config['server_currency'] == "EUR" ? "selected" : "" ) . ">EUR</option></select>" );

		return $Form;
	}

	function form( $order_id, $config, $invoice, $currency, $description ) 
	{
		$amount			= number_format($invoice['invoice_pay'], 2, '.', ''); 
		$method_id		= '';
		$client_email	= '';
		$debug			= ''; // или "1"
		$secret_key		= $config['secret_key']; 
		$description 	= "Balance " . $invoice['invoice_user_name'];
		$signature		= md5($config['secret_key'].md5(join(':', array($config['shop_id'], $amount, $config['server_currency'], $description, $order_id, $method_id, $client_email, $debug, $config['secret_key']))));
		$language		= 'ru'; // или 'en'
	
		return '
			<form method="post" action="https://megakassa.ru/merchant/" accept-charset="UTF-8" id="paysys_form">
				<input type="hidden" name="shop_id" value="' . $config['shop_id'] . '" />
				<input type="hidden" name="amount" value="' . $amount . '" />
				<input type="hidden" name="currency" value="' . $config['server_currency'] . '" />
				<input type="hidden" name="description" value="' . $description . '" />
				<input type="hidden" name="order_id" value="' . $order_id . '" />
				<input type="hidden" name="method_id" value="" />
				<input type="hidden" name="client_email" value="" />
				<input type="hidden" name="debug" value="' . $debug . '" />
				<input type="hidden" name="signature" value="' . $signature . '" />
				<input type="hidden" name="language" value="' . $language . '" />
				
				<input type="submit" class="bs_button" value="Оплатить">
			</form>';
		
	}
	
	function check_id( $DATA ) 
	{
		return $DATA["order_id"];
	}
	
	function check_ok( $DATA ) 
	{
		return 'ok';
	}
	
	function check_out( $DATA, $CONFIG, $INVOICE ) 
	{
		global $_REQUEST;
		
			$ip_checked = false;
	
			foreach(array(
				'HTTP_X_CLUSTER_CLIENT_IP',
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_FORWARDED',
				'HTTP_FORWARDED_FOR',
				'HTTP_FORWARDED',
				'HTTP_CLIENT_IP',
				'REMOTE_ADDR'
			) as $param) {
				if(!empty($_SERVER[$param]) && $_SERVER[$param] === '5.196.121.217') {
					$ip_checked = true;
					break;
				}
			}
			if(!$ip_checked) {
				return 'Error: ip_checked';
			}
			
			// проверка на наличие обязательных полей
			// поля $payment_time и $debug могут дать true для empty() поэтому их нет в проверке
			foreach(array(
				'uid',
				'amount',
				'amount_shop',
				'amount_client',
				'currency',
				'order_id',
				'payment_method_id',
				'payment_method_title',
				'creation_time',
				'client_email',
				'status',
				'signature'
			) as $field) {
				if(empty($_REQUEST[$field])) {
					return 'Error: have fields';
				}
			}
			
			// ваш секретный ключ
			$secret_key	= $CONFIG['secret_key'];
			
			// нормализация данных
			$uid					= (int)$_REQUEST["uid"];
			$amount					= (double)$_REQUEST["amount"];
			$amount_shop			= (double)$_REQUEST["amount_shop"];
			$amount_client			= (double)$_REQUEST["amount_client"];
			$currency				= $_REQUEST["currency"];
			$order_id				= $_REQUEST["order_id"];
			$payment_method_id		= (int)$_REQUEST["payment_method_id"];
			$payment_method_title	= $_REQUEST["payment_method_title"];
			$creation_time			= $_REQUEST["creation_time"];
			$payment_time			= $_REQUEST["payment_time"];
			$client_email			= $_REQUEST["client_email"];
			$status					= $_REQUEST["status"];
			$debug					= (!empty($_REQUEST["debug"])) ? '1' : '0';
			$signature				= $_REQUEST["signature"];
			
			// проверка валюты
			if(!in_array($currency, array('RUB', 'USD', 'EUR'), true)) {
				return 'Error: currency';
			}
			
			// проверка статуса платежа
			if(!in_array($status, array('success', 'fail'), true)) {
				return 'Error: status';
			}
			
			// проверка формата сигнатуры
			if(!preg_match('/^[0-9a-f]{32}$/', $signature)) {
				return 'Error: signature format';
			}
			
			// проверка значения сигнатуры
			$signatureGen		= md5(join(':', array(
				$_REQUEST['uid'], $_REQUEST['amount'], $_REQUEST['amount_shop'], $_REQUEST['amount_client'], $_REQUEST['currency'], 
				$_REQUEST['order_id'], $_REQUEST['payment_method_id'], $_REQUEST['payment_method_title'],
				$_REQUEST['creation_time'], $_REQUEST['payment_time'], $_REQUEST['client_email'], $_REQUEST['status'],
				$_REQUEST['debug'], $CONFIG['secret_key']
			 )));
			
			if($signatureGen !== $_REQUEST["signature"]) {
				return 'Error: signature:: ' . $_REQUEST["signature"] . ' != ' . $signatureGen;
			}

			if( $amount	!= number_format($INVOICE['invoice_pay'], 2, '.', '') )
			{
				return 'Error: amount';
			}
			
			if( $status == 'success' )
			{
				return 200;
			}
			
		return "Error\n";
	}
	
}

$Paysys = new PAYSYS;
?>