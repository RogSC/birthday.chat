<?php
define('STOP_STATISTICS', true);
define("NOT_CHECK_PERMISSIONS", true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('XHR_REQUEST', true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__.'/crest.php');

$connection = Bitrix\Main\Application::getConnection();
$sqlHelper = $connection->getSqlHelper();

$rsRecords = $connection->query("SELECT * FROM app_birthday_chat_settings");

while ($record = $rsRecords->fetch()) {
    CRest::dump($record);

    $birthdayType = json_decode($record['birthday_type']);
    $usersType = $record['users_type'];

    if (in_array('chat', $birthdayType)) {
        $currentDate = new DateTime(date('d-m-Y', strtotime('2020-11-13')));
        $currentDate = $currentDate->modify('+7 days');
        $currentDate = strtotime($currentDate->format('Y-m-d'));

        $birthdayUsers = [];
        $arUsers = CRest::call($record['member_id'], 'user.get', ['ACTIVE' => true])['result'];
        //CRest::dump($arUsers);

        $rsDepartments = CRest::call($record['member_id'], 'department.get', ['ACTIVE' => true])['result'];
        $arDepartments = [];
        foreach ($rsDepartments as $arDepartment) {
            $arDepartments[$arDepartment['ID']] = $arDepartment;
            $arDepartments[$arDepartment['ID']]['CHILDREN'] = getChildDepartments($arDepartment['ID'], $rsDepartments);
        }
        //CRest::dump($arDepartments);

        foreach ($arUsers as $key => &$arUser) {
            if ($record['users_choose'] == 'y' && !in_array($arUser['ID'], json_decode($record['users'], 1))) {
                unset($arUsers[$key]);
                continue;
            }
            if ($arUser['PERSONAL_BIRTHDAY']) {
                $birthDay = new DateTime(date("d-m", strtotime($arUser['PERSONAL_BIRTHDAY'])).'-'.date('Y'));
                $birthDay = strtotime($birthDay->format('Y-m-d'));
                if ($birthDay == $currentDate) {
                    $birthdayUsers[] = $arUser;
                }
            }
        }

        CRest::dump($birthdayUsers);

        foreach ($birthdayUsers as $birthdayUser) {
            $birthDate = date('d-m', strtotime($birthdayUser['PERSONAL_BIRTHDAY']));
            CRest::dump($birthDate);

            $usersInChat = [];
            switch ($usersType) {
                case 'all':
                    CRest::dump($arUsers);

                    foreach ($arUsers as $arUser) {
                        if ($birthdayUser['ID'] != $arUser['ID']) {
                            $usersInChat[] = $arUser['ID'];
                        }
                    }
                    break;
                case 'all_department_and_down':
                case 'all_department':
                    $userDepartments = [];
                    foreach ($birthdayUser['UF_DEPARTMENT'] as $arDepartment) {
                        $userDepartments[] = $arDepartment['ID'];
                        if ($usersType == 'all_department_and_down') {
                            foreach ($arDepartments[$arDepartment['ID']]['CHILDREN'] as $department) {
                                $userDepartments[] = $department['ID'];
                            }
                        }
                    }
                    foreach ($arUsers as $arUser) {
                        if ($birthdayUser['ID'] != $arUser['ID']) {
                            foreach ($arUser['UF_DEPARTMENT'] as $department) {
                                if (in_array($department, $userDepartments)) {
                                    $usersInChat[] = $arUser['ID'];
                                }
                            }
                        }
                    }
                    break;
            }
            CRest::dump($usersInChat);

            $title = str_replace('#NAME#', $birthdayUser['NAME'], $record['chat_name']);
            $title = str_replace('#LAST_NAME#', $birthdayUser['LAST_NAME'], $title);
            $title = str_replace('#BIRTHDATE#', $birthdayUser['PERSONAL_BIRTHDAY'], $title);

            $arParams = [
                'TYPE' => 'CHAT',
                'TITLE' => $title,
                //'DESCRIPTION' => 'Очень важный чат',
                'COLOR' => 'RED',
                'MESSAGE' => $title,
                'USERS' => Array(148,288,454),
                //'AVATAR' => 'base64 image',
                'ENTITY_TYPE' => 'BIRTHDAY_CHAT',
                'ENTITY_ID' => $birthdayUser['ID'],
                'OWNER_ID' => 288,
            ];

            $result = CRest::call($record['member_id'], 'im.chat.add', $arParams);
            CRest::dump($result);
        }
    }
}

function getChildDepartments($departmentId, $arDepartments) {
    foreach ($arDepartments as $department) {
        if ($department['PARENT'] == $departmentId) {
            $resultDepartments[] = $department;
        }
    }
    foreach ($resultDepartments as $department) {
        $resultDepartments = array_merge($resultDepartments, getChildDepartments($department['ID'], $arDepartments) ?: []);
    }

    return $resultDepartments;
}