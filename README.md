# regexpbuilderphp


[![Build Status](https://api.travis-ci.org/gherkins/regexpbuilderphp.svg)](https://travis-ci.org/gherkins/regexpbuilderphp)
[![Coverage Status](https://coveralls.io/repos/gherkins/regexpbuilderphp/badge.svg?branch=master)](https://coveralls.io/r/gherkins/regexpbuilderphp?branch=master)

PHP port of https://github.com/thebinarysearchtree/regexpbuilderjs


Installation
----

`composer require gherkins/regexpbuilderphp`


Usage
----

    $builder = new \Gherkins\RegExpBuilderPHP\RegExpBuilder();

    $regEx = $builder
        ->startOfLine()
        ->exactly(1)
        ->of("p")
        ->getRegExp();

    $regEx->test("pq");  // true
    $regEx->test("qp"); // false
        
Take a look at the [tests](tests/RegExpBuilderTest.php) for more examples
    

API documentation
---

https://github.com/thebinarysearchtree/regexpbuilderjs/wiki
