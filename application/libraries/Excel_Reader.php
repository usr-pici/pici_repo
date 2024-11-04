<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Description of Excel
 *
 * @author Felipe Avila
 */

require_once('./application/libraries/PHPExcel_1.8.0/PHPExcel.php');
require_once('./application/libraries/PHPExcel_1.8.0/PHPExcel/Writer/Excel2007.php');

class Excel_Reader {
    
    private $excel;
    private $objReader;
    private $info = [];
            
    function __construct($params = array()) { 
        
        /**  Identify the type of $inputFileName  **/
        $inputFileType = PHPExcel_IOFactory::identify($params['file']);
        /**  Create a new Reader of the type that has been identified  **/
        $this->objReader = PHPExcel_IOFactory::createReader($inputFileType);        
        
        /**  Obtener el nombre de las hojas del libro  **/
        $this->info['worksheetNames'] = $this->objReader->listWorksheetNames($params['file']);
        $this->info['file'] = $params['file'];        
        
//        $this->excel->getProperties()->setCreator("Maarten Balliauw");
//        $this->excel->getProperties()->setLastModifiedBy("Maarten Balliauw");
//        $this->excel->getProperties()->setTitle("Office 2007 XLSX Test Document");
//        $this->excel->getProperties()->setSubject("Office 2007 XLSX Test Document");
//        $this->excel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
    }
    
    function load($index = 0, $onlyRead = FALSE, $allSheets = FALSE) {

        /**  Advise the Reader of which WorkSheets we want to load  **/ 
        /* Por default, sólo se cargará una hoja */    
        if ( !$allSheets )
            $this->objReader->setLoadSheetsOnly( $this->info['worksheetNames'][$index] ? $this->info['worksheetNames'][$index] : $this->info['worksheetNames'][0]);
        
        /**  Advise the Reader that we only want to load cell data  **/
        if ( $onlyRead )
            $this->objReader->setReadDataOnly(true);

        $this->excel = $this->objReader->load($this->info['file']);
    }
    
    function getInfo($index = '') {
        
        return empty($index) ? $this->info : $this->info[$index];
    }
    
    function setActiveSheet($index = 0) {
        
        $this->excel->setActiveSheetIndex($index);
    }
    
    function getCell($cell) {
        
        return $this->excel->getActiveSheet()->getCell($cell)->getValue();
    }
    
    function getCellByColAndRow($col, $row, $calculate = FALSE) {
       
        if ( $calculate )
            return $this->excel->getActiveSheet()->getCellByColumnAndRow($col, $row)->getCalculatedValue();
        else
            return $this->excel->getActiveSheet()->getCellByColumnAndRow($col, $row)->getValue();
    }
    
    function getDateByColAndRow($col, $row, $format = 'Y-m-d') {
        
        $cell = $this->excel->getActiveSheet()->getCellByColumnAndRow($col, $row);
        
//        var_dump($cell->getValue());
//        var_dump(PHPExcel_Shared_Date::isDateTime($cell));
//        die();
        $unixTimeStamp = PHPExcel_Shared_Date::isDateTime($cell) ? PHPExcel_Shared_Date::ExcelToPHP($cell->getValue()) : FALSE;
        $ajuste = 3600 * 24;
        
        return $unixTimeStamp ? date($format, $unixTimeStamp + $ajuste) : FALSE;
    }
    
    function getNumRows() {
        
        return $this->excel->getActiveSheet()->getHighestRow();
    }
    
    function getNumCols() {
        
        $highestColumn = $this->excel->getActiveSheet()->getHighestColumn();
        
        return PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5
    }
    
    function setTitleActiveSheet($title) {
        
        $this->excel->getActiveSheet()->setTitle($title);
    }
    
    function setCommentByColumnAndRow($col, $row, $comment = '') {
        
        $this->excel->getActiveSheet()->getCommentByColumnAndRow($col, $row)->getText()->createTextRun($comment);
    }
    
    function setVerticalByColumnAndRow($col, $row, $valign) {
        
        if ( !empty($valign) && in_array($valign, array('top', 'bottom', 'center')) ) {
            
            $this->getStyleByColumnAndRow($col, $row)->getAlignment()->setVertical($valign);
        }
    }
    
    function setHorizontalByColumnAndRow($col, $row, $align) {
        
        if ( !empty($align) && in_array($align, array('left', 'center', 'right')) ) {
            
            $this->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal($align);
        }
    }
    
