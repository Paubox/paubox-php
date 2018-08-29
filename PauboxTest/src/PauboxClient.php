<?php
use service\EmailService;
use data\Attachment;
use data\Header;
use data\Message;
use data\Content;
use data\GetEmailDispositionResponse;
use data\SendMessageResponse;


$pauboxPharFilePath='phar://E:/WORK/GIT/paubox-php/PauboxTest/Paubox.Email.API.phar/';
include $pauboxPharFilePath.'src/config/ConfigurationManager.php';
include $pauboxPharFilePath.'src/service/EmailService.php';
include $pauboxPharFilePath.'src/data/Message.php';
include $pauboxPharFilePath.'src/data/Content.php';
include $pauboxPharFilePath.'src/data/Header.php';
include $pauboxPharFilePath.'src/data/Attachment.php';

ConfigurationManager::getProperties("E:\WORK\GIT\paubox-php\PauboxTest\src\config.ini");

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
array_push($email,'someone@domain.com');

$message->setHeader($header);
$message->setContent($content);
$message->setAttachments($attachments);
$message->setRecipients($email);

$sendMessageResponse = new SendMessageResponse();
$sendMessageResponse = $service->SendMessage($message);
print_r($sendMessageResponse); 

$resp= $service->getEmailDisposition($sendMessageResponse->sourceTrackingId);
print_r($resp);