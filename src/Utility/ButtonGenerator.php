<?php

namespace Prolyfix\SymfonyDatatablesBundle\Utility;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


use Prolyfix\SymfonyDatatablesBundle\Utility\DynamicComparaison;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ButtonGenerator
{
    private $params;
    
    public function __construct(UrlGeneratorInterface $router, Security $security, ParameterBagInterface $params)
    {
        $this->router = $router;
        $this->params = $params;
        $this->user = $security->getUser();
    }
    
  
    
    use DynamicComparaison;
    /**
     *
     * @var Symfony\Component\Routing\Generator\UrlGeneratorInterface;
     */
    private $router;
    
    /**
     *
     * @var App\Entity\User;
     */
    private $user;
    

    public function dispatchGenerator($entity)
    {   
        $output = '<div class="dropdown">
                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="fas fa-ellipsis-v"></i>
                        </a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow" style="">';
                        

 
        $rc = new \ReflectionClass($entity);
        if( !array_key_exists(str_replace('Proxies\__CG__\\', '', $rc->getName()), $this->params->get('button_generator')))
            return "";
        $actions = $this->params->get('button_generator')[str_replace('Proxies\__CG__\\', '', $rc->getName())][$this->user->getRoles()[0]];
        foreach ($actions as $action){
            if(!array_key_exists('conditions', $action) || $this->validateConditions($entity, $action)) {
                $output .= $this->generateButton($action, $entity);
            }
        }
        
        return $output.'</div></div>';
    }
    
    protected function validateConditions(Object $entity, array $actions):bool
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach($actions['conditions'] as $condition){
            $var = $propertyAccessor->getValue($entity, $condition['key']);
            if(!$this->is($var, $condition['operator'], $condition['value'])) {
                    return false;
            }
        }
        return true;
    }
    
    protected function generateButton( array $values, Object $entity): string
    {
        if(array_key_exists('isModal',$values) && $values['isModal'] == true )
            return  '<a onclick="showFormModal(\''.$this->router->generate($values['route'], ['id'=>$entity->getId()]).'\')"> '.$values['classIcon'].'</a>';
        if(array_key_exists('isAction',$values) && $values['isAction'] == true )
            return  '<a onclick="fectchAction(\''.$this->router->generate($values['route'], ['id'=>$entity->getId()]).'\')"> '.$values['classIcon'].'</a>';
        $output ='<a class="dropdown-item" href="'.$this->router->generate($values['route'], ['id'=>$entity->getId()]).'">'.$values['classIcon'].'</a>';
        return $output;        
    }
    
    public function generateCustomerActionButtons(string $id): string
    {
        $output = '';
        $output .= '<div data-action="click->hello#next" data-request-id="'.$this->router->generate('customer_demande_detail', ['id'=>$id]).'" >  <i class="bi bi-eye-fill"></i></div>';
        return $output;
    }
    
    public function generateStatut(?string $statut): string
    {
        switch ($statut){
        case 'open':
            return '<span style="font-size:20px; color:green">&#9679;</span>';
                break;
        case 'litige':
            return '<span style="font-size:20px; color:green">&#9679;</span>';
                break;
        }
        return '<span style="font-size:20px; color:green">&#9679;</span>';
    }
    
    
}
