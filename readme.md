# LumaDoc

[![Build Status](https://github.com/lumadoc/lumadoc/workflows/Build/badge.svg)](https://github.com/lumadoc/lumadoc/actions)
[![Downloads this Month](https://img.shields.io/packagist/dm/lumadoc/lumadoc.svg)](https://packagist.org/packages/lumadoc/lumadoc)
[![Latest Stable Version](https://poser.pugx.org/lumadoc/lumadoc/v/stable)](https://github.com/lumadoc/lumadoc/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/lumadoc/lumadoc/blob/master/license.md)

Latte UI documentation generator.

<a href="https://www.janpecha.cz/donate/"><img src="https://buymecoffee.intm.org/img/donate-banner.v1.svg" alt="Donate" height="100"></a>


## Installation

[Download a latest package](https://github.com/lumadoc/lumadoc/releases) or use [Composer](http://getcomposer.org/):

```
composer require lumadoc/lumadoc
```

LumaDoc requires PHP 5.6.0 or later.


## Usage

### Create & setup your documentation entrypoint (path/to/docs/index.php)

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

// settings
$lumadoc = new Lumadoc\Lumadoc(
	new Lumadoc\Settings(
		docName: 'My Doc Name',
		sections: [
			new Lumadoc\Section('section1', 'Section One'),
			new Lumadoc\Section('section2', 'Section Two'),
			new Lumadoc\Section('blog', 'Blog'),
		],
		directory: __DIR__,
		assetsBaseUrl: '/assets/',
		installationBaseUrl: 'https://cdn.example.com/assets/'
	),
	latte: $latteEngine
);

// controller
$httpController = new Lumadoc\HttpController($lumadoc);
$httpController->run(
	$httpRequest,
	$httpResponse
);
```

This setup requires `nette/http` package:

```
composer require nette/http
```


### Create documentation files




### Start webserver (for local development)

```
php -S localhost:8000 path/to/docs-dir
```

------------------------------

License: [New BSD License](license.md)
<br>Author: Jan Pecha, https://www.janpecha.cz/
