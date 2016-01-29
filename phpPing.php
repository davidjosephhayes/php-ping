<?php

$configfile = __DIR__ .'/config.php';
if (!file_exists($configfile)) die('config.php not found; copy config.sample.php to config.php and change as needed' . PHP_EOL);

include_once __DIR__ .'/vendor/autoload.php';
include_once __DIR__ .'/config.php';

$config = new phpPingConfig;
for ($i=1; $i<count($_SERVER['argv']); $i++) $config->targets[] = $_SERVER['argv'][$i];
if (empty($config->targets)) die('no targets specified' . PHP_EOL);

$statuses = array();

foreach ($config->targets as $target) {
	
	$factory = new Clue\React\Icmp\Factory();
	$icmp = $factory->createIcmp4();
	
	if ($config->debug) echo 'Pinging "' . $target . '"...' . PHP_EOL;
	
	$icmp->ping($target, $config->timeout)->then(function ($time) use ($icmp, $config, $target, &$statuses) {
		
		$msg = 'Success: ' . round($time, 3) . 's';
		if ($config->debug) echo $msg  . PHP_EOL;
		
		$statuses[$target] = array(
			'success' => true,
			'msg' => $msg,
		);
		
	}, function (Exception $error) use($config, $target, &$statuses) {
			
		$msg = 'Error: ' . $error->getMessage();
		if ($config->debug) echo $msg . PHP_EOL;
		
		$statuses[$target] = array(
			'success' => false,
			'msg' => $msg,
		);
		
	})->then(array($icmp, 'pause'))->then(function () use($config, $target, &$statuses) {
		
		if (count($config->targets)!=count($statuses)) return;
		
		$body = '';
		$foundSuccess = 0;
		$foundFailure = 0;
		foreach ($statuses as $target =>  $status) {
			if ($status['success']) $foundSuccess++;
			if (!$status['success']) $foundFailure++;
			$body .= $target.': '.$status['msg']."\n";
		}
		if ((!$foundSuccess || empty($config->successEmails)) && (!$foundFailure || empty($config->failureEmails))) {
			if ($config->debug) echo 'No mail to send' . PHP_EOL;
			return;
		}
		
		$mail = new PHPMailer;
		//~ $mail->isHTML(true); 
		if ($config->debug) $mail->SMTPDebug = 3;
		
		if ($config->mailer=='smtp') {
			$mail->isSMTP();
			$mail->Host = $config->smtphost;
			if (!empty($config->smtpsecure)) $mail->SMTPSecure = $config->smtpsecure;
			$mail->Port = $config->smtpport;
			if ($config->smtpauth) {
				$mail->SMTPAuth = true;
				$mail->Username = $config->smtpuser;
				$mail->Password = $config->smtppass;
			}
		}

		$fromEmail = empty($config->fromEmail) ? get_current_user().'@'.gethostname() : $config->fromEmail;
		$fromName = empty($config->fromName) ? $fromEmail : $config->fromName;
		$mail->setFrom($fromEmail, $fromName);
		
		$mail->Body = $body;
			
		if ($foundSuccess>0 && !empty($config->successEmails)) { 
			foreach ($config->successEmails as $email) $mail->addAddress($email);
		}
		
		if ($foundFailure>0 && !empty($config->failureEmails)) { 
			foreach ($config->failureEmails as $email) $mail->addAddress($email);
		}		
		
		if ($foundFailure>0) {
			$mail->Subject = 'Server up status: (failure)';
		} else {
			$mail->Subject = 'Server up status: (success)';
		}
		
		//~ $mail->AltBody = nl2br(;
	
		if ($config->debug) echo '--- sending mail ---' . PHP_EOL;
		if(!$mail->send()) {
			if ($config->debug) echo 'Message could not be sent.' . PHP_EOL;
			if ($config->debug) echo 'Mailer Error: ' . $mail->ErrorInfo . PHP_EOL;
		} else {
			if ($config->debug) echo 'Message has been sent' . PHP_EOL;
		}
	});
	
	$factory->getLoop()->run();
}




