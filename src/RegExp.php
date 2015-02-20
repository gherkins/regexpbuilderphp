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

    /**
     * @var String
     */
    protected $_method = "preg_match";

    public function __construct($expr, $flags)
    {
        $this->_expr  = $expr;
        $this->_flags = $flags;

        if (strpos($this->_flags, "g") !== false) {
            $this->_flags  = str_replace("g", "", $this->_flags);
            $this->_method = "preg_match_all";
        }
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
        $matches = array();

        return (bool)call_user_func_array(
            $this->_method,
            array(
                sprintf("/%s/%s", $this->_expr, $this->_flags),
                $string,
                &$matches
            )
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
        call_user_func_array(
            $this->_method,
            array(
                sprintf("/%s/%s", $this->_expr, $this->_flags),
                $string,
                &$matches
            )
        );

        if (isset($matches[0]) && is_array($matches[0])) {
            return $matches[0];
        }

        return $matches;
    }


    public function replace($tring, $callback)
    {
        return preg_replace_callback(
            sprintf("/%s/%s", $this->_expr, $this->_flags),
            $callback,
            $tring
        );
    }

}
