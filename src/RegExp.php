<?php

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

    public function __construct($expr, $flags)
    {
        $this->_expr  = $expr;
        $this->_flags = $flags;
    }

    /**
     * @return String
     */
    public function __toString()
    {
        return $this->getExpression();
    }

    /**
     * @return String
     */
    public function getExpression()
    {
        return $this->_expr;
    }

    /**
     * @return String
     */
    public function getFlags()
    {
        return $this->_flags;
    }

    /**
     * check string w/ preg_match
     *
     * @param $string
     * @return bool
     */
    public function test($string)
    {
        return (bool)preg_match(
            sprintf("/%s/%s", $this->_expr, $this->_flags),
            $string
        );
    }

    /**
     * execute preg_match, return matches
     *
     * @param $string
     * @return array
     */
    public function exec($string)
    {
        $matches = array();
        preg_match(
            sprintf("/%s/%s", $this->_expr, $this->_flags),
            $string,
            $matches
        );

        return $matches;
    }


}