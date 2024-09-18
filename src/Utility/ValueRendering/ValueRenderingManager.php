<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\StringRendering;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ValueRenderingInterface;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ManyToManyRendering;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\OneToManyRendering;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ManyToOneRendering;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\OneToOneRendering;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Prolyfix\SymfonyComplexIndexRelationBundle\Utility\CustomReader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Description of ValueRenderingManager
 *
 * @author MarcBaudot
 */
class ValueRenderingManager {
   
    public $valueType = [];
    private $importsRendering;
    private $container;

    private $translator;
    private $em;

    private $router;
    
    public function addValueType(string $key, ValueRenderingInterface $vr): self
    {
        $this->valueType[$key] = $vr;
         return $this;
    }
    
    public function getEm(): EntityManagerInterface{
        return $this->em;
    }
    public function getRouter(): UrlGeneratorInterface
    {
        return $this->router;
    }
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
    public function getType($entity, $key): string
    {
        $entity->getId();
        $className = str_replace("Proxies\__CG__\\","",get_class($entity));
        $entity = new $className;
        $reflectionClass = new \ReflectionClass($entity);
        $keys = explode(".",$key);
        $prop = $reflectionClass->getProperty(str_replace('_','',$keys[0]));
        //$docInfos = CustomReader::getPropertyAnnotations($reflectionClass->getProperty(str_replace('_','',$keys[0])));
        $attributes = $prop->getAttributes();
        if($attributes[0]->getName() == "Doctrine\ORM\Mapping\Column"){
            if(array_key_exists('type',$attributes[0]->getArguments()))
               return $attributes[0]->getArguments()['type'];
            return 'string';
        }else{
            return $attributes[0]->getName();
        }
    }
    
    public function renderValue($entity, $key, $option = []):string
    {


        //todo: si debut par _ appeler fonction directement.
        if(is_array($option) && array_key_exists('renderer',$option)){
            $valueType = $option['renderer']['action'];
            return $this->valueType[$valueType]->renderValue($entity,$key,$this,$option['renderer']);
        }
        if(is_array($option) && array_key_exists('rendererInside',$option)){
            $option['renderer'] = $option['rendererInside'];
        }        
        if(substr($key,0,1) == '_')
            return $this->valueType[$key]->renderValue($entity, $key, $this, $option);
        $type = $this->getType($entity,$key);
        return $this->valueType[$type]->renderValue($entity, $key , $this, $option);
        
    }
    
    public function init()
    {
        $str = new StringRendering();
        $bool = new BooleanRendering();
        $dr = new DateRendering();
        $m2mr = new ManyToManyRendering();
        $o2or = new OneToOneRendering();
        $m2or = new ManyToOneRendering();
        $o2mr = new OneToManyRendering();
        $jsonr = new JsonRendering();
        $vichUploaderr = new VichUploaderRendering();
        $linkr = new LinkRendering();

        $this->addValueType('string', $str );
        $this->addValueType('Doctrine\ORM\Mapping\Id', $str );
        $this->addValueType('text', $str );
        $this->addValueType('float', $str );
        $this->addValueType('integer', $str );
        $this->addValueType('json', $jsonr );
        $this->addValueType('boolean', $bool );
        $this->addValueType('link', $linkr );
        $this->addValueType('Doctrine\ORM\Mapping\ManyToMany', $m2mr );
        $this->addValueType('Doctrine\ORM\Mapping\OneToOne', $o2or );
        $this->addValueType('Doctrine\ORM\Mapping\ManyToOne', $m2or );
        $this->addValueType('Doctrine\ORM\Mapping\OneToMany', $o2mr );
        $this->addValueType('Vich\UploaderBundle\Mapping\Annotation\UploadableField',$vichUploaderr);
        $this->addValueType('date', $dr );
        $this->addValueType('datetime', $dr );
        foreach($this->importsRendering as $key => $value){
            if(is_array($value)){
                if(array_key_exists('arguments',$value)){
                    if($value['arguments'] == "EntityManagerInterface")
                        $function = new $value['class']($this->em);    
                }else{
                    $function = new ($value['class'])();
                }

                $this->addValueType($key, $function );
            }
        }
        
    }
    
    public function __construct( EntityManagerInterface $em, UrlGeneratorInterface $router, TranslatorInterface $translator ,?array $pbf = null) {
        $this->importsRendering = $pbf;
        $this->em = $em;
        $this->router = $router;
        $this->translator = $translator;
    }

    public function translate(?string $text = null): string
    {
        return $this->translator->trans($text);
    }
}
