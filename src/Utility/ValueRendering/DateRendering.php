<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Symfony\Component\PropertyAccess\PropertyAccess;

class DateRendering implements ValueRenderingInterface{
    
    public function renderValue($entity, string $key, ValueRenderingManager $valueRenderingManager, $option = []):string
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $format = "d/m/Y";
        if(isset($option['format'])){
            $format = $option['format'];
        }
        return $propertyAccessor->getValue($entity, $key)==null?'':$propertyAccessor->getValue($entity, $key)->format($format)??'';
        
    }
}
