@echo off
set TIMESTAMP=%date:~6,4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
docker exec recuperacion-tfg-db-1 mysqldump -u root -ppasswd bd_clima > backups\backup_%TIMESTAMP%.sql
echo Backup completado: %TIMESTAMP%