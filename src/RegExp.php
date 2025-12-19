<?php

declare(strict_types=1);

namespace Gherkins\RegExpBuilderPHP;

class RegExp
{
    protected string $_expr;

    protected string $_flags;

    protected int $_pregMatchFlags;

    protected string $_method = "preg_match";

    public function __construct(string $expr, string $flags, int $pregMatchFlags = 0)
    {
        $this->_expr  = $expr;
        $this->_flags = $flags;
        $this->_pregMatchFlags = $pregMatchFlags;

        if (strpos($this->_flags, "g") !== false) {
            $this->_flags  = str_replace("g", "", $this->_flags);
            $this->_method = "preg_match_all";
        }
    }

    public function __toString(): string
    {
        return $this->getExpression();
    }

    public function getExpression(): string
    {
        return $this->_expr;
    }

    public function getFlags(): string
    {
        return $this->_flags;
    }

    public function matches(string $string): bool
    {
        $matches = [];

        return (bool)call_user_func_array(
            $this->_method, /* @phpstan-ignore-line */
            [
                sprintf("/%s/%s", $this->_expr, $this->_flags),
                $string,
                &$matches,
                $this->_pregMatchFlags ?: 0,
            ]
        );
    }

    /**
     * @return array<int|string, string|array<int,mixed>>|array<int,mixed>
     */
    public function exec(string $haystack): array
    {
        return $this->findIn($haystack);
    }

    /**
     * @return array<int|string, string|array<int,mixed>>|array<int,mixed>
     */
    public function findIn(string $haystack): array
    {
        /** @var array<int|string, string|array<int,mixed>> $matches */
        $matches = [];
        call_user_func_array(
            $this->_method, /* @phpstan-ignore-line */
            [
                sprintf("/%s/%s", $this->_expr, $this->_flags),
                $haystack,
                &$matches,
                $this->_pregMatchFlags ?: 0,
            ]
        );

        if (!isset($matches[1]) && isset($matches[0]) && is_array($matches[0])) {
            return $matches[0];
        }

        return $matches;
    }


    /**
     * @param callable(string): string $callback
     */
    public function replace(string $string, callable $callback): ?string
    {
        return preg_replace_callback(
            sprintf("/%s/%s", $this->_expr, $this->_flags),
            /** @param array<int, string> $hit */
            function (array $hit) use ($callback): string {
                return $callback($hit[0]);
            },
            $string
        );
    }
}
