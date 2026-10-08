## First steps

### Get the files

Select one of the following methods.

#### Zip file

You donwload the zip file of the project and unzip it.
Then copy the files from here

```bash
curl --follow -o php-filebrowser.zip https://github.com/axelhahn/php-filebrowser/archive/refs/heads/main.zip
unzip php-filebrowser.zip
rm php-filebrowser.zip
cd php-filebrowser-main/
```

#### Git clone

```bash
git clone https://github.com/axelhahn/php-filebrowser.git
cd php-filebrowser
```

#### Single file

To get the merged php file you can copy merged file into your web directory.

```bash
curl -o f.php https://raw.githubusercontent.com/axelhahn/php-filebrowser/refs/heads/main/dist/f.php 
```

### Configure directory to index

Create a file `config.php` where the f.php is.

Set a full path and a relative url to the directory you want to index.

Snippet:

```php
    'dir'=>__DIR__.'/myfiles',
    'reldir'=>'myfiles',
```

For the full description of  the configuration see the next page.

### Index the directory

Run the script with `-r` or `--reindex`:

```bash
php f.php --reindex
```

### Open f.php in your browser

Open `f.php` in your browser eg `http://localhost/f.php`.
You see a directory structure of your files.
