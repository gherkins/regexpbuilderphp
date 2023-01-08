<?php declare(strict_types=1);

namespace Gherkins\RegExpBuilderPHP;

class RegExpBuilder
{

    protected string $_flags = "";

    protected ?int $_pregMatchFlags = null;

    protected array $_literal = [];

    protected int $_groupsUsed = 0;

    protected int $_min;

    protected int $_max;

    protected string $_of;

    protected bool $_ofAny;

    protected int $_ofGroup;

    protected string $_from;

    protected string $_notFrom;

    protected string $_like;

    protected string $_either;

    protected bool $_reluctant;

    protected bool $_capture;

    protected string $_captureName;

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

    private function getQuantityLiteral() : string
    {
        if ($this->_min != -1) {
            if ($this->_max != -1) {
                return "{" . $this->_min . "," . $this->_max . "}";
            }

            return "{" . $this->_min . ",}";
        }

        return "{0," . $this->_max . "}";
    }


    /**
     * @return null|string
     */
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

    public function getLiteral() : string
    {
        $this->flushState();

        return implode("", $this->_literal);
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

    public function getRegExp() : RegExp
    {
        $this->flushState();

        return new RegExp(implode("", $this->_literal), $this->_flags, (int)$this->_pregMatchFlags);
    }

    private function addFlag($flag)
    {
        if (strpos($this->_flags, $flag) === false) {
            $this->_flags .= $flag;
        }

        return $this;
    }

    public function ignoreCase() : RegExpBuilder
    {
        return $this->addFlag("i");
    }


    public function multiLine() : RegExpBuilder
    {
        return $this->addFlag("m");
    }

    public function globalMatch() : RegExpBuilder
    {
        return $this->addFlag("g");
    }

    public function pregMatchFlags($flags) : RegExpBuilder
    {
        $this->_pregMatchFlags = $flags;

        return $this;
    }

    public function startOfInput() : RegExpBuilder
    {
        $this->_literal[] = "(?:^)";

        return $this;
    }

    public function startOfLine() : RegExpBuilder
    {
        $this->multiLine();

        return $this->startOfInput();
    }

    public function endOfInput() : RegExpBuilder
    {
        $this->flushState();
        $this->_literal[] = "(?:$)";

        return $this;
    }

    public function endOfLine() : RegExpBuilder
    {
        $this->multiLine();

        return $this->endOfInput();
    }

    public function eitherFind($r) : RegExpBuilder
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

    public function orFind($r) : RegExpBuilder
    {
        if (is_string($r)) {
            return $this->setOr($this->getNew()->exactly(1)->of($r));
        }

        return $this->setOr($r);
    }

    public function anyOf(array $r) : RegExpBuilder
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

    private function setOr($r) : RegExpBuilder
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


    public function neither($r) : RegExpBuilder
    {
        if (is_string($r)) {
            return $this->notAhead($this->getNew()->exactly(1)->of($r));
        }

        return $this->notAhead($r);
    }

    public function nor($r) : RegExpBuilder
    {
        if ($this->_min == 0 && $this->_ofAny) {
            $this->_min   = -1;
            $this->_ofAny = false;
        }
        $this->neither($r);

        return $this->min(0)->ofAny();
    }

    public function exactly($n) : RegExpBuilder
    {
        $this->flushState();
        $this->_min = $n;
        $this->_max = $n;

        return $this;
    }

    public function min($n) : RegExpBuilder
    {
        $this->flushState();
        $this->_min = $n;

        return $this;
    }

    public function max($n) : RegExpBuilder
    {
        $this->flushState();
        $this->_max = $n;

        return $this;
    }

    public function of($s) : RegExpBuilder
    {
        $this->_of = $this->sanitize($s);

        return $this;
    }


    public function ofAny() : RegExpBuilder
    {
        $this->_ofAny = true;

        return $this;
    }

    public function ofGroup($n) : RegExpBuilder
    {
        $this->_ofGroup = $n;

        return $this;
    }

    public function from($s) : RegExpBuilder
    {
        $this->_from = $this->sanitize(join("", $s));

        return $this;
    }

    public function notFrom($s) : RegExpBuilder
    {
        $this->_notFrom = $this->sanitize(join("", $s));

        return $this;
    }

    public function like($r) : RegExpBuilder
    {
        $this->_like = $this->combineGroupNumberingAndGetLiteralral($r);

        return $this;
    }


    public function reluctantly() : RegExpBuilder
    {
        $this->_reluctant = true;

        return $this;
    }


    public function ahead($r) : RegExpBuilder
    {
        $this->flushState();
        $this->_literal[] = "(?=" . $this->combineGroupNumberingAndGetLiteralral($r) . ")";

        return $this;
    }


    public function notAhead($r) : RegExpBuilder
    {
        $this->flushState();
        $this->_literal[] = "(?!" . $this->combineGroupNumberingAndGetLiteralral($r) . ")";

        return $this;
    }

    public function asGroup($name = null) : RegExpBuilder
    {
        $this->_capture = true;
        $this->_captureName = $name;
        $this->_groupsUsed++;

        return $this;
    }


    public function then($s) : RegExpBuilder
    {
        return $this->exactly(1)->of($s);
    }

    public function find($s) : RegExpBuilder
    {
        return $this->then($s);
    }

    public function some($s) : RegExpBuilder
    {
        return $this->min(1)->from($s);
    }

    public function maybeSome($s) : RegExpBuilder
    {
        return $this->min(0)->from($s);
    }

    public function maybe($s) : RegExpBuilder
    {
        return $this->max(1)->of($s);
    }

    public function anything() : RegExpBuilder
    {
        return $this->min(0)->ofAny();
    }

    public function anythingBut($s) : RegExpBuilder
    {
        if (strlen($s) === 1) {
            return $this->min(1)->notFrom([$s]);
        }
        $this->notAhead($this->getNew()->exactly(1)->of($s));

        return $this->min(0)->ofAny();
    }

    public function something() : RegExpBuilder
    {
        return $this->min(1)->ofAny();
    }


    public function any() : RegExpBuilder
    {
        return $this->exactly(1)->ofAny();
    }

    public function lineBreak() : RegExpBuilder
    {
        $this->flushState();
        $this->_literal[] = "(?:\\r\\n|\\r|\\n)";

        return $this;
    }

    public function lineBreaks() : RegExpBuilder
    {
        return $this->like($this->getNew()->lineBreak());
    }


    public function whitespace() : RegExpBuilder
    {
        if ($this->_min == -1 && $this->_max == -1) {
            $this->flushState();
            $this->_literal[] = "(?:\\s)";

            return $this;
        }
        $this->_like = "\\s";

        return $this;
    }

    public function notWhitespace() : RegExpBuilder
    {
        if ($this->_min == -1 && $this->_max == -1) {
            $this->flushState();
            $this->_literal[] = "(?:\\S)";

            return $this;
        }
        $this->_like = "\\S";

        return $this;
    }

    public function tab() : RegExpBuilder
    {
        $this->flushState();
        $this->_literal[] = "(?:\\t)";

        return $this;
    }

    public function tabs() : RegExpBuilder
    {
        return $this->like($this->getNew()->tab());
    }

    public function digit() : RegExpBuilder
    {
        $this->flushState();
        $this->_literal[] = "(?:\\d)";

        return $this;
    }


    public function notDigit() : RegExpBuilder
    {
        $this->flushState();
        $this->_literal[] = "(?:\\D)";

        return $this;
    }

    public function digits() : RegExpBuilder
    {
        return $this->like($this->getNew()->digit());
    }

    public function notDigits() : RegExpBuilder
    {
        return $this->like($this->getNew()->notDigit());
    }

    public function letter() : RegExpBuilder
    {
        $this->exactly(1);
        $this->_from = "A-Za-z";

        return $this;
    }

    public function notLetter() : RegExpBuilder
    {
        $this->exactly(1);
        $this->_notFrom = "A-Za-z";

        return $this;
    }

    public function letters() : RegExpBuilder
    {
        $this->_from = "A-Za-z";

        return $this;
    }

    public function notLetters() : RegExpBuilder
    {
        $this->_notFrom = "A-Za-z";

        return $this;
    }

    public function lowerCaseLetter() : RegExpBuilder
    {
        $this->exactly(1);
        $this->_from = "a-z";

        return $this;
    }

    public function lowerCaseLetters() : RegExpBuilder
    {
        $this->_from = "a-z";

        return $this;
    }

    public function upperCaseLetter() : RegExpBuilder
    {
        $this->exactly(1);
        $this->_from = "A-Z";

        return $this;
    }

    public function upperCaseLetters() : RegExpBuilder
    {
        $this->_from = "A-Z";

        return $this;
    }

    public function append(RegExpBuilder $r) : RegExpBuilder
    {
        $this->exactly(1);
        $this->_like = $this->combineGroupNumberingAndGetLiteralral($r);

        return $this;
    }

    public function optional(RegExpBuilder $r) : RegExpBuilder
    {
        $this->max(1);
        $this->_like = $this->combineGroupNumberingAndGetLiteralral($r);

        return $this;
    }

    private function sanitize(string $s) : string
    {
        return preg_quote($s, "/");
    }

    /**
     * get a fresh instance
     */
    public function getNew() : RegExpBuilder
    {
        $class = get_class($this);

        return new $class;
    }
}
