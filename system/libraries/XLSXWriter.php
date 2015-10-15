<?php
/*
 * @license MIT License
 * */

//if (!class_exists('ZipArchive')) { throw new Exception('ZipArchive not found'); }

class XLSXWriter
{
	//------------------------------------------------------------------
	//http://office.microsoft.com/en-us/excel-help/excel-specifications-and-limits-HP010073849.aspx
	const EXCEL_2007_MAX_ROW=1048576; 
	const EXCEL_2007_MAX_COL=16384;
	//------------------------------------------------------------------
	protected $author ='Doc Author';
	protected $sheets = array();
	protected $shared_strings = array();//unique set
	protected $shared_string_count = 0;//count of non-unique references to the unique set
	protected $temp_files = array();

	protected $formats = array();
	protected $align = array();
	protected $width = array();
	protected $headeralign = 'general';
	protected $headerstyle = '';
	protected $headerstyle2 = '';
	protected $totalcounter = 0;
	protected $tableborder = 0;
	protected $columnCount = 0;
	protected $filename = '';

	protected $current_sheet = '';

	public function __construct()
	{
		if(!ini_get('date.timezone'))
		{
			//using date functions can kick out warning if this isn't set
			date_default_timezone_set('UTC');
		}
	}

	public function setAuthor($author='') { $this->author=$author; }

	public function __destruct()
	{
		if (!empty($this->temp_files)) {
			foreach($this->temp_files as $temp_file) {
				@unlink($temp_file);
			}
		}
	}

	public function setFormat($format = array()){
		$this->formats = $format;
	}

	public function setFilename($filename){
		$this->filename = $filename;
	}

	public function getFormat(){
		return $this->formats;
	}

	public function setAlign($align = array()){
		$this->align = $align;
	}

	public function getAlign(){
		return $this->align;
	}

	public function setWidth($width = array()){
		$this->width = $width;
	}

	public function getWidth(){
		return $this->width;
	}

	public function setHeaderAlign($headeralign){
		$this->headeralign = $headeralign;
	}

	public function setHeaderStyle1($headerstyle){
		$this->headerstyle = $headerstyle;
	}

	public function setHeaderStyle2($headerstyle2){
		$this->headerstyle2 = $headerstyle2;
	}

	public function getHeaderAlign(){
		return $this->headeralign;
	}

	public function getHeaderStyle(){
		return $this->headerstyle;
	}

	public function getHeaderStyle2(){
		return $this->headerstyle2;
	}

	public function setBorder($border){
		$this->tableborder = $border;
	}

	public function getBorder(){
		return $this->tableborder;
	}

	public function setColumnCount($count){
		$this->columnCount = $count;
	}

	public function getColumnCount(){
		return $this->columnCount;
	}

	protected function tempFilename()
	{
		$filename = tempnam(sys_get_temp_dir(), "xlsx_writer_");
		$this->temp_files[] = $filename;
		return $filename;
	}

	public function writeToStdOut($type = 0, $path = '')
	{
		self::writeToFile($type, $path);
	}

	public function writeToString()
	{
		$temp_file = $this->tempFilename();
		self::writeToFile($temp_file);
		$string = file_get_contents($temp_file);
		return $string;
	}

	public function writeToFile($type, $path)
	{
		$ci =& get_instance();
		foreach($this->sheets as $sheet_name => $sheet) {
			self::finalizeSheet($sheet_name);//making sure all footers have been written
		}
		if (empty($this->sheets))                       { self::log("Error in ".__CLASS__."::".__FUNCTION__.", no worksheets defined."); return; }

		$ci->zip->add_dir('docProps');
		$ci->zip->add_data("docProps/app.xml" , self::buildAppXML());
		$ci->zip->add_data("docProps/core.xml", self::buildCoreXML());

		$ci->zip->add_dir('_rels');
		$ci->zip->add_data("_rels/.rels", self::buildRelationshipsXML());

		$ci->zip->add_dir('xl/worksheets');
		foreach($this->sheets as $sheet) {
			$ci->zip->read_file($sheet->filename,"xl/worksheets/".$sheet->xmlname); 
		}
		if (!empty($this->shared_strings)) {
			$ci->zip->read_file($this->writeSharedStringsXML(), "xl/sharedStrings.xml"); 
		}
		$ci->zip->add_data("xl/workbook.xml"         , self::buildWorkbookXML() );
		$ci->zip->read_file($this->customStylesXML($this->formats, $this->align, $this->headeralign, $this->headerstyle, $this->headerstyle2, $this->tableborder), "xl/styles.xml"); 
		$ci->zip->add_data("[Content_Types].xml"     , self::buildContentTypesXML() );
		$ci->zip->add_dir("xl/_rels");
		$ci->zip->add_data("xl/_rels/workbook.xml.rels", self::buildWorkbookRelsXML() );
		if ($type == 0) {
			$ci->zip->download($this->filename,'excel');
		}else{
			$ci->zip->archive($path.$this->filename);
		}
	}

