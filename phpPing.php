<?php

$configfile = __DIR__ .'/config.php';
if (!file_exists($configfile)) die('config.php not found; copy config.sample.php to config.php and change as needed');

include_once __DIR__ .'/vendor/autoload.php';
include_once __DIR__ .'/config.php';

$config = new phpPingConfig;
for ($i=1; $i<count($_SERVER['argv']); $i++) $config->targets[] = $_SERVER['argv'][$i];
if (empty($config->targets)) die('no targets specified');

$factory = new Clue\React\Icmp\Factory();
$icmp = $factory->createIcmp4();

$statuses = array();

foreach ($config->targets as $target) {
	
	if ($config->debug) echo 'Pinging "' . $target . '"...' . PHP_EOL;
	
	$icmp->ping($target, $config->timeout)->then(function ($time) use ($icmp, $config, $target, &$statuses) {
		
		$msg = 'Success ('.$target.'): ' . round($time, 3) . 's';
		if ($config->debug) echo $msg  . PHP_EOL;
		
		$statuses[$target] = array(
			'success' => false,
			'msg' => $msg,
		);
		
	}, function (Exception $error) use($config, $target, &$statuses) {
			
		$msg = 'Error  ('.$target.'): ' . $error->getMessage();
		if ($config->debug) echo $msg . PHP_EOL;
		
		$statuses[$target] = array(
			'success' => false,
			'msg' => $msg,
		);
		
	})->then(array($icmp, 'pause'))->then(function () use($config, $target, &$statuses) {
		
		if (count($config->targets)!=count($statuses)) return;
		
		$mail = new PHPMailer;
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
		
		$mail->addAddress('blackbricksoftware@gmail.com', 'Black Brick Software');
		
		$mail->Subject = 'Server Statuses';
		$mail->Body    = print_r($statuses,true);
	
		if(!$mail->send()) {
			if ($config->debug) echo 'Message could not be sent.';
			if ($config->debug) echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			if ($config->debug) echo 'Message has been sent';
		}
	});
}

$factory->getLoop()->run();



