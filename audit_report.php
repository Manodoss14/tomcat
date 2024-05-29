<?php
	# creating user session
	include("include/session.php");
	include("./nbrl_constant.php");
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Audit Report - NBRL</title>
	</head>
	
	<?php
		# Import
		include("include/bootstrap.html");
		include("custom_utils.php");
		include("connection.php");
		include("include/secure.php");
		
		
		$company_id = $period = "";
		$from_year = $to_year = "";
		$from_month = $to_month = "";
		$from_date = $to_date = "";
		
		$debit_total = "";
		if ($_SERVER["REQUEST_METHOD"] == "GET") {
			$connection = create_connection();
			$debit_query = "SELECT  debit_id as id, debit_date as date, company_booking_debit.company_id, company_name, lr_no,truck_no,destination,gst_number, booking_amount as amount FROM company_booking_debit, company_details WHERE company_booking_debit.company_id = company_details.company_id " ;
			#appending target_period to WHERE condition
			if (!empty($_GET['btn_transaction'])) {
				if(!empty($_GET['target_period'])){
					$period = $_GET['target_period'];
					if($period == "yearly" && !empty($_GET['from_year']) && !empty($_GET['to_year'])){
						
						$debit_query = $debit_query . " AND ";
						
						$from_year = $_GET['from_year'];
						$to_year = $_GET['to_year'];
						
						$debit_query = $debit_query . "YEAR(debit_date) BETWEEN $from_year AND $to_year ";
						} else if($period == "monthly" && !empty($_GET['from_month']) && !empty($_GET['to_month'])){
						
						
						$debit_query = $debit_query . " AND ";
						
						
						$from_month = $_GET['from_month'];
						$to_month = $_GET['to_month'];
						
						$from_value = explode("-", $from_month);
						$to_value = explode("-", $to_month);
						
						$debit_query = $debit_query . "MONTH(debit_date) BETWEEN ".$from_value['1'] ." AND ". $to_value['1'] ." AND YEAR(debit_date) BETWEEN ". $from_value['0'] ." AND ". $to_value['0'];
						} else if($period == "date" && !empty($_GET['from_date']) && !empty($_GET['to_date'])){
						
						
						$debit_query = $debit_query . " AND ";
						
						
						$from_date = $_GET['from_date'];
						$to_date = $_GET['to_date'];
						
						$debit_query = $debit_query . "debit_date BETWEEN '$from_date' AND '$to_date' ";
						
						
					}	
				}
			}
			$transaction_query = $debit_query . " order by DATE,lr_no";
			$transaction_data = mysqli_query($connection, $transaction_query);
			$transaction_count = mysqli_num_rows($transaction_data);
		}
		$export_parameters = array(
				"from_year" => $from_year,
				"to_year" => $to_year,
				"from_month" => $from_month,
				"to_month" => $to_month,
				"from_date" => $from_date,
				"to_date" => $to_date
				);
				
				$export_parameters = http_build_query($export_parameters);
	?>
	
	<body>
		<!-- Navigation Bar -->
		<?php navigation_bar("audit_report.php") ?>
		<div class="dashboard-content">
			<div class="card">
				<div class="card-header">
					<div class="row">
						<div class="col-sm">
							<h4 class="d-inline">Audit Report</h4>
						</div>
						<div class="col-sm text-right">
							<a class='btn btn-secondary d-inline' href='menu.php'>
								<i class='fas fa-arrow-left' aria-hidden='true'></i>
							</a>
						</div>
					</div>
				</div>
				<div class="card-body">
					<form class="needs-validation" method="GET" novalidate>
						<div id="id_transaction_accordion">
							<div class="card">
								<div class="card-header">
									<a class="card-link" data-toggle="collapse" href="#id_transaction_filter">
										<i class='fas fa-filter' aria-hidden='true'></i>
										Filter
									</a>
								</div>
								<div id="id_transaction_filter" class="collapse" data-parent="#id_transaction_accordion">
									<div class="card-body bg-light">
										<div class="form-group">
											<label for="id_period">Target Period</label>	
											<select id="target_period" name="target_period" class="form-control">	
												<option></option>
												<option value="yearly" <?php if($period == "yearly") echo "selected";?>>Yearly</option>
												<option value="monthly" <?php if($period == "monthly") echo "selected";?>>Monthly</option>
												<option value="date" <?php if($period == "date") echo "selected";?>>Specific Date</option>
											</select>
										</div>
										<div class="form-group" id="yearly_div" style="display:none">
											<label class="sr-only" for="id_period">Yearly Date</label>	
											<div class="row">
												<div class="col-md-auto">
													<input type="number" class="form-control date-field" name="from_year" value="<?php echo $from_year; ?>" min="1900" max="2900" placeholder="YYYY" >
												</div>
												<span>~</span>
												<div class="col-md-auto">
													<input type="number" class="form-control date-field" name="to_year" value="<?php echo $to_year; ?>" min="1900" max="2900" placeholder="YYYY" >
												</div>
											</div>
										</div>
										<div class="form-group" id="monthly_div" style="display:none">
											<label class="sr-only" for="id_period">Monthly Date</label>	
											<div class="row">
												<div class="col-md-auto">
													<input type="month" class="form-control date-field" name="from_month" value="<?php echo $from_month; ?>">
												</div>
												<span>~</span>
												<div class="col-md-auto">
													<input type="month" class="form-control date-field" name="to_month" value="<?php echo $to_month; ?>">
												</div>
											</div>
										</div>
										<div class="form-group" id="date_div" style="display:none">
											<label class="sr-only" for="id_period">Specific Date</label>	
											<div class="row">
												<div class="col-md-auto">
													<input type="date" class="form-control date-field" name="from_date" value="<?php echo $from_date; ?>">
												</div>
												<span>~</span>
												<div class="col-md-auto">
													<input type="date" class="form-control date-field" name="to_date" value="<?php echo $to_date; ?>">
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<br>
							<input type="submit" value="Search" class="btn btn-primary" name="btn_transaction">
							<input type="submit" value="Clear" class="btn" name="btn_clear">
							
							<div class="btn-toolbar mb-3 float-right">
								<div class="btn-group mr-2" role="group">
									<button type="button" class="btn btn-outline-dark border-0" disabled>Export Data</button>
								</div>
								<div class="btn-group mr-2" role="group">
									<a href="report/audit_report.php?<?php echo $export_parameters; ?>" target="_blank" class="btn btn-outline-danger"><i class='fas fa-file-pdf' aria-hidden='true'></i></a>
									<!-- <button type="submit" class="btn btn-outline-success" name="btn_export_excel" target="_blank"  value="excel"><i class='fas fa-file-excel' aria-hidden='true'></i></button> -->
								</div>
							</div>
						</div>
						
					</form>
					<div class="overflow-auto table-div" style="width:100%;">
						<table class="table table-sm table-bordered table-hover">
							<thead class="thead-light">
								<tr class="border border-success">
									<th class='text-center'>#</th>
									<th class='text-center'>Date</th>
									<th class='text-center'>LR No</th>
									<th class='text-center'>Party Name</th>
									<th class='text-center'>GST No</th>
									<th class='text-center'>Truck No</th>
									<th class='text-center'>Destination</th>
									<th class='text-center'>Amount</th>
								</tr>
							</thead>
							<tbody>
								<?php
									if($transaction_count == 0){
										echo "<tr>";   		   
										echo "<td class='alert-info' colspan='6' align='center'>There is no transaction</td>";
										echo "</tr>"; 
									}
									while ($transaction_information = mysqli_fetch_array($transaction_data)) {
										$debit_total = (double)$debit_total + (double)$transaction_information['amount'];
										static $transaction_number = 1; 
										echo "
										<tr>
										<td class='text-center'>$transaction_number</td>
										<td class='text-center'>". $transaction_information['date'] ."</td>
										<td class='text-center'>". $transaction_information['lr_no'] ."</td>
										<td class='text-center'>". $transaction_information['company_name'] ."</td>
										<td class='text-center'>". $transaction_information['gst_number'] ."</td>
										<td class='text-center'>". $transaction_information['truck_no'] ."</td>
										<td class='text-center'>".$transaction_information['destination'] ."</td>
										<td class='text-center'>".$INR. moneyFormat($transaction_information['amount']) ."</td> </tr>";
										
										
										$transaction_number++;
									}
									
									close_connection($connection);
								?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="card-footer">
					<form class="form-inline float-right">
						<div class="input-group mb-2 mr-sm-2">
							<div class="input-group-prepend">
								<div class="input-group-text"><b>Total Debit</b></div>
							</div>
							<input type="text" class="form-control" style="width:120px" id="total-debit" value="<?php echo $INR. moneyFormat($debit_total); ?>" readonly>
						</div>
					</form>
				</div>
				
			</div>
			<hr>
			
		</body>
	</html>
	<script type="text/javascript">
		
		//Target Period change operation
		$(document).on("change", "#target_period", function(event){
			var target_period = document.getElementById("target_period").value;
			var yearly_div = document.getElementById("yearly_div");
			var monthly_div = document.getElementById("monthly_div");
			var date_div = document.getElementById("date_div");
			
			if(target_period == "yearly"){
				yearly_div.style.display = "block";
				monthly_div.style.display = "none";
				date_div.style.display = "none";
				} else if(target_period == "monthly"){
				yearly_div.style.display = "none";
				monthly_div.style.display = "block";
				date_div.style.display = "none";
				}else if(target_period == "date"){
				yearly_div.style.display = "none";
				monthly_div.style.display = "none";
				date_div.style.display = "block";
			}
			
		}); 
		
		$('#target_period').trigger("change");
	</script>			