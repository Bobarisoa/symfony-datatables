<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Symfony\Component\PropertyAccess\PropertyAccess;

class ShowCrudRendering implements ValueRenderingInterface{
    
    public function renderValue($entity, string $key, ValueRenderingManager $valueRenderingManager, $option = []):string
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $value =  $propertyAccessor->getValue($entity, $key)??'';
        $className = get_class($entity);
        return "<a href='/backend/".$className."/".$entity->getId()."' >".$value."</a>";
    }
}
