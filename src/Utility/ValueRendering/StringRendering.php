<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Symfony\Component\PropertyAccess\PropertyAccess;

class StringRendering implements ValueRenderingInterface{
    
    public function renderValue($entity, string $key, ValueRenderingManager $valueRenderingManager, $option = []):string
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        return $propertyAccessor->getValue($entity, $key)??'';
        
    }
}
