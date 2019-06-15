<?php declare(strict_types=1);

namespace Mrself\PHPUnit\Tests\Functional\ExtensionTrait;

use Mrself\PHPUnit\ExtensionTrait;
use PHPUnit\Framework\TestCase;

class CallMethodTest extends TestCase
{
    use ExtensionTrait;

    public function testPrivateMethodCanBeCalled()
    {
        $object = new class {
            private function method() {
                return 1;
            }
        };

        $actual = $this->callMethod($object, 'method');
        $this->assertEquals(1, $actual);
    }

    public function testMethodCanBeCalledWithArgumetns()
    {
        $object = new class {
            private function method($param) {
                return $param;
            }
        };

        $actual = $this->callMethod($object, 'method', [1]);
        $this->assertEquals(1, $actual);
    }
}