<?php
define('STOP_STATISTICS', true);
define("NOT_CHECK_PERMISSIONS", true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('XHR_REQUEST', true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once (__DIR__.'/crest.php');

$result = CRest::installApp();

if ($result['rest_only'] === false) {?>
    <head>
        <script src="//api.bitrix24.com/api/v1/"></script>
        <?if($result['install'] == true) {?>
            <script>
                BX24.init(function(){
                    BX24.installFinish();
                });
            </script>
        <?}?>
    </head>
    <body>
    <?if($result['install'] == true) {?>
        installation has been finished
    <?} else {?>
        installation error
    <?}?>
    </body>
<?}?>