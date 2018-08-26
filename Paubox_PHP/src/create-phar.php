<?php
/* require dirname(__DIR__) . '\vendor\autoload.php';
use Symfony\Component\Finder\Finder;

$srcRoot = getcwd();
$buildRoot = dirname(__DIR__)."\build";

echo $srcRoot;
echo "\n";
echo  $buildRoot;

$phar = new Phar($buildRoot . "/Paubox-Email-API.phar",
    FilesystemIterator::CURRENT_AS_FILEINFO |     	FilesystemIterator::KEY_AS_FILENAME, "Paubox-Email-API.phar");
$phar["PauboxClient.php"] = file_get_contents($srcRoot . "\common\*.php");
$phar["ConfigurationManager.php"] = file_get_contents($srcRoot . "\config\ConfigurationManager.php");
/* $phar->startBuffering();
$finder = new Finder();
$finder->files()
->ignoreVCS(true)
->name('*.php')
->notName('Compiler.php')
->notName('ClassLoader.php')
->in(__DIR__.'/..');

foreach ($finder as $file) {
    $this->addFile($phar, $file);
}

$phar->setStub($this->getStub()); */

//copy(dirname(__DIR__) . "/config.ini", $buildRoot . "/config.ini");




// create phar
$phar = new Phar(dirname(__DIR__)."\build" . "/Paubox-Email-API.phar");

// start buffering. Mandatory to modify stub to add shebang
$phar->startBuffering();
$defaultStub = $phar->createDefaultStub('EmailService.php');
// Add the rest of the apps files
$phar->buildFromDirectory(getcwd());
$phar->setStub($defaultStub);
$phar->stopBuffering();