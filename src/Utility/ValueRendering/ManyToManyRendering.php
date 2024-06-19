<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ValueRenderingManager;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\StringRendering;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ManyToManyRendering implements ValueRenderingInterface{
    
    public function renderValue($entity, string $key, ValueRenderingManager $valueRenderingManager, $option = []):string
    {
        //TODO: ici doublon, regarder pour avoir qqchse de plus simple.
        //$renderingManager = new ValueRenderingManager();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $str = new StringRendering();
        $m2mr = new ManyToManyRendering();
        $valueRenderingManager->addValueType('string', $str );
        $valueRenderingManager->addValueType('Doctrine\ORM\Mapping\ManyToMany', $m2mr );
        $keys = explode(".", $key);
        $entities2 = $propertyAccessor->getValue($entity, $keys[0]);
        $output ="<ul>";
        foreach( $entities2 as $entity2){
            $output .= "<li>".$valueRenderingManager->renderValue( $entity2, $keys[1])."</li>";
        }
        $output .= "</ul>";
        return $output;
        
    }
}
