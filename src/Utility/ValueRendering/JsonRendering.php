<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Symfony\Component\PropertyAccess\PropertyAccess;

class JsonRendering implements ValueRenderingInterface{
    
    public function renderValue($entity, string $key, ValueRenderingManager $valueRenderingManager, $option = []):string
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $values = $propertyAccessor->getValue($entity, $key)??'[]';
        $output = '<ul>';
        foreach($values as $key => $value){
            $output .= '<li>'.$value.'</li>';
        }
        $output .= '</ul>';
        return $output;
        
    }
}
