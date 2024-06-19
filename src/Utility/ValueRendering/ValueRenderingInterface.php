<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

interface ValueRenderingInterface {
    
  public function renderValue($entity, string $key, ValueRenderingManager $valueRenderingManager, array $options = []):string;
  
}