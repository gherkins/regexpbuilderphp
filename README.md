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
Or download [the latest release](https://github.com/gherkins/regexpbuilderphp/releases/latest) and require `RegExpBuilder.php` and `RegExp.php` from `/src`.


## Documentation

https://github.com/gherkins/regexpbuilderphp/wiki


## Examples

```php
use Gherkins\RegExpBuilderPHP;
$builder = new RegExpBuilder();
```

### Validation

```php
$regExp = $builder
    ->startOfInput()
    ->exactly(4)->digits()
    ->then("_")
    ->exactly(2)->digits()
    ->then("_")
    ->min(3)->max(10)->letters()
    ->then(".")
    ->anyOf(array("png", "jpg", "gif"))
    ->endOfInput()
    ->getRegExp();

//true
$regExp->matches("2020_10_hund.jpg");
$regExp->matches("2030_11_katze.png");
$regExp->matches("4000_99_maus.gif");

//false
$regExp->matches("123_00_nein.gif");
$regExp->matches("4000_0_nein.pdf");
$regExp->matches("201505_nein.jpg");
```

### Search

```php
$regExp = $builder
    ->multiLine()
    ->globalMatch()
    ->min(1)->max(10)->anythingBut(" ")
    ->anyOf(array(".pdf", ".doc"))
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

### Replace

```php
$regExp = $builder
    ->min(1)
    ->max(10)
    ->digits()
    ->getRegExp();

$text = "98 bottles of beer on the wall";

$text = $regExp->replace(
    $text,
    function ($match) {
        return (int)$match + 1;
    }
);

//true
("99 bottles of beer on the wall" === $text);
```

### Validation with multiple patterns

```php
$a = $builder
    ->startOfInput()
    ->exactly(3)->digits()
    ->anyOf(array(".pdf", ".doc"))
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
$regExp->matches("123.pdf");
$regExp->matches("456.doc");
$regExp->matches("bbbb.jpg");
$regExp->matches("aaaa.jpg");

//false
$regExp->matches("1234.pdf");
$regExp->matches("123.gif");
$regExp->matches("aaaaa.jpg");
$regExp->matches("456.docx");
```
        
Take a look at the [tests](tests/RegExpBuilderTest.php) for more examples
