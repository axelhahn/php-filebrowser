## Introduction

The Filebrowser is a project for a web ui for a file browser including a search.
It uses a PDO sqlite database for storing file info.

A merger creates a single php file `dist/f.php` that you can put into your web directory.

### Features

* Sqlite database to show directory index and search results
* Javascript to sort and filter html tables
* Show readme.txt or readme.md in the directory

### Thank you

This product uses these components:

* https://github.com/axelhahn/php-abstract-dbo - PHP class to abtract access to a database
* https://github.com/jstable/JSTable - Javascript to sort and filter html tables
