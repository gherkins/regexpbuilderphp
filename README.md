```asciidoc
______           _____          ______       _ _     _          ______ _   _______ 
| ___ \         |  ___|         | ___ \     (_| |   | |         | ___ | | | | ___ \
| |_/ /___  __ _| |____  ___ __ | |_/ /_   _ _| | __| | ___ _ __| |_/ | |_| | |_/ /
|    // _ \/ _` |  __\ \/ | '_ \| ___ | | | | | |/ _` |/ _ | '__|  __/|  _  |  __/ 
| |\ |  __| (_| | |___>  <| |_) | |_/ | |_| | | | (_| |  __| |  | |   | | | | |    
\_| \_\___|\__, \____/_/\_| .__/\____/ \__,_|_|_|\__,_|\___|_|  \_|   \_| |_\_|    
            __/ |         | |                                                      
           |___/          |_|                                   
```

# PHP Regular Expression Builder    [![Build Status](https://api.travis-ci.org/gherkins/regexpbuilderphp.svg)](https://travis-ci.org/gherkins/regexpbuilderphp) [![Coverage Status](https://coveralls.io/repos/gherkins/regexpbuilderphp/badge.svg?branch=master)](https://coveralls.io/r/gherkins/regexpbuilderphp?branch=master)



PHP port of https://github.com/thebinarysearchtree/regexpbuilderjs


Installation
----

```text
composer require gherkins/regexpbuilderphp:dev-master
```


Usage
----

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
    

API documentation
---

https://github.com/thebinarysearchtree/regexpbuilderjs/wiki
