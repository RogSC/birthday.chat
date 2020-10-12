<?php
require_once (__DIR__.'/crest.php');
require_once (__DIR__.'/libs/database/db.php');

$db = new YNDb(__DIR__.'/libs/database/data/');
define('TABLE', 'chat_settings');

$arSettings = $_REQUEST;

$arRecord = $db->select(TABLE, ['col' => 'id, member_id', 'cond' => 'member_id = '.$arSettings['member_id'], 'limit' => 1]);

if (!isset($arSettings['birthday_type'])) $arSettings['birthday_type'] = '';

if (!$arRecord) {
    $result = $db->insert(TABLE, [
        'member_id' => $arSettings['member_id'],
        'birthday_type' => $arSettings['birthday_type'],
        'users_type' => $arSettings['users_type'],
        'users' => json_encode($arSettings['users'], JSON_UNESCAPED_UNICODE),
    ]);
} else {
    $result = $db->update(TABLE, ['cond' => 'member_id = '.$arSettings['member_id']], [
        'member_id' => $arSettings['member_id'],
        'birthday_type' => $arSettings['birthday_type'],
        'users_type' => $arSettings['users_type'],
        'users' => json_encode($arSettings['users'], JSON_UNESCAPED_UNICODE),
    ]);
}

