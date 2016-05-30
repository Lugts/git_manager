<?php
# LOGIN:
$users = array(
	//$user => array($passwd, $token),
	"username" => array('md5(passwd)', 'gitlab:private_token'),
);

# LOCAL PATH:
# where your chroot tree start
$base_url = "/var/www/vhosts/";
# your public html dir
$html ="";

# REMOTE DEPLOY SERVERS:
# list of deploy server
$deployes = array(
//	$name=>$ip,
	"PRODUCTION - 1" => "0.0.0.0",
	"PRODUCTION - 2" => "0.0.0.0"
);
