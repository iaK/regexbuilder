<?php

namespace RegexBuilder;

class Regex
{
    public static function __callStatic($method, $args)
    {
        return (new PatternBuilder)->{$method}(...$args);
    }
}
