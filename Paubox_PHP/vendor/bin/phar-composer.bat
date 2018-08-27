@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../clue/phar-composer/bin/phar-composer
php "%BIN_TARGET%" %*
