@ECHO OFF

openfiles > NUL 2>&1
IF NOT %ERRORLEVEL% EQU 0 GOTO NotAdmin

SET BIN_TARGET=%~dp0/../laravel/valet/valet.php
IF NOT EXIST BIN_TARGET (
    SET BIN_TARGET=%~dp0/valet.php
)

IF "%~1" == "share" (
    ECHO Not available on Windows.
) ELSE (
    php "%BIN_TARGET%" %*
)

GOTO End

:NotAdmin
echo You must start a Command Prompt as an Administrator.
:End
