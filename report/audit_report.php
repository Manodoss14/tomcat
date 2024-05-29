<?php
	require('pdf.php');
	
	include("../connection.php");
	include("../custom_utils.php");
	include("../nbrl_constant.php");
	
	if ($_SERVER["REQUEST_METHOD"] == "GET") {
		$pdf=new pdf();
		
		$pdf->SetTitle("Company Transaction Report");
		$pdf->AddPage();
		$pdf->SetMargins(5,5,5);
		//Table with 20 rows and 4 columns
		$pdf->SetWidths(array(7, 55, 40, 67, 30));
		
		
		##Processing the Debit Details
		$debit_query = "SELECT  debit_id as id, debit_date as date, company_booking_debit.company_id, company_name, lr_no,truck_no,destination,gst_number, booking_amount as amount FROM company_booking_debit, company_details WHERE company_booking_debit.company_id = company_details.company_id " ;
		
		if(!empty($_GET['from_year']) && !empty($_GET['to_year'])){
			$from_year = $_GET['from_year'];
			$to_year = $_GET['to_year'];
			
			$debit_query = $debit_query . "AND YEAR(debit_date) BETWEEN $from_year AND $to_year ";
			
			} else if(!empty($_GET['from_month']) && !empty($_GET['to_month'])){
			$from_month = $_GET['from_month'];
			$to_month = $_GET['to_month'];
			
			$from_value = explode("-", $from_month);
			$to_value = explode("-", $to_month);
			
			$debit_query = $debit_query . "AND MONTH(debit_date) BETWEEN ".$from_value['1'] ." AND ". $to_value['1'] ." AND YEAR(debit_date) BETWEEN ". $from_value['0'] ." AND ". $to_value['0'];
			
			} else if(!empty($_GET['from_date']) && !empty($_GET['to_date'])){
			$from_date = $_GET['from_date'];
			$to_date = $_GET['to_date'];
			
			$debit_query = $debit_query . "AND debit_date BETWEEN '$from_date' AND '$to_date' ";
		} 
		
		#Ordering data based on date ,LR No.
		$transaction_query = $debit_query . " order by DATE,lr_no";
		$connection = create_connection();
		
		##Getting company details
		$company_data = mysqli_query($connection, $transaction_query);
		
		
		#Balance amount initialization
		$total_balance = 0;
		$totalDebit=0;
		
		
		
		
		
		#Header debit transaction details
		$pdf->SetFont('verdana','B',12);
		$pdf->SetXY(5, 40);
		$pdf->Cell(40,7,'Transactions',0,0,'L');
		$pdf->Ln(10);
		
		$header = array('#', 'Date', 'LR No', 'Party Name', 'GST No','Truck No', 'Destination', 'Amount');
		
		$pdf->headerData = $header;
		
		// Colors, line width and bold font
		$pdf->SetFillColor(128,128,128);
		$pdf->SetTextColor(0);
		$pdf->SetLineWidth(.1);
		$pdf->SetFont('verdana','B',10);
		
		// Header
		$pdf->SetXY(5, 50);
		$pdf->widths = $w = array(8, 25, 12, 40, 40, 27, 23,22);
		for($i=0;$i<count($header);$i++)
		$pdf->Cell($w[$i],7,$header[$i],1,0,'C',true);
		$pdf->Ln();
		
		$pdf->SetFont('verdana','',10);
		
		
		
		static $rowValue = 60; 
		while ($debit_information = mysqli_fetch_array($company_data)) {
			static $debit_number = 1; 
			
			$pdf->Row(array($debit_number,
			$debit_information['date'],
			$debit_information['lr_no'],
			$debit_information['company_name'],
			$debit_information['gst_number'],
			$debit_information['truck_no'],
			$debit_information['destination'],
			moneyFormat($debit_information['amount'])));
			$totalDebit = $totalDebit + $debit_information['amount'];
			  
			$debit_number++;
			$rowValue = $rowValue + 8;
		} 
		$pdf->Ln();
		
		$pdf->SetFillColor(128,128,128);
		$pdf->SetTextColor(0);
		$pdf->SetFont('verdana','B',10);
		
		// Header
		$pdf->SetXY(157	, 42);
		$pdf->Cell(23,6,"Total",1,0,'C',true);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(22,6,"" .moneyFormat($totalDebit),1,0,'L',true);
		
		$pdf->Ln();	
		
		
		close_connection($connection);
		
		$pdfFileName = "NBRL_Booking_Report_" . getCurrentDateTime() .".pdf";
		$pdf->Output($pdfFileName, "I");
		
		
		} else{
		echo "<script>window.close();</script>";
	}
?>