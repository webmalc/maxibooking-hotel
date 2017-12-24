MaxiBooking Hotel project
========================

Client maintenance cli commands
--------
**Install clients**
```bash
bin/console mbh:client:install --clients=client1,client2 --env=prod
```
**Cache clear for clients**
```bash
bin/console mbh:cache:clear --clients=client1,client2 --env=prod
``` 


dotenv style
-------
1. database.env
2. create default env - maxibooking.env




scripts
-------
* **scripts/docker/start.sh** запуск docker контейнеров
* **scripts/docker/connect.sh** подключение к docker контейнеру
* **scripts/docker/console.sh** комманды symfony (bin/console)
* **scripts/docker/mongo.sh** подключение к mongodb в docker
* **scripts/docker/phpunit.sh** запуск phpunit из docker


phpstorm
--------
* **command**: {{project_dir}}/scripts/docker/console.sh
* **cli php interpreter**: {{project_dir}}/scripts/docker/php.sh
* **phpunit**: {{project_dir}}/scripts/docker/phpunit.sh
* **xdebug**: add directory mappings to server settings
* **cli debug**: XDEBUG_CONFIG="ideKey=PHPSTORM" PHP_IDE_CONFIG="serverName=cli"

search ru text
---------
* **command**: grep -ri '[А-Яа-яЁё]' --exclude-dir={vendor,web,var,.git,docs,pdfTemplates,PdfTemplates,Oktogo,OrderData.php,TranslatorCommand.php,VegaBundle,AbstractTranslateConverter.php,WebTestCase.php,TranslatorCommand.php,README.md} *|grep -v "\.\(csv\|yml\|png\|gif\|jpg\)"