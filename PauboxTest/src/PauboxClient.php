<?php
use service\EmailService;

include ('phar://Paubox-Email-API.phar/config/ConfigurationManager.php');
include ('phar://Paubox-Email-API.phar/service/EmailService.php');

ConfigurationManager::getProperties("E:\workspaces\paubox-php\PauboxTest\src\config.ini");

$service=new EmailService();
$resp= $service->getEmailDisposition("97b18032-59d5-47c7-a7c6-a2ed27f0f44e");
print_r($resp);