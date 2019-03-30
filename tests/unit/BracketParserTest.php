<?php 
class BracketParserTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var \voodooism\bracketparser\BracketParser */
    protected $bracketParser;
    
    protected function _before()
    {
        $this->bracketParser = new \voodooism\bracketparser\BracketParser();
    }

    protected function _after()
    {
    }

    /**
     * @dataProvider getMakePatternVariants
     * @param $symbols
     * @param $pattern
     */
    public function testMakePatten($symbols, $pattern)
    {
        $this->bracketParser->setForbiddenSymbols($symbols);

        expect('Method returns the correct pattern',
            $this->invokeMethod(
                $this->bracketParser,
                'makePattern'
            ))
            ->equals($pattern);

    }

    public function getMakePatternVariants()
    {
        return [
            ['symbols' =>['a', 'b', 'c'], 'pattern' => '/[abc]+/' ],
            ['symbols' =>[], 'pattern' => null ],
            ['symbols' =>['\n', '\r', '\t', ' '], 'pattern' => '/[\n\r\t ]+/' ],
            [
                'symbols' =>['^', '[', '.', '$', '{', '*', '(', '\\', '+', ')', '|', '?', '<',  '>', ']'],
                'pattern' => '/[\^\[\.\$\{\*\(\\\\\+\)\|\?\<\>\]]+/'
            ]
        ];
    }

    /**
     * @dataProvider getIsValidStringVariants
     * @param $symbols
     */
    public function testIsValidString($symbols, $string, $result)
    {
        $this->bracketParser->setForbiddenSymbols($symbols);

        expect('Method returns whether the string ' . $string . ' is valid',
            $this->invokeMethod(
                $this->bracketParser,
                'isValidString',
                [$string]
            ))
            ->equals($result);

    }

    public function getIsValidStringVariants()
    {
        return [
            ['symbols' =>['a', 'b', 'c'], 'string' => 'hello world', 'result' => true ],
            [
                'symbols' => [],
                'string' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!"#$%&\\\'() * +,-./:;<=>?@[\\] ^ _`{|}~' ,
                'result' => true,
            ],
            ['symbols' =>['\r', '\n', '\t', ' '], 'string' => "\thello\r\n world ", 'result' => false ],
            ['symbols' =>['a', 'b', 'c'], 'string' => 'hello abc', 'result' => false ],
            ['symbols' =>['a', 'b', 'c'], 'string' => 'a b c ', 'result' => false ],

        ];
    }

    /**
     * @dataProvider getParseStringVariants
     * @param $string
     * @param $result
     */
    public function testParseString($string, $result)
    {
        expect(
            'Method returns whether brackets are closed and opened correctly.',
            $this->bracketParser->parseString($string)
        )->equals($result);
    }

    public function getParseStringVariants()
    {
        return [
            ['string' => '', 'result' => true],
            ['string' => '()', 'result' => true],
            ['string' => '(s)(o)(m(e(t)(h)i)n)g!', 'result' => true],
            ['string' => ')))))', 'result' => false],
            ['string' => '(((((', 'result' => false],
            ['string' => '(()))))))', 'result' => false],
            ['string' => '(())(((((', 'result' => false],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testParseStringInvalidString()
    {
        $this->bracketParser->setForbiddenSymbols(['a']);
        $this->bracketParser->parseString('(((z)(y)(a))');
    }
    protected function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(\get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}