<?php declare(strict_types=1);

namespace Mrself\PHPUnit\Tests\Functional\ExtensionTrait;

use Mrself\PHPUnit\ExtensionTrait;
use PHPUnit\Framework\TestCase;

class AssertHasExceptionTest extends TestCase
{
    use ExtensionTrait;

    public function testItAssertsProperException()
    {
        $object = new class {
            function exception() {
                throw new \RuntimeException();
            }
        };

        $e = $this->_assertHasException([
            '_class' => \RuntimeException::class,
            '_callable' => [$object, 'exception']
        ]);
        $this->assertInstanceOf(\RuntimeException::class, $e);
    }

    /**
     * @expectedException \PHPUnit\Framework\ExpectationFailedException
     */
    public function testItFailsWithImproperException()
    {
        $object = new class {
            function exception() {
                throw new \Exception();
            }
        };

        $this->_assertHasException([
            '_class' => \RuntimeException::class,
            '_callable' => [$object, 'exception']
        ]);
    }

    public function testCallableCanBeWithArguments()
    {
        $object = new class {
            public $param;

            function exception($param) {
                $this->param = $param;
                throw new \RuntimeException();
            }
        };

        $this->_assertHasException([
            '_class' => \RuntimeException::class,
            '_callable' => [$object, 'exception', 1]
        ]);
        $this->assertEquals(1, $object->param);
    }

    /**
     * @expectedException \PHPUnit\Framework\ExpectationFailedException
     */
    public function testIfFailsIfExceptionFieldsDoNotMatch()
    {
        $object = new class {
            function exception() {
                throw new \Exception('Message');
            }
        };

        $this->_assertHasException([
            '_class' => \Exception::class,
            '_callable' => [$object, 'exception'],
            'message' => 'non-message'
        ]);
    }
}