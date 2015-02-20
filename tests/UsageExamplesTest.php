<?php

namespace Gherkins\RegExpBuilderPHP\Test;


class UsageExamplesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Gherkins\RegExpBuilderPHP\RegExpBuilder
     */
    public $r;

    public function setUp()
    {
        $this->r = new \Gherkins\RegExpBuilderPHP\RegExpBuilder();
    }

    public function testUsageExample()
    {
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

        $this->assertTrue($regExp->test("2020_10_hund.jpg"));
        $this->assertTrue($regExp->test("2030_11_katze.png"));
        $this->assertTrue($regExp->test("4000_99_maus.gif"));

        $this->assertFalse($regExp->test("4000_99_f.gif"));
        $this->assertFalse($regExp->test("4000_09_frogt.pdf"));
        $this->assertFalse($regExp->test("2015_05_thisnameistoolong.jpg"));

    }

    public function testUsageExample2()
    {
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

        $this->assertTrue($regExp->test("123.pdf"));
        $this->assertTrue($regExp->test("456.doc"));
        $this->assertTrue($regExp->test("bbbb.jpg"));
        $this->assertTrue($regExp->test("aaaa.jpg"));

        $this->assertFalse($regExp->test("1234.pdf"));
        $this->assertFalse($regExp->test("123.gif"));
        $this->assertFalse($regExp->test("aaaaa.jpg"));
        $this->assertFalse($regExp->test("456.docx"));

    }

    public function testUsageExample3()
    {
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
et justo duo dolores et ea rebum. doc_04.pdf Stet clita kasd gubergren,
no sea takimata sanctus est Lorem ipsum dolor sit amet.
Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
sed diam nonumy eirmod tempor invidunt ut File.doc labore et
dolore magna aliquyam erat, sed diam voluptua.
EOF;

        $matches = $regExp->exec($text);

        $this->assertTrue($matches[0] === "SomeFile.pdf");
        $this->assertTrue($matches[1] === "doc_04.pdf");
        $this->assertTrue($matches[2] === "File.doc");

    }

    public function testReplace()
    {
        $builder = new \Gherkins\RegExpBuilderPHP\RegExpBuilder();

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

        $this->assertTrue("99 bottles of beer on the wall" === $text);

    }

}