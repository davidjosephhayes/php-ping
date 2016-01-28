# php Ping 

## Introduction

This is a simple script that will ping ip's or hostnames at regular intervals and fire off emails if there is a failure.

## Installation

[Install composer](https://getcomposer.org/doc/00-intro.md) and run `composer install`.

## Setup

Copy config.sample.php to config.php. Change variables as necessary.

## Run

Install a cron tab. For example, I want to run it twice an hour. The php comman can be replaces by the output of which php (something like `/usr/bin/php`).

```
0,30 * * * * php patch/to/phpPing.php
```
