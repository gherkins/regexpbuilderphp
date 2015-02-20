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
[![release](https://img.shields.io/github/release/gherkins/regexpbuilderphp.svg?style=flat-square)](https://github.com/gherkins/regexpbuilderphp/releases)

PHP port of https://github.com/thebinarysearchtree/regexpbuilderjs

> RegExpBuilder integrates regular expressions into the programming language, thereby making them easy to read and maintain. Regular Expressions are created by using chained methods and variables such as arrays or strings.

## Installation

```bash
composer require gherkins/regexpbuilderphp
```
... or download [the latest release](https://github.com/gherkins/regexpbuilderphp/releases/latest) and require both `RegExpBuilder.php` and `RegExp.php` from the `src` folder.


## Documentation

https://github.com/gherkins/regexpbuilderphp/wiki


## Examples

### Validating filenames

```php
$builder = new \Gherkins\RegExpBuilderPHP\RegExpBuilder();

$regExp = $builder
    ->startOfInput()
    ->exactly(4)->digits()
    ->then("_")
    ->exactly(2)->digits()
    ->then("_")
    ->min(3)->max(10)->letters()
    ->then(".")
    ->eitherFind("png")->orFind("jpg")->orFind("gif")
    ->endOfInput()
    ->getRegExp();

//true
$regExp->test("2020_10_hund.jpg");
$regExp->test("2030_11_katze.png");
$regExp->test("4000_99_maus.gif");

//false
$regExp->test("123_00_nein.gif");
$regExp->test("4000_0_nein.pdf");
$regExp->test("201505_nein.jpg");
```

### Finding filenames in text

```php
$builder = new \Gherkins\RegExpBuilderPHP\RegExpBuilder();

$regExp = $builder
    ->multiLine()
    ->globalMatch()
    ->min(1)->max(10)->anythingBut(" ")
    ->eitherFind(".pdf")->orFind(".doc")
    ->getRegExp();

$text = <<<EOF
Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
sed diam nonumy SomeFile.pdf eirmod tempor invidunt ut labore et dolore
magna aliquyam erat, sed diam voluptua. At vero eos et accusam
et justo duo dolores et ea rebum. doc_04.pdf Stet clita kasd File.doc.
EOF;

$matches = $regExp->exec($text);

//true
($matches[0] === "SomeFile.pdf");
($matches[1] === "doc_04.pdf");
($matches[2] === "File.doc");
```

### Combining multiple patterns

```php
$builder = new \Gherkins\RegExpBuilderPHP\RegExpBuilder();

$a = $builder
    ->startOfInput()
    ->exactly(3)->digits()
    ->eitherFind(".pdf")->orFind(".doc")
    ->endOfInput();

$b = $builder
    ->getNew()
    ->startOfInput()
    ->exactly(4)->letters()
    ->then(".jpg")
    ->endOfInput();

$regExp = $builder
    ->getNew()
    ->eitherFind($a)
    ->orFind($b)
    ->getRegExp();

//true
$regExp->test("123.pdf");
$regExp->test("456.doc");
$regExp->test("bbbb.jpg");
$regExp->test("aaaa.jpg");

//false
$regExp->test("1234.pdf");
$regExp->test("123.gif");
$regExp->test("aaaaa.jpg");
$regExp->test("456.docx");
```
        
Take a look at the [tests](tests/RegExpBuilderTest.php) for more examples
