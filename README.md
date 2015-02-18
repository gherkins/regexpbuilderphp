```asciidoc
 ____            _____            ____        _ _     _           ____  _   _ ____  
|  _ \ ___  __ _| ____|_  ___ __ | __ ) _   _(_) | __| | ___ _ __|  _ \| | | |  _ \ 
| |_) / _ \/ _` |  _| \ \/ / '_ \|  _ \| | | | | |/ _` |/ _ \ '__| |_) | |_| | |_) |
|  _ <  __/ (_| | |___ >  <| |_) | |_) | |_| | | | (_| |  __/ |  |  __/|  _  |  __/ 
|_| \_\___|\__, |_____/_/\_\ .__/|____/ \__,_|_|_|\__,_|\___|_|  |_|   |_| |_|_|    
           |___/           |_|                                                      
```
## human-readable regular expressions for PHP 5.3+
[![Travis](https://img.shields.io/travis/gherkins/regexpbuilderphp.svg?style=flat-square)](https://travis-ci.org/gherkins/regexpbuilderphp)
[![Coveralls](https://img.shields.io/coveralls/gherkins/regexpbuilderphp.svg?style=flat-square)](https://coveralls.io/r/gherkins/regexpbuilderphp?branch=master)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/555ad19b-0c18-4434-ad43-5b19779e2e9c.svg?style=flat-square)](https://insight.sensiolabs.com/projects/555ad19b-0c18-4434-ad43-5b19779e2e9c)

PHP port of https://github.com/thebinarysearchtree/regexpbuilderjs

> RegExpBuilder integrates regular expressions into the programming language, thereby making them easy to read and maintain. Regular Expressions are created by using chained methods and variables such as arrays or strings.

## Installation

```bash
composer require gherkins/regexpbuilderphp:dev-master
```
... or download https://github.com/gherkins/regexpbuilderphp/archive/master.zip
and require both RegExpBuilder.php and RegExp.php manually from the src Folder.


## Documentation

https://github.com/gherkins/regexpbuilderphp/wiki


## Usage example

```php
$builder = new \Gherkins\RegExpBuilderPHP\RegExpBuilder();


$builder1 = $builder
    ->find("€")
    ->exactly(1)->whitespace()
    ->min(1)->digits()
    ->then(",")
    ->digit()
    ->digit();
    
$builder1->getRegExp()->test("€ 128,99");     //true
$builder1->getRegExp()->test("€ 81,99");      //true

$builder1->getRegExp()->test("€ 1.228,99");   //false
    
   
                 
$builder2 = $builder
    ->getNew() // <- create a new builder instance !
    ->find("€")
    ->exactly(1)->whitespace()
    ->min(1)->digits()
    ->then(".")
    ->exactly(3)->digits()
    ->then(",")
    ->digit()
    ->digit();
    
$builder2->getRegExp()->test("€ 1.228,99");   //true
$builder2->getRegExp()->test("€ 452.000,99"); //true
    
$builder2->getRegExp()->test("€ 81,99");      //false

    
   
$combined = $builder
    ->getNew() // <- create a new builder instance !
    ->eitherIs($builder1)
    ->orIs($builder2);
    
$combined->getRegExp()->test("€ 128,99");     //true
$combined->getRegExp()->test("€ 81,99");      //true

$combined->getRegExp()->test("€ 1.228,99");   //true
$combined->getRegExp()->test("€ 452.000,99"); //true
```
        
Take a look at the [tests](tests/RegExpBuilderTest.php) for more examples
