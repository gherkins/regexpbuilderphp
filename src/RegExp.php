<?php declare(strict_types=1);

namespace Gherkins\RegExpBuilderPHP;

class RegExp
{

    /**
     * @var String
     */
    protected $_expr;

    /**
     * @var String
     */
    protected $_flags;

    /**
     * @var String
     */
    protected $_pregMatchFlags;

    /**
     * @var String
     */
    protected $_method = "preg_match";

    public function __construct($expr, $flags, $pregMatchFlags = null)
    {
        $this->_expr  = $expr;
        $this->_flags = $flags;
        $this->_pregMatchFlags = $pregMatchFlags;

        if (strpos($this->_flags, "g") !== false) {
            $this->_flags  = str_replace("g", "", $this->_flags);
            $this->_method = "preg_match_all";
        }
    }

    public function __toString() : string
    {
        return $this->getExpression();
    }

    public function getExpression() : string
    {
        return $this->_expr;
    }

    public function getFlags() : string
    {
        return $this->_flags;
    }

    /**
     * alias for matches
     *
     * @deprecated
     */
    public function test(string $string) : bool
    {
        return $this->matches($string);
    }

    /**
     * check string w/ preg_match
     */
    public function matches(string $string) : bool
    {
        $matches = [];

        return (bool)call_user_func_array(
            $this->_method,
            [
                sprintf("/%s/%s", $this->_expr, $this->_flags),
                $string,
                &$matches,
                $this->_pregMatchFlags ?: null,
            ]
        );
    }

    public function exec($haystack) : array
    {
        return $this->findIn($haystack);
    }

    /**
     * execute preg_match, return matches
     *
     * @param $haystack
     */
    public function findIn($haystack)
    {
        $matches = [];
        call_user_func_array(
            $this->_method,
            [
                sprintf("/%s/%s", $this->_expr, $this->_flags),
                $haystack,
                &$matches,
                $this->_pregMatchFlags ?: null,
            ]
        );

        if (!isset($matches[1]) && isset($matches[0]) && !is_array($matches[0])) {
            return $matches[0];
        }

        return $matches;
    }


    public function replace($string, $callback)
    {
        return preg_replace_callback(
            sprintf("/%s/%s", $this->_expr, $this->_flags),
            function ($hit) use ($callback) {
                return call_user_func($callback, $hit[0]);
            },
            $string
        );
    }

}
