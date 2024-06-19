<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Symfony\Component\PropertyAccess\PropertyAccess;

class BooleanRendering implements ValueRenderingInterface{
    
    public function renderValue($entity, string $key, ValueRenderingManager $valueRenderingManager, $option = []):string
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $value =  $propertyAccessor->getValue($entity, $key)??false;
        if($value){
            return '<i class="fas fa-check"></i>';
        }
        return '<i class="fas fa-times"></i>';
    }
}
