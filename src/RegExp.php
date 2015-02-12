<?php

namespace Gherkins\RegExpBuilderPHP;

class RegExp
{

    public $expr;
    public $flags;

    public function __construct($expr, $flags)
    {
        $this->expr  = $expr;
        $this->flags = $flags;
    }

    public function test($string)
    {
        return (bool)preg_match(
            sprintf("/%s/%s", $this->expr, $this->flags),
            $string
        );
    }

    public function exec($string)
    {
        $matches = array();
        preg_match(
            sprintf("/%s/%s", $this->expr, $this->flags),
            $string,
            $matches
        );

        return $matches;
    }
}