<?php	if( !defined( 'BILLING_MODULE' ) ) die( "Hacking attempt!" );
/*
=====================================================
 Billing
-----------------------------------------------------
 gara-soft@mail.ru
-----------------------------------------------------
 This code is copyrighted
=====================================================
*/

Class PAYSYS 
{
	var $doc = "http://forum.dle-billing.ru/topic6.html";
	var $server = 0;

	function Settings( $config ) 
	{
		$Form = array();
	
		$Form[] = array("Идентификатор магазина:", "Ваш идентификатор в системе Free-Kassa.", "<input name=\"save_con[login]\" class=\"edit bk\" type=\"text\" value=\"" . $config['login'] ."\">" );
		$Form[] = array("Пароль #1:", "Используется интерфейсом инициализации оплаты.", "<input name=\"save_con[pass1]\" class=\"edit bk\" type=\"password\" value=\"" . $config['pass1'] ."\">" );
		$Form[] = array("Пароль #2:", "Используется интерфейсом оповещения о платеже, XML-интерфейсах.", "<input name=\"save_con[pass2]\" class=\"edit bk\" type=\"password\" value=\"" . $config['pass2'] ."\">" );

		return $Form;
	}

	function form( $id, $config, $invoice, $currency ) 
	{
		$sign_hash = md5("$config[login]:$invoice[invoice_pay]:$config[pass1]:$id");

		return '
			<form method="post" id="paysys_form" action="http://www.free-kassa.ru/merchant/cash.php">
				
				<input type=hidden name=m value="' . $config['login'] . '">
				<input type=hidden name=oa value="' . $invoice['invoice_pay'] . '">
				<input type=hidden name=o value="' . $id . '">
				<input type=hidden name=s value="' . $sign_hash . '">

				<input type="submit" name="process" class="bs_button" value="Оплатить" />
			</form>';
		
	}
	
	function check_id( $DATA ) 
	{
		return $DATA["o"];
	}
	
	function check_ok( $DATA ) 
	{
		return 'OK'.$DATA["o"];
	}
	
	function check_out( $DATA, $CONFIG, $INVOICE ) 
	{
		$out_summ = $INVOICE['invoice_pay'];
		$inv_id = $DATA["o"];
		$crc = $DATA["s"];

		$crc = strtoupper($crc);

		$my_crc = strtoupper(md5("$CONFIG[login]:$out_summ:$CONFIG[pass2]:$inv_id"));
	
		if ($my_crc !=$crc)
			return "Verifying the signature information about the payment failed!\n";

		return 200;
	}
	
}

$Paysys = new PAYSYS;
?>