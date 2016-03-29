# [echo-msz.eu](http://denrodinypp.echo-msz.eu)

## My Nette project of webside denrodinypp.echo-msz.eu

This is my [Nette](https://nette.org) project of webside [echo-msz.eu](http://denrodinypp.echo-msz.eu)...

## Môj Nette projekt stránky denrodinypp.echo-msz.eu

Toto je môj [Nette](https://nette.org) projekt web-stránky [echo-msz.eu](http://denrodinypp.echo-msz.eu)...

## Inštalácia

1. pre prácu na localhost-e bez pripojenia na internet do adresára `www/local/css`, `www/local/fonts` a `www/local/js` nakopíruj [bootstrap](http://getbootstrap.com/) a [Font Awesome](http://fontawesome.io)

2. vytvor DB a do nej naimportuj súbor `/sql/db_echo-msz_default.sql`

3. vytvor súbor `/app/config/database.neon` s parametrami pre pripojenie DB:
```
nette:
	database:
		dsn: 'mysql:host=my.host.com;dbname=echomsz'
		user:	test
		password: VeryStrong1978+/*-password
		options:
			lazy: yes
```

4. pre localhost vytvor súbor `/app/config/config.local.neon` s parametrami a pripojenín na lokálnu DB:
```
parameters:
  web_files:
    default:
      css:
        font-awesome: 'local/css/font-awesome.css'
        bootstrap: 'local/css/bootstrap.css'
      js:
        jquery: 'local/js/jquery.js'
        bootstrap: 'local/js/bootstrap.min.js'

nette:
	database:
		dsn: 'mysql:host=localhost;dbname=test'
		user:	user
		password: xxx
		options:
			lazy: yes
```

5. pre localhost v súbore `/app/bootstrap.php` odkomentovať riadok `$configurator->addConfig(__DIR__ . '/config/config.local.neon');`