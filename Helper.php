<?php

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require BASE_PATH . 'packages/PHPMailer/src/Exception.php';
require BASE_PATH . 'packages/PHPMailer/src/PHPMailer.php';
require BASE_PATH . 'packages/PHPMailer/src/SMTP.php';
require_once BASE_PATH.'DbQuery.php';
/**
 *
 */
class Helper {
	public static function getCurrentTime()
	{
		return  date('H:i:s');
	}
	public static function getCurrentDate()
	{
		return  date('Y-m-d');
	}
	public static function getCurrentDay()
	{
		return  date('D');
	}
	/**
	 * @param int $days
	 * @param string $type
	 *
	 * @return false|string
	 */
	public static function afterDate( int $days,string $type ) {
		$db = new DbQuery();
		$settings= $db->getSetting();
		$days    = sprintf( '%s %s', $days, 'Days' );
		$current = date_create( date( "Y-m-d" ) );
		date_sub( $current, date_interval_create_from_date_string( $days ) );
		if($settings&&key_exists('smtp_username',$settings))
		{
			$time = null;
			if($type == 'pre_order')
			{
				$time=current($db->getSetting()['preorder_time']);
			}
			else if($type == 'order')
			{
				$time=current($db->getSetting()['order_time']);
			}
			$time = explode(':',$time);
			return date_format( $current->setTime($time[0],$time[1],'00'), "Y-m-d H:i:s" );
		}
		else
		{
			return date_format( $current, "Y-m-d" );
		}

	}

	public static function generatePdf( string $html, string $type, $location = BASE_PATH ) {
		//Configure the directory where you have the dompdf
		require_once BASE_PATH . "packages/dompdf/autoload.inc.php";
		//$dompdf = new dompdf();
		//$dompdf = new DOMPDF();
		$dompdf = new \Dompdf\Dompdf(array('enable_remote' => true));

//		$options = $dompdf->getOptions();
//		$options->set( array( 'isRemoteEnabled' => true ) );
//		$dompdf->setOptions( $options );
		$dompdf->loadHtml( $html );
		// (Optional) Setup the paper size and orientation
		// $dompdf->setPaper( 'A4','portrait' );
		// Render the HTML as PDF
		$dompdf->render();
		//return pdf as string
		$output = $dompdf->output();
		//save to a pdf file
		file_put_contents( $location . ( $type == 'butcher' ? 'labels.pdf' : 'slips.pdf' ), $output );
		// Output the generated PDF to Browser
//		$dompdf->stream();
	}

	/**
	 * @param string $emailTo
	 * @param string $emailToName
	 * @param string $type
	 *
	 * @throws Exception
	 */
	public static function sendMail( string $emailTo, string $emailToName, string $type,string $orderType ) {
		$db=new DbQuery();
		$settings=$db->getSetting();
		$smtpUsername = $settings&&key_exists('smtp_username',$settings)?current($settings['smtp_username']):"esookhlinknbit@gmail.com";
		$smtpPassword = $settings&&key_exists('smtp_password',$settings)?current($settings['smtp_password']):"@@EsookhLinknbit123!!";
//		EMAIL_FROM     = "hello@linknbit.com";
		$emailFrom     = $settings&&key_exists('sender_email',$settings)?current($settings['sender_email']):"sarmadking@gmail.com";
		$emailFromName = $settings&&key_exists('sender_name',$settings)?current($settings['sender_name']):"Sarmad Sohail";
		$mail          = new PHPMailer;
		$mail->isSMTP();
		$mail->SMTPDebug  = 0; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
		$mail->Host       = $settings&&key_exists('smtp_host',$settings)?current($settings['smtp_host']):"smtp.gmail.com"; // use $mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
		$mail->Port       = $settings&&key_exists('smtp_port',$settings)?current($settings['smtp_port']):587; // TLS only
		$mail->SMTPSecure = $settings&&key_exists('smtp_security',$settings)?current($settings['smtp_security']):'tls'; // ssl is depracated
		$mail->SMTPAuth   = true;
		$mail->Username   = $smtpUsername;
		$mail->Password   = $smtpPassword;
		$mail->setFrom( $emailFrom, $emailFromName );
		$mail->addAddress( $emailTo, $emailToName );
		$mail->Subject = 'Order details';
		$mail->msgHTML( "Order details" ); //$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
		$mail->AltBody = 'HTML messaging not supported';
		if($orderType=='pre_order')
		{
			$mail->addAttachment( BASE_PATH . 'order_details.csv' ); //csv file
		}
		else if($orderType=='order')
		{
			if ( strtolower( $type == 'logistics' ) ) {
				$mail->addAttachment( BASE_PATH . 'logistics.csv' ); //csv file
			} else if ( strtolower( $type == 'butcher' ) ) {
				$mail->addAttachment( BASE_PATH . 'order_details.csv' ); //csv file
				$mail->addAttachment( BASE_PATH . 'labels.pdf' );//pdf file
				$mail->addAttachment( BASE_PATH . 'slips.pdf' );//pdf file
			}
		}


		if ( ! $mail->send() ) {
			echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
			echo "Message sent!";
		}
	}
}