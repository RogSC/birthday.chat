<?php
define('STOP_STATISTICS', true);
define("NOT_CHECK_PERMISSIONS", true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('XHR_REQUEST', true);

$_SERVER['DOCUMENT_ROOT'] = '/home/bitrix/www';

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__.'/crest.php');

$connection = Bitrix\Main\Application::getConnection();
$sqlHelper = $connection->getSqlHelper();

$columns = $connection->query("SELECT * FROM app_birthday_auth_token_user");

$arColumns = [];
while ($column = $columns->fetch()) {
    $result = CRest::GetNewAuth($column['member_id'], []);
}