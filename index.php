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

$columns = $connection->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'app_birthday_chat_settings'");

$arColumns = [];
while ($column = $columns->fetch()) {
    $arColumns[] = $column['COLUMN_NAME'];
}

if (isset($_REQUEST['save_configs']) && $_REQUEST['save_configs'] == 'y') {
    $arNewSettings = $_REQUEST;
    $arNewSettings['birthday_type'] = isset($arNewSettings['birthday_type']) ? $arNewSettings['birthday_type'] : '';
    $arNewSettings['users'] = isset($arNewSettings['users']) ? $arNewSettings['users'] : '';
    $arNewSettings['users_choose'] = isset($arNewSettings['users_choose']) ? $arNewSettings['users_choose'] : '';

    $arRecord = $connection->query("SELECT * FROM app_birthday_chat_settings WHERE member_id = '".$sqlHelper->forSql($_REQUEST['member_id'], 50)."'")->fetch();

    $sqlFields = [];
    $sqlValues = [];
    $sqlQuery = [];
    foreach ($arNewSettings as $code => &$value) {
        switch ($code) {
            case 'birthday_type':
            case 'users':
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                break;
        }
        if (in_array($code, $arColumns)) {
            $sqlFields[] = $code;
            $sqlValues[] = "'".$value."'";
            $sqlQuery[] = $code." = '".$value."'";
        }
    }

    if (!$arRecord) {
        $sqlFields = implode(',', $sqlFields);
        $sqlValues = implode(',', $sqlValues);
        $result = $connection->query("INSERT INTO app_birthday_chat_settings (".$sqlFields.") VALUES (".$sqlValues.")");
    } else {
        $sqlQuery = implode(',', $sqlQuery);
        $result = $connection->query("UPDATE app_birthday_chat_settings SET ".$sqlQuery." WHERE member_id = '".$arNewSettings['member_id']."'");
    }
}

$arDepartments = [];
if ($_REQUEST['member_id']) {
    $rsDepartments = CRest::call($_REQUEST['member_id'], 'department.get', ['ACTIVE' => true])['result'];
    foreach ($rsDepartments as $arDepartment) {
        $arDepartments[$arDepartment['ID']] = $arDepartment;
    }

    $arUsers = CRest::call($_REQUEST['member_id'], 'user.get', ['ACTIVE' => true])['result'];
    foreach ($arUsers as $arUser) {
        foreach ($arUser['UF_DEPARTMENT'] as $departmentId) {
            $arDepartments[$departmentId]['USERS'][] = $arUser;
        }
    }
}

