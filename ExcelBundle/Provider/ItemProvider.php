<?php

namespace Nik\ExcelBundle\Provider;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Nik\ExcelBundle\Exception\ClassNotFindException;
use Nik\ExcelBundle\Exception\NikEntityNotFoundException;

/**
 * Provide item for adaptor.
 *
 * Class ItemProvider
 * @package Nik\ExcelBundle\Provider
 */
final class ItemProvider
{
    private static $defaultValues;
    // Define that class was initialized at once or not.
    private static $initialized = false;

    /** @var  EntityManager */
    private static $em;


    private static function initialize(EntityManager $em)
    {
        if (self::$initialized) {
            return;
        }

        // Register entity manager.
        self::$em = $em;

        // Just initialize at once.
        self::$initialized = true;
    }

    /**
     * Create or return existing item.
     *
     * @param EntityManager $manager
     * @param $className
     * @param null $id
     *
     * @throws ClassNotFindException
     *
     * @return object
     */
    public static function getItem(EntityManager $manager, $className, $id = null)
    {
        self::initialize($manager);
        try {
            $item = self::$em->getRepository($className)->findOneBy(['id' => $id]);
        } catch (MappingException $exception) {
            throw new ClassNotFindException(sprintf('Class %s not find in mapping.', $className));
        }

        if (is_null($item)) {
            $item = self::createItem($className);
        }

        return $item;
    }

    /**
     * Set default value as array of key value.
     *
     * @param $defaultValues
     */
    public static function setDefaultValues(array $defaultValues)
    {
        self::$defaultValues = $defaultValues;
    }

    /**
     * Set default value to new object.
     *
     * @param $className
     * @return mixed
     * @throws NikEntityNotFoundException
     */
    private static function createItem($className)
    {
        $item = new $className;
        $reflector = new \ReflectionClass($className);

        foreach (self::$defaultValues as $methodName => $defaultValue) {
            // Build setter method.
            $setterMethod = 'set'.ucfirst($methodName);
            // TODO: Support more one parameters.
            /** @var \ReflectionParameter $parameter input parameter of setter method. */
            $parameter = $reflector->getMethod($setterMethod)->getParameters()[0];
            /** @var \ReflectionClass|null $parameterClass */
            $parameterClass = $parameter->getClass();
            // Parameter have object type hinting.
            if (!is_null($parameterClass)) {
                $id = $defaultValue;
                $parameterClass = $parameter->getClass()->getName();
                $defaultValue = self::$em->getRepository($parameterClass)->find($defaultValue);
                if (is_null($defaultValue)) {
                    throw new NikEntityNotFoundException(
                        sprintf('Class %s with id %s was not found.', $parameterClass, $id)
                    );
                }
            }

            $item->{$setterMethod}($defaultValue);
        }

        return $item;
    }
}