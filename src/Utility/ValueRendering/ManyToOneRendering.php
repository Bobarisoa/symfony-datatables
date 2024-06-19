<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ValueRenderingManager;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\StringRendering;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ManyToManyRendering;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ManyToOneRendering implements ValueRenderingInterface{
    
    public function renderValue($entity, string $key, ValueRenderingManager $valueRenderingManager, $option = []):string
    {
        //TODO: ici doublon, regarder pour avoir qqchse de plus simple.
        //ATTENTION: trouver une solution pour le manytoone et le proxy
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $keys = explode(".", $key);
        $entity2 = $propertyAccessor->getValue($entity, $keys[0]);
        if($entity2 == null) return "<i class='novalue'> aucune valeur </i>";
        // Attention il me pond un proxy. donc obliger de passer par le get.
        $functionName= "get".ucfirst($keys[1]);
        $type = $valueRenderingManager->getType($entity2,$keys[1]);
        array_shift($keys);
        return $valueRenderingManager->valueType[$type]->renderValue($entity2, implode(".",$keys) , $valueRenderingManager);
        //return $entity2->$functionName();
        
    }
}