$arSettings = $connection->query("SELECT * FROM app_birthday_chat_settings WHERE member_id = '".$sqlHelper->forSql($_REQUEST['member_id'], 50)."'")->fetch();
$arSettingBirthdayType = json_decode($arSettings['birthday_type']);
$arSettingUsers = json_decode($arSettings['users']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quick start. Local server-side application</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    <script src="public/js/script.js"></script>
</head>
<body class="main-container">
<section class="left-column">
    <header class="main-header">
        <h4 class="header-title">Поздравлять коллег стало проще!</h4>
        <div class="header-text">
            Это приложение поможет вам быстро создать чат для поздравления нужного сотрудника и включить в него всех,
            кто будет обсуждать подарок, автоматически.
        </div>
    </header>
    <main class="main-body">
        <?if (isset($_REQUEST['save_configs']) && $_REQUEST['save_configs'] == 'y') {?>
            <h5 class="form-title">Готово!</h5>
            <div class="form-text">
                <p>Чат для организации создан.</p>
                <p>Разработано в ИИТ.</p>
                <p>Пишите нам, чтобы автоматизировать в Б24 не только поздравления сотрудников, а все бизнес-процессы.</p>
            </div>
        <?} else {?>
            <form action="index.php" method="post">
                <input type="hidden" name="member_id" value="<?=$_REQUEST['member_id']?>">
                <input type="hidden" name="save_configs" value="y">
                <div class="form__step">
                    <h5 class="form-title">Выберите, кого будем поздравлять:</h5>
                    <div class="form-block form-group">
                        <?foreach ($arDepartments as $arDepartment) {?>
                            <?if ($arDepartment['USERS']) {?>
                                <h6><?=$arDepartment['NAME']?></h6>
                                <?foreach ($arDepartment['USERS'] as $arUser) {?>
                                    <div class="form-check">
                                        <input class="form-check-input" name="users[]" type="checkbox" value="<?=$arUser['ID']?>"
                                               id="user<?=$arUser['ID']?>" <?=in_array($arUser['ID'], $arSettingUsers) ? 'checked' : ''?>>
                                        <label class="form-check-label" for="user<?=$arUser['ID']?>"><?=$arUser['NAME'].' '.$arUser['LAST_NAME'].' '.$arUser['WORK_POSITION'].' '.$arUser['PERSONAL_BIRTHDAY']?></label>
                                    </div>
                                <?}?>
                            <?}?>
                        <?}?>
                    </div>
                </div>
                <div class="form__step" style="display: none">
                    <h5 class="form-title">Выберите, кого добавить в чат: </h5>
                    <div class="form-check js-init-check-users-type">
                        <input class="form-check-input" type="radio" name="users_type" id="users_type1" value="all" <?=$arSettings['users_type'] == 'all' || !$arSettings['users_type'] ? 'checked' : ''?>>
                        <label class="form-check-label" for="users_type1">
                            Все сотрудники компании
                        </label>
                    </div>
                    <div class="form-check js-init-check-users-type">
                        <input class="form-check-input" type="radio" name="users_type" id="users_type2" value="all_department_and_down" <?=$arSettings['users_type'] == 'all_department_and_down' ? 'checked' : ''?>>
                        <label class="form-check-label" for="users_type2">
                            Все сотрудники отдела с подотделами
                        </label>
                    </div>
                    <div class="form-check js-init-check-users-type">
                        <input class="form-check-input" type="radio" name="users_type" id="users_type3" value="all_department" <?=$arSettings['users_type'] == 'all_department' ? 'checked' : ''?>>
                        <label class="form-check-label" for="users_type3">
                            Все сотрудники отдела
                        </label>
                    </div>
                </div>
                <div class="form__step" style="display: none">
                    <div class="form-group">
                        <label for="chatName">Измените название чата (по желанию)</label>
                        <input type="text" class="form-control" id="chatName" name="chat_name" aria-describedby="chatNameHelp"
                               placeholder="День рождения сотрудника #NAME# #LAST_NAME# - #BIRTHDATE#"
                               value="<?=$arSettings['chat_name'] ?: 'День рождения сотрудника #NAME# #LAST_NAME# - #BIRTHDATE#'?>">
                        <small id="chatNameHelp" class="form-text text-muted">Здесь могла быть ваша реклама</small>
                    </div>
                    <div class="form-group">
                        <label for="chatBefore">За сколько дней до дня рождения создавать чат</label>
                        <input type="number" class="form-control" id="chatBefore" name="chat_before_birthday" aria-describedby="chatBeforeHelp"
                               placeholder="7" value="<?=$arSettings['chat_before_birthday'] ?: '7'?>">
                        <small id="chatBeforeHelp" class="form-text text-muted">Подсказка</small>
                    </div>
                </div>
            </form>
        <?}?>
    </main>
    <footer class="main-footer">
        <?if (!isset($_REQUEST['save_configs']) && $_REQUEST['save_configs'] != 'y') {?>
            <div class="footer-top">
                <button type="button" class="btn btn-primary btn-submit form-btn-submit js-init-next-step">Дальше</button>
                <div class="footer-steps">Шаг <span class="footer-steps__number">1</span> из 3</div>
            </div>
        <?}?>
        <div class="footer-bottom">
            <div class="footer-logo">
                <img src="public/img/logo.png">
            </div>
            <div class="footer-text">
                текст подвала
            </div>
            <button type="button" class="btn btn-primary btn-submit">Написать разработчикам</button>
        </div>
    </footer>
</section>
<section class="right-column">
    <h5 class="right-column__title">Как это работает?</h5>
    <ol class="right-column__list">
        <li class="right-column__item right-column__item_fat">Выберите сотрудника, которого собираетесь поздравить</li>
        <li class="right-column__item">Выберите, кого нужно добавить в чат (именинник в него не попадет)</li>
        <li class="right-column__item">Приложение создаст чат для поздравления выбранного сотрудника</li>
    </ol>
</section>
</body>
</html>