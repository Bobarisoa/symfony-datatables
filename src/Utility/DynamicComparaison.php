<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility;
/**
 * @file
 * comment: tool allowing to make a comparaison between differents values given: 
 */

/**
 * comment. outil de comparaison
 */
trait DynamicComparaison
{

    private $operatorToMethodTranslation = [
    '=='  => 'equal',
    '===' => 'totallyEqual',
    '!='  => 'notEqual',
    '>'   => 'greaterThan',
    '<'   => 'lessThan',
    '<='   => '_lessThanOrEqual',
    ];

    protected function is($value_a, $operation, $value_b)
    {

        if($method = $this->operatorToMethodTranslation[$operation]) {
            return $this->$method($value_a, $value_b);
        }

        throw new \Exception('Unknown Dynamic Operator.');
    }

    private function equal($value_a, $value_b)
    {
        
        if(is_array($value_a)) {
            return in_array($value_b, $value_a);
        }
        return $value_a == $value_b;
    }

    private function totallyEqual($value_a, $value_b)
    {
        return $value_a === $value_b;
    }

    private function notEqual($value_a, $value_b)
    {
        return $value_a != $value_b;
    }

    private function greaterThan($value_a, $value_b)
    {
        return $value_a > $value_b;
    }

    private function lessThan($value_a, $value_b)
    {
        return $value_a < $value_b;
    }

    private function greaterThanOrEqual($value_a, $value_b)
    {
        return $value_a >= $value_b;
    }

    /**
     * 
     * @param type $value_a ,comment: valeur a
     * @param type $value_b ,comment: valeur b dans la comparaison
     * 
     * @return bool
     * Return if a <= b
     */
    private function _lessThanOrEqual($value_a, $value_b): bool
    {
        return $value_a <= $value_b;
    }

}
