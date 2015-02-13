# regexpbuilderphp


[![Build Status](https://api.travis-ci.org/gherkins/regexpbuilderphp.svg)](https://travis-ci.org/gherkins/regexpbuilderphp)
[![Coverage Status](https://coveralls.io/repos/gherkins/regexpbuilderphp/badge.svg?branch=master)](https://coveralls.io/r/gherkins/regexpbuilderphp?branch=master)

PHP port of https://github.com/thebinarysearchtree/regexpbuilderjs


Installation
----

```text
composer require gherkins/regexpbuilderphp
```


Usage
----

```php
//create a builder instance
$builder = new \Gherkins\RegExpBuilderPHP\RegExpBuilder();


//build first expression
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
    

//create another builder instance and build second expression            
$builder2 = $builder
    ->getNew()                                // <-  getNew() returns a new build instance !
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


//create new builder instance and build a third expression by combining both
$combined = $builder
    ->getNew()                                // <-  getNew() returns a new build instance !
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
