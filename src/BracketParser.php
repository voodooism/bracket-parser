<?php

namespace voodooism\bracketparser;

class BracketParser
{
    private $forbiddenSymbols = [];
    private const META_SYMBOLS = ['^', '[', '.', '$', '{', '*', '(', '\\', '+', ')', '|', '?', '<',  '>', ']'];

    /**
     * BracketParser constructor.
     * @param array|null $forbiddenCharacters
     */
    public function __construct(array $forbiddenCharacters = null)
    {
        if ($forbiddenCharacters) {
            $this->setForbiddenSymbols($forbiddenCharacters);
        }
    }

    /**
     * @param string $character
     */
    public function delForbiddenSymbol(string $character)
    {
        $this->forbiddenSymbols = array_filter($this->forbiddenSymbols, function ($element) use ($character) {
            return ($element !== $character);
        });
    }

    /**
     * @param string $character
     */
    public function addForbiddenSymbol(string $character)
    {
        $this->forbiddenSymbols[] = $character;
        $this->forbiddenSymbols = array_unique($this->forbiddenSymbols);
    }
    /**
     * @param array $forbiddenSymbols
     */
    public function setForbiddenSymbols(array $forbiddenSymbols)
    {
        array_map(function ($element) {
            if (!is_string($element)) {
                throw new \InvalidArgumentException('Forbidden characters can only be a string type');
            }
        }, $forbiddenSymbols);
        $this->forbiddenSymbols = array_unique($forbiddenSymbols);
    }

    /**
     * @return array
     */
    public function getForbiddenSymbols()
    {
        return $this->forbiddenSymbols;
    }

    /**
     * @param string $string
     * @return bool
     */
    public function parseString(string $string)
    {
        if (empty($string)) {
            return true;
        }

        if (!$this->isValidString($string)) {
            throw new \InvalidArgumentException('Given string has incorrect symbol');
        }

        $stringLength = strlen($string);
        $balance = 0;
        for ($i = 0; $i < $stringLength ; $i++) {
            if ($string[$i] === '(') {
                $balance++;
            } else if ($string[$i] === ')') {
                $balance--;
            }
            if ($balance < 0) {
                return false;
            }
        }

        return $balance === 0;
    }

    /**
     * @param string $string
     * @return bool
     */
    private function isValidString(string $string)
    {
        $pattern = $this->makePattern();
        return $pattern ? !preg_match($pattern, $string) : true;
    }

    /**
     * @return string|null
     */
    private function makePattern() {
        $pattern = '';
        foreach ($this->forbiddenSymbols as $character) {
            if (\in_array($character, self::META_SYMBOLS, true)) {
                $character = '\\' . $character;
            }
            $pattern .= $character;
        }
        return empty($pattern) ? null :  '/[' . $pattern . ']+/';
    }
}