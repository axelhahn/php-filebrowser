## Indexer

You can index your directory 

* on command line (or cronjob)
* in the browser (not recommended in production)

The main file for te file browser can be accessed via web interface and can be started on the command line as well.

```bash
php f.php --help


    ⣎⣱ ⡀⢀ ⢀⡀ ⡇ ⢀⣀   ⣏⡉ ⠄ ⡇ ⢀⡀ ⣇⡀ ⡀⣀ ⢀⡀ ⡀ ⢀ ⢀⣀ ⢀⡀ ⡀⣀
    ⠇⠸ ⠜⠣ ⠣⠭ ⠣ ⠭⠕   ⠇  ⠇ ⠣ ⠣⠭ ⠧⠜ ⠏  ⠣⠜ ⠱⠱⠃ ⠭⠕ ⠣⠭ ⠏ 

                                       Version: 0.6

👤 Author: Axel Hahn
🧾 Source: <https://github.com/axelhahn/php-filebrowser/>
📜 License: GNU GPL 3.0
📗 Docs: see <https://www.axel-hahn.de/docs/php-filebrowser/>


USAGE: index.php [options] [parameters]

OPTIONS:
  -h, --help     Show this help message
  -i, --index    Update Index with changed files
  -r, --reindex  Reindex: delete Index and rebuild index from scratch

PARAMETERS:
  none

```

### Create index

To initialize the index or use the parameter `-r` or `--reindex`.

### Update index

To refresh the index yyou can use these options

* `-i` or `--index` 
Soft update of the index

* `-r` or `--reindex` 
Reindex: delete Index and rebuild index from scratch

#### Soft update

With `-i` or `--index` you can update the index without deleting the current index. During the index process the web ui is available.

The index is updated in 2 steps

* remove all non existing files or entries that that match the exclude list
* update all missing or changed files

#### Rebuild index

With `-r` or `--reindex` you can recreate thein dex from scratch. During the index process the web ui is partly available until the index is complete.

The index is updated in 2 steps

* delete all entries of the index
* add all files

#### Why 2 indexing methods?

The **soft index** update is recommended.
It needs to read file entry by file entry in the database and compare it with the file tamestamp and size. On some thousand files this can take a long time.

The **reindex** is recommended for large directories or slow actions on the filesystem (mounts of network storage, RAID) ... short: where the soft index is too slow. 

Reindex can work faster because it deletes all file entries in the index. When adding all files it doesn't need to read the database for each file (because it doesn't exist). 

You need to try it out on your system, eg.

```bash
time php f.php --index
time php f.php --reindex
```
