<?php

class phpPingConfig {
	
	/* ips/hostnames to ping */
	
	/* email settings */
	public $fromEmail = 'from@example.com';
	public $successEmails = array(
		'email1@example.com',
		'email2@example.org',
	);
	public $failureEmails = array(
		'email3@example.com',
		'email4@example.org',
	);
	
	/* smtp settings */
	public $smtpauth = false;
	public $smtpuser = '';
	public $smtppass = '';
	public $smtphost = 'localhost';
	public $smtpsecure = 'none';
	public $smtpport = '25';
}
