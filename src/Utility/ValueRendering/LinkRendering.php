<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Symfony\Component\PropertyAccess\PropertyAccess;

class LinkRendering implements ValueRenderingInterface{
    
    public function renderValue($entity, string $key2, ValueRenderingManager $valueRenderingManager, $option = []):string
    {
        $output = "";
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $params = [];
        $ok = true;
        foreach($option['params'] as $key => $value){
            $params[$key] = $propertyAccessor->getValue($entity, $value)??'[]';
        }
        $content = $valueRenderingManager->renderValue($entity,$key2, $option);
        $url = $valueRenderingManager->getRouter()->generate($option['path'],$params);
        return '<a href="'.$url.'"> '.$content.'</a>';
        
    }
}
