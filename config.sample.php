<?php

class phpPingConfig {
	
	public $debug = false; // set to true for STDOUT output
	
	/* ping settings */
	public $targets = array( // ips/hostnames to ping cant also be specified on the command line
		'example.com',
		'example.org',
		'93.184.216.34',
	);
	public $timeout = 3; // number of seconds to wait on ping before timeout
	
	/* mail settings */
	public $mailer = 'mail'; // mail for php mail, smtp for smtp mail
	public $fromName = 'John Doe'; // defaults to username@hostname
	public $fromEmail = 'from@example.com'; // defaults to username@hostname
	public $successEmails = array( // leave empty for none
		'email1@example.com',
		'email2@example.org',
	);
	public $failureEmails = array( // leave empty for none
		'email3@example.com',
		'email4@example.org',
	);
	
	/* smtp settings */
	public $smtpauth = false;
	public $smtpuser = '';
	public $smtppass = '';
	public $smtphost = 'localhost';
	public $smtpsecure = ''; // tls or ssl
	public $smtpport = '25';
}
