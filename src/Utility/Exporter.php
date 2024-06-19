<?php
namespace Prolyfix\SymfonyDatatablesBundle\Utility;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 
 */

use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class Exporter{
    
    private $pbi;
    
    public function __construct(ParameterBagInterface $pbi)
    {
        $this->pbi = $pbi;
        
    }
    
    public function exportCSV($output)
    {
        $serializer = new Serializer([], [new CsvEncoder()]);
        $tralala = $serializer->encode($output, 'csv');
        $file = uniqid().".csv";
        $path = $this->pbi->get('rootFolder').'/private/export/'.$file;
        file_put_contents($path, $tralala);
        return new BinaryFileResponse($path);  
    }
    
    public function exportXLS($output)
    {
        $spreadsheet = new Spreadsheet();
        // TODO: mettre en place le header
        if(count($output)> 0){
            $firstLine = array_keys($output[0]);
            array_unshift($output,$firstLine);
        }
        
        $sheet = $spreadsheet->getActiveSheet()
            ->fromArray(
                $output,  // The data to set
                NULL,        // Array values with this value will not be set
                'A1'         // Top left coordinate of the worksheet range where
                             //    we want to set these values (default is A1)
            );
        $writer = new Xlsx($spreadsheet);
        $file = uniqid().".xlsx";
        $path = $this->pbi->get('rootFolder').'/private/export/'.$file;
        $writer->save($path);

        return new BinaryFileResponse($path);        
    }
    
    
    
    
}
