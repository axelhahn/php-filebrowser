# PHP file browser

Web ui for file browser with a file search.
It uses an PDO database for storing file info.

## 🪄 Installation

* You can use a single php file and put it into your web directory.
* Then you need a configuration file that defines the database and the directory to browse.
* index the directory
* start browsing your indexed file strcture

## ⚙️ Configuration

```txt
config.php.dist
```

Copy the dist file and change the configuration.

## ⭐ Customization

Here are xome dist files to customize the look and feel:

```txt
config_icons.php.dist
config_lang.php
config_types.php.dist
style.css.dist
```

### 🔷 Icons

Copy the file `config_icons.php.dist` and change the emojies:

```php
<?php
return [
    'abort' => '❌',
    'back'  => '⬅️',
    'dir'   => '📁',
    'dirup' => '↖️',
    'file'  => '🔸',
    'home'  => '🏡',

    'reindex' => '🔁',
    'search'  => '🔎',
    'unknown' => '❓',
    'warning' => '⚠️',
];

```
### 💬 Language

Copy the file `config_lang.php.dist` and change the language.

```php
<?php
return [
    'title' => 'File Browser',
    'search' => 'Search',
    'search_placeholder' => 'Type',

    'back' => 'Back',

    'indexing_start' => 'Indexing files ...',
    'index_status' => 'Index status',
        'dirs' => 'Dirs',
        'files' => 'Files',
        'timestamp' => 'Timestamp',
        'age' => 'Age',
        'reindex' => 'Reindex',

    // file table
    'type' => 'Type',
    'name' => 'Name',
    'size' => 'Size',
    'modified' => 'Modified',

    // search results
    'hits' => 'Hits',

    'current_dir' => 'Current directory',
];
```

### 🏷️  Types

Copy the file `config_types.php.dist` and change the file types.

Each file type has its own subkey with

* an icon and 
* a list of file extensions.

```php  
<?php
return [
    'audio' => [
        'icon' => '🎵',
        'ext' => [            
            'm4a'  ,
            'mp3'  ,
            'ogg'  ,
            'wav'  ,
        ],
    ],
    'image' => [
        'icon' => '📷',
        // 'icon' => '🏙️',
        'ext' => [
            'bmp' ,
            'gif' ,
            'ico' ,
            'jpg' ,
            'jpeg',
            'png' ,
            'svg' ,
            'webp',
        ],
    ],
    'text' => [
        'icon' => '📝',
        'ext' => [
            'asp'  ,
            'css'  ,
            'js'   ,
            'json' ,
            'html' ,
            'log'  ,
            'md'   ,
            'php'  ,
            'py'  ,
            'rb'  ,
            'ts'   ,
            'txt'  ,
            'xml'  ,
        ],
    ],
    'video' => [
        'icon' => '🎬',
        'ext' => [
            'mp4'  ,
            'webm' ,
            'ogg'  ,
        ],
    ],
];
```

### 🎨 Styling

Copy the file `style.css.dist` to `style.css` and override the internal default css.

## ⌨️ Development

TODO