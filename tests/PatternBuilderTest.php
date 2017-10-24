<?php

namespace Tests;

use RegexBuilder\Regex;
use PHPUnit\Framework\TestCase;
use RegexBuilder\PatternBuilder;

class PatternBuilderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->builder = new PatternBuilder;

        $this->lorem = $this->loadFile("lorem");
        $this->signs = $this->loadFile("signs");
        $this->leadspeak = $this->loadFile("leadspeak");
    }

    protected function loadFile($name)
    {
        return file_get_contents(__DIR__ . "/text/$name.txt");
    }

    /**
    * @test
    */
    public function try()
    {
        $string = "This is a hashtag: @. I'm sure!";

        dd(Regex::group("a-z")->symbol("!")->release()->symbols("only this")->getPattern());
    }

    /**
    * @test
    */
    public function it_can_match_a_pattern()
    {
        $this->builder->pattern("\s[a-z]{3}\s");
        $text = $this->lorem;

        $this->assertEquals(" sed ", $this->builder->match($text));
    }

    /**
    * @test
    */
    public function it_can_match_symbols()
    {
        $this->builder->symbols("mmod");
        $text = $this->lorem;

        $this->assertEquals("mmod", $this->builder->match($text));
    }

    /**
    * @test
    */
    public function it_can_match_special_operators()
    {
        $this->builder
        ->digit()
        ->notDigit()
        ->whitespace()
        ->notWhitespace()
        ->space()
        ->char()
        ->notChar();

        $text = "NOTPATTERN1e a a!NOTPATTERN";

        $this->assertEquals(
            "1e a a!",
            $this->builder->match($text)
        );
    }


    /**
    * @test
    */
    public function it_can_match_one_or_more_of_something()
    {
        $this->builder
        ->symbols("z")
        ->oneOrMore();

        $text = $this->leadspeak;

        $this->assertEquals(
            "zzz",
            $this->builder->match($text)
        );
    }

    /**
    * @test
    */
    public function it_can_match_zero_or_more_of_something()
    {
        $this->builder
        ->symbols("sp3@k")
        ->zeroOrMore();

        $text = $this->leadspeak;

        $this->assertEquals(
            "sp3@k",
            $this->builder->match($text)
        );

        $this->builder
        ->release()
        ->symbols("t3st ")
        ->symbols("izzz")
        ->capture()
        ->zeroOrMore();

        $this->assertEquals(
            "t3st izzzizzz",
            $this->builder->match($text)
        );
    }

    /**
    * @test
    */
    public function it_can_match_a_word()
    {
        $this->builder->word("aliqua");
        $text = $this->lorem;

        $this->assertEquals("aliqua", $this->builder->match($text));
    }



    /**
    * @test
    */
    public function it_can_match_several_words()
    {
        $this->builder->words(["eiusmod", "consequat", "ex"]);
        $text = $this->lorem;

        $this->assertEquals([
            "eiusmod", "consequat", "ex", "ex"
        ], $this->builder->matchAll($text));
    }

    /**
    * @test
    */
    public function it_can_make_capture_groups()
    {
        $pattern = $this->builder->group("a-zA-Z");

        $this->assertEquals("[a-zA-Z]", (string) $pattern);

        $pattern = $this->builder->release()->group(function ($query) {
            return "a-zA-Z";
        });

        $this->assertEquals("[a-zA-Z]", (string) $pattern);
    }

    /**
    * @test
    */
    public function it_can_create_capture_groups()
    {
        $pattern = $this->builder
        ->symbols("test")
        ->capture();

        $this->assertEquals("(test)", (string) $pattern);

        $pattern = $this->builder
        ->release()
        ->capture(function ($query) {
            return $query->symbols("test");
        });

        $this->assertEquals("(test)", (string) $pattern);
    }

    /**
    * @test
    */
    public function it_can_create_capture_groups_using_start_and_end()
    {
        $pattern = $this->builder
        ->startCapture()
        ->symbols("test")
        ->endCapture();

        $this->assertEquals("(test)", (string) $pattern);
    }

    /**
    * @test
    */
    public function it_can_create_look_behinds()
    {
        $this->builder
        ->beginsWith(function ($query) {
            return $query->symbols("pariatur")->space();
        })
        ->symbols("aliqua");

        $text = $this->lorem;

        $this->assertEquals("aliqua", $this->builder->match($text));
    }

    /**
    * @test
    */
    public function it_can_create_look_aheads()
    {
        $this->builder
        ->symbols("aliqua")
        ->endsWith(function ($query) {
            return $query->space()->symbols("sed");
        });

        $text = $this->lorem;

        $this->assertEquals("aliqua", $this->builder->match($text));
    }

    /**
    * @test
    */
    public function it_can_make_optional_symbols_using_a_string()
    {
        $this->builder->word("consequat")->optional("qu");
        $text = $this->lorem;

        $this->assertEquals(
            ["consequat", "conseat"],
            $this->builder->matchAll($text)
        );
    }

    /**
    * @test
    */
    public function it_can_make_optinal_symbols_using_substring_syntax()
    {
        $this->builder->word("consequat")->optional(5, 2);
        $text = $this->lorem;

        $this->assertEquals(
            ["consequat", "conseat"],
            $this->builder->matchAll($text)
        );
    }

    /**
    * @test
    */
    public function it_can_escape_special_characters()
    {
     $this->builder->symbols("\/\/!t|-|");

     $text = $this->leadspeak;

     $this->assertEquals(
        "\/\/!t|-|",
        $this->builder->match($text)
    );
 }

    /**
    * @test
    */
    public function it_can_replace_values()
    {
        $this->builder
        ->symbols("sp3@k");

        $text = $this->leadspeak;
        str_replace("sp3@k", "new", $text);

        $this->assertEquals(
            str_replace("sp3@k", "new", $text),
            $this->builder->replace("new", $text)
        );
    }
}
