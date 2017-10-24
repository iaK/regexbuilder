# Regex builder

A fluent api that simplifies writing regular expressions. _(for the ones of us who always forget the syntax)_

## Installation

Grab it using composer
```shell
$ composer require iak/regexbuilder
```
```json
{
    "require": {
        "iak/regexbuilder": "^1.0"
    }
}
```

Simple as that :)

## Introduction

This library is for all of us that find regular expressions hard to write and impossible to remember all the different flags, look aheads, capture groups etc.

Instead of spending that half hour searching stackoverflow, I hope you can easily whip up a pattern using this lib.

Note. a basic understading of how regular expressions is still needed.

## Quick start

First of all, use the class at the top of you file,

```php
use RegexBuilder\Regex;
```

Now you can use it like this

```php
$string = "wow! this is cool!"

$match = Regex::word("wow")->symbol("!")->match($string); // wow!
```

Or maybe something more advanced (and demostrating some different ways of using the library)

```php

    // Match an email address

    $email = "info@isakberglind.se";

    Regex::group("a-z0-9_-.")
        ->oneOrMore()
        ->symbol("@")
        ->group("a-z0-9_-].")
        ->oneOrMore()
        ->symbol(".")
        ->group("a-z")
        ->count(2,6)
        ->match($email);

    // a simple url-matcher

    $url = "http://www.landslide-design.se";

    Regex::word(["http", "https", "ftp"])
        ->symbols("://")
        ->capture(function ($query) {
            return $query->symbols("www.");
        })
        ->optional()
        ->group("a-zA-Z0-9@:._")
        ->count(2, 255)
        ->symbols(".")
        ->group(function ($query) {
            return $query->range("a", "z");
        })
        ->count(2, 6)
        ->group(function ($query) {
            return $query
                ->range("a", "z")
                ->range("A", "Z")
                ->range(0, 9)
                ->symbols("@:_.?//");
        })
        ->zeroOrMore()
        ->match($url);
```

# Documentation

## Words, patterns and symbols

<br/>

#### word(mixed $word = null)

_Matches provided word, array of words or any word_

```php
    $string = "This is a hard example!";

    Regex::word("simple")->replace("simple", $string);   // This is a simple example

    Regex::word(["This", "simple", "example"])->matchAll($string); // ["this", "example"]

    Regex::word()->matchAll($string) // ["this", "is", "a", "hard", "example"]
```
<br/>

#### notWord()

_Matches anything but a word_

```php
    $string = "Hi!!!!! What's up?";

    Regex::notWord()->match($string); // '!!!! '
```
<br/>

#### symbols(string $symbols)

