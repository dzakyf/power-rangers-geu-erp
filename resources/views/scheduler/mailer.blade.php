  <meta http-equiv="refresh" content="10">

  <?php
	//error_reporting(0);

	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Database\Eloquent\Builder;
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	/*$passtrue = md5("B928739239CDEB665A3981892002983984459".date('Ymd'));

if (strcmp($passtrue,$key)!== 0){
	$status['status'] = "errorkey02 $passtrue";
		
		echo json_encode($status); exit;
		exit;
	
}
*/

	if ($_SERVER['REMOTE_ADDR'] != "::1") {
		error_log("Url Mailer dicoba akses dari IP " . $_SERVER['REMOTE_ADDR']);
		exit(0);
	}


	$data = DB::table("mail_logs")->where("Status", 'UNSENT')->first();

	if ($data) {

		if (filter_var(trim($data->Email_To), FILTER_VALIDATE_EMAIL)) {
			$mail = new PHPMailer(true);
			$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
			$mail->isSMTP();                                            // Send using SMTP
			$mail->Host       = 'mail.ksps.co.id';                    // Set the SMTP server to send through
			$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
			$mail->Username   = 'notifikasi@ksps.co.id';                     // SMTP username
			$mail->Password   = '98kspsNoti#$';                               // SMTP password
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
			$mail->Port       = 587;
			$mail->setFrom('notifikasi@ksps.co.id', 'ERP-ALERTER');

			$mail->addAddress($data->Email_To);     // Add a recipient
			DB::update(DB::raw("UPDATE mail_logs set Status ='PROGRESSED', Send_Date=NOW() where ID=$data->ID"));
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $data->Subject;
			$mail->Body    = $data->Contents;

			try {
				$mail->send();
				DB::update(DB::raw("UPDATE mail_logs set Status ='SENT', Send_Date=NOW() where ID=$data->ID"));
			} catch (Exception $e) {
				error_log("Mailer Error: " . $mail->ErrorInfo);
			}


			$data->Email_To = "";
		} else {
			DB::update(DB::raw("UPDATE mail_logs set Status ='RETRY', Send_Date=NOW() where ID=$data->ID"));
		}
	}

	echo "check load date : " . date('c');

	?>