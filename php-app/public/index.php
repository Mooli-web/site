<?php
/**
 * نقطه‌ی ورود (front controller). همه‌ی درخواست‌ها از طریق .htaccess به این فایل می‌رسند.
 */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

$app = new App();
$app->run(new Request())->send();