_Matches provided symbols (escapes string, if you don't want that, use "pattern")_

```php
    $string = "This is &!^@? awesome!"

    Regex::symbols("&!^@?")->replace("totally", $string) // This is totally awesome
```
<br/>

#### pattern(string $pattern)

_Matches provided pattern_
<br/>
_Aliases: raw()_

```php
    $string = "kickass example text";

    Regex::pattern("(example|text)")->matchAll($string); // ["example", "text"]
```

<br/>

## Characters

<br/>

You can match a bunch of characters using the following helper methods

```php
    Regex::digit();
    Regex::notDigit();
    Regex::whitespace();
    Regex::notWhitespace();
    Regex::char();
    Regex::notChar();
    Regex::hexDigit();
    Regex::octalDigit();
    Regex::newLine();
    Regex::carriageReturn();
    Regex::tab();
    Regex::verticalTab();
    Regex::formFeed();
    Regex::space();
    Regex::any();
```

<br/>

## Quantifiers

<br/>

#### oneOrMore()

_Matches one or more of preceding group, character or character set._

```php
    $string = "Here are some numbers 123456. Cool huh?"

    Regex::digit()->oneOrMore()->match($string) // 123456

```
<br/>

#### zeroOrMore()

_Matches zero or more_

```php
    $string = "AA A1A A12A";

    Regex::char()->digit()->zeroOrMore()->char()->matchAll($string) // ["AA", "A1A", "A12A"]

```
<br/>

#### count(int $count/$start, int $end = null)

_Matches the specified amount or the minimum and maximun count_

```php
    $string = "1 12 123 1234";

    // Specify the exact count to match..
    Regex::digit()->count(3)->match($string); // 123

    // Or a minimum and maximum..
    Regex::digit()->count(2,4)->matchAll($string); // [12, 123, 1234]
```

<br/>

## Groups & Character sets

<br/>

#### range(mixed $start, $mixed $end)

_Specifies a range, made especially for working with character sets_

```php
    Regex::range("a", "z"); // a-z

```
<br/>

#### group(mixed $pattern/$callback)

_Creates a character set_

```php
    // Using a callback

    Regex::group(function ($builder) {
        return $builder->range("a", "z");
    });

    // Using a raw pattern

    Regex::group("a-z");

    // Produces the same;  [a-z]
```

<br/>

## Capture groups

<br/>

#### capture(callable $callback = null)

_Creates a capture group_

```php
    $string = "Capture this if you can!";

    // you can either capture the previous statement..

    Regex::word("this")->capture();

    // .. or using a callback

    Regex::capture(function ($builder) {
        return $builder->word("this");
    });

    // Produces the same; (this)
```
<br/>

#### opionalCapture(mixed $pattern/$callback)

_Creates a non capturing group_

```php
    $string = "Do not capture this if you can!";

    // you can either capture the previous statement..

    Regex::word("this")->capture();

    // .. or using a callback

    Regex::capture(function ($builder) {
        return $builder->word("this");
    });

    // Produces the same; (?:this)?
```
<br/>

#### startCapture() _and_ endCapture()

_You can also surround what you want to capture with these methods_

```php
    $string = "Capture this if you can";

    Regex::startCapture()->word("this")->endCapture(); // (this)
```

<br/>

## Look aheads & look behinds

<br/>

#### behind(mixed $pattern/$callback)

_Creates a look behind_
<br/>
_Aliases: beginsWith(), before()_

```php
    $string = "important";

    // Using a callback..
    Regex::behind(function ($builder) {
        return $builder->symbols("");
    })
    ->word()
    ->match($string);

    // .. or a raw pattern..
    Regex::behind("\*\*\*\*")->word()->match($string);

    // important
```
<br/>

#### after(mixed $pattern/$callback)

_Creates a look ahead, works exactly like before()_
<br/>
_Aliases: endsWith()_

<br/>

## Other helpers

<br/>

#### optional(mixed $characters/$start = null, $length = null)

_Makes capture group, character set or character optional_

```php
    $string = "Is it spelled color or colour?";

    // Using a characters
    Regex::word("colour")->optional("u")->matchAll($string); // ["color", "colour"]

    // Using a start and a length
    Regex::word("colour")->optional(4,1)->matchAll($string); // ["color", "colour"]

    // Make last statement optinal

    Regex::symbols("colo")->char("u")->optional()->symbols("r")->matchAll($string); // ["color", "colour"]
```

<br/>

#### escape(string $pattern)

_Escapes provided pattern_

```php
    $pattern = "^[]$<";

    Regex::escape($pattern); // \^\[\]\$\<
```

<br/>

#### getPattern()

_Returs the built up pattern_

```php
    Regex::group("a-zA-Z")->oneOrMore()->symbols("!!")->optional()->zeroOrMore()->getPattern(); // /[a-zA-Z]+!!?*/
```

<br/>

#### release()

_Removes built up pattern_

```php
    Regex::group("a-z")->symbol("!")->release()->symbols("only this")->getPattern(); // /only this/
```

<br/>

## Matching and replacing

<br/>

#### replace($string, $subject)

_Replace built up pattern with provided string_

```php
    $string = "This is a hashtag: @. I'm sure!";

    Regex::symbol("@")->replace("#", $string); // This is a hashtag: #. I'm sure!
```

<br/>

#### match($string)

_Matches the first occurrence of the built up pattern_
<br/>
_Note! only return the match. If you want all capture groups, use matchWithGroups()_

```php
    $string = "Follow me on twitter: @Isak_Berglind!";

    Regex::symbol("@")->group("a-zA-Z_")->oneOrMore()->match($string); // @Isak_Berglind

```

<br/>

#### matchAll($string)

_Matches all of the occurences of the built up pattern_
<br/>
_Note! only return the match. If you want all capture groups, use matchAllWithGroups()_

```php
    $string = "this is as good as it gets";

    Regex::any()->symbol("s")->matchAll($string); // ["is", "is", "as", "as", "ts"]
```









