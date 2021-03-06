<?php

namespace Acts\CamdramApiBundle\Service;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Doctrine\Inflector\Rules\English\InflectorFactory;

class EntityUrlGenerator
{
    private $accessor;
    private $inflector;
    private $router;

    private static $class_map = array(
        'TechieAdvert' => 'techie'
    );

    public function __construct(RouterInterface $router)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->inflector = (new InflectorFactory())->build();
        $this->router = $router;
    }

    private function getRouteResourceName($class)
    {
        $class = new \ReflectionClass($class);
        $className = $class->getShortName();
        if (isset(self::$class_map[$className])) {
            return self::$class_map[$className];
        } else {
            return strtolower($className);
        }
    }

    public function getCollectionRoute($class)
    {
        $route = 'get_' . $this->inflector->pluralize($this->getRouteResourceName($class));
        if ($this->router->getRouteCollection()->get($route) === null) {
            throw new \InvalidArgumentException('That entity does not have a corresponding collection route: ' . $route);
        }

        return $route;
    }

    public function getRouteAndEntity($entity)
    {
        $route = 'get_' . $this->getRouteResourceName($entity);
        if ($this->router->getRouteCollection()->get($route) !== null) {
            return [$route, $entity];
        }
        else if ($show = $this->accessor->getValue($entity, 'show')) {
            return ['get_show', $show];
        }
    }

    public function getIdentifier($entity)
    {
        try {
            $id = $this->accessor->getValue($entity, 'slug');
        } catch (NoSuchPropertyException $e) {
            $id = $this->accessor->getValue($entity, 'id');
        }

        return $id;
    }

    public function generateUrl($entity, $format = null)
    {
        list($route, $routeEntity) = $this->getRouteAndEntity($entity);

        return $this->router->generate($route, array(
            'identifier' => $this->getIdentifier($routeEntity),
            '_format'     => $format
        ), true);
    }

    public function generateCollectionUrl($class, $format = null)
    {
        return $this->router->generate($this->getCollectionRoute($class), array('_format' => $format), true);
    }

    public function getDefaultUrl()
    {
        return $this->router->generate('acts_camdram_homepage');
    }
}
