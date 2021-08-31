<?php

namespace Grixu\Synchronizer\Helpers;

use Grixu\Synchronizer\Process\Exceptions\InterfaceNotImplemented;
use ReflectionClass;

class CheckInterface
{
    public function __invoke(string $className, string $interfaceName)
    {
        $classReflection = new ReflectionClass($className);

        $interfacesImplemented = array_keys($classReflection->getInterfaces());
        $isInterfaceImplemented = in_array($interfaceName, $interfacesImplemented);

        if (!$isInterfaceImplemented) {
            throw new InterfaceNotImplemented();
        }
    }
}
