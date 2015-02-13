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

    public function testMoney()
    {


        $regEx = $this->r
            ->find("€")
            ->min(1)->digits()
            ->then(",")
            ->digit()
            ->digit()
            ->getRegExp();

        $this->assertTrue($regEx->test("€8,99"));
        $this->assertTrue($regEx->test("€81,99"));

        $this->assertFalse($regEx->test("€8,9"));
        $this->assertFalse($regEx->test("8,99 €"));

    }

    public function testMaybe()
    {
        $regEx = $this->r
            ->startOfLine()
            ->notDigit()
            ->maybe("a")
            ->getRegExp();

        $this->assertTrue($regEx->test("aabba1"));

        $this->assertFalse($regEx->test("12aabba1"));

    }

    public function testMaybeSome()
    {
        $regEx = $this->r
            ->startOfLine()
            ->notDigit()
            ->maybeSome(array("a", "b", "c"))
            ->getRegExp();

        $this->assertTrue($regEx->test("aabba1"));

        $this->assertFalse($regEx->test("12aabba1"));
    }

    public function testSome()
    {
        $regEx = $this->r
            ->startOfLine()
            ->notDigit()
            ->some(array("a", "b", "c"))
            ->getRegExp();

        $this->assertTrue($regEx->test("aabba1"));

        $this->assertFalse($regEx->test("12aabba1"));
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

    public function testNotLetters()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(1)
            ->notLetters()
            ->getRegExp();

        $this->assertTrue($regEx->test("234asd"));
        $this->assertTrue($regEx->test("@234asd"));

        $this->assertFalse($regEx->test("asd425"));
    }

    public function testNotDigit()
    {
        $regEx = $this->r
            ->startOfLine()
            ->notDigit()
            ->getRegExp();

        $this->assertTrue($regEx->test("a234asd"));

        $this->assertFalse($regEx->test("45asd"));
    }

    public function testNotDigits()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(1)
            ->notDigits()
            ->getRegExp();

        $this->assertTrue($regEx->test("a234asd"));
        $this->assertTrue($regEx->test("@234asd"));

        $this->assertFalse($regEx->test("425asd"));
    }

    public function testAny()
    {
        $regEx = $this->r
            ->startOfLine()
            ->any()
            ->getRegExp();

        $this->assertTrue($regEx->test("a.jpg"));
        $this->assertTrue($regEx->test("a.b_asdasd"));
        $this->assertTrue($regEx->test("4"));

        $this->assertFalse($regEx->test(""));
    }

    public function testOfAny()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(2)
            ->ofAny()
            ->find("_")
            ->getRegExp();

        $this->assertTrue($regEx->test("12_123123.jpg"));
        $this->assertTrue($regEx->test("ab_asdasd"));

        $this->assertFalse($regEx->test("425asd"));
    }

    public function testOfAny2()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(3)->ofAny()
            ->endOfLine()
            ->getRegExp();

        $this->assertTrue($regEx->test("pqr"));
    }

    public function testAnything()
    {
        $regEx = $this->r
            ->startOfLine()
            ->anything()
            ->getRegExp();

        $this->assertTrue($regEx->test("a.jpg"));
        $this->assertTrue($regEx->test("a.b_asdasd"));
        $this->assertTrue($regEx->test("4"));
    }

    public function testAnythingBut()
    {
        $regEx = $this->r
            ->startOfInput()
            ->anythingBut("admin")
            ->getRegExp();

        $this->assertTrue($regEx->test("a.jpg"));
        $this->assertTrue($regEx->test("a.b_asdasd"));
        $this->assertTrue($regEx->test("4"));

        $this->assertFalse($regEx->test("admin"));

    }

    public function testAnythingBut2()
    {
        $regEx = $this->r
            ->startOfLine()
            ->anythingBut("Y")
            ->getRegExp();

        $this->assertTrue($regEx->test("a.jpg"));
        $this->assertTrue($regEx->test("a.b_asdasd"));
        $this->assertTrue($regEx->test("4"));

        $this->assertFalse($regEx->test("YY"));
        $this->assertFalse($regEx->test("Y"));

    }

    public function testNeitherNor()
    {

        $regEx = $this->r
            ->startOfLine()
            ->neither($this->r->getNew()->exactly(1)->of("milk"))
            ->nor($this->r->getNew()->exactly(1)->of("juice"))
            ->getRegExp();

        $this->assertTrue($regEx->test("beer"));

        $this->assertFalse($regEx->test("milk"));
        $this->assertFalse($regEx->test("juice"));

    }

    public function testNeitherNor2()
    {

        $regEx = $this->r
            ->startOfLine()
            ->neither("milk")
            ->min(0)
            ->ofAny()
            ->nor($this->r->getNew()->exactly(1)->of("juice"))
            ->getRegExp();

        $this->assertTrue($regEx->test("beer"));

        $this->assertFalse($regEx->test("milk"));
        $this->assertFalse($regEx->test("juice"));

    }

    public function testLowerCasew()
    {
        $regEx = $this->r
            ->startOfLine()
            ->lowerCaseLetter()
            ->getRegExp();

        $this->assertTrue($regEx->test("a24"));

        $this->assertFalse($regEx->test("234a"));
        $this->assertFalse($regEx->test("A34"));
    }

    public function testLowerCaseLetters()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(2)
            ->lowerCaseLetters()
            ->getRegExp();

        $this->assertTrue($regEx->test("aa24"));

        $this->assertFalse($regEx->test("aAa234a"));
        $this->assertFalse($regEx->test("234a"));
        $this->assertFalse($regEx->test("A34"));
    }

    public function testUpperCaseLetter()
    {
        $regEx = $this->r
            ->startOfLine()
            ->upperCaseLetter()
            ->getRegExp();

        $this->assertTrue($regEx->test("A24"));

        $this->assertFalse($regEx->test("aa234a"));
        $this->assertFalse($regEx->test("34aa"));
    }

    public function testUpperCaseLetters()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(2)
            ->upperCaseLetters()
            ->getRegExp();

        $this->assertTrue($regEx->test("AA24"));

        $this->assertFalse($regEx->test("aAa234a"));
        $this->assertFalse($regEx->test("234a"));
        $this->assertFalse($regEx->test("a34"));
    }

    public function testLetterDigit()
    {
        $regEx = $this->r
            ->ignoreCase()
            ->globalMatch()
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

    public function testTabs()
    {
        $regEx = $this->r
            ->startOfLine()
            ->exactly(2)
            ->tabs()
            ->getRegExp();

        $this->assertTrue($regEx->test("\t\tp"));

        $this->assertFalse($regEx->test("\tp"));
        $this->assertFalse($regEx->test("q\tp\t"));
        $this->assertFalse($regEx->test("p\t"));

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

    public function testNotWhitespace()
    {
        $regEx = $this->r
            ->startOfLine()
            ->notWhitespace()
            ->getRegExp();

        $this->assertTrue($regEx->test("a234asd"));

        $this->assertFalse($regEx->test(" 45asd"));
        $this->assertFalse($regEx->test("\t45asd"));
    }

    public function testNotWhitespace2()
    {
        $regEx = $this->r
            ->startOfLine()
            ->min(1)
            ->notWhitespace()
            ->getRegExp();

        $this->assertTrue($regEx->test("a234asd"));

        $this->assertFalse($regEx->test(" 45asd"));
        $this->assertFalse($regEx->test("\t45asd"));
    }

    public function testLineBreak()
    {
        $regEx = $this->r
            ->startOfLine()
            ->lineBreak()
            ->getRegExp();

        $this->assertTrue($regEx->test("\n\ra234asd"));
        $this->assertTrue($regEx->test("\na234asd"));
        $this->assertTrue($regEx->test("\ra234asd"));

        $this->assertFalse($regEx->test(" 45asd"));
        $this->assertFalse($regEx->test("\t45asd"));
    }

    public function testLineBreaks()
    {
        $regEx = $this->r
            ->startOfLine()
            ->min(2)
            ->lineBreaks()
            ->getRegExp();

        $this->assertTrue($regEx->test("\n\ra234asd"));
        $this->assertTrue($regEx->test("\n\na234asd"));
        $this->assertTrue($regEx->test("\r\ra234asd"));

        $this->assertFalse($regEx->test(" 45asd"));
        $this->assertFalse($regEx->test("\t45asd"));
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

    public function testOptional()
    {
        $regEx = $this->r
            ->min(1)->max(3)->of("p")
            ->exactly(1)->of("dart")
            ->optional($this->r->getNew()->exactly(1)->from(array("p", "q", "r")))
            ->getRegExp();

        $this->assertTrue($regEx->test("pdartq"));
    }

}