<?php
use service\EmailService;
use data\Attachment;
use data\Header;
use data\Message;
use data\Content;

include ('phar://Paubox.Email.API.phar/src/config/ConfigurationManager.php');
include ('phar://Paubox.Email.API.phar/src/service/EmailService.php');
include ('phar://Paubox.Email.API.phar/src/data/Message.php');
include ('phar://Paubox.Email.API.phar/src/data/Content.php');
include ('phar://Paubox.Email.API.phar/src/data/Header.php');
include ('phar://Paubox.Email.API.phar/src/data/Attachment.php');

ConfigurationManager::getProperties("E:\projects\paubox-php\PauboxTest\src\config.ini");

$service=new EmailService();

$message = new Message();
$content = new Content();
$content->setPlainText("Hello World");

$header = new Header();
$header->setSubject("Hello from the Paubox PHP with attachment 2");
$header->setFrom("renee@undefeatedgames.com");

$attachment = new Attachment();
$attachment->setFileName("hello_worldnew.txt");
$attachment->setContentType("text/plain");
$attachment->setContent("SGVsbG8gV29ybGQh\n");

$attachments = array($attachment);
$attachment2 = new Attachment();
$attachment2->setFileName("hello_world2.txt");
$attachment2->setContentType("text/plain");
$attachment2->setContent("SGVsbG8gV29ybGQh\n");


$attachments = array();
array_push($attachments,$attachment);
array_push($attachments,$attachment2);

$email= array();
array_push($email,'vighneshtrivedi2004@gmail.com');

$message->setHeader($header);
$message->setContent($content);
$message->setAttachments($attachments);
$message->setRecipients($email);

//$sendMessageResponse = new SendMessageResponse();
$sendMessageResponse = $service->SendMessage($message);
print_r($sendMessageResponse); 

//$resp= $service->getEmailDisposition("97b18032-59d5-47c7-a7c6-a2ed27f0f44e");
//print_r($resp);