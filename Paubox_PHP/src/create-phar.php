<?php

// create phar
$phar = new Phar(dirname(__DIR__)."\build" . "/Paubox-Email-API.phar");
// start buffering. Mandatory to modify stub to add shebang
$phar->startBuffering();
$defaultStub = $phar->createDefaultStub('stub.php');
// Add the rest of the apps files
$phar->buildFromDirectory(getcwd());
$phar->setStub($defaultStub);
$phar->stopBuffering();