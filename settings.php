<?php
define('C_REST_CLIENT_ID','app.5f353f823da596.57357269');//Application ID
define('C_REST_CLIENT_SECRET','hEwXVqyP1s0ogT3hfTizIdPl7r7hJQkjdrVXoMwj72iCe1vYvC');//Application key
// or
//define('C_REST_WEB_HOOK_URL','https://rest-api.bitrix24.com/rest/1/doutwqkjxgc3mgc1/');//url on creat Webhook

define('C_REST_CURRENT_ENCODING','UTF-8');
//define('C_REST_IGNORE_SSL',true);//turn off validate ssl by curl
define('C_REST_LOG_TYPE_DUMP',true); //logs save var_export for viewing convenience
define('C_REST_BLOCK_LOG',true);//turn off default logs
define('C_REST_LOGS_DIR', __DIR__ .'/logs/'); //directory path to save the log

define('PATH', '/run/apps/birthday.chat/index.php');
define('REDIRECT_URI', 'http://software.iit.company' . PATH);