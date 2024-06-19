<?php

namespace Prolyfix\SymfonyDatatablesBundle\Controller;

use App\Entity\User;
use Prolyfix\SymfonyDatatablesBundle\Utility\DatatablesConverter;
use Prolyfix\SymfonyDatatablesBundle\Utility\Exporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Prolyfix\SymfonyDatatablesBundle\Utility\ButtonGenerator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/datatables")
 */
#[Route('/datatables')]
class DatatablesController extends AbstractController
{
    const ISNOTNULL = "is_not_null";
    const IS_NULL = "is_null";

    /**
     * @Route("/requestItems/modifyPos", name="modify_pos")
     */
    #[Route('/requestItems/modifyPos', name: 'modify_pos', methods: ['GET', 'POST'])]
    public function modifyPos(Request $request, EntityManagerInterface $em)
    {
        $entityName = $request->get('entity')[0]['entity'];
        if($request->get('data') == null)
            return new JsonResponse("OK");
        foreach($request->get('data') as $pos => $entityId){
            $entity = $em->getRepository($entityName)->findOneById($entityId);
            $entity->setPosition($pos);
            $em->flush();
        }
        return new JsonResponse(['message'=>'bien effectué']);
    }


    /**
     * @Route("/requestItems", name="requestItems")
     */
    #[Route('/requestItems', name: 'requestItems', methods: ['GET', 'POST'])]
    public function index(Request $request, ButtonGenerator $bg,TranslatorInterface $translator, Exporter $exporter, EntityManagerInterface $em, UrlGeneratorInterface $ugi, UrlGeneratorInterface $router, ParameterBagInterface $pbf, Security $security): Response
    {
        //TODO Mettre en place la structure pour requeter
        //$repo = $this->getDoctrine()->getRepository();
        $params = $request->get('request')[0]['params']??[];
        $user = $this->getUser();
        $params['user'] = $this->getUser();
        if ($request->getMethod() == 'POST') {
            $draw = intval($request->request->get('draw'));
            $params['start'] = $request->request->get('start');
            if($request->request->get('length') > -1) {  
                $params['length'] = $request->request->get('length');
            }
            $params['search'] = $request->request->all()['search'];
            $params['order'] = $request->request->all()['order'];
        }
        else { // If the request is not a POST one, die hard
            die;
        }
                // Orders
        $columns = $request->get('columns');
        foreach($params['order'] as $key=>$order){
            $verif = false;
            $i = 0;
            while( $verif == false and $i < count($request->get('columns'))){
                if($columns[$i]['data'] == $order['column']) {
                    $params['order'][$key]['columnName'] = $columns[$i]['name'];
                    $verif  = true;
                }
                $i++;
            }
        }
        
        $repo = $em->getRepository($request->get('request')[0]['entity']);
        $entities= $repo->findDatatables($params);
        // Returned objects are of type Town
        $objects = $entities['results'];
        // Get total number of objects
        $total_objects_count = $entities['count'];
        // Get total number of results
        $selected_objects_count = count($objects);
        // Get total number of filtered data
        $filtered_objects_count = $entities['count'];
        //Normalisation à la Datatables
        $converter = new DatatablesConverter(
            $request, 
            $bg, 
            $translator, 
            $this->getUser(), 
            $ugi,   
            $pbf,
            $security, 
            $em
        );
        $export = ($request->request->get('output') !== null)? true:false;
        $output = $converter->render($objects, $export);
        $output['draw'] = $draw;
        $output['recordsTotal'] = $total_objects_count;
        $output['recordsFiltered'] = $filtered_objects_count;
        if($request->request->get('output') == null) {  
            return new jsonResponse($output);
        }
        if($export){
            $fn = "export".strtoupper($request->request->get('output'));
            return $exporter->$fn($output['data']);
        }
    }
}
