<?php
/**
 * Defines some convenience methods for working with normally-inaccessible
 * properties and methods in a testing context.
 */

namespace Beporter\Tictactoe\Tests;

use \ReflectionClass;
use \ReflectionProperty;

/**
 * \Beporter\Tictactoe\Tests\ReflectionHelperTrait
 */
trait ReflectionHelperTrait
{
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object on which to execute the method.
     * @param string $methodName Method name to call.
     * @param array $parameters Array of parameters to pass into method.
     * @return mixed Method return value.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Call protected/private static method of a class.
     *
     * @param string $class Fully namespaced class name.
     * @param string $methodName Method name to call.
     * @param array $parameters Array of parameters to pass into method.
     * @return mixed Method return value.
     */
    public function invokeStaticMethod(string $class, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass($class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs(null, $parameters);
    }

    /**
     * Get a ReflectionProperty for a class and make it public.
     *
     * @param object $object The object from which to get the property.
     * @param string $property The name of the property to get.
     * @return \ReflectionProperty Returns the property.
     */
    private function getReflectionProperty($object, string $property)
    {
        $reflection = new ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty;
    }

    /**
     * Fetch the value of a protected or private class property from an object.
     *
     * @param object $object The object from which to get the property.
     * @param string $property The name of the property to get.
     * @return mixed Returns the property's value.
     */
    public function getProperty($object, $property)
    {
        $reflectionProperty = $this->getReflectionProperty($object, $property);

        return $reflectionProperty->getValue($object);
    }

    /**
     * Modifies a protected property of an object.
     *
     * @param object $object The object to modify.
     * @param string $property The name of the property to modify.
     * @param mixed $value The value to set.
     * @return void
     */
    public function setProperty(&$object, string $property, $value)
    {
        $reflectionProperty = $this->getReflectionProperty($object, $property);
        $reflectionProperty->setValue($object, $value);
    }
}