    function setCellByColumnAndRow($col, $row, $value, $extras = array()) {
        
        $bold = FALSE;
        $border = FALSE;
        $width = 0;
        
        if ( !empty($extras) ) {
            
            extract($extras);
        }
        
        if ( isset($col2) && isset($row2) ) {
            
            $this->excel->getActiveSheet()->mergeCellsByColumnAndRow($col, $row, $col2, $row2);
        }
        
        if ( !empty($valign) ) {
            
            $this->setVerticalByColumnAndRow($col, $row, $valign);
        }
        
        if ( !empty($align) ) {
            
            $this->setHorizontalByColumnAndRow($col, $row, $align);
        }
        
        if ( !empty($backgroundColor) ) {
            
            $this->setBackgroundColorByColumnAndRow($col, $row, $backgroundColor);
        }
        
        $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, ($value));
        
        if ( $bold ) {
            
            $this->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
        }
        
        if ( $border ) {
            
            if ( isset($col2) && isset($row2) ) {
                
                $prefijo1 = $prefijo2 = '';
                
                $cell_col = (int) ( $col / 26 );
                $cell_col2 = (int) ( $col2 / 26 );

                if ( $cell_col > 0 ) {

                    $prefijo1 = chr(64 + $cell_col);
                    $cell_col = (int) ( $col % 26 );
                    $cell_col = $prefijo1 . chr(65 + $cell_col);

                } else {
                    
//                    echo ( "paso con col({$col}) = {$cell_col}" );
                    $cell_col = chr(65 + $col);
                }

                if ( $cell_col2 > 0 ) {

                    $prefijo2 = chr(64 + $cell_col2);
                    $cell_col2 = (int) ( $col2 % 26 );
                    $cell_col2 = $prefijo2 . chr(65 + $cell_col2);

                } else {

//                    echo ( "paso con col2({$col2}) = {$cell_col2}" );
                    $cell_col2 = chr(65 + $col2);
                }

                $range_cell = $cell_col . $row . ':' . $cell_col2 . $row2;
//                echo $range_cell . " [$col, $row, $col2, $row2]";
                $this->setBorder($range_cell);
                
                
            } else {
            
                $this->setBorderByColumnAndRow($col, $row);
            }
        }
        
        if ( $width > 0 ) {
            
            $this->excel->getActiveSheet()->getColumnDimensionByColumn($col)->setWidth($width);
            
        } elseif ( $width === -1 ) {
            
            $this->excel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
        
//        die( "pruebaaaaa" . $range_cell );
    }
    
    function setBackgroundColorByColumnAndRow($col, $row, $color = '') {
        
        $this->getStyleByColumnAndRow($col, $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($color);
    }
    
    function setCell($cell, $value, $extras = array()) {
        
        $bold = FALSE;
        $border = TRUE;
        $width = 0;
        
        if ( !empty($extras) ) {
            
            extract($extras);
        }
        
        $this->excel->getActiveSheet()->SetCellValue($cell, $value);
        
        if ( $bold ) {
            
            $this->getStyle($cell)->getFont()->setBold(true);
        }
        
        if ( $border ) {
            
            $this->setBorder($cell);
        }
        
        if ( $width > 0 ) {
            
            $this->excel->getActiveSheet()->getColumnDimension($cell)->setWidth($width);
            
        } elseif ( $width === -1 ) {
            
            $this->excel->getActiveSheet()->getColumnDimension($cell)->setAutoSize(true);
        }
    }
    
    function setBorderByColumnAndRow($col, $row, $tipo = '') {
        
        if ( empty($tipo) ) {
            
            $tipo = PHPExcel_Style_Border::BORDER_THIN;
        }
        
        $this->getStyleByColumnAndRow($col, $row)->getBorders()->getAllBorders()->setBorderStyle($tipo);
    }
    
    function setBorder($cell, $tipo = '') {
        
        if ( empty($tipo) ) {
            
            $tipo = PHPExcel_Style_Border::BORDER_THIN;
        }
        
        $this->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle($tipo);
    }
            
    function getStyle($cell) {
        
        return $this->excel->getActiveSheet()->getStyle($cell);
    }
    
    function getStyleByColumnAndRow($col, $row) {
        
        return $this->excel->getActiveSheet()->getStyleByColumnAndRow($col, $row);
    }
    
    function export($file_name = '') {
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$file_name.'.xlsx"');
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $objWriter->save('php://output');
    }

    function save($file_name = '', $path = '') {
        
        $objWriter = new PHPExcel_Writer_Excel2007($this->excel);
        $objWriter->save($path . $file_name);
    }
}
