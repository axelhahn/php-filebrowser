## Configuration

For a first start you can use the config file `config.php.dist`

```txt
config.php.dist
```

Copy the dist file and change the configuration.

```php
<?php

return [


    // --- START single dir to index

    // dir without trailing "/"
    'dir'=>__DIR__.'/myfiles',
    // relative dir or alias without trailing "/"
    'reldir'=>'/myfiles',
    'label'=>'My files',

    'reindex_after'=>300, // 5 min
    'exclude'=>[
        'regex'=>[
            '\.mmdb$',
        ]
    ],
    'show_info'=>1,

    // --- END single dir to index

    // for local testing
    'show_index'=>1,
    'show_reindex'=>0, // set to 1 only in local environment, not in production

    "pdo" => [
        "db" => [
            'dsn' => 'sqlite:'.__DIR__.'/data/filesearch.sqlite3',
        ],
        // 'showdebug'=>true,
        // 'showerrors'=>true,
    ],
];
```

Define a directory:

| Key              | Type    |  Description
|---               |---      |---
| `dir`            | string  | Directory to index
| `reldir`         | string  | Relative directory to index
| `label`          | string  | label for the directory (used in breadcrumb)
| `reindex_after`  | integer | Enable Reindex after x seconds in the web ui (if show_reindex is enabled)
| `exclude`        | array   | Exclude files by regex
| `show_info`      | bool    | Show info about the index in the web ui


Other settings:

| Key              | Type    |  Description
|---               |---      |---
| `show_index`     | bool    | Show index status in the web ui (not needed in production)
| `show_reindex`   | bool    | Show reindex button in the web ui (dangerous in production)
| `pdo`            | array   | Database connection settings
