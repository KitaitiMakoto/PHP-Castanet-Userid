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
it uses `setrawcookie()` function internally and `setrawcookie()` occurs error
if you have output any string already.

### Apache log
Add custome log format to `httpd.conf` like this:

    LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" \"%{uid_got}n\" \"%{uid_set}n\"" combined_cookie

And set `combined_cookie` as current log format, then restart Apache.

`uid_got` and `uid_set` variables are set by this library.

Settings
--------

### Enabling
`Castanet_Userid` does nothing even `start()` is called unless
you call `enable()`(or `setEnabled(true)`).

    $uid = new Castanet_Userid;
    $uid->start(); // do nothing
    $uid->enable()->start(); // do something

### Name(key of cookie)
`name` is used as key of cookie. By default, `uid` is used:

    uid=fwAAAVEbtF1USQfEAwMEAg==

You can modify it by `setConfig()` method:

    $uid->setConfig('name', 'castanet');

Then key of cookie is modified:

    castanet=fwAAAVEbtF1USQfEAwMEAg==

Be careful not to use the same key already used for other purpose.

### Service
`service` is an arbitrary integer and defaults to IP address of server PHP is processed(in practice, calculated by `ip2long()`).
It appears as first eight characters on log. Let it, for instance, `127.0.0.1`, and logs noted by this library(as `uid_got` and `uid_set`) start with characters `0100007F`.

Characters themselves mean nothing but they play a role of identity. By seeing it, you may know the first server given user accessed. So you might have set the same value for multiple server load-balanced by one reverse proxy.

You can set `service` like this:

    $uid->setConfig('service', ip2long('127.0.0.1'));

### Cookie attributes
Cookie attributes(`domain`, `path` and `expires`) are able to be set by `setConfig()` method:

    $uid->setConfig('domain', 'www.example.net')
        ->setConfig('path', '/sandbox');

### Setting at a time
Using `setConfigs()` method, you can set properties above at a time:

    $uid->setConfigs(array(
        'name'    => 'castanet',
        'service' => ip2long('127.0.0.1'),
        'domain'  => 'www.example.net',
        'path'    => '/sandbox'
    ));

### Merged uid
Now Castanet Userid notes a note `uid` in addition to `uid_set` and `uid_got`.
It is the value of either exists `uid_set` or `uid_set` and it doesn't have key name(`uid=`).

PHP 4
-----
For PHP 4, Use Castanet_Userid4.
