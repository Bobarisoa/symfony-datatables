<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\Exception;
use Prolyfix\SymfonyDatatablesBundle\Utility\ButtonGenerator;
use App\Entity\Sejour;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Annotations\AnnotationReader;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ValueRenderingManager;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\StringRendering;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ManyToManyRendering;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\OneToOneRendering;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ActionRendering;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class DatatablesConverter
{
    
    const HAS_STYLE = ['statut'=>[
            'delayed' => 'warning',
            'ligitation' => 'danger',
            'open' => 'success'
        ]];        
            
    /**
     *
     * @var array
     */
    private $schema;

    /**
     *
     * @var App\Utility\ButtonGenerator
     */
    private $bg;
    
        /**
     *
     * @var App\Entity\User;
     */
    private $user;
    
    private $router;
    
    private $translator;
    private $em;
    
    private $valueRenderingManager;
    
    private $parameterBagInterface;
    private $security;
    
    public function __construct(
        Request $params, 
        ButtonGenerator $bg, 
        TranslatorInterface $translator, 
        User $user, 
        UrlGeneratorInterface $router, 
        ParameterBagInterface $pbf, 
        Security $security,
        EntityManagerInterface $em,
        )
    {
        $schema = [];
        foreach($params->get('columns') as $key => $value){
            if(isset($params->get('request')[0]["testouille"][$key]['options'])){
                $schema[] = array_merge($value, ['options'=>$params->get('request')[0]["testouille"][$key]['options']]);
            }
            else
                $schema[] = $value;
        }
        $this->schema = $schema;
        $this->bg = $bg;
        $this->translator = $translator;
        $this->user = $user;
        $this->router = $router;
        $this->em = $em;
        //doublon
        $this->valueRenderingManager = new ValueRenderingManager( $this->em, $this->router, $this->translator, $pbf->get('valueRendering'));
        $this->parameterBagInterface = $pbf;
        $this->security = $security;
        
    }
    
    public function render(array $entities, $isExport):array
    {
        $output = [];
        $renderingManager = new ValueRenderingManager($this->em, $this->router, $this->translator, $this->parameterBagInterface->get('valueRendering'));
        $ar = new ActionRendering($this->router, $this->security, $this->parameterBagInterface);

        $renderingManager->init();
        $renderingManager->addValueType('_action', $ar );
        //TODO: doit rajouter le style;
        foreach($entities as $entity){
            $outputEntity = [];
             $i = 0;
            foreach($this->schema as $column){
                $key = $isExport ? $column['name'] : $i;
                $output2 = $renderingManager->renderValue($entity, $column['name'], array_key_exists('options',$column)?$column['options']:[]);
                $outputEntity[$key] = $this->translate($output2);
                $i++;
            }
            $output[] = $outputEntity;
        }
        return ['data'=>$output];
    }
    
    public function _action(Object $entity):string
    {
        return $this->bg->dispatchGenerator($entity);
    }

    
    public function _servePrivateFile($entity):string
    {
        return '<a href="/admin/serveImport/'.$entity->getPath().'">'.$entity->getPath().'</a>';
    }
    
    public function translate(?string $value):string
    {
        return  $this->translator->trans($value);
        
    }    
    public function addStyle(?string $value,$name):string
    {
        $class= self::HAS_STYLE[$name][$value]??"";
        $output = '<span class="'.$class.'">';
        $output .= $this->translator->trans($value);
        return $output."</span>";
    }
    
}
