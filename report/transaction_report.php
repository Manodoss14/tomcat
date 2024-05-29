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
		
		
		##Processing the Debit, Credit and Company Details
		$balance_query = "
		SELECT
    balance_amount
		FROM
    balance_details
		";
		$total_debit_query = "
		SELECT
    SUM(booking_amount) as total_debit
		FROM
    company_booking_debit
		";
		$total_credit_query = "
		SELECT
    SUM(credited_amount) as total_credit
		FROM
    company_booking_credit
		";
		$debit_pdf_query="
		SELECT 
		debit_date as date, lr_no, truck_no, weight, destination, booking_amount,''
		FROM
		company_booking_debit
		";
		$credit_pdf_query="
		SELECT
		credited_date,'','','','','',credited_amount
		FROM
		company_booking_credit
		";
		$company_pdf_query = "
		SELECT
		company_name, gst_number
		FROM
		company_details
		";
		
		$company_id = $_GET['company_id'];
		$balance_query = $balance_query . " WHERE company_id=$company_id AND balance_type='". NBRLConstants::$Cons_Company_Transaction ."'";
		$debit_pdf_query = $debit_pdf_query . " WHERE company_id=$company_id ";
		$credit_pdf_query = $credit_pdf_query . " WHERE company_id=$company_id ";
		$company_pdf_query = $company_pdf_query . " WHERE company_id=$company_id";
		$total_debit_query = $total_debit_query . " WHERE company_id=$company_id ";
		$total_credit_query = $total_credit_query . " WHERE company_id=$company_id ";
		
		$after_debit_query = $total_debit_query;
		$after_credit_query = $total_credit_query;
		
		$isNoFilter = false;
		
		if(!empty($_GET['from_year']) && !empty($_GET['to_year'])){
			$from_year = $_GET['from_year'];
			$to_year = $_GET['to_year'];
			
			$debit_pdf_query = $debit_pdf_query . "AND YEAR(debit_date) BETWEEN $from_year AND $to_year ";
			$total_debit_query = $total_debit_query . "AND YEAR(debit_date) BETWEEN $from_year AND $to_year ";
			$after_debit_query = $after_debit_query . "AND YEAR(debit_date) > $to_year";
			$credit_pdf_query = $credit_pdf_query . "AND YEAR(credited_date) BETWEEN $from_year AND $to_year ";
			$total_credit_query = $total_credit_query . "AND YEAR(credited_date) BETWEEN $from_year AND $to_year ";
			$after_credit_query = $after_credit_query . "AND YEAR(credited_date) > $to_year";
			
			} else if(!empty($_GET['from_month']) && !empty($_GET['to_month'])){
			$from_month = $_GET['from_month'];
			$to_month = $_GET['to_month'];
			
			$from_value = explode("-", $from_month);
			$to_value = explode("-", $to_month);
			
			$debit_pdf_query = $debit_pdf_query . "AND MONTH(debit_date) BETWEEN ".$from_value['1'] ." AND ". $to_value['1'] ." AND YEAR(debit_date) BETWEEN ". $from_value['0'] ." AND ". $to_value['0'];
			$total_debit_query = $total_debit_query . "AND MONTH(debit_date) BETWEEN ".$from_value['1'] ." AND ". $to_value['1'] ." AND YEAR(debit_date) BETWEEN ". $from_value['0'] ." AND ". $to_value['0'];
			$after_debit_query = $after_debit_query . "AND MONTH(debit_date) > ". $to_value['1'] ." AND YEAR(debit_date) >= ". $to_value['0'];
			$credit_pdf_query = $credit_pdf_query . "AND MONTH(credited_date) BETWEEN ".$from_value['1'] ." AND ". $to_value['1'] ." AND YEAR(credited_date) BETWEEN ". $from_value['0'] ." AND ". $to_value['0'];
			$total_credit_query = $total_credit_query . "AND MONTH(credited_date) BETWEEN ".$from_value['1'] ." AND ". $to_value['1'] ." AND YEAR(credited_date) BETWEEN ". $from_value['0'] ." AND ". $to_value['0'];
			$after_credit_query = $after_credit_query . "AND MONTH(credited_date) > ". $to_value['1'] ." AND YEAR(credited_date) >= ". $to_value['0'];
			} else if(!empty($_GET['from_date']) && !empty($_GET['to_date'])){
			$from_date = $_GET['from_date'];
			$to_date = $_GET['to_date'];
			
			$debit_pdf_query = $debit_pdf_query . "AND debit_date BETWEEN '$from_date' AND '$to_date' ";
			$total_debit_query = $total_debit_query . "AND debit_date BETWEEN '$from_date' AND '$to_date' ";
			$after_debit_query = $after_debit_query . "AND debit_date > '$to_date' ";
			$credit_pdf_query = $credit_pdf_query . "AND credited_date BETWEEN '$from_date' AND '$to_date' ";
			$total_credit_query = $total_credit_query . "AND credited_date BETWEEN '$from_date' AND '$to_date' ";
			$after_credit_query = $after_credit_query . "AND credited_date > '$to_date' ";
		} else {
		    $isNoFilter = true;
		}
		
		#Ordering data based on date.
		$debit_pdf_query = $debit_pdf_query ;
		$credit_pdf_query = $credit_pdf_query ;
		
		$connection = create_connection();
		
		##Getting company details
		$company_data = mysqli_query($connection, $company_pdf_query);
		while ($company_information = mysqli_fetch_array($company_data)) {
			
			// Colors, line width and bold font
			$pdf->SetFillColor(128,128,128);
			$pdf->SetTextColor(0);
			$pdf->SetLineWidth(.1);
			$pdf->SetFont('verdana','B',10);
			
			// Header
			$pdf->SetXY(5, 43);
			$pdf->Cell(40,7,"Company Name",1,0,'C',true);
			$pdf->SetFillColor(255,255,255);
			$pdf->Cell(151,7," " .$company_information['company_name'],1,0,'L',true);
			
			$pdf->SetFillColor(128,128,128);
			$pdf->SetXY(5, 50);
			$pdf->Cell(40,7,"GST Number",1,0,'C',true);
			$pdf->SetFillColor(255,255,255);
			$pdf->Cell(151,7," " .$company_information['gst_number'],1,0,'L',true);
			
		}
		
		$pdf->Ln();
		
		#Balance amount initialization
		$total_balance = 0;
		$total_debit = $after_debit = 0;
		$total_credit = $after_credit = 0;
		
		#Balance Amount detail
		$balance_data = mysqli_query($connection, $balance_query);
		while ($balance_information = mysqli_fetch_array($balance_data)) {
			$total_balance = $balance_information['balance_amount'];
		}	
		$debit_balance_data = mysqli_query($connection, $total_debit_query);
		while ($debit_balance_information = mysqli_fetch_array($debit_balance_data)) {
			$total_debit = $debit_balance_information['total_debit'];
		}	
		$after_debit_balance_data = mysqli_query($connection, $after_debit_query);
		while ($after_debit_balance_information = mysqli_fetch_array($after_debit_balance_data)) {
			$after_debit = $after_debit_balance_information['total_debit'];
		}	
		$after_credit_balance_data = mysqli_query($connection, $after_credit_query);
		while ($after_credit_balance_information = mysqli_fetch_array($after_credit_balance_data)) {
			$after_credit = $after_credit_balance_information['total_credit'];
		}
		$credit_balance_data = mysqli_query($connection, $total_credit_query);
		while ($credit_balance_information = mysqli_fetch_array($credit_balance_data)) {
			$total_credit = $credit_balance_information['total_credit'];
		}		
		
		$opening_balance = $display_total_balance = 0;
		if(!$isNoFilter){
		$opening_balance = $total_balance - ($total_debit - $total_credit) - ($after_debit - $after_credit);
		$display_total_balance = $total_balance - ($after_debit - $after_credit);
		}else {
		$opening_balance = $total_balance - ($total_debit - $total_credit);
		$display_total_balance = $opening_balance + ($total_debit - $total_credit);
		}
		
		$pdf->SetFillColor(128,128,128);
		$pdf->SetXY(5, 60);
		$pdf->Cell(40,7,"Opening Balance",1,0,'C',true);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(40,7," " .moneyFormat($opening_balance),1,0,'L',true);
		
		
		
		
		#Header debit transaction details
		$pdf->SetFont('verdana','B',12);
		$pdf->SetXY(4, 77);
		$pdf->Cell(199,5,'Company Transactions',0,0,'L');
		$pdf->Ln(10);
		
		$header = array('#', 'Date', 'LR No', 'Truck No', 'Weight', 'Destination', 'Debit Amount','Credit Amount');
		
		$pdf->headerData = $header;
		
		// Colors, line width and bold font
		$pdf->SetFillColor(128,128,128);
		$pdf->SetTextColor(0);
		$pdf->SetLineWidth(.1);
		$pdf->SetFont('verdana','B',10);
		
		// Header
		$pdf->SetXY(5, 84);
		$pdf->widths = $w = array(10, 25, 15, 27, 25, 25, 32,32);
		for($i=0;$i<count($header);$i++)
		$pdf->Cell($w[$i],7,$header[$i],1,0,'C',true);
		$pdf->Ln();
		
		$pdf->SetFont('verdana','',10);
		
		static $rowValue = 91; 
		$totalDebit = 0;
		$pdf_result_query = "(".$debit_pdf_query . ") UNION (" . $credit_pdf_query .")order by DATE"; 
		$debit_data = mysqli_query($connection, $pdf_result_query);
		
		while ($debit_information = mysqli_fetch_array($debit_data)) {
			static $debit_number = 1; 
			if(empty($debit_information['lr_no'])){
				$pdf->Row(
				$pdf->Cell(10,5,$debit_number,1,0,'L',false),
				$pdf->Cell(25,5,$debit_information['date'],1,0,'L',false),
				$pdf->Cell(15+27+25+25+32,5,"Credited Amount ",1,0,'R',false),
				$pdf->Cell(32,5,moneyFormat($debit_information['']),1,0,'L',false));
				$pdf->Ln();
			}else{
			$pdf->Row(array($debit_number,
			$debit_information['date'],
			$debit_information['lr_no'],
			$debit_information['truck_no'],
			$debit_information['weight'],
			$debit_information['destination'],
			moneyFormat($debit_information['booking_amount']),
			$debit_information['']));
			$totalDebit = $totalDebit + $debit_information['booking_amount'];
			}
			
			$debit_number++;
			$rowValue = $rowValue + 5;
		} 
		$pdf->Ln();
		
		$pdf->SetFillColor(128,128,128);
		$pdf->SetTextColor(0);
		$pdf->SetLineWidth(.1);
		$pdf->SetFont('verdana','B',10);
		
		// Header
		$pdf->SetXY(5, $rowValue);
		$pdf->Cell(127,6,"Total",1,0,'C',true);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(32,6," " .moneyFormat($totalDebit),1,0,'L',true);
		
		$pdf->Ln();
		
		
		$totalCredit = 0;
		$credit_data = mysqli_query($connection, $credit_pdf_query);
		while ($credit_information = mysqli_fetch_array($credit_data)) {
			static $credit_number = 1; 
			
			$totalCredit = $totalCredit + $credit_information['credited_amount'];
			$credit_number++;
		} 
		
		#Header
		$pdf->SetXY(164, $rowValue);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(32,6," " .moneyFormat($totalCredit),1,0,'L',true);
		
		$pdf->SetFillColor(128,128,128);
		$pdf->SetXY(132, $rowValue+10);
		$pdf->Cell(32,7,"Total Balance",1,0,'C',true);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell(32,7," " .moneyFormat($display_total_balance),1,0,'L',true);
		
		$pdf->Ln();
		
		close_connection($connection);
		
		$pdfFileName = "Company_Transaction_Report_" . getCurrentDateTime() .".pdf";
		$pdf->Output($pdfFileName, "I");
		
		
		} else{
		echo "<script>window.close();</script>";
	}
?>