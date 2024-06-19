<?php

namespace Prolyfix\SymfonyDatatablesBundle\Repository;

use Prolyfix\SymfonyDatatablesBundle\Controller\DatatablesController;
use Doctrine\ORM\QueryBuilder;

trait DatatablesTrait {

    private $alreadyJoin = [];

    function sortQueryAction(QueryBuilder $qb, $value, array $alreadyJoin): QueryBuilder {
        foreach ($value as $order) {
            switch ($order["columnName"]) {
                case '_cityCountry':
                    if (!in_array('locations', $alreadyJoin)) {
                        $qb->join("r." . 'locations', 'locations');
                        $alreadyJoin[] = 'locations';
                    }

                    $qb->orderBy("locations.0.city", $order['dir']);
                default:
                    $relations = explode(".", str_replace('_','',$order["columnName"]));
                    $countRelations = count($relations);
                    $ref = 'r';
                    for ($ii = 0; ($ii + 1) < $countRelations; $ii++) {
                        if (!in_array($relations[$ii], $alreadyJoin) && !in_array($relations[$ii], $this->alreadyJoin)) {
                            $qb->join($ref . "." . $relations[$ii], $relations[$ii]);

                            $alreadyJoin[] = $relations[$ii];
                            $this->alreadyJoin[] = $relations[$ii];
                        }
                        $ref = $relations[$ii];
                    }
                    $qb->orderBy($ref . "." . $relations[$ii], $order['dir']);
            }
        }
        return $qb;
    }

    public function returnCount(QueryBuilder $qb) {
        return $qb->select('count(r.id)')->getQuery()->getSingleScalarResult();
    }

    /**
     * @return RequestItem[] Returns an array of RequestItem objects
     */
    public function findDatatables(array $params): array {
        $qb = $this->createQueryBuilder('r');
        $relation = [];
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'user':
                    break;
                case 'start':
                    break;
                case 'end':
                    break;
                case 'length':
                    break;
                case 'search':
                    break;
                case 'order':
                    $qb = $this->sortQueryAction($qb, $value, []);
                    break;
                default:
                    if($value == DatatablesController::ISNOTNULL){
                        $qb->andWHere('r.' . $key . ' IS NOT NULL');
                    }
                    elseif($value == null){
                        $qb->andWHere('r.' . $key . ' IS NULL');
                    }
                    elseif (is_array($value)) {
                        $qb->andWhere('r.' . $key . ' in (:' . $key . ')')
                                ->setParameter($key, $value);
                    }
                    else {
                        if ($value != "null" && strlen($value) > 0) {

                            $relations = explode(".", $key);
                            $countRelations = count($relations);
                            $ref = 'r';
                            for ($ii = 0; ($ii + 1) < $countRelations; $ii++) {
                                if (!in_array($relations[$ii], $this->alreadyJoin) && (!in_array($relations[$ii], $relation))) {
                                    $qb->join($ref . "." . $relations[$ii], $relations[$ii]);

                                    $relation[] = $relations[$ii];
                                    $this->alreadyJoin[] = $relations[$ii];
                                }
                                $ref = $relations[$ii];
                            }
                            $temp = uniqid();
                            $qb->andWhere($ref . "." . $relations[$ii] . " LIKE :" . str_replace(".", "", $key))
                                    ->setParameter(str_replace(".", "", $key), "".$value."");
                        }
                    }
                    break;
            }
        }
        $count = $this->returnCount(clone($qb));

        if (array_key_exists("length", $params)) {
            $qb->setMaxResults($params['length']);
        }
        if (array_key_exists("start", $params)) {
            $qb->setFirstResult($params['start']);
        }
        $entities = $qb->getQuery()
                ->getResult();
        return['count' => $count, 'results' => $entities];
    }

}
