<?php

namespace Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Prolyfix\SymfonyDatatablesBundle\Utility\ValueRendering\ValueRenderingInterface;
use Prolyfix\SymfonyDatatablesBundle\Utility\DynamicComparaison;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ActionRendering implements ValueRenderingInterface {

    private $params;

    public function __construct(UrlGeneratorInterface $router, Security $security, ParameterBagInterface $params, array $options = []) {
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

    public function renderValue($entity, string $key = "", ValueRenderingManager $valueRenderingManager, $option = []): string {
        $uId = uniqid();
        $output = '<div class="dropdown">
              <a class="btn-icon-only text-light" type="button" id="'.$uId.'" data-bs-toggle="dropdown" aria-expanded="false" data-action="click->datatables#show">
    <i class="bi bi-three-dots"></i>
  </a>
  <ul class="dropdown-menu" aria-labelledby="'.$uId.'">';

        $rc = new \ReflectionClass($entity);
        if (!array_key_exists(str_replace('Proxies\__CG__\\', '', $rc->getName()), $this->params->get('button_generator')))
            return "";
        $actions = $this->params->get('button_generator')[str_replace('Proxies\__CG__\\', '', $rc->getName())][$this->user->getRoles()[0]];
        foreach ($actions as $action) {
            if (!array_key_exists('conditions', $action) || $this->validateConditions($entity, $action)) {
                $output .= $this->generateButton($action, $entity);
            }
        }

        return $output . '</ul></div>';
    }

    protected function validateConditions(Object $entity, array $actions): bool {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($actions['conditions'] as $condition) {
            $var = $propertyAccessor->getValue($entity, $condition['key']);
            if (!$this->is($var, $condition['operator'], $condition['value'])) {
                return false;
            }
        }
        return true;
    }

    protected function generateButton(array $values, Object $entity): string {
        if(array_key_exists('params',$values) && $values['params']!== null)
            $route = $this->router->generate($values['route'],array_merge( ['id' => $entity->getId()], $values['params']));
        else
            $route = $this->router->generate($values['route'], ['id' => $entity->getId()]);
        if (array_key_exists('isModal', $values) && $values['isModal'] == true)
            return '<li><a class="dropdown-item" onclick="showFormModal(\'' . $route . '\')"> ' . $values['classIcon'] . '</a></li>';
        if (array_key_exists('isAction', $values) && $values['isAction'] == true)
            return '<li><a class="dropdown-item"  onclick="fetchAction(\'' . $route . '\')"> ' . $values['classIcon'] . '</a></li>';

        $output = '<li><a class="dropdown-item" href="' . $route . '">' . $values['classIcon'] . '</a></li>';

        return $output;
    }

    public function generateCustomerActionButtons(string $id): string {
        $output = '';
        $output .= '<div data-action="click->hello#next" data-request-id="' . $this->router->generate('customer_demande_detail', ['id' => $id]) . '" >  <i class="bi bi-eye-fill"></i></div>';
        return $output;
    }

    public function generateStatut(?string $statut): string {
        switch ($statut) {
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
