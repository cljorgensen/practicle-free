@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../sqonk/mysql-sync/bin/mysql-sync
php "%BIN_TARGET%" %*
