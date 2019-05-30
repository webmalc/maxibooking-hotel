MaxiBooking Hotel project
========================

jwt
--------
    $ mkdir -p config/jwt # For Symfony3+, no need of the -p option
    $ openssl genrsa -out config/jwt/private.pem -aes256 4096
    $ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
    
    Also need rights to www-data  or chmod 666 for keys ?
    
    

jenkins
--------
Jenkins TEST

vagrant
-------
**Запуск виртуальной машины**

Копируем из vagrant_files нужный файл в Vagrantfile корня.
Делаем нужные настройки.

* **vagrant up** создание виртуальной машины плюс настройка окружения

**Деплой**

 `default_vars.yml.dist -> deploy.vars.yml`
 
* **ssh-agent bash** создаем агент
* **ssh-add** добавляем ключ который в битбакете прописан
При первом разворачивании 
* **ansible-playbook -i inventory deploy.yml** inventory здесь либо `develop_inventory.yml` 
  для разработки либо `stage_inventory.yml` если залить изменения на stage server.
Внимание! Фикстуры будут грузится только если переменная `load_fixtures` - true.
Т.е. ставим true только один раз при разворачивании проекта.

* **ansible-playbook -i develop_inventory.yml deploy.yml** исключительно для разработки. (cache:clear вот это все...) 

 
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