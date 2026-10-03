<?php

return [
    // dsn syntax
    // see https://www.php.net/manual/de/pdo.construct.php
    'dsn' => 'sqlite:'.__DIR__.'/../protected/data/my-example-app.sqlite3',

    // more params for mysql:
    'user' =>'',
    'password'=>'',
    'options' => []
];