<?php

namespace Gherkins\RegExpBuilderPHP\Test;


class RegExpBuilderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Gherkins\RegExpBuilderPHP\RegExpBuilder
     */
    public $r;

    public function setUp()
    {
        $this->r = new \Gherkins\RegExpBuilderPHP\RegExpBuilder();
    }

    public function testRegExp()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(1)
            ->of("p")
            ->getRegExp();


        $this->assertTrue(is_string($regEx->getFlags()));
        $this->assertTrue($regEx->getFlags() === "m");

        $this->assertTrue(is_string($regEx->__toString()));
        $this->assertTrue(is_string($regEx->getExpression()));

    }

    public function testLettersDigits()
    {
        $regEx = $this->r
            ->startOfLine()
            ->min(3)
            ->letters()
            ->append($this->r->getNew()->min(2)->digits())
            ->getRegExp();

        $this->assertTrue($regEx->test("asf24"));

        $this->assertFalse($regEx->test("af24"));
        $this->assertFalse($regEx->test("afs4"));
        $this->assertFalse($regEx->test("234asas"));

    }

    public function testNotLetter()
    {
        $regEx = $this->r
            ->startOfLine()
            ->notLetter()
            ->getRegExp();

        $this->assertTrue($regEx->test("234asd"));
        $this->assertFalse($regEx->test("asd425"));
    }

    public function testLetterDigit()
    {
        $regEx = $this->r
            ->startOfLine()
            ->letter()
            ->append($this->r->getNew()->digit())
            ->getRegExp();

        $this->assertTrue($regEx->test("a5"));

        $this->assertFalse($regEx->test("5a"));

    }

    public function testTab()
    {
        $regEx = $this->r
            ->startOfLine()
            ->tab()
            ->getRegExp();

        $this->assertTrue($regEx->test("\tp"));
        $this->assertFalse($regEx->test("q\tp\t"));
        $this->assertFalse($regEx->test("p\t"));

    }

    public function testTab2()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(1)->of("p")
            ->tab()
            ->exactly(1)->of("q")
            ->getRegExp();

        $this->assertTrue($regEx->test("p\tq"));
    }


    public function testWhiteSpace()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(2)->whitespace()
            ->then("p")
            ->then("d")
            ->then("r")
            ->exactly(1)->whitespace()
            ->getRegExp();

        $this->assertTrue($regEx->test("  pdr "));

        $this->assertFalse($regEx->test(" pdr "));
        $this->assertFalse($regEx->test("  pd r "));
        $this->assertFalse($regEx->test(" p dr "));

    }

    public function testMoreWhiteSpace()
    {
        $regEx = $this->r
            ->startOfLine()
            ->whitespace()
            ->then("p")
            ->then("d")
            ->then("r")
            ->exactly(1)->whitespace()
            ->getRegExp();

        $this->assertTrue($regEx->test("\tpdr\t"));
    }


    public function testStartOfLine()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(1)
            ->of("p")
            ->getRegExp();

        $this->assertTrue($regEx->test("p"));
        $this->assertFalse($regEx->test("qp"));
    }

    public function testEndOfLine()
    {
        $regEx = $this->r
            ->exactly(1)
            ->of("p")
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("p"));
        $this->assertFalse($regEx->test("pq"));
    }

    public function testEitherLikeOrLike()
    {
        $regEx = $this->r
            ->startOfLine()
            ->either($this->r->getNew()->exactly(1)->of("p"))
            ->orLike($this->r->getNew()->exactly(2)->of("q"))
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("p"));
        $this->assertTrue($regEx->test("qq"));

        $this->assertFalse($regEx->test("pqq"));
        $this->assertFalse($regEx->test("qqp"));
    }


    public function testOrLikeChain()
    {

        $regEx = $this->r
            ->either($this->r->getNew()->exactly(1)->of("p"))
            ->orLike($this->r->getNew()->exactly(1)->of("q"))
            ->orLike($this->r->getNew()->exactly(1)->of("r"))
            ->getRegExp();

        $this->assertTrue($regEx->test("p"));
        $this->assertTrue($regEx->test("q"));
        $this->assertTrue($regEx->test("r"));

        $this->assertFalse($regEx->test("s"));
    }

    public function testEitherOr()
    {
        $regEx = $this->r
            ->either("p")
            ->orLike("q")
            ->getRegExp();

        $this->assertTrue($regEx->test("p"));
        $this->assertTrue($regEx->test("q"));

        $this->assertFalse($regEx->test("r"));
    }

    public function testExactly()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(3)->of("p")
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("ppp"));

        $this->assertFalse($regEx->test("pp"));
        $this->assertFalse($regEx->test("pppp"));
    }

    public function testMin()
    {
        $regEx = $this->r
            ->startOfLine()
            ->min(2)->of("p")
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("pp"));
        $this->assertTrue($regEx->test("ppp"));
        $this->assertTrue($regEx->test("ppppppp"));

        $this->assertFalse($regEx->test("p"));
    }

    public function testMax()
    {
        $regEx = $this->r
            ->startOfLine()
            ->max(3)->of("p")
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("p"));
        $this->assertTrue($regEx->test("pp"));
        $this->assertTrue($regEx->test("ppp"));

        $this->assertFalse($regEx->test("pppp"));
        $this->assertFalse($regEx->test("pppppppp"));
    }

    public function testMinMax()
    {
        $regEx = $this->r
            ->startOfLine()
            ->min(3)->max(7)->of("p")
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("ppp"));
        $this->assertTrue($regEx->test("ppppp"));
        $this->assertTrue($regEx->test("ppppppp"));

        $this->assertFalse($regEx->test("pp"));
        $this->assertFalse($regEx->test("p"));
        $this->assertFalse($regEx->test("pppppppp"));
        $this->assertFalse($regEx->test("pppppppppppp"));
    }

    public function testOf()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(2)->of("p p p ")
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("p p p p p p "));

        $this->assertFalse($regEx->test("p p p p pp"));
    }


    public function testOfAny()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(3)->ofAny()
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("pqr"));
    }

    public function testOfGroup()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(3)->of("p")->asGroup()
            ->exactly(1)->of("q")
            ->exactly(1)->ofGroup(1)
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("pppqppp"));
    }

    public function testFrom()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(3)->from(array("p", "q", "r"))
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("ppp"));
        $this->assertTrue($regEx->test("qqq"));
        $this->assertTrue($regEx->test("ppq"));
        $this->assertTrue($regEx->test("rqp"));

        $this->assertFalse($regEx->test("pyy"));
    }


    public function testNotFrom()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(3)->notFrom(array("p", "q", "r"))
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("lmn"));

        $this->assertFalse($regEx->test("mnq"));
    }

    public function testLike()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(2)->like(
                $this->r->getNew()
                    ->min(1)->of("p")
                    ->min(2)->of("q")
            )
            ->endOfLine()
            ->getRegExp();


        $this->assertTrue($regEx->test("pqqpqq"));

        $this->assertFalse($regEx->test("qppqpp"));
    }


    public function testReluctantly()
    {

        $regEx = $this->r
            ->exactly(2)->of("p")
            ->min(2)->ofAny()->reluctantly()
            ->exactly(2)->of("p")
            ->getRegExp();

        $matches = $regEx->exec("pprrrrpprrpp");
        $this->assertTrue($matches[0] == "pprrrrpp");
    }

    public function testAhead()
    {
        $regEx = $this->r
            ->exactly(1)->of("dart")
            ->ahead($this->r->getNew()->exactly(1)->of("lang"))
            ->getRegExp();

        $this->assertTrue($regEx->test("dartlang"));
        $this->assertTrue($regEx->test("dartlanglang"));
        $this->assertTrue($regEx->test("langdartlang"));

        $this->assertFalse($regEx->test("dartpqr"));
        $this->assertFalse($regEx->test("langdart"));

    }

    public function testNotAhead()
    {
        $regEx = $this->r
            ->exactly(1)->of("dart")
            ->notAhead($this->r->getNew()->exactly(1)->of("pqr"))
            ->getRegExp();

        $this->assertTrue($regEx->test("dartlang"));

        $this->assertFalse($regEx->test("dartpqr"));
    }

    public function testAsGroup()
    {
        $regEx = $this->r
            ->min(1)->max(3)->of("p")
            ->exactly(1)->of("dart")->asGroup()
            ->exactly(1)->from(array("p", "q", "r"))
            ->getRegExp();

        $matches = $regEx->exec("pdartq");
        $this->assertTrue($matches[1] == "dart");
    }

}