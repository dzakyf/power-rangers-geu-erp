<?php

class gridExcelGenerator {

	public $headerHeight = 30;
	public $rowHeight = 20;

	public $fontFamily = 'Helvetica';
	public $headerFontSize = 9;
	public $gridFontSize = 9;
	
	public $strip_tags = false;

	public $bgColor = 'D1E5FE';
	public $lineColor = 'A4BED4';
	public $scaleOneColor = 'FFFFFF';
	public $scaleTwoColor = 'E3EFFF';

	public $creator = 'DHTMLX LTD';
	public $lastModifiedBy = '';
	public $title = 'Excell_Download';
	public $subject = '';
	public $dsc = '';
	public $keywords = '';
	public $category = '';
	
	private $columns = Array();
	private $rows = Array();
	private $summaryWidth;
	private $profile;
	private $header = null;
	private $footer = null;

	public function printGrid($xml) {
		$this->headerParse($xml->head);
		$this->mainParse($xml);
		$this->rowsParse($xml->row);
		$this->printGridExcel();
	}


	private function setProfile() {
		switch ($this->profile) {
			case 'color':
				$this->bgColor = 'D1E5FE';
				$this->lineColor = 'A4BED4';
				$this->scaleOneColor = 'FFFFFF';
				$this->scaleTwoColor = 'E3EFFF';
				break;
			case 'gray':
				$this->bgColor = 'E3E3E3';
				$this->lineColor = 'B8B8B8';
				$this->scaleOneColor = 'FFFFFF';
				$this->scaleTwoColor = 'EDEDED';
				break;
			case 'bw':
				$this->bgColor = 'FFFFFF';
				$this->lineColor = '000000';
				$this->scaleOneColor = 'FFFFFF';
				$this->scaleTwoColor = 'FFFFFF';
				break;
		}
	}
	
	
	private function mainParse($xml) {
		$this->profile = (string) $xml->attributes()->profile;
		$this->setProfile();
	}

	private function headerParse($header) {
		if (isset($header->column)) {
			$columns = $header->column;
			$summaryWidth = 0;
			$this->columns[0] = Array();
			foreach ($columns as $column) {
				$columnArr = Array();
				$columnArr['text'] = trim((string) $column);
				$columnArr['width'] = trim((string) $column->attributes()->width);
				$columnArr['type'] = trim((string) $column->attributes()->type);
				$columnArr['align'] = trim((string) $column->attributes()->align);
				if (isset($column->attributes()->colspan)) {
					$columnArr['colspan'] = (int) $column->attributes()->colspan;
				}
				if (isset($column->attributes()->rowspan)) {
					$columnArr['rowspan'] = (int) $column->attributes()->rowspan;
				}
				$summaryWidth += $columnArr['width'];
				$this->columns[0][] = $columnArr;
			}
		} else {
			if (isset($header->columns)) {
				$columns = $header->columns;
				$summaryWidth = 0;
				$i = 0;
				foreach ($columns as $row) {
					$this->columns[$i] = Array();
					foreach ($row as $column) {
						$columnArr = Array();
						$columnArr['text'] = trim((string) $column);
						$columnArr['width'] = trim((string) $column->attributes()->width);
						$columnArr['type'] = trim((string) $column->attributes()->type);
						$columnArr['align'] = trim((string) $column->attributes()->align);
						if (isset($column->attributes()->colspan)) {
							$columnArr['colspan'] = (int) $column->attributes()->colspan;
						}
						if (isset($column->attributes()->rowspan)) {
							$columnArr['rowspan'] = (int) $column->attributes()->rowspan;
						}
						if ($i == 0) {
							$summaryWidth += $columnArr['width'];
						}
						$this->columns[$i][] = $columnArr;
					}
					$i++;
				}
			}
		}
		$this->summaryWidth = $summaryWidth;
	}


	private function rowsParse($rows) {
		foreach ($rows as $row) {
			$rowArr = Array();
			$cells = $row->cell;
			foreach ($cells as $cell) {
				if ($this->strip_tags == true) {
					$rowArr[] = strip_tags(((string) $cell));
				} else {
					$rowArr[] = ((string) $cell);
				}
			}
			$this->rows[] = $rowArr;
		}
	}


	public function printGridExcel() {
		$this->wrapper = new gridExcelWrapper();
		$this->wrapper->createXLS($this->creator, $this->lastModifiedBy, $this->title, $this->subject, $this->dsc, $this->keywords, $this->category);
		$this->wrapper->headerPrint($this->columns, $this->summaryWidth, $this->headerHeight, $this->bgColor, $this->lineColor, $this->headerFontSize, $this->fontFamily);

		for ($i = 0; $i < count($this->rows); $i++) {
			if ($i%2 == 0) {
				$color = $this->scaleOneColor;
			} else {
				$color = $this->scaleTwoColor;
			}
			$this->wrapper->rowPrint($this->rows[$i], $this->rowHeight, $color, $this->lineColor, $this->gridFontSize, $this->fontFamily);
		}        
		$this->wrapper->outXLS($this->title);
	}

}


?>