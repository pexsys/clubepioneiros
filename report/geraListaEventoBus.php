<?php
@require_once('../include/functions.php');
@require_once('../include/_core/lib/tcpdf/tcpdf.php');

class LISTAEVENTOBUS extends TCPDF {
	
	//lines styles
	private $stLine;
	private $stLine2;
	private $posY;
	private $lineAlt;
	private $header;
	public $seq;
	
	function __construct() {
		parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		$this->stLine = array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->stLine2 = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->stLine3 = array(
		    'position' => '',
		    'align' => 'C',
		    'stretch' => false,
		    'fitwidth' => true,
		    'cellfitalign' => '',
		    'border' => false,
		    'hpadding' => 'auto',
		    'vpadding' => 'auto',
		    'fgcolor' => array(0,0,0),
		    'bgcolor' => false, //array(255,255,255),
		    'text' => true,
		    'font' => 'helvetica',
		    'fontsize' => 9,
		    'stretchtext' => 0
		);

		$this->SetCreator(PDF_CREATOR);
		$this->SetAuthor('Ricardo J. Cesar');
		$this->SetTitle('Listagem de Passageiros do Evento');
		$this->SetSubject('Clube Pioneiros');
		$this->SetKeywords('Desbravadores, Especialidades, Pioneiros, Capão Redondo');
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
	}

	public function Footer() {
		$this->Line(5, 288, 205, 288, $this->stLine3);
		$this->SetY(-9);
		$this->SetTextColor(90,90,90);
		$this->SetFont(PDF_FONT_NAME_MAIN, 'N', 6);
		//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
		$this->SetX(5);
		$this->Cell(50, 3, date("d/m/Y H:i:s"), 0, false, 'L');
		$this->SetX(172);
		$this->Cell(40, 3, "Página ". $this->getAliasNumPage() ." de ". $this->getAliasNbPages(), 0, false, 'R');
	}
	
 	public function Header() {
		$this->setXY(0,0);
		$this->Image("img/logo.jpg", 5, 5, 14, 16, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
		$this->posY = 5;
		
		$this->setXY(20,$this->posY);
		$this->SetFont(PDF_FONT_NAME_MAIN, 'B', 20);
		$this->Cell(185, 6, "LISTAGEM DE PASSAGEIROS - ÔNIBUS ". $this->header["BUS"], 0, false, 'C', false, false, false, false, 'T', 'M');
		$this->posY += 8;

		$this->setXY(20,$this->posY);
		$this->SetTextColor(80,80,80);
		$this->SetFont(PDF_FONT_NAME_MAIN, 'N', 8);
		$this->Cell(185, 5, fClubeID(), 0, false, 'C', false, false, false, false, false, false, 'T', 'M');
		$this->posY += 5;
		
		$this->setXY(20,$this->posY);
		$this->SetFont(PDF_FONT_NAME_MAIN, 'N', 8);
		$this->Cell(185, 5, utf8_encode($this->header["DS"] . (!is_null($this->header["DS_TEMA"]) ? " - ".$this->header["DS_TEMA"] : "")  ." - ". $this->header["DS_DEST"]), 0, false, 'C', false, false, false, false, 'T', 'M');
		$this->posY += 5;

		$this->SetFont(PDF_FONT_NAME_MAIN, 'B', 8);
		$this->SetTextColor(255,255,255);
		$this->SetFillColor(218,165,32);
		$this->setCellPaddings(1,0,1,0);
		$this->setXY(5, $this->posY);
		$this->Cell(8, 6, "Seq.", 0, false, 'C', true);
		$this->setX(13);
		$this->Cell(102, 6, "Nome Completo", 0, false, 'L', true);
		$this->setX(115);
		$this->Cell(30, 6, "Nascimento", 0, false, 'C', true);
		$this->setX(145);
		$this->Cell(30, 6, "Identificação", 0, false, 'L', true);
		$this->setX(175);
		$this->Cell(30, 6, "CPF", 0, false, 'L', true);
		$this->posY += 6;
	}

	public function setHeaderFields($header){
		$this->header = $header;
	}
	
	public function addGrupoBus($g) {
	    $this->setHeaderFields($g);
	    $this->newPage();
	    
	    $rsM = $GLOBALS['conn']->Execute("
        	SELECT *
              FROM EVE_SAIDA_USE
             WHERE ID_EVE_SAIDA = ?
               AND BUS = ?
            ORDER BY NM, DT
        ", array( $g["ID"], $g["BUS"] ) );
		foreach ($rsM as $k => $f):
			$this->startTransaction();
			$start_page = $this->getPage();
			$this->addLine($f);
			if  ($this->getNumPages() != $start_page):
				$this->rollbackTransaction(true);
				$this->newPage();
				$this->addLine($f);
			else:
				$this->commitTransaction();     
			endif;
		endforeach;
	}
	
	public function addLine($f){
		$this->SetFont(PDF_FONT_NAME_MAIN, 'N', 7);
		$this->setCellPaddings(1,1,1,1);
		$this->SetTextColor(0,0,0);
		if ($this->lineAlt):
			$this->SetFillColor(240,240,240);
		else:
			$this->SetFillColor(255,255,255);
		endif;
		$this->setXY(5, $this->posY);
		$this->Cell(8, 5, str_pad( ++$this->seq, 2, "0", STR_PAD_LEFT), 0, false, 'C', true);
		$this->setX(13);
		$this->Cell(102, 5, utf8_encode($f["NM"]), 0, false, 'L', true, false, 1);
		$this->setX(115);
		$this->Cell(30, 5, strftime("%d/%m/%Y",strtotime($f["DT"])), 0, false, 'C', true, false, 1);
		$this->setX(145);
		$this->Cell(30, 5, $f["IDENT"], 0, false, 'L', true, false, 1);
		$this->setX(175);
		$this->Cell(30, 5, $f["CPF"], 0, false, 'L', true, false, 1);
		$this->posY+=5;
		$this->lineAlt = !$this->lineAlt;
	}
	
	public function newPage() {
		$this->AddPage();
		$this->setCellPaddings(0,0,0,0);
		$this->SetTextColor(0,0,0);
		$this->setXY(0,0);
	}

	public function download() {
		$this->lastPage();
		$this->Output("ListagemPassageirosEvento_".date('Y-m-d_H:i:s').".pdf", "I");
	}
}

$eveID = fRequest("eve");
$pdf = new LISTAEVENTOBUS();

fConnDB();
$result = $GLOBALS['conn']->Execute("
	SELECT es.ID, es.DS, es.DS_TEMA, es.DS_ORG, es.DS_DEST, esu.BUS
      FROM EVE_SAIDA es
INNER JOIN EVE_SAIDA_USE esu ON (esu.ID_EVE_SAIDA = es.ID AND esu.BUS IS NOT NULL)
     WHERE es.ID = ?
    ORDER BY esu.BUS
", array($eveID) );

if (!$result->EOF):
    foreach ( $result as $o => $g ):
        $pdf->seq = 0;
    	$pdf->addGrupoBus($g);
    endforeach;
    $pdf->download();
endif;
exit;
?>