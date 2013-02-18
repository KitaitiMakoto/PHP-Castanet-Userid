Castanet_Userid
===============

This library emulates Apahce's [mod_uid][moduid] or
Nginx's [User ID][httpuserid] module.
For the idea of User ID, visit these links.

[moduid]: http://www.lexa.ru/programs/mod-uid-eng.html
[httpuserid]: http://wiki.nginx.org/HttpUseridModule

Target Users
------------
People who

* use Apache HTTP server, 
* use PHP by Apache module and
* don't use mod_uid Apache module

Are you using Apache 1.x.x? or 2.0.x? Visit [mod_uid page][moduid].

Are you using Nginx? Visit [HttpUseridModule page][httpuserid].

Installation
------------
Add below to your composer.json:

    "require": {
        "castanet/userid": "*"
    }

And then execute

    composer install

Usage
-----

### PHP

    require 'vendor/autoload.php';
    $uid = new \Castanet_Userid;
    $uid->enable()
        ->start();

You need to call `\Castanet_Userid::start()` as early as possible because
it uses `setcookie()` function internally and `setcookie()` occurs error
if you have output any string already.

### Apache log
Add custome log format to `httpd.conf` like this:

    LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" \"%{uid_got}n\" \"%{uid_set}n\"" combined_cookie

And set `combined_cookie` as current log format, then restart Apache.

`uid_got` and `uid_set` variables are set by this library.
