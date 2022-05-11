# conjoon/php-lib-conjoon ![MIT](https://img.shields.io/github/license/conjoon/php-lib-conjoon) ![Tests](https://github.com/conjoon/php-lib-conjoon/actions/workflows/run.tests.yml/badge.svg)
PHP library for IMAP operations. 

## Installation

```shell
$ composer i
```

## Troubleshooting

### Composer 2.0 - Pear/Horde vows
If you experience any troubles running `composer`, please revert to **Composer 1.***. You can safely 
go back to **Composer 2** afterwards.

 - remove `requires` and `repositories` from the _composer.json_, then run
```shell
composer self-update --1
```
 - add the previously removed `requires` and `repositories` back to _composer.json_, then run
```shell
composer update
composer self-update --rollback
```