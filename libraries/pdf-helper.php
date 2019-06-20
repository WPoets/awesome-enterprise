<?php


//require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');

class awesome_pdf extends tcpdf
{
	private $pdf_data;
	
	function __construct($pdf_data)
	{
		global $l; //defined in lang folder eng.php file
		//$creator='awesome_pdf',$author='awesome studio',$page_orientation=PDF_PAGE_ORIENTATION,$pdf_unit=PDF_UNIT
		$this->pdf_data = $pdf_data;
		
		parent::__construct($this->pdf_data['setup']['page_orientation'], $this->pdf_data['setup']['pdf_unit'], $this->pdf_data['setup']['page_format'], $this->pdf_data['setup']['unicode'], $this->pdf_data['setup']['encoding'], $this->pdf_data['setup']['diskcache']);
		
		$this->SetCreator($this->pdf_data['setup']['creator']);
		$this->SetAuthor($this->pdf_data['setup']['author']);
		
		$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$this->SetMargins($this->pdf_data['setup']['left_margin'], $this->pdf_data['setup']['top_margin'], $this->pdf_data['setup']['right_margin']);
		
		$this->SetFooterMargin($this->pdf_data['setup']['footer_margin']);
		$this->SetHeaderMargin($this->pdf_data['setup']['header_margin']);
		//set auto page breaks
		$margin = 20;
		if(isset($this->pdf_data['setup']['footer_margin']))
			$margin = $this->pdf_data['setup']['footer_margin'];
		$this->SetAutoPageBreak(TRUE, $margin);
		//set image scale factor
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
		//set some language-dependent strings
		$this->setLanguageArray($l);
		// set default font subsetting mode
		$this->setFontSubsetting(true);
		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$this->SetFont($this->pdf_data['setup']['font_name'], '', 5, '', true);
		
		if($this->pdf_data['setup']['show_header']=='no')
			$this->setPrintHeader(false);
		
		if($this->pdf_data['setup']['show_footer']=='no')
			$this->setPrintFooter(false);
		
		
		
	}
	
	function Header()
	{
		/* // store current auto-page-break status
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->AutoPageBreak;
        $this->SetAutoPageBreak(false, 0);
	
		$this->Rect(0, 0, 55, 82, 'DF', array(), array(237,235,235));
        $headerdata = $this->getHeaderData();
		// restore auto-page-break status
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
		$this->SetY(0.1);
		if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
			$this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
			$imgy = $this->getImageRBY();
		} else {
			$imgy = $this->GetY();
		}
		$this->SetY(1);
		$headerfont = $this->getHeaderFont();
		$headerdata = $this->getHeaderData();
		$this->SetTextColor(35, 35, 35);
		// header title
		$this->SetLineStyle(array('width' => 0.012, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(144, 143, 143)));
		$this->SetFont($headerfont[0], 'B', 2.3);
		$this->SetX(39);
		$this->Cell(10, 0, $headerdata['title'], 'B', 1, 'R', 0, '', 0);
		// header string
		$this->SetTextColor(67, 67, 67);
		$this->SetFont($headerfont[0], 'B', 2);
		$this->SetX(39);
		$this->Cell(10, 0, $headerdata['string'], 'T', 0, 'R'); */
		
		if(!isset($this->pdf_data['header']) || !isset($this->pdf_data['header']['items']))
			return;
		
		if(empty($this->pdf_data['header']) || empty($this->pdf_data['header']['items']))
			return;
		if(!empty($this->pdf_data['header']['font'])){
			$this->SetFont($this->pdf_data['header']['font']['name'], $this->pdf_data['header']['font']['style'], $this->pdf_data['header']['font']['size']);
		}	
		
		foreach($this->pdf_data['header']['items'] as $item){
			extract( shortcode_atts( array(
					"item_type" => null,
					"SetY" => "p",
					"SetX" => "mm",
					"w" => "0",
					"h" => "0",
					"text" => "",
					"border" => "0",
					"ln" =>"0",
					"align" => "",
					"fill" => false,
					"link" => "",
					"stretch" => "0",
					"ignore_min_height" => false,
					"calign" => "T",
					"valign" => "M",
					"y" => "0",
					"fstroke" => false,
					"ffill" => true,
					"fclip" => false,
					"rtloff" => false,
					"file" => null,
					"type" => "",
					"resize" => false,
					"dpi" => "300",
					"palign" => "",
					"ismask" => false,
					"imgmask" => false,
					"fitbox" => false,
					"hidden" => false,
					"fitonpage" => false,
					"x" => "0"
				), $item));
				
			if(!empty($item['SetX'])){
				$this->SetX($item['SetX']);
			}
			if(!empty($item['SetY'])){
				$this->SetY($item['SetY']);
			}
			
			if(is_null($item_type))
				return;
			
			if($item_type == 'cell')	{
				$this->Cell( $w, $h, $text, $border, $ln, $align, $fill, $link, $stretch, $ignore_min_height, $calign, $valign );
			}
			
			if($item_type == 'text')	{
				$this->Text( $x, $y, $text, $fstroke, $fclip, $ffill, $border, $ln, $align, $fill, $link, $stretch, $ignore_min_height, $calign, $valign, $rtloff );
			}	
			
			if($item_type == 'image')	{
				$this->Image( $file, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, $ismask, $imgmask, $border, $fitbox, $hidden, $fitonpage);
			}
		
		}

	}
	
