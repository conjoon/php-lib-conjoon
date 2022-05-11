# conjoon/php-lib-conjoon ![MIT](https://img.shields.io/github/license/conjoon/php-lib-conjoon) ![Tests](https://github.com/conjoon/php-lib-conjoon/actions/workflows/run.tests.yml/badge.svg)
PHP library for IMAP operations. 

## Installation

```shell
$ composer i
```

## Troubleshooting

### Composer 2.0 - Pear/Horde vows
As of **v1.0.1**, _php-lib-conjoon_ no longer requires _Composer 1.*_ for installation.
For _Composer 2.*_-compatibility, _php-lib-conjoon_ relies on the following private composer 
package repository:

```
https://horde-satis.maintaina.com
```

This repository is also mentioned in the _composer.json_-file of 
[horde\/horde_deployment](https://github.com/horde/horde-deployment/blob/master/composer.json).
