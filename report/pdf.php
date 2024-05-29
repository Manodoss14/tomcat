<?php
	require('fpdf.php');
	
	class PDF extends FPDF
	{
		
		public $headerData;
		public $widths;
		
		function Header()
		{
			$this->AddFont('verdana','','verdana.php');
			$this->AddFont('verdana','B','verdanab.php');
			$this->AddFont('verdana','I','verdanai.php');
			
			
			
			$contactDetail1 = "CELL: Mani : 94432 20707 
			        M.C. : 93447 28335";
			
			$contactDetail2 = "STD(0427): 2350734,
			2350013 ";
			
			$this->SetFont('helvetica','',10);
			$this->SetXY(5, 5);
			$this->MultiCell(99,4,$contactDetail1,0,'L');
			$this->SetXY(105, 5);
			$this->MultiCell(100,4,$contactDetail2,0,'R');
			$this->Ln(10);
			
			$this-> Image("../asset/img/logo_black.png",6,14,45,25);
			
			$this->SetFont('verdana','B',15);
			$this->SetXY(5, 7);
			$this->Cell(199,10,'NEW BOMBAY ROADLINES',0,0,'C');
			$this->Ln(10);
			
			$headerContent = "LORRY SUPPLIERS & COMMISSION AGENT,
			225/38-C, Suramangalam Main Road,
			PALLAPATTI, SALEM - 636 009.
			GSTIN.33AACFN2203J1ZS
			Email: nbrlsalem@gmail.com
			";
			$this->SetFont('helvetica','',10);
			$this->SetXY(5, 17);
			$this->MultiCell(199,4,$headerContent,0,'C');
			$this->SetXY(175, 33);
			$this->Cell(198,6,'Date : ' .date("d-m-Y"),0,'C');
			$this->Ln(10);
			
			$this->Line(5,38, 205, 38);
			$this->Line(5,39, 205, 39);
		}
		
		function Footer()
		{
			$this->SetXY(5,-7);
			$this->SetFont('verdana','',10);
			$this->Cell(199,5,$this->PageNo(),0,0,'C');
		}
		
		function SetWidths($w)
		{
			//Set the array of column widths
			$this->widths=$w;
		}
		
		function SetAligns($a)
		{
			//Set the array of column alignments
			$this->aligns=$a;
		}
		
		function Row($data)
		{			
			//Calculate the height of the row
			$nb=0;
			for($i=0;$i<count($data);$i++)
			$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
			$h=5*$nb;
			//Issue a page break first if needed
			$this->CheckPageBreak($h);
			$this->SetFont('verdana','',10);
			//Draw the cells of the row
			for($i=0;$i<count(($data));$i++)
			{
				$w=$this->widths[$i];
				$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
				//Save the current position
				$x=$this->GetX();
				$y=$this->GetY();
				//Draw the border
				$this->Rect($x,$y,$w,$h);
				//Print the text
				$this->MultiCell($w,5,$data[$i],0,$a);
				//Put the position to the right of the cell
				$this->SetXY($x+$w,$y);
			}
			//Go to the next line
			$this->Ln($h);
		}
		
		function CheckPageBreak($h)
		{
			//If the height h would cause an overflow, add a new page immediately
			if($this->GetY()+$h>$this->PageBreakTrigger){
				$this->AddPage($this->CurOrientation);
				$this->SetFont('verdana','B',10);
				$this->SetXY(5,43);
				for($i=0;$i<count($this->headerData);$i++){
					$this->Cell($this->widths[$i],7,$this->headerData[$i],1,0,'C',true);
				}
				$this->Ln();
			}
		}
		
		function NbLines($w,$txt)
		{
			//Computes the number of lines a MultiCell of width w will take
			$cw=&$this->CurrentFont['cw'];
			if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
			$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			$s=str_replace("\r",'',$txt);
			$nb=strlen($s);
			if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
			$sep=-1;
			$i=0;
			$j=0;
			$l=0;
			$nl=1;
			while($i<$nb)
			{
				$c=$s[$i];
				if($c=="\n")
				{
					$i++;
					$sep=-1;
					$j=$i;
					$l=0;
					$nl++;
					continue;
				}
				if($c==' ')
				$sep=$i;
				$l+=$cw[$c];
				if($l>$wmax)
				{
					if($sep==-1)
					{
						if($i==$j)
						$i++;
					}
					else
					$i=$sep+1;
					$sep=-1;
					$j=$i;
					$l=0;
					$nl++;
				}
				else
				$i++;
			}
			return $nl;
		}
	}
?>