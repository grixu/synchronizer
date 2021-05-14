<?php

namespace Grixu\Synchronizer\Config\Traits;

use Grixu\Synchronizer\Exceptions\InterfaceNotImplemented;
use ReflectionClass;

trait CheckClassImplementsInterface
{
    protected function checkClassIsImplementingInterface(string $className, string $interfaceName)
    {
        $classReflection = new ReflectionClass($className);

        $interfacesImplemented = array_keys($classReflection->getInterfaces());
        $isInterfaceImplemented = in_array($interfaceName, $interfacesImplemented);

        if (!$isInterfaceImplemented) {
            throw new InterfaceNotImplemented();
        }
    }
}
