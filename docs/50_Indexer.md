## Indexer

You can index your directory 

* on command line (or cronjob)
* in the browser (not recommended in production)

The main file for te file browser can be accessed via web interface and can be started on the command line as well.

```bash
php index.php --help

    
    ⣎⣱ ⡀⢀ ⢀⡀ ⡇ ⢀⣀   ⣏⡉ ⠄ ⡇ ⢀⡀ ⣇⡀ ⡀⣀ ⢀⡀ ⡀ ⢀ ⢀⣀ ⢀⡀ ⡀⣀
    ⠇⠸ ⠜⠣ ⠣⠭ ⠣ ⠭⠕   ⠇  ⠇ ⠣ ⠣⠭ ⠧⠜ ⠏  ⠣⠜ ⠱⠱⠃ ⠭⠕ ⠣⠭ ⠏ 

                                          Version: 0.3


USAGE: index.php [options] [parameters]

OPTIONS:
  -h, --help   Show this help message
  -i, --index  Index files

PARAMETERS:
  none

```

To reindex use the parameter `-i` or `--index`.

In the first step all existing files are indexed and updated if needed.
In the 2nd step the non existing files of the ndex will be deleted.

This allows to usage of the file browser web ui during the indexing.

```bash
php index.php -i

    
    ⣎⣱ ⡀⢀ ⢀⡀ ⡇ ⢀⣀   ⣏⡉ ⠄ ⡇ ⢀⡀ ⣇⡀ ⡀⣀ ⢀⡀ ⡀ ⢀ ⢀⣀ ⢀⡀ ⡀⣀
    ⠇⠸ ⠜⠣ ⠣⠭ ⠣ ⠭⠕   ⠇  ⠇ ⠣ ⠣⠭ ⠧⠜ ⠏  ⠣⠜ ⠱⠱⠃ ⠭⠕ ⠣⠭ ⠏ 

                                          Version: 0.2

Indexing files ...

>>> Step 1 of 2 - Refreshing dir = /home/axel/sources/php-classes/php-filebrowser/myfiles (myfiles)
No changes for dir 'myfiles/A'...
No changes for file 'myfiles/A/bla.txt'...
No changes for dir 'myfiles/B'...
No changes for file 'myfiles/B/blubb.txt'...
No changes for file 'myfiles/Screenshot_download_vulnerability_scan_01(1).png'...
No changes for file 'myfiles/Screenshot_download_vulnerability_scan_02.png'...
No changes for file 'myfiles/Screenshot_download_vulnerability_scan_01.png'...
No changes for file 'myfiles/readme.md'...
Dirs: 2
Files: 6
Used time: 0.001s for indexing
Speed: 9,646 file objects per second

>>> Step 2 of 2 - Cleanup deleted files...
Used time: 0.001s for cleanup
Done.

```
