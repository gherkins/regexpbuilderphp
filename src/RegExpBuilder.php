<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 12.02.15
 * Time: 17:06
 */

namespace Gherkins\RegExpBuilderPHP;

class RegExpBuilder
{

    /**
     * @var string
     */
    protected $_flags = "";

    /**
     * @var array
     */
    protected $_literal = array();

    /**
     * @var int
     */
    protected $_groupsUsed = 0;

    /**
     * @var int
     */
    protected $_min;

    /**
     * @var int
     */
    protected $_max;

    /**
     * @var string
     */
    protected $_of;

    /**
     * @var string
     */
    protected $_ofAny;

    /**
     * @var string
     */
    protected $_ofGroup;

    /**
     * @var string
     */
    protected $_from;

    /**
     * @var string
     */
    protected $_notFrom;

    /**
     * @var string
     */
    protected $_like;

    /**
     * @var string
     */
    protected $_either;

    /**
     * @var bool
     */
    protected $_reluctant;

    /**
     * @var bool
     */
    protected $_capture;

    /**
     * @var string
     */
    protected $_captureName;

    public function __construct()
    {
        $this->clear();
    }

    /**
     * reset values
     */
    private function clear()
    {
        $this->_min       = -1;
        $this->_max       = -1;
        $this->_of        = "";
        $this->_ofAny     = false;
        $this->_ofGroup   = -1;
        $this->_from      = "";
        $this->_notFrom   = "";
        $this->_like      = "";
        $this->_either    = "";
        $this->_reluctant = false;
        $this->_capture   = false;
    }

    private function flushState()
    {
        if ($this->_of != "" || $this->_ofAny || $this->_ofGroup > 0 || $this->_from != "" || $this->_notFrom != "" || $this->_like != "") {
            $captureLiteral   = $this->_capture
                ? $this->_captureName ? "?P<".$this->_captureName.">" : ""
                : "?:";
            $quantityLiteral  = $this->getQuantityLiteral();
            $characterLiteral = $this->getCharacterLiteral();
            $reluctantLiteral = $this->_reluctant ? "?" : "";
            $this->_literal[] = ("(" . $captureLiteral . "(?:" . $characterLiteral . ")" . $quantityLiteral . $reluctantLiteral . ")");
            $this->clear();
        }
    }

    private function getQuantityLiteral()
    {
        if ($this->_min != -1) {
            if ($this->_max != -1) {
                return "{" . $this->_min . "," . $this->_max . "}";
            }

            return "{" . $this->_min . ",}";
        }

        return "{0," . $this->_max . "}";
    }

    private function getCharacterLiteral()
    {
        if ($this->_of != "") {
            return $this->_of;
        }
        if ($this->_ofAny) {
            return ".";
        }
        if ($this->_ofGroup > 0) {
            return "\\" . $this->_ofGroup;
        }
        if ($this->_from != "") {
            return "[" . $this->_from . "]";
        }
        if ($this->_notFrom != "") {
            return "[^" . $this->_notFrom . "]";
        }
        if ($this->_like != "") {
            return $this->_like;
        }

        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
    }

    public function getLiteral()
    {
        $this->flushState();

        return join("", $this->_literal);
    }

    private function combineGroupNumberingAndGetLiteralral(RegExpBuilder $r)
    {
        $literal = $this->incrementGroupNumbering($r->getLiteral(), $this->_groupsUsed);
        $this->_groupsUsed += $r->_groupsUsed;

        return $literal;
    }

    private function incrementGroupNumbering($literal, $increment)
    {

        if ($increment > 0) {
            $literal = preg_replace_callback(
                '/\\\(\d+)/',
                function ($groupReference) use ($increment) {
                    $groupNumber = (int)substr($groupReference[0], 1) + $increment;

                    return sprintf("\\%s", $groupNumber);
                },
                $literal
            );
        }

        return $literal;
    }

    public function getRegExp()
    {
        $this->flushState();

        return new RegExp(join("", $this->_literal), $this->_flags);
    }

