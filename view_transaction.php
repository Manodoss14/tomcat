<?php
	# creating user session
	include("include/session.php");
	include("./nbrl_constant.php");
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Transaction - NBRL</title>
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
		
		$debit_total = $credit_total = "";
		
		$export_error = "";
		
		$export_parameters = "";
		
		$isCompanyFilter = true;
		
		$debit_query = "SELECT 'debit' as type, debit_id as id, debit_date as date, company_booking_debit.company_id, company_name, CONCAT(lr_no,' / ',truck_no, ' / ', destination, ' / ', weight) as particular, booking_amount as amount FROM company_booking_debit, company_details WHERE company_booking_debit.company_id = company_details.company_id";
		$credit_query = "SELECT 'credit', credit_id, credited_date, company_booking_credit.company_id, company_name, '', credited_amount FROM company_booking_credit, company_details WHERE company_booking_credit.company_id = company_details.company_id";
		
		$isNoFilter = false;
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
		
		$after_debit_query = $total_debit_query;
		$after_credit_query = $total_credit_query;
		if ($_SERVER["REQUEST_METHOD"] == "GET") {
			$connection = create_connection();
			
			## apply button click operation
			if (!empty($_GET['btn_transaction'])) {
				#appending company_id to WHERE condition
				if(!empty($_GET['company_id'])){
					$isCompanyFilter = false;
					$company_id = $_GET['company_id'];
					$debit_query = $debit_query . " AND company_booking_debit.company_id=$company_id AND ";
					$credit_query = $credit_query . " AND company_booking_credit.company_id=$company_id AND ";
					$balance_query = $balance_query . " WHERE company_id=$company_id AND balance_type='". NBRLConstants::$Cons_Company_Transaction ."'";
					$total_debit_query = $total_debit_query . " WHERE company_id=$company_id ";
					$total_credit_query = $total_credit_query . " WHERE company_id=$company_id ";
				}
				
				
				
				#appending target_period to WHERE condition
				if(!empty($_GET['target_period'])){
					$isNoFilter = true;
					$period = $_GET['target_period'];
					if($period == "yearly" && !empty($_GET['from_year']) && !empty($_GET['to_year'])){
						if(empty($_GET['company_id'])){
							$debit_query = $debit_query . " AND ";
							$credit_query = $credit_query . " AND ";
						}
						$from_year = $_GET['from_year'];
						$to_year = $_GET['to_year'];
						
						$debit_query = $debit_query . "YEAR(debit_date) BETWEEN $from_year AND $to_year ";
						$credit_query = $credit_query . "YEAR(credited_date) BETWEEN $from_year AND $to_year ";
						$total_debit_query = $total_debit_query . "AND YEAR(debit_date) BETWEEN $from_year AND $to_year ";
						$after_debit_query = $after_debit_query . "AND YEAR(debit_date) > $to_year";
						$total_credit_query = $total_credit_query . "AND YEAR(credited_date) BETWEEN $from_year AND $to_year ";
						$after_credit_query = $after_credit_query . "AND YEAR(credited_date) > $to_year";
						
						} else if($period == "monthly" && !empty($_GET['from_month']) && !empty($_GET['to_month'])){
						
						if(empty($_GET['company_id'])){
							$debit_query = $debit_query . " AND ";
							$credit_query = $credit_query . " AND ";
						}
						
						$from_month = $_GET['from_month'];
						$to_month = $_GET['to_month'];
						
						$from_value = explode("-", $from_month);
						$to_value = explode("-", $to_month);
						
						$debit_query = $debit_query . "MONTH(debit_date) BETWEEN ".$from_value['1'] ." AND ". $to_value['1'] ." AND YEAR(debit_date) BETWEEN ". $from_value['0'] ." AND ". $to_value['0'];
						$credit_query = $credit_query . "MONTH(credited_date) BETWEEN ".$from_value['1'] ." AND ". $to_value['1'] ." AND YEAR(credited_date) BETWEEN ". $from_value['0'] ." AND ". $to_value['0'];
						$total_debit_query = $total_debit_query . "AND MONTH(debit_date) BETWEEN ".$from_value['1'] ." AND ". $to_value['1'] ." AND YEAR(debit_date) BETWEEN ". $from_value['0'] ." AND ". $to_value['0'];
						$after_debit_query = $after_debit_query . "AND MONTH(debit_date) > ". $to_value['1'] ." AND YEAR(debit_date) >= ". $to_value['0'];
						$total_credit_query = $total_credit_query . "AND MONTH(credited_date) BETWEEN ".$from_value['1'] ." AND ". $to_value['1'] ." AND YEAR(credited_date) BETWEEN ". $from_value['0'] ." AND ". $to_value['0'];
						$after_credit_query = $after_credit_query . "AND MONTH(credited_date) > ". $to_value['1'] ." AND YEAR(credited_date) >= ". $to_value['0'];
						} else if($period == "date" && !empty($_GET['from_date']) && !empty($_GET['to_date'])){
						
						if(empty($_GET['company_id'])){
							$debit_query = $debit_query . " AND ";
							$credit_query = $credit_query . " AND ";
						}
						
						$from_date = $_GET['from_date'];
						$to_date = $_GET['to_date'];
						
						$debit_query = $debit_query . "debit_date BETWEEN '$from_date' AND '$to_date' ";
						$credit_query = $credit_query . "credited_date BETWEEN '$from_date' AND '$to_date' ";
						$total_debit_query = $total_debit_query . "AND debit_date BETWEEN '$from_date' AND '$to_date' ";
						$after_debit_query = $after_debit_query . "AND debit_date > '$to_date' ";
						
						$total_credit_query = $total_credit_query . "AND credited_date BETWEEN '$from_date' AND '$to_date' ";
						$after_credit_query = $after_credit_query . "AND credited_date > '$to_date' ";
					}	
				}
				
				##processing exporting procedure
				$export_parameters = array(
				"company_id" => $company_id,
				"from_year" => $from_year,
				"to_year" => $to_year,
				"from_month" => $from_month,
				"to_month" => $to_month,
				"from_date" => $from_date,
				"to_date" => $to_date
				);
				
				$export_parameters = http_build_query($export_parameters);
				
				
				$debit_query = removeString($debit_query, " AND ");
				$credit_query = removeString($credit_query, " AND ");
				
				if(empty($company_id)){
					$export_parameters = "";
					$export_error = "Please select the company id to export the data";
				}
			}
			if($isCompanyFilter){
				$balance_query = "";
				$balance_query = "SELECT
				SUM(balance_amount) AS balance_amount
				FROM
				balance_details";
			}
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
			
			if($isNoFilter){
				
				
				$opening_balance = $total_balance - ($total_debit - $total_credit) - ($after_debit - $after_credit);
				$display_total_balance = $total_balance - ($after_debit - $after_credit);
				}else {
				
				$opening_balance = $total_balance - ($total_debit - $total_credit);
				$display_total_balance = $opening_balance + ($total_debit - $total_credit);
			}
			
			$transaction_query = "(".$debit_query . ") UNION (" . $credit_query .")order by DATE,particular";
			
			$transaction_data = mysqli_query($connection, $transaction_query);
			$transaction_count = mysqli_num_rows($transaction_data);
			if($transaction_count == 0)
			$export_parameters = "";
		}
		
		
	?>
	
	<body>
		<!-- Navigation Bar -->
		<?php navigation_bar("view_transaction.php") ?>
		
		<div class="dashboard-content">
			
			<div class="card">
				<div class="card-header">
					<div class="row">
						<div class="col-sm">
							<h4 class="d-inline">View Company Transactions</h4>
						</div>
						<div class="col-sm text-right">
							<a class='btn btn-secondary d-inline' href='menu.php'>
								<i class='fas fa-arrow-left' aria-hidden='true'></i>
							</a>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class='alert alert-danger' id="alert-error" style='display:none'></div>
					<?php 
						if(!empty($export_error)) {
							echo "<div class='alert alert-info'>". $export_error ."</div>";
						}
					?>
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
										<div class="form-group">
											<label for="id_company">Company Name</label>	
											<select name="company_id" class="form-control">	
												<option></option>
												<?php loadCompanyName($company_id); ?>
											</select>
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
									<a href="report/transaction_report.php?<?php echo $export_parameters; ?>" target="_blank" class="btn btn-outline-danger <?php if(empty($export_parameters)) echo "disabled";?>"><i class='fas fa-file-pdf' aria-hidden='true'></i></a>
									<!-- <button type="submit" class="btn btn-outline-success" name="btn_export_excel" target="_blank"  value="excel"><i class='fas fa-file-excel' aria-hidden='true'></i></button> -->
								</div>
							</div>
						</div>
					</form>
					<hr>
					
					<div class="overflow-auto table-div" style="width:100%;">
						<table class="table table-sm table-bordered table-hover">
							<thead class="thead-light">
								<tr class="border border-success">
									<th class='text-center'>#</th>
									<th class='text-center'>Date</th>
									<th class='text-center'>Particulars</th>
									<th class='text-center'>Debit Amount</th>
									<th class='text-center'>Credit Amount</th>
									<th class='text-center'>Operation</th>
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
										static $transaction_number = 1; 
										echo "
										<tr>
										<td class='text-center'>$transaction_number</td>
										<td class='text-center'>". $transaction_information['date'] ."</td>
										";
										
										$editPage = $deleteData = "";
										if($transaction_information['type'] == "credit"){
											$credit_total = (double)$credit_total + (double)$transaction_information['amount'];
											echo "
											<td class='text-center'>". $transaction_information['company_name'] ."</td>
											<td></td>";
											echo "<td class='text-center'>".$INR. moneyFormat($transaction_information['amount']) ."</td>";
											$editPage = "add_credit.php";
											$deleteData = "credit_detail";
											}  else if($transaction_information['type'] == "debit"){
											$debit_total = (double)$debit_total + (double)$transaction_information['amount'];
											echo "
											<td class='text-center'>". $transaction_information['particular'] ."</td>
											<td class='text-center'>".$INR. moneyFormat($transaction_information['amount']) ."</td>";
											echo "<td></td>";
											$editPage = "add_debit.php";
											$deleteData = "debit_detail";
										}
										
										
										echo "
										<td class='text-nowrap text-center'>
										<a class='btn btn-outline-success btn-sm' href='$editPage?edit=". $transaction_information['id'] ."' >
										<i class='fas fa-pencil-alt' aria-hidden='true'></i>
										</a>
										<button class='btn btn-outline-danger btn-sm'data-toggle='modal' data-target='#dialog' data-id='". $transaction_information['id'] ."' data-title='Confirmation' data-message='Do you want to delete the company transaction?' data-type='$deleteData' >
										<i class='fas fa-trash-alt' aria-hidden='true'></i>
										</button>
										</td>
										</tr>
										";
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
								<div class="input-group-text"><b>Opening Balance</b></div>
							</div> 
							<input type="text" class="form-control" style="width:120px" id="total-debit" value="<?php echo $INR. moneyFormat($opening_balance); ?>" readonly>
						</div>
						<div class="input-group mb-2 mr-sm-2">
							<div class="input-group-prepend">
								<div class="input-group-text"><b>Total Debit</b></div>
							</div>
							<input type="text" class="form-control" style="width:120px" id="total-debit" value="<?php echo $INR. moneyFormat($debit_total); ?>" readonly>
						</div>
						<div class="input-group mb-2 mr-sm-2">
							<div class="input-group-prepend">
								<div class="input-group-text"><b>Total Credit</b></div>
							</div>
							<input type="text" class="form-control" style="width:120px" id="total-debit" value="<?php echo $INR. moneyFormat($credit_total); ?>" readonly>
						</div>
						<div class="input-group mb-2 mr-sm-2">
							<div class="input-group-prepend">
								<div class="input-group-text"><b>Total Balance</b></div>
							</div>
							<input type="text" class="form-control" style="width:120px" id="total-debit" value="<?php echo $INR. moneyFormat($display_total_balance); ?>" readonly>
						</div>
					</form>
				</div>
				<?php include("include/modal.php"); ?> 
			</div>
			<br>
		</body>
	</html>		
	
	<script type="text/javascript">
		//Confirmation dialog
		$('#dialog').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget)
			var modal = $(this)
			modal.find('.modal-title').text(button.data('title'))
			modal.find('.modal-body #message').text(button.data('message'))
			modal.find('.modal-body #id').val(button.data('id'))
			modal.find('.modal-body #type').val(button.data('type'))
		})
		
		$(document).on("click", "#ok", function(event){
			var transaction_id = document.getElementById("id").value;
			var operation_type = document.getElementById("type").value;
			
			$.post('include/delete_operation.php', {id:transaction_id,operation_type:operation_type}, function(data){
				var errorDiv = document.getElementById("alert-error");
				if(data.startsWith("success")){
					document.getElementById("info_message").innerHTML = "Company Transaction Deleted Successfully.";
					$('#dialog').modal('hide');
					$('#info_dialog').modal('show');
					} else {
					errorDiv.innerHTML = data;
					errorDiv.style.display = "block";
					$('#dialog').modal('hide');
				}
			});
		}); 
		
		$(document).on("click", "#info_ok", function(event){
			document.getElementById("alert-error").style.display = "none";
			location.reload();
		}); 
		
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