	protected function customInitializeSheet($sheet_name)
	{
		//if already initialized
		if ($this->current_sheet==$sheet_name || isset($this->sheets[$sheet_name]))
			return;
		$width = $this->getWidth();
		$sheet_filename = $this->tempFilename();
		$sheet_xmlname = 'sheet' . (count($this->sheets) + 1).".xml";
		$this->sheets[$sheet_name] = (object)array(
			'filename' => $sheet_filename, 
			'sheetname' => $sheet_name, 
			'xmlname' => $sheet_xmlname,
			'row_count' => 0,
			'file_writer' => new XLSXWriter_BuffererWriter($sheet_filename),
			'cell_formats' => array(),
			'max_cell_tag_start' => 0,
			'max_cell_tag_end' => 0,
			'finalized' => false,
		);
		$sheet = &$this->sheets[$sheet_name];
		$tabselected = count($this->sheets) == 1 ? 'true' : 'false';//only first sheet is selected
		$max_cell=XLSXWriter::xlsCell(self::EXCEL_2007_MAX_ROW, self::EXCEL_2007_MAX_COL);//XFE1048577
		$sheet->file_writer->write('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
		$sheet->file_writer->write('<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">');
		$sheet->file_writer->write(  '<sheetPr filterMode="false">');
		$sheet->file_writer->write(    '<pageSetUpPr fitToPage="false"/>');
		$sheet->file_writer->write(  '</sheetPr>');
		$sheet->max_cell_tag_start = $sheet->file_writer->ftell();
		$sheet->file_writer->write('<dimension ref="A1:' . $max_cell . '"/>');
		$sheet->max_cell_tag_end = $sheet->file_writer->ftell();
		$sheet->file_writer->write(  '<sheetViews>');
		$sheet->file_writer->write(    '<sheetView colorId="64" defaultGridColor="true" rightToLeft="false" showFormulas="false" showGridLines="true" showOutlineSymbols="true" showRowColHeaders="true" showZeros="true" tabSelected="' . $tabselected . '" topLeftCell="A1" view="normal" windowProtection="false" workbookViewId="0" zoomScale="100" zoomScaleNormal="100" zoomScalePageLayoutView="100">');
		$sheet->file_writer->write(      '<selection activeCell="A1" activeCellId="0" pane="topLeft" sqref="A1"/>');
		$sheet->file_writer->write(    '</sheetView>');
		$sheet->file_writer->write(  '</sheetViews>');
		$sheet->file_writer->write(  '<cols>');
		$i=1;
		foreach ($width as $row) {
			$sheet->file_writer->write(    '<col collapsed="false" hidden="false" max="'.$i.'" min="'.$i.'" style="0" width="'.$row.'"/>');
			$i++;
		}
		$sheet->file_writer->write(    '<col collapsed="false" hidden="false" max="1025" min="'.$i.'" style="0" width="11.5"/>');
		$sheet->file_writer->write(  '</cols>');
		$sheet->file_writer->write(  '<sheetData>');
	}

	protected function initializeSheet($sheet_name)
	{
		//if already initialized
		if ($this->current_sheet==$sheet_name || isset($this->sheets[$sheet_name]))
			return;

		$sheet_filename = $this->tempFilename();
		$sheet_xmlname = 'sheet' . (count($this->sheets) + 1).".xml";
		$this->sheets[$sheet_name] = (object)array(
			'filename' => $sheet_filename, 
			'sheetname' => $sheet_name, 
			'xmlname' => $sheet_xmlname,
			'row_count' => 0,
			'file_writer' => new XLSXWriter_BuffererWriter($sheet_filename),
			'cell_formats' => array(),
			'max_cell_tag_start' => 0,
			'max_cell_tag_end' => 0,
			'finalized' => false,
		);
		$sheet = &$this->sheets[$sheet_name];
		$tabselected = count($this->sheets) == 1 ? 'true' : 'false';//only first sheet is selected
		$max_cell=XLSXWriter::xlsCell(self::EXCEL_2007_MAX_ROW, self::EXCEL_2007_MAX_COL);//XFE1048577
		$sheet->file_writer->write('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
		$sheet->file_writer->write('<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">');
		$sheet->file_writer->write(  '<sheetPr filterMode="false">');
		$sheet->file_writer->write(    '<pageSetUpPr fitToPage="false"/>');
		$sheet->file_writer->write(  '</sheetPr>');
		$sheet->max_cell_tag_start = $sheet->file_writer->ftell();
		$sheet->file_writer->write('<dimension ref="A1:' . $max_cell . '"/>');
		$sheet->max_cell_tag_end = $sheet->file_writer->ftell();
		$sheet->file_writer->write(  '<sheetViews>');
		$sheet->file_writer->write(    '<sheetView colorId="64" defaultGridColor="true" rightToLeft="false" showFormulas="false" showGridLines="true" showOutlineSymbols="true" showRowColHeaders="true" showZeros="true" tabSelected="' . $tabselected . '" topLeftCell="A1" view="normal" windowProtection="false" workbookViewId="0" zoomScale="100" zoomScaleNormal="100" zoomScalePageLayoutView="100">');
		$sheet->file_writer->write(      '<selection activeCell="A1" activeCellId="0" pane="topLeft" sqref="A1"/>');
		$sheet->file_writer->write(    '</sheetView>');
		$sheet->file_writer->write(  '</sheetViews>');
		$sheet->file_writer->write(  '<cols>');
		$sheet->file_writer->write(    '<col collapsed="false" hidden="false" max="1025" min="1" style="0" width="11.5"/>');
		$sheet->file_writer->write(  '</cols>');
		$sheet->file_writer->write(  '<sheetData>');
	}

	public function writeSheetHeader($sheet_name, array $header_types)
	{
		if (empty($sheet_name) || empty($header_types) || !empty($this->sheets[$sheet_name]))
			return;

		self::customInitializeSheet($sheet_name);
		$sheet = &$this->sheets[$sheet_name];

		$sheet->file_writer->write('<row collapsed="false" customFormat="false" customHeight="false" hidden="false" ht="12.1" outlineLevel="0" r="' . (1) . '">');
		foreach ($header_types as $k => $v) {
			$this->customWriteCell($sheet->file_writer, 0, $k, $v, $cell_format = 'string', 1);
		}
		$sheet->file_writer->write('</row>');
		$sheet->row_count++;
		$this->current_sheet = $sheet_name;
	}

	public function writeSheetRow($sheet_name, array $row)
	{
		if (empty($sheet_name) || empty($row))
			return;

		self::customInitializeSheet($sheet_name);
		$sheet = &$this->sheets[$sheet_name];
		if (empty($sheet->cell_formats))
		{
			$sheet->cell_formats = array_fill(0, count($row), 'string');
		}

		$sheet->file_writer->write('<row collapsed="false" customFormat="false" customHeight="false" hidden="false" ht="12.1" outlineLevel="0" r="' . ($sheet->row_count + 1) . '">');
		$i = 2;
		foreach ($row as $k => $v) {
			$this->customWriteCell($sheet->file_writer, $sheet->row_count, $k, $v, $sheet->cell_formats[$k], $i);
			$i++;
		}
		$this->totalcounter = $i;
		$sheet->file_writer->write('</row>');
		$sheet->row_count++;
		$this->current_sheet = $sheet_name;
	}

	public function writeSheetTotal($sheet_name, array $row, array $indexes)
	{
		if (empty($sheet_name) || empty($row) || empty($indexes))
			return;

		self::customInitializeSheet($sheet_name);
		$sheet = &$this->sheets[$sheet_name];
		if (empty($sheet->cell_formats))
		{
			$sheet->cell_formats = array_fill(0, count($row), 'string');
		}

		$sheet->file_writer->write('<row collapsed="false" customFormat="false" customHeight="false" hidden="false" ht="12.1" outlineLevel="0" r="' . ($sheet->row_count + 1) . '">');
		$i = 2;
		$i += $indexes[0] - 1;
		$this->customWriteCell($sheet->file_writer, $sheet->row_count, $indexes[0]-1, "TOTAL:", $sheet->cell_formats[$indexes[$o] - 1], $i);
		$i++;
		
		for($o=0; $o<sizeof($row); $o++){
			$this->customWriteCell($sheet->file_writer, $sheet->row_count, $indexes[$o], $row[$o], $sheet->cell_formats[$indexes[$o]], $i);
			$i++;
		}

		$sheet->file_writer->write('</row>');
		$sheet->row_count++;
		$this->current_sheet = $sheet_name;
	}
	
	protected function finalizeSheet($sheet_name)
	{
		if (empty($sheet_name) || $this->sheets[$sheet_name]->finalized)
			return;

		$sheet = &$this->sheets[$sheet_name];

		$sheet->file_writer->write(    '</sheetData>');
		$sheet->file_writer->write(    '<printOptions headings="false" gridLines="false" gridLinesSet="true" horizontalCentered="false" verticalCentered="false"/>');
		$sheet->file_writer->write(    '<pageMargins left="0.5" right="0.5" top="1.0" bottom="1.0" header="0.5" footer="0.5"/>');
		$sheet->file_writer->write(    '<pageSetup blackAndWhite="false" cellComments="none" copies="1" draft="false" firstPageNumber="1" fitToHeight="1" fitToWidth="1" horizontalDpi="300" orientation="portrait" pageOrder="downThenOver" paperSize="1" scale="100" useFirstPageNumber="true" usePrinterDefaults="false" verticalDpi="300"/>');
		$sheet->file_writer->write(    '<headerFooter differentFirst="false" differentOddEven="false">');
		$sheet->file_writer->write(        '<oddHeader>&amp;C&amp;&quot;Times New Roman,Regular&quot;&amp;12&amp;A</oddHeader>');
		$sheet->file_writer->write(        '<oddFooter>&amp;C&amp;&quot;Times New Roman,Regular&quot;&amp;12Page &amp;P</oddFooter>');
		$sheet->file_writer->write(    '</headerFooter>');
		$sheet->file_writer->write('</worksheet>');

		$max_cell = self::xlsCell($sheet->row_count - 1, count($sheet->cell_formats) - 1);
		$max_cell_tag = '<dimension ref="A1:' . $max_cell . '"/>';
		$padding_length = $sheet->max_cell_tag_end - $sheet->max_cell_tag_start - strlen($max_cell_tag);
		$sheet->file_writer->fseek($sheet->max_cell_tag_start);
		$sheet->file_writer->write($max_cell_tag.str_repeat(" ", $padding_length));
		$sheet->file_writer->close();
		$sheet->finalized=true;
	}

	public function customWriteSheet($data, $sheet_name='' , array $header_types=array())
	{
		$sheet_name = empty($sheet_name) ? 'Sheet1' : $sheet_name;
		$data = empty($data) ? array('') : $data;
		if (!empty($header_types))
		{
			$this->writeSheetHeader($sheet_name, $header_types);
		}
		$this->writeSheetRow($sheet_name, $data);
	}

	public function customWriteSheetTotal($data, $sheet_name='' , $indexes)
	{
		$sheet_name = empty($sheet_name) ? 'Sheet1' : $sheet_name;
		$data = empty($data) ? array('') : $data;
		$this->writeSheetTotal($sheet_name, $data, $indexes);
	}

	public function endSheet($sheet_name=''){
		$sheet_name = empty($sheet_name) ? 'Sheet1' : $sheet_name;
		$this->finalizeSheet($sheet_name);
	}

	public function writeSheet(array $data, $sheet_name='' , $width = array(), array $header_types=array())
	{
		$sheet_name = empty($sheet_name) ? 'Sheet1' : $sheet_name;
		$data = empty($data) ? array(array('')) : $data;
		if (!empty($header_types))
		{
			$this->writeSheetHeader($sheet_name, $header_types, $width);
		}
		foreach($data as $i=>$row)
		{
			$this->writeSheetRow($sheet_name, $row, $width);
		}
		$this->finalizeSheet($sheet_name);
	}

	protected function customWriteCell(XLSXWriter_BuffererWriter &$file, $row_number, $column_number, $value, $cell_format, $i)
	{
		static $styles = array('money'=>1,'dollar'=>1,'datetime'=>2,'date'=>3,'string'=>0);
		$cell = self::xlsCell($row_number, $column_number);
		$s = isset($styles[$cell_format]) ? $styles[$cell_format] : '0';
		$t = 's';

		if ($row_number == 0) {
			$file->write('<c r="'.$cell.'" s="'.$i.'" t="'.$t.'"><v>'.self::xmlspecialchars($this->setSharedString($value)).'</v></c>');
		}else{
			if ($this->formats[$i-2] != 'String') {
				$file->write('<c r="'.$cell.'" s="'.$i.'" t="n"><v>'.($value*1).'</v></c>');//int,float, etc
			} else { //excel wants to trim leading zeros
				$file->write('<c r="'.$cell.'" s="'.$i.'" t="s"><v>'.self::xmlspecialchars($this->setSharedString($value)).'</v></c>');
			}
		}
	}

	protected function writeCell(XLSXWriter_BuffererWriter &$file, $row_number, $column_number, $value, $cell_format)
	{
		static $styles = array('money'=>1,'dollar'=>1,'datetime'=>2,'date'=>3,'string'=>0);
		$cell = self::xlsCell($row_number, $column_number);
		$s = isset($styles[$cell_format]) ? $styles[$cell_format] : '0';

		if (!is_scalar($value) || $value=='') { //objects, array, empty
			$file->write('<c r="'.$cell.'" s="'.$s.'"/>');
		} elseif ($cell_format=='date') {
			$file->write('<c r="'.$cell.'" s="'.$s.'" t="n"><v>'.intval(self::convert_date_time($value)).'</v></c>');
		} elseif ($cell_format=='datetime') {
			$file->write('<c r="'.$cell.'" s="'.$s.'" t="n"><v>'.self::convert_date_time($value).'</v></c>');
		} elseif (!is_string($value)) {
			$file->write('<c r="'.$cell.'" s="'.$s.'" t="n"><v>'.($value*1).'</v></c>');//int,float, etc
		} elseif ($value{0}!='0' && filter_var($value, FILTER_VALIDATE_INT)){ //excel wants to trim leading zeros
			$file->write('<c r="'.$cell.'" s="'.$s.'" t="n"><v>'.($value).'</v></c>');//numeric string
		} elseif ($value{0}=='='){
			$file->write('<c r="'.$cell.'" s="'.$s.'" t="s"><f>'.self::xmlspecialchars($value).'</f></c>');
		} elseif ($value!==''){
			$file->write('<c r="'.$cell.'" s="'.$s.'" t="s"><v>'.self::xmlspecialchars($this->setSharedString($value)).'</v></c>');
		}
	}

	protected function customStylesXML($formats = array(), $align = array(), $headeralign = 'center', $headerstyle = '', $headerstyle2 = '', $tableborder = ''){
		$count = $this->getColumnCount();
		$temporary_filename = $this->tempFilename();
		$file = new XLSXWriter_BuffererWriter($temporary_filename);
		$file->write('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n");
		$file->write('<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
		$file->write('<numFmts count="'.(count($count)+1).'">');
		$file->write(		'<numFmt formatCode="GENERAL" numFmtId="0"/>');
		$file->write(		'<numFmt formatCode="GENERAL" numFmtId="0"/>');
		$i = 164;
		if(!empty($formats)){
			foreach ($formats as $row) {
				if (substr($row, 0,6)=="Number") {
					$num = substr($row,7);
					$point = "";
					for ($x=0; $x < $num; $x++)
						$point .= "0";
					$decimal = $num==0 ? $num : "0.$point"; 
					$file->write(		'<numFmt formatCode="'.$decimal.'" numFmtId="'.$i.'"/>');
				}
				else if (substr($row, 0,5)=="Money") {
					$num = substr($row,6);
					$point = "";
					for ($x=0; $x < $num; $x++)
						$point .= "0";
					$decimal = $num==0 ? $num : "#,##0.$point";
					$file->write(		'<numFmt formatCode="'.$decimal.'" numFmtId="'.$i.'"/>');
				}
				else if ($row=="Text") {
					$file->write(		'<numFmt formatCode="@" numFmtId="'.$i.'"/>');
				}
				else if ($row=="String") {
					$file->write(		'<numFmt formatCode="GENERAL" numFmtId="'.$i.'"/>');
				}
				else if ($row=="Date") {
					$file->write(		'<numFmt formatCode="YYYY-MM-DD" numFmtId="'.$i.'"/>');
				}
				else if ($row=="DateTime") {
					$file->write(		'<numFmt formatCode="YYYY-MM-DD\ HH:MM:SS" numFmtId="'.$i.'"/>');
				}
				else if (substr($row, 0,10)=="Percentage") {
					$num = substr($row,11);
					$point = "";
					for ($x=0; $x < $num; $x++)
						$point .= "0";
					$decimal = $num==0 ? $num : "0.$point"; 
					$file->write(		'<numFmt formatCode="'.$decimal.'%" numFmtId="'.$i.'"/>');
				}
				$i++;
			}
		}else{
			for($o=0; $o<$count; $o++){
				$file->write(		'<numFmt formatCode="GENERAL" numFmtId="'.$i.'"/>');
				$i++;
			}
		}
		$file->write('</numFmts>');
		$file->write('<fonts count="4">');
		$headerstyle = (strlen($headerstyle) > 0) ? "<".strtolower(substr($headerstyle, 0,1))."/>" : '';
		$headerstyle2 = (strlen($headerstyle2) > 0) ? "<".strtolower(substr($headerstyle2, 0,1))."/>" : '';
		$file->write(		'<font><name val="Arial"/><charset val="1"/><family val="2"/><sz val="10"/></font>');
		$file->write(		'<font><name val="Arial"/><charset val="1"/><family val="2"/><sz val="10"/>'.$headerstyle.''.$headerstyle2.'</font>');
		$file->write(		'<font><name val="Arial"/><charset val="1"/><family val="2"/><sz val="10"/></font>');
		$file->write(		'<font><name val="Tahoma"/><family val="0"/><sz val="10"/></font>');
		$file->write(		'<font><name val="Calibri"/><family val="0"/><sz val="10"/></font>');
		$file->write(		'<font><name val="Times New Roman"/><family val="0"/><sz val="10"/></font>');
		$file->write('</fonts>');
		$file->write('<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>');
		$file->write('<borders count="2">');
		$file->write('		<border><left/><right/><top/><bottom/><diagonal/></border>');
		if($tableborder == 1){
			$file->write('		<border><left style="thin"><color indexed="64"/></left><right style="thin"><color indexed="64"/></right>');
			$file->write('		<top style="thin"><color indexed="64"/></top><bottom style="thin"><color indexed="64"/></bottom><diagonal/></border>');
		}
		$file->write('</borders>');
		$file->write(	'<cellStyleXfs count="1">');
		$file->write(		'<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>');
		$file->write(	'</cellStyleXfs>');
		$file->write(	'<cellXfs count="'.(count($align)+2).'">');
		$file->write(		'<xf applyAlignment="1" borderId="0" fillId="0" fontId="0" numFmtId="0">');
		$file->write(		'<alignment horizontal="general"/>');
		$file->write(		'<protection hidden="false" locked="true"/>');
		$file->write(		'</xf>');
		$file->write(		'<xf applyAlignment="1" borderId="'.$tableborder.'" fillId="0" fontId="1" numFmtId="1">');
		$file->write(		'<alignment horizontal="'.strtolower($headeralign).'"/>');
		$file->write(		'<protection hidden="false" locked="true"/>');
		$file->write(		'</xf>');
		$i = 164;
		if(!empty($align)){
			foreach ($align as $row) {
				$file->write(		'<xf applyAlignment="1" borderId="'.$tableborder.'" fillId="0" fontId="2" numFmtId="'.$i.'">');
				$file->write(		'<alignment horizontal="'.strtolower($row).'"/>');
				$file->write(		'<protection hidden="false" locked="true"/>');
				$file->write(		'</xf>');
				$i++;
			}
		}else{
			for ($o=0; $o<$count; $o++) {
				$file->write(		'<xf applyAlignment="1" borderId="'.$tableborder.'" fillId="0" fontId="2" numFmtId="'.$i.'">');
				$file->write(		'<alignment horizontal="general"/>');
				$file->write(		'<protection hidden="false" locked="true"/>');
				$file->write(		'</xf>');
				$i++;
			}
		}
		$file->write(	'</cellXfs>');
		$file->write(	'<cellStyles count="1">');
		$file->write(		'<cellStyle name="Normal" xfId="0" builtinId="0"/>');
		$file->write(	'</cellStyles>');
		$file->write('</styleSheet>');
		$file->close();
		return $temporary_filename;
	}

	protected function writeStylesXML()
	{
		$temporary_filename = $this->tempFilename();
		$file = new XLSXWriter_BuffererWriter($temporary_filename);
		$file->write('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n");
		$file->write('<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
		$file->write('<numFmts count="4">');
		$file->write(		'<numFmt formatCode="GENERAL" numFmtId="164"/>');
		$file->write(		'<numFmt formatCode="[$$-1009]#,##0.00;[RED]\-[$$-1009]#,##0.00" numFmtId="165"/>');
		$file->write(		'<numFmt formatCode="YYYY-MM-DD\ HH:MM:SS" numFmtId="166"/>');
		$file->write(		'<numFmt formatCode="YYYY-MM-DD" numFmtId="167"/>');
		$file->write('</numFmts>');
		$file->write('<fonts count="4">');
		$file->write(		'<font><name val="Arial"/><charset val="1"/><family val="2"/><sz val="10"/></font>');
		$file->write(		'<font><name val="Arial"/><family val="0"/><sz val="10"/></font>');
		$file->write(		'<font><name val="Arial"/><family val="0"/><sz val="10"/></font>');
		$file->write(		'<font><name val="Arial"/><family val="0"/><sz val="10"/></font>');
		$file->write('</fonts>');
		$file->write('<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>');
		$file->write('<borders count="1"><border diagonalDown="false" diagonalUp="false"><left/><right/><top/><bottom/><diagonal/></border></borders>');
		$file->write(	'<cellStyleXfs count="20">');
		$file->write(		'<xf applyAlignment="true" applyBorder="true" applyFont="true" applyProtection="true" borderId="0" fillId="0" fontId="0" numFmtId="164">');
		$file->write(		'<alignment horizontal="general" indent="0" shrinkToFit="false" textRotation="0" vertical="bottom" wrapText="false"/>');
		$file->write(		'<protection hidden="false" locked="true"/>');
		$file->write(		'</xf>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="2" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="2" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="43"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="41"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="44"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="42"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="9"/>');
		$file->write(	'</cellStyleXfs>');
		$file->write(	'<cellXfs count="4">');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="false" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="164" xfId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="false" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="165" xfId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="false" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="166" xfId="0"/>');
		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="false" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="167" xfId="0"/>');
		$file->write(	'</cellXfs>');
		$file->write(	'<cellStyles count="6">');
		$file->write(		'<cellStyle builtinId="0" customBuiltin="false" name="Normal" xfId="0"/>');
		$file->write(		'<cellStyle builtinId="3" customBuiltin="false" name="Comma" xfId="15"/>');
		$file->write(		'<cellStyle builtinId="6" customBuiltin="false" name="Comma [0]" xfId="16"/>');
		$file->write(		'<cellStyle builtinId="4" customBuiltin="false" name="Currency" xfId="17"/>');
		$file->write(		'<cellStyle builtinId="7" customBuiltin="false" name="Currency [0]" xfId="18"/>');
		$file->write(		'<cellStyle builtinId="5" customBuiltin="false" name="Percent" xfId="19"/>');
		$file->write(	'</cellStyles>');
		$file->write('</styleSheet>');
		$file->close();
		return $temporary_filename;
	}

	protected function setSharedString($v)
	{
		if (isset($this->shared_strings[$v]))
		{
			$string_value = $this->shared_strings[$v];
		}
		else
		{
			$string_value = count($this->shared_strings);
			$this->shared_strings[$v] = $string_value;
		}
		$this->shared_string_count++;//non-unique count
		return $string_value;
	}

	protected function writeSharedStringsXML()
	{
		$temporary_filename = $this->tempFilename();
		$file = new XLSXWriter_BuffererWriter($temporary_filename, $fd_flags='w', $check_utf8=true);
		$file->write('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n");
		$file->write('<sst count="'.($this->shared_string_count).'" uniqueCount="'.count($this->shared_strings).'" xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
		foreach($this->shared_strings as $s=>$c)
		{
			$file->write('<si><t>'.self::xmlspecialchars($s).'</t></si>');
		}
		$file->write('</sst>');
		$file->close();
		
		return $temporary_filename;
	}

	protected function buildAppXML()
	{
		$app_xml="";
		$app_xml.='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
		$app_xml.='<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><TotalTime>0</TotalTime></Properties>';
		return $app_xml;
	}

	protected function buildCoreXML()
	{
		$core_xml="";
		$core_xml.='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
		$core_xml.='<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
		$core_xml.='<dcterms:created xsi:type="dcterms:W3CDTF">'.date("Y-m-d\TH:i:s.00\Z").'</dcterms:created>';//$date_time = '2014-10-25T15:54:37.00Z';
		$core_xml.='<dc:creator>'.self::xmlspecialchars($this->author).'</dc:creator>';
		$core_xml.='<cp:revision>0</cp:revision>';
		$core_xml.='</cp:coreProperties>';
		return $core_xml;
	}

	protected function buildRelationshipsXML()
	{
		$rels_xml="";
		$rels_xml.='<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$rels_xml.='<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
		$rels_xml.='<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>';
		$rels_xml.='<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>';
		$rels_xml.='<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>';
		$rels_xml.="\n";
		$rels_xml.='</Relationships>';
		return $rels_xml;
	}

	protected function buildWorkbookXML()
	{
		$i=0;
		$workbook_xml="";
		$workbook_xml.='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
		$workbook_xml.='<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
		$workbook_xml.='<fileVersion appName="Calc"/><workbookPr backupFile="false" showObjects="all" date1904="false"/><workbookProtection/>';
		$workbook_xml.='<bookViews><workbookView activeTab="0" firstSheet="0" showHorizontalScroll="true" showSheetTabs="true" showVerticalScroll="true" tabRatio="212" windowHeight="8192" windowWidth="16384" xWindow="0" yWindow="0"/></bookViews>';
		$workbook_xml.='<sheets>';
		foreach($this->sheets as $sheet_name=>$sheet) {
			$workbook_xml.='<sheet name="'.self::xmlspecialchars($sheet->sheetname).'" sheetId="'.($i+1).'" state="visible" r:id="rId'.($i+2).'"/>';
			$i++;
		}
		$workbook_xml.='</sheets>';
		$workbook_xml.='<calcPr iterateCount="100" refMode="A1" iterate="false" iterateDelta="0.001"/></workbook>';
		return $workbook_xml;
	}

	protected function buildWorkbookRelsXML()
	{
		$i=0;
		$wkbkrels_xml="";
		$wkbkrels_xml.='<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$wkbkrels_xml.='<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
		$wkbkrels_xml.='<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
		foreach($this->sheets as $sheet_name=>$sheet) {
			$wkbkrels_xml.='<Relationship Id="rId'.($i+2).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/'.($sheet->xmlname).'"/>';
			$i++;
		}
		if (!empty($this->shared_strings)) {
			$wkbkrels_xml.='<Relationship Id="rId'.(count($this->sheets)+2).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';
		}
		$wkbkrels_xml.="\n";
		$wkbkrels_xml.='</Relationships>';
		return $wkbkrels_xml;
	}

	protected function buildContentTypesXML()
	{
		$content_types_xml="";
		$content_types_xml.='<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$content_types_xml.='<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
		$content_types_xml.='<Override PartName="/_rels/.rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
		$content_types_xml.='<Override PartName="/xl/_rels/workbook.xml.rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
		foreach($this->sheets as $sheet_name=>$sheet) {
			$content_types_xml.='<Override PartName="/xl/worksheets/'.($sheet->xmlname).'" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
		}
		if (!empty($this->shared_strings)) {
			$content_types_xml.='<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';
		}
		$content_types_xml.='<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
		$content_types_xml.='<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
		$content_types_xml.='<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>';
		$content_types_xml.='<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>';
		$content_types_xml.="\n";
		$content_types_xml.='</Types>';
		return $content_types_xml;
	}

	//------------------------------------------------------------------
	/*
	 * @param $row_number int, zero based
	 * @param $column_number int, zero based
	 * @return Cell label/coordinates, ex: A1, C3, AA42
	 * */
	public static function xlsCell($row_number, $column_number)
	{
		$n = $column_number;
		for($r = ""; $n >= 0; $n = intval($n / 26) - 1) {
			$r = chr($n%26 + 0x41) . $r;
		}
		return $r . ($row_number+1);
	}
	//------------------------------------------------------------------
	public static function log($string)
	{
		file_put_contents("php://stderr", date("Y-m-d H:i:s:").rtrim(is_array($string) ? json_encode($string) : $string)."\n");
	}
	//------------------------------------------------------------------
	public static function sanitize_filename($filename) //http://msdn.microsoft.com/en-us/library/aa365247%28VS.85%29.aspx
	{
		$nonprinting = array_map('chr', range(0,31));
		$invalid_chars = array('<', '>', '?', '"', ':', '|', '\\', '/', '*', '&');
		$all_invalids = array_merge($nonprinting,$invalid_chars);
		return str_replace($all_invalids, "", $filename);
	}
	//------------------------------------------------------------------
	public static function xmlspecialchars($val)
	{
		return str_replace("'", "&#39;", htmlspecialchars($val));
	}
	//------------------------------------------------------------------
	public static function array_first_key(array $arr)
	{
		reset($arr);
		$first_key = key($arr);
		return $first_key;
	}
	//------------------------------------------------------------------
	public static function convert_date_time($date_input) //thanks to Excel::Writer::XLSX::Worksheet.pm (perl)
	{
		$days    = 0;    # Number of days since epoch
		$seconds = 0;    # Time expressed as fraction of 24h hours in seconds
		$year=$month=$day=0;
		$hour=$min  =$sec=0;

		$date_time = $date_input;
		if (preg_match("/(\d{4})\-(\d{2})\-(\d{2})/", $date_time, $matches))
		{
			list($junk,$year,$month,$day) = $matches;
		}
		if (preg_match("/(\d{2}):(\d{2}):(\d{2})/", $date_time, $matches))
		{
			list($junk,$hour,$min,$sec) = $matches;
			$seconds = ( $hour * 60 * 60 + $min * 60 + $sec ) / ( 24 * 60 * 60 );
		}

		//using 1900 as epoch, not 1904, ignoring 1904 special case
		
		# Special cases for Excel.
		if ("$year-$month-$day"=='1899-12-31')  return $seconds      ;    # Excel 1900 epoch
		if ("$year-$month-$day"=='1900-01-00')  return $seconds      ;    # Excel 1900 epoch
		if ("$year-$month-$day"=='1900-02-29')  return 60 + $seconds ;    # Excel false leapday

		# We calculate the date by calculating the number of days since the epoch
		# and adjust for the number of leap days. We calculate the number of leap
		# days by normalising the year in relation to the epoch. Thus the year 2000
		# becomes 100 for 4 and 100 year leapdays and 400 for 400 year leapdays.
		$epoch  = 1900;
		$offset = 0;
		$norm   = 300;
		$range  = $year - $epoch;

		# Set month days and check for leap year.
		$leap = (($year % 400 == 0) || (($year % 4 == 0) && ($year % 100)) ) ? 1 : 0;
		$mdays = array( 31, ($leap ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );

		# Some boundary checks
		if($year < $epoch || $year > 9999) return 0;
		if($month < 1     || $month > 12)  return 0;
		if($day < 1       || $day > $mdays[ $month - 1 ]) return 0;

		# Accumulate the number of days since the epoch.
		$days = $day;    # Add days for current month
		$days += array_sum( array_slice($mdays, 0, $month-1 ) );    # Add days for past months
		$days += $range * 365;                      # Add days for past years
		$days += intval( ( $range ) / 4 );             # Add leapdays
		$days -= intval( ( $range + $offset ) / 100 ); # Subtract 100 year leapdays
		$days += intval( ( $range + $offset + $norm ) / 400 );  # Add 400 year leapdays
		$days -= $leap;                                      # Already counted above

		# Adjust for Excel erroneously treating 1900 as a leap year.
		if ($days > 59) { $days++;}

		return $days + $seconds;
	}
	//------------------------------------------------------------------
}

class XLSXWriter_BuffererWriter
{
	protected $fd=null;
	protected $buffer='';
	protected $check_utf8=false;

	public function __construct($filename, $fd_fopen_flags='w', $check_utf8=false)
	{
		$this->check_utf8 = $check_utf8;
		$this->fd = fopen($filename, $fd_fopen_flags);
		if ($this->fd===false) {
			XLSXWriter::log("Unable to open $filename for writing.");
		}
	}

	public function write($string)
	{
		$this->buffer.=$string;
		if (isset($this->buffer[8191])) {
			$this->purge();
		}
	}

	protected function purge()
	{
		if ($this->fd) {
			if ($this->check_utf8 && !self::isValidUTF8($this->buffer)) {
				XLSXWriter::log("Error, invalid UTF8 encoding detected.");
				$this->check_utf8 = false;
			}
			fwrite($this->fd, $this->buffer);
			$this->buffer='';
		}
	}

	public function close()
	{
		$this->purge();
		if ($this->fd) {
			fclose($this->fd);
			$this->fd=null;
		}
	}

	public function __destruct() 
	{
		$this->close();
	}
	
	public function ftell()
	{
		if ($this->fd) {
			$this->purge();
			return ftell($this->fd);
		}
		return -1;
	}

	public function fseek($pos)
	{
		if ($this->fd) {
			$this->purge();
			return fseek($this->fd, $pos);
		}
		return -1;
	}

	protected static function isValidUTF8($string)
	{
		if (function_exists('mb_check_encoding'))
		{
			return mb_check_encoding($string, 'UTF-8') ? true : false;
		}
		return preg_match("//u", $string) ? true : false;
	}
}



// vim: set filetype=php expandtab tabstop=4 shiftwidth=4 autoindent smartindent:
