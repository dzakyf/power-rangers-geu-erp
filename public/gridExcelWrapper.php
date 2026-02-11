<?php

error_reporting(0);

require_once './lib/PHPExcel.php';
require_once './lib/PHPExcel/IOFactory.php';

class gridExcelWrapper {
    private $currentRow = 1;
	private $columns;

    public function createXLS($creator, $lastModifiedBy, $title, $subject, $dsc, $keywords, $category) {
        $this->excel = new PHPExcel();
        $this->excel->getProperties()->setCreator($creator)
                ->setLastModifiedBy($lastModifiedBy)
                ->setTitle($title)
                ->setSubject($subject)
                ->setDescription($dsc)
                ->setKeywords($keywords)
                ->setCategory($category);
    }

    public function headerPrint($columns, $summaryWidth, $headerHeight, $headerColor, $lineColor, $headerFontSize, $fontFamily) {
		$this->columns = $columns;
        for ($i = 0; $i < count($columns); $i++) {
			$this->excel->getActiveSheet()->getRowDimension($this->currentRow)->setRowHeight($headerHeight);
			for ($j = 0; $j < count($columns[$i]); $j++) {
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($j, $this->currentRow, $columns[$i][$j]['text']);
				$this->excel->getActiveSheet()->getColumnDimension($this->getColName($j))->setWidth(($columns[0][$j]['width']*180)/$summaryWidth);
				$this->excel->getActiveSheet()->getStyle($this->getColName($j).$this->currentRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle($this->getColName($j).$this->currentRow)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			}
			$this->currentRow++;
        }
		for ($i = 0; $i < count($columns); $i++) {
			for ($j = 0; $j < count($columns[$i]); $j++) {
				if (isset($columns[$i][$j]['colspan'])) {
					$this->excel->getActiveSheet()->mergeCells($this->getColName($j).($i + 1).':'.$this->getColName($j + $this->columns[$i][$j]['colspan'] - 1).($i + 1));
				}
				if (isset($columns[$i][$j]['rowspan'])) {
					$this->excel->getActiveSheet()->mergeCells($this->getColName($j).($i + 1).':'.$this->getColName($j).($i + $this->columns[$i][$j]['rowspan']));
				}
			}
		}
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => $this->processColor($lineColor)),
                ),
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'rotation' => 90,
                'startcolor' => array(
                    'argb' => $this->processColor($headerColor)
                )
            ),
			'font' => array(
				'bold' => true,
				'name' => $fontFamily,
				'size' => $headerFontSize
			)
        );
        $this->excel->getActiveSheet()->getStyle(($this->getColName(0).'1:'.$this->getColName(count($columns[0]) - 1).($this->currentRow - 1)))->applyFromArray($styleArray);
		$this->excel->getActiveSheet()->freezePane("A".(count($columns) + 1));
		$this->excel->getActiveSheet()->setBreak( 'H4' , PHPExcel_Worksheet::BREAK_ROW );
    }


    public function rowPrint($row, $rowHeight, $rowColor, $lineColor, $gridFontSize, $fontFamily) {
        $this->excel->getActiveSheet()->getRowDimension($this->currentRow)->setRowHeight($rowHeight);
        for ($i = 0; $i < count($row); $i++) {
            $this->excel->setActiveSheetIndex(0);
			$text = $row[$i];
			if ((isset($this->columns[$i]['type']))&&(($this->columns[$i]['type'] == 'ch')||($this->columns[$i]['type'] == 'ra'))) {
				if ($text == '1') {
					$text = 'Yes';
				} else {
					$text = 'No';
				}
			}
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow($i, $this->currentRow, $text);
        }

		$styleArray = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => $this->processColor($lineColor)),
				),
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'rotation' => 90,
				'startcolor' => array(
					'argb' => $this->processColor($rowColor)
				)
			),			
			'font' => array(
				'bold' => false,
				'name' => $fontFamily,
				'size' => $gridFontSize
			)
		);
		$this->excel->getActiveSheet()->getStyle(($this->getColName(0).$this->currentRow.':'.$this->getColName(count($row) - 1).$this->currentRow))->applyFromArray($styleArray);
        
		$this->currentRow++;
	}


	public function outXLS($title) {
		$this->excel->getActiveSheet()->setTitle($title);
		$this->excel->setActiveSheetIndex(0);

		$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="excell_output.xls"');
		header('Cache-Control: max-age=0');
		
		$objWriter->save('php://output'); 
	}
    
	
	public function headerDraw($img) {

	}
	
	
	public function footerDraw($img) {

	}
	
	
	private function getColName($i) {
		if ($i < 26) {
			$result = chr($i + 65);
		} else {
			$a = 0;
			while ($i > 25) {
				$i -= 25;
				$a++;
			}

			if ($i >= $a) {
				$i++;
			}

			$result = chr($a + 64).chr($i + 64);
		}
		return $result;
	}
    
    private function processColor($color) {
        if (!preg_match('/[0-9A-F]{6}/i', $color)) {
            return false;
        } else {
            return "FF".strToUpper($color);
        }
    }
}


?>