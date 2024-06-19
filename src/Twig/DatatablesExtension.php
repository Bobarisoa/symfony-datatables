<?php

namespace Prolyfix\SymfonyDatatablesBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Environment;
use Symfony\Contracts\Translation\TranslatorInterface;

class DatatablesExtension extends AbstractExtension
{
    private $translator;
    private $twig;
    private $params;
    private $security;

    private EntityManagerInterface $entityManager;
    public function __construct(TranslatorInterface $translator, Environment $twig, ParameterBagInterface $params, Security $security, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->params = $params;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }
    public function getFunctions()
    {
        return [
            new TwigFunction('generateDatatableDOM', [$this, 'generateDatatableDOM']),
            new TwigFunction('generateFilter', [$this, 'generateFilter']),
            new TwigFunction('generateChoiceFromEntity', [$this, 'generateChoiceFromEntity']),            
        ];
    }

    public function generateDatatableDOM(string $name, string $entity, Array $columns, Array $params = []):string
    {
        $output='<table id="'.$name.'" data-hello-target="'.$name.'" class="table align-items-center table-flush datatables" data-params=\'[';
        $params['entity']=$entity;
        $output .= json_encode($params).']\' ';
        $output .= 'data-url="/datatables/requestItems"';
        foreach($columns as $key=>$column){
            $columns[$key]['targets'] = $key;
        }
        $output .= 'data-columndef=\''.json_encode($columns).'\' >';
        $output .= '<thead><tr>';
        foreach($columns as $column){
            $output .= '<th>'.$this->translator->trans('dt.columns.'.isset($column['label'])?$column['label']:$column['name']).'</th>';
        }
        $output.='</tr></thead>';
        if(isset($params['footer']))
            $output.= '<tfoot>'.$params['footer'].'</tfoot>';
        $output .= '</table>';
        return $output;

    }
    public function generateChoiceFromEntity($filter){
        if(isset($filter['filter']))
            $entities = $this->entityManager->getRepository($filter['repository'])->findBy($filter['filter']);
        elseif(isset($filter['queryMethod'])){    
            $function = $filter['queryMethod'];
            $entities = $this->entityManager->getRepository($filter['repository'])->$function();
        }
        else 
            $entities = $this->entityManager->getRepository($filter['repository'])->findAll();
        $output = [];
        foreach($entities as $entity){
            if(method_exists($entity,'getParent') && $entity->getParent() == null)
                $output[$entity->getId()] = $entity->getParent()->getName().": ".$entity->getName();
            else
                $output[$entity->getId()] = $entity->getName();
        }
        return $output;

    }
    public function generateFilter($entity): string
    {
        $rc = new \ReflectionClass($entity);
        if( !array_key_exists(str_replace('Proxies\__CG__\\', '', $rc->getName()), $this->params->get('filter')))
            return "";
        $filterAll = $this->params->get('filter')[str_replace('Proxies\__CG__\\', '', $rc->getName())];
        $filter = isset($filterAll[$this->security->getUser()->getRoles()[0]])?$filterAll[$this->security->getUser()->getRoles()[0]]:$filterAll['ROLE_USER'] ;
        return $this->twig->render('@ProlyfixSymfonyDatatables/filter.html.twig',['filters' => $filter]);
    }
    
    
}
