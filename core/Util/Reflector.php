<?php

namespace core\Util;


class Reflector
{
    /**
     * 构造任意类实例对象
     *
     * @param mixed $className
     * @param array $paramArr
     * @return object
     * @throws \ReflectionException
     */
    public static function make($className, array $paramArr = [])
    {
        $class = new \ReflectionClass($className);
        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return $class->newInstanceWithoutConstructor();
        } else {
            $args = [];
            foreach ($constructor->getParameters() as $param) {
                $paramClass = $param->getClass();
                $paramName = $param->getName();
                $args[] = $paramClass === NULL ?
                    $paramArr[$paramName] ?? $param->getDefaultValue() :
                    self::make($paramClass->getName(), $paramArr);
            }
            return empty($args) ? $class->newInstanceArgs() : $class->newInstanceArgs($args);
        }
    }

    /**
     * 执行任意类的方法
     *
     * @param mixed $className
     * @param string $method
     * @param array $paramArr
     * @return mixed
     * @throws \ReflectionException
     */
    public static function call($className, string $method, array $paramArr = [])
    {
        $args = [];
        $parameters = (new \ReflectionClass($className))->getMethod($method)->getParameters();
        if (!empty($parameters)) {
            foreach ($parameters as $param) {
                $paramClass = $param->getClass();
                $paramName = $param->getName();
                $args[] = $paramClass === NULL ? $paramArr[$paramName] : self::make($paramClass->getName(), $paramArr);
            }
        }
        if ((new \ReflectionClass($className))->getMethod($method)->isStatic()) {
            return $className::$method(...$args);
        } else {
            $class = self::make($className, $paramArr);
            return $class->$method(...$args);
        }
    }

    /**
     * 获取类方法参数列表
     *
     * @param mixed     $class
     * @param string    $method
     * @return array
     * @throws \ReflectionException
     */
    public static function getClassMethodArgsName($class, $method)
    {
        $parameters = (new \ReflectionClass($class))->getMethod($method)->getParameters();
        $args = [];
        if (!empty($parameters)) {
            foreach ($parameters as $parameter) {
                $args[] = $parameter->getName();
            }
        }
        return $args;
    }

    /**
     * 获取任意类属性值
     *
     * @param mixed $class
     * @param string $name
     * @return mixed
     * @throws \ReflectionException
     */
    public static function getPropertyValue($class, string $name)
    {
        $property = new \ReflectionProperty($class, $name);
        if ($property->isPrivate() || $property->isProtected()) {
            $property->setAccessible(true);
        }
        $value = $property->getValue();
        return $value;
    }

    /**
     * 设置任意类属性值
     *
     * @param $class
     * @param string $name
     * @param $value
     * @param void
     * @throws \ReflectionException
     */
    public static function setPropertyValue($class, string $name, $value, object $obj = null)
    {
        $property = new \ReflectionProperty($class, $name);
        if ($property->isPrivate() || $property->isProtected()) {
            $property->setAccessible(true);
        }
        $obj !== null ? $property->setValue($obj, $value) : $property->setValue($value);
    }

    /**
     * 获取方法参数列表
     *
     * @param mixed $function
     * @return array
     * @throws \ReflectionException
     */
    public static function getFunArgsName($function)
    {
        $fun = new \ReflectionFunction($function);
        $args = [];
        foreach ($fun->getParameters() as $param) {
            $args[] = $param->getName();
        }
        return $args;
    }
}