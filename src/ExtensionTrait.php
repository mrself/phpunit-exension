<?php declare(strict_types=1);

namespace Mrself\PHPUnit;

use ICanBoogie\Inflector;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 */
trait ExtensionTrait
{
    /**
     * Asserts that callable throws exception
     * @param array $props
     *      _class: (string) Class of expected exception
     *      _callable: (array) What should be called and possible
     *                  can throws an exception.
     *                  If first element is object, second element should be
     *                  method from this object and rest of elements are
     *                  arguments to this method.
     *      Rest of $props are fields to check that thrown exception has
     * @return \Exception|null
     */
    protected function _assertHasException(array $props): ?\Exception
    {
        $inflector = Inflector::get();
        $class = $props['_class'];
        unset($props['_class']);
        $cbParams = $props['_callable'];
        unset($props['_callable']);

        try {
            $args = array_slice($cbParams, 2);
            call_user_func_array([$cbParams[0], $cbParams[1]], $args);
        } catch (\Exception $e) {
            $this->assertInstanceOf($class, $e);
            foreach ($props as $key => $value) {
                $method = 'get' . $inflector->camelize($key);
                $this->assertEquals($value, $e->$method());
            }
            return $e;
        }
        $this->assertTrue(false);

        return null;
    }

    /**
     * Calls non-accessible (private and protected) methods
     * @param $object Object with non-accessible method
     * @param string $method Method name
     * @param array $arguments Array of arguments
     * @return mixed
     * @throws \ReflectionException
     */
    protected function callMethod($object, string $method, array $arguments = [])
    {
        $class = new \ReflectionClass($object);
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $arguments);
    }
}