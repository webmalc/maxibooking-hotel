MaxiBooking Hotel project
========================

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

search ru text
---------
* **command**: grep -ri '[А-Яа-яЁё]' --exclude-dir={vendor,web,var,.git} *|grep -v "\.\(csv\|yml\|png\|gif\|jpg\)"