    private function addFlag($flag)
    {
        if (strpos($this->_flags, $flag) === false) {
            $this->_flags .= $flag;
        }

        return $this;
    }


    public function ignoreCase()
    {
        return $this->addFlag("i");
    }


    public function multiLine()
    {
        return $this->addFlag("m");
    }

    public function globalMatch()
    {
        return $this->addFlag("g");
    }

    public function startOfInput()
    {
        $this->_literal[] = "(?:^)";

        return $this;
    }

    public function startOfLine()
    {
        $this->multiLine();

        return $this->startOfInput();
    }

    public function endOfInput()
    {
        $this->flushState();
        $this->_literal[] = "(?:$)";

        return $this;
    }

    public function endOfLine()
    {
        $this->multiLine();

        return $this->endOfInput();
    }

    public function eitherFind($r)
    {
        if (is_string($r)) {
            return $this->setEither($this->getNew()->exactly(1)->of($r));
        }

        return $this->setEither($r);
    }


    private function setEither($r)
    {
        $this->flushState();
        $this->_either = $this->combineGroupNumberingAndGetLiteralral($r);

        return $this;
    }

    public function orFind($r)
    {

        if (is_string($r)) {
            return $this->setOr($this->getNew()->exactly(1)->of($r));
        }

        return $this->setOr($r);
    }

    public function anyOf(array $r)
    {
        if (count($r) < 1) {
            return $this;
        }

        $firstToken = array_shift($r);
        $this->eitherFind($firstToken);

        foreach ($r as $token) {
            $this->orFind($token);
        }

        return $this;
    }

    private function setOr($r)
    {
        $either = $this->_either;
        $or     = $this->combineGroupNumberingAndGetLiteralral($r);
        if ($either == "") {
            $lastOr = $this->_literal[count($this->_literal) - 1];

            $lastOr                                     = substr($lastOr, 0, (strlen($lastOr) - 1));
            $this->_literal[count($this->_literal) - 1] = $lastOr;
            $this->_literal[]                           = "|(?:" . $or . "))";
        } else {
            $this->_literal[] = "(?:(?:" . $either . ")|(?:" . $or . "))";
        }
        $this->clear();

        return $this;
    }


    public function neither($r)
    {

        if (is_string($r)) {
            return $this->notAhead($this->getNew()->exactly(1)->of($r));
        }

        return $this->notAhead($r);
    }

    public function nor($r)
    {
        if ($this->_min == 0 && $this->_ofAny) {
            $this->_min   = -1;
            $this->_ofAny = false;
        }
        $this->neither($r);

        return $this->min(0)->ofAny();
    }

    public function exactly($n)
    {
        $this->flushState();
        $this->_min = $n;
        $this->_max = $n;

        return $this;
    }

    public function min($n)
    {
        $this->flushState();
        $this->_min = $n;

        return $this;
    }

    public function max($n)
    {
        $this->flushState();
        $this->_max = $n;

        return $this;
    }

    public function of($s)
    {
        $this->_of = $this->sanitize($s);

        return $this;
    }


    public function ofAny()
    {
        $this->_ofAny = true;

        return $this;
    }

    public function ofGroup($n)
    {
        $this->_ofGroup = $n;

        return $this;
    }

    public function from($s)
    {
        $this->_from = $this->sanitize(join("", $s));

        return $this;
    }

    public function notFrom($s)
    {
        $this->_notFrom = $this->sanitize(join("", $s));

        return $this;
    }

    public function like($r)
    {
        $this->_like = $this->combineGroupNumberingAndGetLiteralral($r);

        return $this;
    }


    public function reluctantly()
    {
        $this->_reluctant = true;


        return $this;
    }


    public function ahead($r)
    {
        $this->flushState();
        $this->_literal[] = "(?=" . $this->combineGroupNumberingAndGetLiteralral($r) . ")";

        return $this;
    }


    public function notAhead($r)
    {
        $this->flushState();
        $this->_literal[] = "(?!" . $this->combineGroupNumberingAndGetLiteralral($r) . ")";

        return $this;
    }

