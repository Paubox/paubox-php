<?php

//use \Model\Message;
use \Model\Content;
use \Model\Header;
use Model\Message;
use \Model\Attachment;

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
$header->setSubject("Hello from the Paubox PHP with file");
$header->setFrom("renee@undefeatedgames.com");

$attachment = new Attachment();
$attachment->setFileName("hello_worldnew.txt");
$attachment->setContentType("text/plain");
$attachment->setContent("SGVsbG8gV29ybGQh\n");

$attachments = array($attachment);

$message->setHeader($header);
$message->setContent($content);
$message->setAttachments($attachments);
 
$service=new EmailService();

$resp = $service->SendEmail($message);

ConfigurationManager::getProperties("E:/projects/paubox-php/Paubox_PHP/config.ini");
$resp= $service->getEmailDisposition("97b18032-59d5-47c7-a7c6-a2ed27f0f44e");
print_r($resp);

?>

