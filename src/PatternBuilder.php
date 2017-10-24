<?php

namespace RegexBuilder;

class PatternBuilder
{
    private $pattern = [];
    private $metaCharacters = '^[].${}*(\+)|/?<>';
    private $groupCharacters = "]";

    /**
     * Alias of pattern
     * @param  string $pattern
     * @return RegexBuilder\PatternBuilder
     */
    public function raw($pattern)
    {
        return $this->pattern($pattern);
    }


    /**
     * Adds a raw pattern to the pattern
     * @param string $pattern
     * @return RegexBuilder\PatternBuilder
     */
    public function pattern($pattern)
    {
        $this->pattern[] = $pattern;

        return $this;
    }

    /**
     * Words or symbols to match
     * @param  string $symbols the symbols to match
     * @return RegexBuilder\PatternBuilder
     */
    public function symbols($symbols)
    {
        $this->pattern[] = $this->escape($symbols);

        return $this;
    }

    /**
     * Alias of the symbols method
     * @param  string $symbol the symbols to match
     * @return RegexBuilder\PatternBuilder
     */
    public function symbol($symbol)
    {
        return $this->symbols($symbol);
    }


    /**
     * Matches a digit character
     * @return RegexBuilder\PatternBuilder
     */
    public function digit()
    {
        $this->pattern[] = "\d";

        return $this;
    }

    /**
     * Matches a non-digit characters
     * @return RegexBuilder\PatternBuilder
     */
    public function notDigit()
    {
        $this->pattern[] = "\D";

        return $this;
    }

    /**
     * Matches a whitespace character
     * @return RegexBuilder\PatternBuilder
     */
    public function whitespace()
    {
        $this->pattern[] = "\s";

        return $this;
    }

    /**
     * matches a non whitespace character
     * @return RegexBuilder\PatternBuilder
     */
    public function notWhitespace()
    {
        $this->pattern[] = "\S";

        return $this;
    }

    /**
     * Matches a word character
     * @return RegexBuilder\PatternBuilder
     */
    public function char()
    {
        $this->pattern[] = "\w";

        return $this;
    }

    /**
     * Matches a non word character
     * @return RegexBuilder\PatternBuilder
     */
    public function notChar()
    {
        $this->pattern[] = "\W";

        return $this;
    }

    /**
     * Matches a hex digit character
     * @return RegexBuilder\PatternBuilder
     */
    public function hexDigit()
    {
        $this->pattern[] = "\x";

        return $this;
    }

    /**
     * Matches a octal digit character
     * @return RegexBuilder\PatternBuilder
     */
    public function octalDigit()
    {
        $this->pattern[] = "\O";

        return $this;
    }

    /**
     * Matches a new line character
     * @return RegexBuilder\PatternBuilder
     */
    public function newLine()
    {
        $this->pattern[] = "\n";

        return $this;
    }

    /**
     * Matches a carrage return character
     * @return RegexBuilder\PatternBuilder
     */
    public function carriageReturn()
    {
        $this->pattern[] = "\r";

        return $this;
    }

    /**
     * Matches a tab character
     * @return RegexBuilder\PatternBuilder
     */
    public function tab()
    {
        $this->pattern[] = "\t";

        return $this;
    }

    /**
     * Matches a vertical tab character
     * @return RegexBuilder\PatternBuilder
     */
    public function verticalTab()
    {
        $this->pattern[] = "\v";

        return $this;
    }

    /**
     * Matches a form feed character
     * @return RegexBuilder\PatternBuilder
     */
    public function formFeed()
    {
        $this->pattern[] = "\f";

        return $this;
    }

    /**
     * Matches a space character
     * @return RegexBuilder\PatternBuilder
     */
    public function space()
    {
        $this->pattern[] = " ";

        return $this;
    }

    /**
     * Adds one or more of the previous group, character set or character
     * @return RegexBuilder\PatternBuilder
     */
    public function oneOrMore()
    {
        $this->pattern[] = "+";

        return $this;
    }

    /**
     * Adds zero or more of the previous group, character set or character
     * @return [type] [description]
     */
    public function zeroOrMore()
    {
        $this->pattern[] = "*";

        return $this;
    }

    /**
     * Matches any symbol
     * @return RegexBuilder\PatternBuilder
     */
    public function any()
    {
        $this->pattern[] = ".";

        return $this;
    }

