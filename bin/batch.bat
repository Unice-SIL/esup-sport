@ECHO OFF

set vDate=%date:~-4%%date:~3,2%%date:~0,2%%time:~0,2%%time:~3,2%%time:~6,2%
set vDate=%vDate: =0%
set vDir=src\UcaBundle\Resources\sql\
set vDumpFile=Dump.sql-
set vSaveFile=Dump.%vDate%.sql~

set vDirLocal=web\upload\public\image
set vDirRemote=\\acatusdc\Echanges_Lyon\UNICE\upload\public\image
set vOptions=/e/i

if "%2" == "--overwrite" set vOptions=%vOptions%/Y
if not "%2" == "--overwrite" set vOptions=%vOptions%/d

:: Vous devez ajouter le repertoire C:\wamp64\bin\mysql\mysql5.7.24\bin a votre PATH.

if "%1" == "database:load" call:doDatabaseLoad %vDir%%vDumpFile%
if "%1" == "d:l" call:doDatabaseLoad %vDir%%vDumpFile%
if "%1" == "database:dump" call:doDatabaseDump %vDir%%vDumpFile%
if "%1" == "d:d" call:doDatabaseDump %vDir%%vDumpFile%
if "%1" == "doctrine:schema:upadate" call:doDsu 
if "%1" == "d:s:u" call:doDsu 
if "%1" == "image:export" call:doImageExport
if "%1" == "image:import" call:doImageImport
if "%1" == "image:delete" call:doImageDelete

echo Fin du traitement
goto:eof


:: Functions
:doDatabaseDump
call:doDsu
call:doImageExport
call:doDatabaseDumpFile %~1 
goto :eof

:doDatabaseDumpFile
mysqldump -u root uca > %~1 
::--no-create-db --replace --no-create-info
echo Base uca sauvegardee dans le fichier %~1
goto :eof

:doDatabaseLoad
call:doDatabaseDumpFile %vDir%%vSaveFile%
mysql -u root uca < %~1
echo Base uca chargee a partir du fichier %~1
call:doImageImport
goto:eof

:doDsu
php bin/console d:s:u -f
goto:eof

:doImageImport
xcopy %vDirRemote% %vDirLocal% %vOptions%
goto :eof

:doImageExport
xcopy %vDirLocal% %vDirRemote% %vOptions%
goto:eof

:doImageDelete
del /s/f/q %vDirLocal%\*
goto:eof