    public function asGroup($name = null)
    {
        $this->_capture = true;
        $this->_captureName = $name;
        $this->_groupsUsed++;

        return $this;
    }

    /**
     * @param $s
     * @return $this
     */
    public function then($s)
    {
        return $this->exactly(1)->of($s);
    }

    public function find($s)
    {
        return $this->then($s);
    }

    public function some($s)
    {
        return $this->min(1)->from($s);
    }

    public function maybeSome($s)
    {
        return $this->min(0)->from($s);
    }

    public function maybe($s)
    {
        return $this->max(1)->of($s);
    }

    public function anything()
    {
        return $this->min(0)->ofAny();
    }

    public function anythingBut($s)
    {
        if (strlen($s) === 1) {
            return $this->min(1)->notFrom(array($s));
        }
        $this->notAhead($this->getNew()->exactly(1)->of($s));

        return $this->min(0)->ofAny();
    }

    public function something()
    {
        return $this->min(1)->ofAny();
    }
    
    /**
     * @return $this
     */
    public function any()
    {
        return $this->exactly(1)->ofAny();
    }

    public function lineBreak()
    {
        $this->flushState();
        $this->_literal[] = "(?:\\r\\n|\\r|\\n)";

        return $this;
    }

    public function lineBreaks()
    {
        return $this->like($this->getNew()->lineBreak());
    }


    public function whitespace()
    {
        if ($this->_min == -1 && $this->_max == -1) {
            $this->flushState();
            $this->_literal[] = "(?:\\s)";

            return $this;
        }
        $this->_like = "\\s";

        return $this;
    }

    public function notWhitespace()
    {
        if ($this->_min == -1 && $this->_max == -1) {
            $this->flushState();
            $this->_literal[] = "(?:\\S)";

            return $this;
        }
        $this->_like = "\\S";

        return $this;
    }

    public function tab()
    {
        $this->flushState();
        $this->_literal[] = "(?:\\t)";

        return $this;
    }

    public function tabs()
    {
        return $this->like($this->getNew()->tab());
    }

    public function digit()
    {
        $this->flushState();
        $this->_literal[] = "(?:\\d)";

        return $this;
    }


    public function notDigit()
    {
        $this->flushState();
        $this->_literal[] = "(?:\\D)";

        return $this;
    }

    public function digits()
    {

        return $this->like($this->getNew()->digit());
    }

    public function notDigits()
    {
        return $this->like($this->getNew()->notDigit());
    }

    public function letter()
    {
        $this->exactly(1);
        $this->_from = "A-Za-z";

        return $this;
    }

    public function notLetter()
    {
        $this->exactly(1);
        $this->_notFrom = "A-Za-z";

        return $this;
    }

    public function letters()
    {
        $this->_from = "A-Za-z";

        return $this;
    }

    public function notLetters()
    {
        $this->_notFrom = "A-Za-z";

        return $this;
    }

    public function lowerCaseLetter()
    {
        $this->exactly(1);
        $this->_from = "a-z";

        return $this;
    }

    public function lowerCaseLetters()
    {
        $this->_from = "a-z";

        return $this;
    }

    public function upperCaseLetter()
    {
        $this->exactly(1);
        $this->_from = "A-Z";

        return $this;
    }

    public function upperCaseLetters()
    {
        $this->_from = "A-Z";

        return $this;
    }

    public function append($r)
    {
        $this->exactly(1);
        $this->_like = $this->combineGroupNumberingAndGetLiteralral($r);

        return $this;
    }

    public function optional($r)
    {
        $this->max(1);
        $this->_like = $this->combineGroupNumberingAndGetLiteralral($r);

        return $this;
    }

    private function sanitize($s)
    {
        return preg_quote($s, "/");
    }

    /**
     * get a fresh instance
     *
     * @return RegExpBuilder
     */
    public function getNew()
    {
        $class = get_class($this);

        return new $class;
    }

}
