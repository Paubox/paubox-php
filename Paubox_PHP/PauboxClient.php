<?php

//use \Model\Message;
use \data\Content;
use \data\Header;
use data\Message;
use \data\Attachment;
use data\SendMessageResponse;
use data\GetEmailDispositionResponse;

use service\EmailService;
include_once 'service/EmailService.php';
include('./data/Message.php');
include('./data/Content.php');
include('./data/Header.php');
include('./data/Attachment.php');
include_once 'config/ConfigurationManager.php';

$message = new Message();
$message->setRecipients(["vighneshtrivedi2004@gmail.com"]);

$content = new Content();
$content->setPlainText("Hello World");

$header = new Header();
$header->setSubject("Hello from the Paubox PHP with attachment 2");
$header->setFrom("renee@undefeatedgames.com");

$attachment = new Attachment();
$attachment->setFileName("hello_worldnew.txt");
$attachment->setContentType("text/plain");
$attachment->setContent("SGVsbG8gV29ybGQh\n");

$attachment2 = new Attachment();
$attachment2->setFileName("hello_world2.txt");
$attachment2->setContentType("text/plain");
$attachment2->setContent("SGVsbG8gV29ybGQh\n");


$attachments = array();
array_push($attachments,$attachment);
array_push($attachments,$attachment2);

$message->setHeader($header);
$message->setContent($content);
$message->setAttachments($attachments);

ConfigurationManager::getProperties("E:\WORK\GIT\paubox-php\Paubox_PHP\config.ini");

$service=new EmailService();
$sendMessageResponse = new SendMessageResponse();
$sendMessageResponse = $service->SendMessage($message);
print_r($sendMessageResponse);

$emailDispositionResponse = new GetEmailDispositionResponse();
$emailDispositionResponse= $service->getEmailDisposition("97b18032-59d5-47c7-a7c6-a2ed27f0f44e");
print_r($emailDispositionResponse);

?>

