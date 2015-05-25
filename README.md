php7ize
=====
php7ize is a command-line utility for quickly adding PHP7 features to a PHP5 codebase.

Installation
-----
The only officially-supported method of installation is via Composer and Packagist.
Because this is not intended to be treated as a project dependency, it's recommended to be installed globally:

    composer global require firehed/php7ize

If you have not already done so, add Composer's global `bin` directory to your `PATH`:

    # ~/.bash_profile
    export PATH=$PATH:~/.composer/vendor/bin

Usage
-----
(more details coming soon)


Updating
-----
Use Composer's global update mechanism:

    composer global update

Contributing
-----
Please see more information in CONTRIBUTING.md

Future features
-----
All features are subject to change.
Feel free to add a suggestion by adding a Github issue.

* Drastically improve the internal implementation (use/access an AST?)
* Improve/test type hint for variadic functions
* Add test coverage for interfaces, traits, and abstract classes
* Flag to add `declare(strict_types=1)`
* Auto-rename PHP4-style constructors?
* Replace deprecated PHP tags (<% <%= %>)
* Null coalesce: Replace `isset($foo) ? $foo : DEFAULT` with `$foo ?? DEFAULT`
* `mysql_` deprecation: replace with `mysqli_` equivalent
