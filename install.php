<?php
require_once (__DIR__.'/crest.php');

$result = CRest::installApp();
var_dump($result);
var_dump($_REQUEST);
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