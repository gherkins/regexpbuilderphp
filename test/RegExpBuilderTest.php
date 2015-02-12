<?php

class RegExpBuilderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var RegExpBuilder
     */
    public $r;

    public function setUp()
    {
        require_once __DIR__ . "/../RegExpBuilder.php";
        $this->r = new RegExpBuilder();
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
            ->either($this->r->another()->exactly(1)->of("p"))
            ->orLike($this->r->another()->exactly(2)->of("q"))
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
            ->either($this->r->another()->exactly(1)->of("p"))
            ->orLike($this->r->another()->exactly(1)->of("q"))
            ->orLike($this->r->another()->exactly(1)->of("r"))
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
            ->exactly(3)->from(["p", "q", "r"])
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
            ->exactly(3)->notFrom(["p", "q", "r"])
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
                $this->r->another()
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

        $this->assertTrue($regEx->exec("pprrrrpprrpp")[0] == "pprrrrpp");
    }


    public function testAhead()
    {
        $regEx = $this->r
            ->exactly(1)->of("dart")
            ->ahead($this->r->another()->exactly(1)->of("lang"))
            ->getRegExp();


        $this->assertArrayHasKey(0, $regEx->exec("dartlang"));
        $this->assertTrue($regEx->exec("dartlang")[0] == "dart");

        $this->assertFalse($regEx->test("dartpqr"));
    }

    public function testNotAhead()
    {
        $regEx = $this->r
            ->exactly(1)->of("dart")
            ->notAhead($this->r->another()->exactly(1)->of("pqr"))
            ->getRegExp();

        $this->assertTrue($regEx->test("dartlang"));

        $this->assertFalse($regEx->test("dartpqr"));
    }

    public function testAsGroup()
    {
        $regEx = $this->r
            ->min(1)->max(3)->of("p")
            ->exactly(1)->of("dart")->asGroup()
            ->exactly(1)->from(["p", "q", "r"])
            ->getRegExp();

        $this->assertTrue($regEx->exec("pdartq")[1] == "dart");
    }

}