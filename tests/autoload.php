<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 28.03.18
 * Time: 13:24
 */

echo "Run command 'mbh:create_template_test_dbcommand'... \n";
exec(__DIR__.'/../bin/console --env=test mbh:create_template_test_dbcommand');
echo "completed!\n";

require __DIR__.'/../app/autoload.php';