<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ValueRenderingManager;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\StringRendering;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ManyToManyRendering;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ManyToOneRendering;
use Symfony\Component\PropertyAccess\PropertyAccess;

class OneToManyRendering implements ValueRenderingInterface{
    
    public function renderValue($entity, string $key, ValueRenderingManager $valueRenderingManager, $option = []):string
    {
        //TODO: ici doublon, regarder pour avoir qqchse de plus simple.
        $renderingManager = $valueRenderingManager;
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $str = new StringRendering();
        $dr = new DateRendering();
        $m2mr = new ManyToManyRendering();
        $m2or = new ManyToOneRendering();
        $renderingManager->addValueType('string', $str );
        $renderingManager->addValueType('date', $dr );
        $renderingManager->addValueType('Doctrine\ORM\Mapping\ManyToMany', $m2mr );
        $renderingManager->addValueType('Doctrine\ORM\Mapping\ManyToOne', $m2or );
        $keys = explode(".", $key);
        $entities2 = $propertyAccessor->getValue($entity, $keys[0]);
        $output ="<ul>";
        array_shift($keys);  
        if(is_numeric($keys[0])){
            $id = $keys[0];
            array_shift($keys);
            if(isset($entities2[$id]))
            return  $renderingManager->renderValue( $entities2[$id], implode(".",$keys));
            return "";
        }
        foreach( $entities2 as $entity2){
            $output .= "<li>".$renderingManager->renderValue( $entity2, implode(".",$keys),$option)."</li>";
        }
        $output .= "</ul>";
        return $output;
        
    }
}
