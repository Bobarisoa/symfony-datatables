<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ValueRenderingManager;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\StringRendering;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ManyToManyRendering;
use Symfony\Component\PropertyAccess\PropertyAccess;

class OneToOneRendering implements ValueRenderingInterface{
    
    public function renderValue($entity, string $key, ValueRenderingManager $valueRenderingManager, $option = []):string
    {
        //TODO: ici doublon, regarder pour avoir qqchse de plus simple.
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $keys = explode(".", $key);
        $entity2 = $propertyAccessor->getValue($entity, $keys[0]);
        // Attention il me pond un proxy. donc obliger de passer par le get.
        $functionName= "get".ucfirst($keys[1]);
        array_shift($keys);
        return $valueRenderingManager->renderValue($entity2,implode(".",$keys));
        
    }
}
