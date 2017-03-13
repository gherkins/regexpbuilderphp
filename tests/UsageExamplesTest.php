<?php declare(strict_types=1);

namespace Gherkins\RegExpBuilderPHP\Test;

use Gherkins\RegExpBuilderPHP\RegExpBuilder;
use PHPUnit\Framework\TestCase;

final class UsageExamplesTest extends TestCase
{

    /**
     * @var RegExpBuilder
     */
    private $r;

    protected function setUp()
    {
        $this->r = new RegExpBuilder();
    }

    public function testUsageExample()
    {
        $builder = new RegExpBuilder();

        $regExp = $builder
            ->startOfInput()
            ->exactly(4)->digits()
            ->then("_")
            ->exactly(2)->digits()
            ->then("_")
            ->min(3)->max(10)->letters()
            ->then(".")
            ->anyOf(["png", "jpg", "gif"])
            ->endOfInput()
            ->getRegExp();

        $this->assertTrue($regExp->matches("2020_10_hund.jpg"));
        $this->assertTrue($regExp->matches("2030_11_katze.png"));
        $this->assertTrue($regExp->matches("4000_99_maus.gif"));

        $this->assertFalse($regExp->matches("4000_99_f.gif"));
        $this->assertFalse($regExp->matches("4000_09_frogt.pdf"));
        $this->assertFalse($regExp->matches("2015_05_thisnameistoolong.jpg"));

    }

    public function testUsageExample2()
    {
        $builder = new RegExpBuilder();

        $a = $builder
            ->startOfInput()
            ->exactly(3)->digits()
            ->anyOf([".pdf", ".doc"])
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

        $this->assertTrue($regExp->matches("123.pdf"));
        $this->assertTrue($regExp->matches("456.doc"));
        $this->assertTrue($regExp->matches("bbbb.jpg"));
        $this->assertTrue($regExp->matches("aaaa.jpg"));

        $this->assertFalse($regExp->matches("1234.pdf"));
        $this->assertFalse($regExp->matches("123.gif"));
        $this->assertFalse($regExp->matches("aaaaa.jpg"));
        $this->assertFalse($regExp->matches("456.docx"));

    }

    public function testUsageExample3()
    {
        $builder = new RegExpBuilder();

        $regExp = $builder
            ->multiLine()
            ->globalMatch()
            ->min(1)->max(10)->anythingBut(" ")
            ->anyOf([".pdf", ".doc"])
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
        $builder = new RegExpBuilder();

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

    public function testPregMatchFlags()
    {
        $builder = new RegExpBuilder();

        $regExp = $builder
            ->multiLine()
            ->globalMatch()
            ->min(1)->max(10)->anythingBut(" ")
            ->anyOf([".pdf", ".doc"])
            ->pregMatchFlags(PREG_OFFSET_CAPTURE)
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

        $this->assertTrue(is_array($matches[0]));
        $this->assertTrue($matches[0][0] === "SomeFile.pdf");
        $this->assertTrue($matches[0][1] === 73);

        $this->assertTrue(is_array($matches[1]));
        $this->assertTrue($matches[1][0] === "doc_04.pdf");
        $this->assertTrue($matches[1][1] === 226);

        $this->assertTrue(is_array($matches[2]));
        $this->assertTrue($matches[2][0] === "File.doc");
        $this->assertTrue($matches[2][1] === 419);
    }

}