	function Footer()
	{
		$cur_y = $this->GetY();
		$ormargins = $this->getOriginalMargins();
		$this->SetTextColor(57, 57, 57);
		$this->SetLineStyle(array('width' => 0.02 , 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(181, 181, 181)));
		
		if (empty($this->pagegroups)) {
			$pagenumtxt = $this->l['w_page'].' '.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $this->l['w_page'].' '.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
		}
		$this->SetY(($cur_y));
		
		//util::var_dump($cur_y);
		
		$this->SetX($ormargins['left']);
		$this->Cell(0, 1, ' ', 'T', 1, 'R');
		
		$this->SetX($ormargins['left']);
		$this->Cell(0, 0, $pagenumtxt, '', 0, 'R');
	
		if(!isset($this->pdf_data['footer']) || !isset($this->pdf_data['footer']['items']))
			return;
		
		if(empty($this->pdf_data['footer']) || empty($this->pdf_data['footer']['items']))
			return;
		if(!empty($this->pdf_data['footer']['font'])){
			$this->SetFont($this->pdf_data['footer']['font']['name'], $this->pdf_data['footer']['font']['style'], $this->pdf_data['footer']['font']['size']);
		}	
		
		foreach($this->pdf_data['footer']['items'] as $item){
			extract( shortcode_atts( array(
					"item_type" => null,
					"SetY" => "p",
					"SetX" => "mm",
					"w" => "0",
					"h" => "0",
					"text" => "",
					"border" => "0",
					"ln" =>"0",
					"align" => "",
					"fill" => false,
					"link" => "",
					"stretch" => "0",
					"ignore_min_height" => false,
					"calign" => "T",
					"valign" => "M",
					"y" => "0",
					"fstroke" => false,
					"ffill" => true,
					"fclip" => false,
					"rtloff" => false,
					"file" => null,
					"type" => "",
					"resize" => false,
					"dpi" => "300",
					"palign" => "",
					"ismask" => false,
					"imgmask" => false,
					"fitbox" => false,
					"hidden" => false,
					"fitonpage" => false,
					"x" => "0"
				), $item));
				
			if(!empty($item['SetX'])){
				$this->SetX($item['SetX']);
			}
			
			if(is_null($item_type))
				return;
			
			if($item_type == 'cell')	{
				$this->Cell( $w, $h, $text, $border, $ln, $align, $fill, $link, $stretch, $ignore_min_height, $calign, $valign );
			}
			
			if($item_type == 'text')	{
				$this->Text( $x, $y, $text, $fstroke, $fclip, $ffill, $border, $ln, $align, $fill, $link, $stretch, $ignore_min_height, $calign, $valign, $rtloff );
			}	
			
			if($item_type == 'image')	{
				$this->Image( $file, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, $ismask, $imgmask, $border, $fitbox, $hidden, $fitonpage);
			}
		
		}
	}
	//works
	function SetHeaders($title,$date_str)
	{
		$this->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $title, $date_str);
		$this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 2));

	}
	//works
	function SetFooters()
	{
		$this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', 1.8));
	}

	//works
	function ChapterTitle($label)
	{
		$this->SetTextColor(44, 45, 50);
		$html ='<h2>'. $label.'</h2>';
		$this->writeHTML($html, true, false, true, false);
	}
	//works
	function ChapeterRef($link)
	{
		$html ='<p>Article Source : <a href="'.$link.'">'. $link.'</a></p>';
		$this->writeHTML($html, true, false, true, false);
	}
	//works
	function ChapterBody($html)
	{
		$this->SetTextColor(10, 10, 10);
		$this->writeHTML($html, true, false, true, false);
	}
//works
	function AddChapter($num,$title,$html,$domain,$link,$date)
	{
		$this->SetHeaders($domain,date('l F d Y'));
		$this->SetFooters();
		$this->AddPage();
		// set a bookmark for the current position
		$this->Bookmark($title, 0, 0, '');
		$this->ChapterTitle($title);
		$this->ChapterBody($html);
		$this->ChapeterRef($link);
	}

	
	function AddIndexPage()
	{
		// add a new page for TOC
		$this->addTOCPage();

		// write the TOC title
		$this->SetFont('times', 'B', 16);
		$this->MultiCell(0, 0, 'Table Of Content', 0, 'C', 0, 1, '', '', true, 0);
		$this->Ln();

		$this->SetFont('dejavusans', '', 12);

		// add a simple Table Of Content at first page
		// (check the example n. 59 for the HTML version)
		$this->addTOC(1, 'courier', '.', 'INDEX', 'B', array(128,0,0));

		// end of TOC page
		$this->endTOCPage();
	}
	//works
	function Advertise($html)
	{
		$this->SetHeaders('Magazinify Updates', date('l F d Y'));
		$this->SetFooters();
		$this->AddPage();
		$this->ChapterBody($html);
	}
	//save
	function Save($filename)
	{
		$this->Output($filename, 'F');
		return $filename;
		
	}
	
	 //Download
	function Download($filename){
		$this->Output($filename, 'D');
	}
	
	function SetPDF(){ 


		$this->AddPage();

		$this->SetTextColor(10, 10, 10);
		$this->writeHTML($this->pdf_data['content'], true, false, true, false);
	}
}