    /**
     * Specifies the number of previous group, character set or character
     * @param  int $start sets the minimum number of previous caracters to match if two parameters, or the precise number if one parameter
     * @param  int $end (optional) sets the maximum number of previour characters to match
     * @return RegexBuilder\PatternBuilder
     */
    public function count()
    {
        $args = func_get_args();

        $this->pattern[] = "{" . implode(",", $args) . "}";

        return $this;
    }

    /**
     * Sets a character or digit range
     * @param  mixed $start where to start
     * @param  mixed $end where to end
     * @return RegexBuilder\PatternBuilder
     */
    public function range($start, $end)
    {
        $this->pattern[] = $start . "-" . $end;

        return $this;
    }

    /**
     * Matches a word, either specified in $word or any word
     * @param  string $word (optional) the woprd to match
     * @return  RegexBuilder\PatternBuilder
     */
    public function word($word = null)
    {
        if (gettype($word) == "array") {
            $this->pattern[] = "(" . implode("|", $word) . ")";
        } else {
            $this->pattern[] = $word ? $word : "\w+";
        }

        return $this;
    }

    /**
     * Matches a non word
     * @return  RegexBuilder\PatternBuilder
     */
    public function notWord()
    {
        $this->pattern[] = "\W+";

        return $this;
    }

    /**
     * Matches a sequens of words
     * @param  mixed $words (optional) the words to match
     * @return [type]        [description]
     */
    public function words($words = null)
    {
        if (gettype($words) == "array") {
            $this->pattern[] = "(" . implode("|", $words) . ")";
        } else {
            $this->pattern[] = $words ?? "[\s\w]+?";
        }

        return $this;
    }

    /**
     * Matches a character group
     * @param  mixed $args callback or pattern
     * @return RegexBuilder\PatternBuilder
     */
    public function group($args)
    {
        $pattern = $this->callbackOrPattern($args);

        $pattern = $this->escapeGroup($pattern);

        $this->pattern[] = "[$pattern]";

        return $this;
    }

    /**
     * Matches previous pattern in a group
     * @param  (RegexBuilder\PatternBuilder) $callback the callback
     * @return RegexBuilder\PatternBuilder
     */
    public function capture($callback = null)
    {
        if ($callback && gettype($callback) == "object") {
            $pattern = $callback(new static);
        } else {
            $pattern = array_pop($this->pattern);
        }

        $this->pattern[] = "(" . $pattern . ")";

        return $this;
    }

    /**
     * Starts a capture group
     * @return RegexBuilder\PatternBuilder
     */
    public function startCapture()
    {
        $this->pattern[] = "(";

        return $this;
    }

    /**
     * Ends a capture group
     * @return RegexBuilder\PatternBuilder
     */
    public function endCapture()
    {
        $this->pattern[] = ")";

        return $this;
    }

    /**
     * Sets a look behind pattern
     * @param  mixed $arg Callback or pattern
     * @return RegexBuilder\PatternBuilder
     */
     public function behind($arg)
    {
        $pattern = $this->callbackOrPattern($arg);

        $this->pattern[] = "(?<=$pattern)";

        return $this;
    }

    /**
     * Alias of behind()
     * @param  mixed $arg Callback or pattern
     * @return RegexBuilder\PatternBuilder
     */
     public function beginsWith($arg)
    {
        return $this->behind($arg);
    }

    /**
     * Alias of behind()
     * @param  mixed $arg Callback or pattern
     * @return RegexBuilder\PatternBuilder
     */
    public function before($arg)
    {
        return $this->behind($arg);
    }

    /**
     * Sets a look ahead pattern
     * @param  mixed $arg Callback or pattern
     * @return RegexBuilder\PatternBuilder
     */
     public function after($arg)
    {
        $pattern = $this->callbackOrPattern($arg);

        $this->pattern[] = "(?=$pattern)";

        return $this;
    }

    /**
     * Alias of after()
     * @param  mixed $arg Callback or pattern
     * @return RegexBuilder\PatternBuilder
     */
    public function endsWith($arg)
    {
        return $this->after($arg);
    }

    /**
     * Makes the previous group, character set or character optional
     * @param  mixed $start (optional) Start of replace if two params or the string to make optional if one param
     * @param int $end (optional) End of replace
     * @return RegexBuilder\PatternBuilder
     */
    public function optional()
    {
        $args = func_get_args();
        $pattern = array_pop($this->pattern);

        if (count($args) == 2) {
            $pattern = $this->optionalSubstring($pattern, $args[0], $args[1]);
        } elseif (count($args) == 1) {
            $pattern = $this->optionalSymbols($pattern, $args[0]);
        } else {
            $pattern .= "?";
        }

        $this->pattern[] = $pattern;

        return $this;
    }

    /**
     * Makes a part of the pattern optional using a string
     * @param  string $pattern the pattern
     * @param  string $optional what to make optional
     * @return string the new pattern
     */
    public function optionalSymbols($pattern, $optional)
    {
        return str_replace(
                $optional,
                $this->optionalCaptionGroup($optional),
                $pattern
            );
    }

    /**
     * Makes part of the pattern using substr
     * @param  string $pattern the pattern
     * @param  int $start where to start
     * @param  int $end where to end
     * @return string          the new pattern
     */
    public function optionalSubstring($pattern, $start, $end)
    {
        $subject = substr($pattern, $start, $end);
        $optional = $this->optionalCaptionGroup($subject);

        return substr($pattern, 0, $start)
            . $optional
            . substr($pattern, $end + $start, strlen($pattern) -1);
    }


    /**
     * Escapes a pattern
     * @param  string $pattern the pattern to escape
     * @return string the escaped pattern
     */
    public function escape($pattern)
    {
        $pattern = array_map(function ($char) {
            return in_array($char, str_split($this->metaCharacters))
                ? "\\$char"
                : $char;
        }, str_split($pattern));

        return implode($pattern);
    }

    /**
     * Escapes a group
     * @param  string $pattern the group characters to escape
     * @return string the escaped characters
     */
    protected function escapeGroup($pattern)
    {
        $pattern = array_map(function ($char) {
            return in_array($char, str_split($this->groupCharacters))
                ? "\\$char"
                : $char;
        }, str_split($pattern));

        return implode($pattern);
    }


    /**
     * Makes a pattern an optional caption group
     * @param  mixed $pattern PatternBuilder
     * @return string the pattern wrapped in a optional caption group
     */
    protected function optionalCaptionGroup($arg)
    {
        $pattern = $this->callbackOrPattern($arg);

        return "(?:" . $pattern . ")?";
    }

    /**
     * Matches the built up pattern and return only the match
     * @param  string $string the subject
     * @return array the match
     */
    public function match($string)
    {
        $match = $this->matchWithGroups($string);

        return empty($match) ? false : $match[0];
    }

    /**
     * Matches all of the build up pattern and returns the full output
     * @param  string $string the subject
     * @return array the match
     */
    public function matchWithGroups($string)
    {
        preg_match($this->getPattern(), $string, $output);

        return $output;
    }


    /**
     * Matches all of the built up pattern and returns only the match
     * @param  string $string the subject
     * @return mixed the match
     */
    public function matchAll($string)
    {
        $match = $this->matchAllWithGroups($string);

        return empty($match) ? false : $match[0];
    }

    /**
     * Matches all of the build up pattern and returns the full output
     * @param  string $string the subject
     * @return array
     */
    public function matchAllWithGroups($string)
    {
        preg_match_all($this->getPattern(), $string, $output);

        return $output;
    }


    /**
     * Replaces the matches
     * @param  string $string what to replace with
     * @param  string $subject what to replace in
     * @return string the replaced string
     */
    public function replace($string, $subject)
    {
        return preg_replace($this->getPattern(), $string, $subject);
    }

    /**
     * Returns the built up pattern
     * @return string pattern
     */
    public function getPattern()
    {
        return "/" . implode($this->pattern) . "/";
    }

   /**
     * Calls the callback if param is a callback, or return the pattern if a string is provided
     * @param  mixed $args Callback or pattern
     * @return mixed
     */
    protected function callbackOrPattern($args)
    {
        return gettype($args) == "object"
            ? $args(new static)
            : $args;
    }

    /**
     * Removes everything from pattern
     * @return RegexBuilder\PatternBuilder
     */
    public function release()
    {
        $this->pattern = [];

        return $this;
    }


    /**
     * Convert the pattern to a string
     * @return string pattern
     */
    public function __toString()
    {
        return implode($this->pattern);
    }
}
