<?php
	# creating user session
	include("include/session.php");
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Debit - NBRL</title>
	</head>
	
	<?php
		
		# Import
		include("include/bootstrap.html");
		include("custom_utils.php");
		include("include/validation.php");
		include("connection.php");
		include("nbrl_constant.php");
		
		#Value initialization
		$debit_id = $debit_date  = $lr_no = $company_id = $gst_no = "";
		$truck_no = $destination = $weight = $old_booking_amount = $booking_amount = "";
		$hire_amount = $advance_amount = $return_amount = "";
		$loading_charges = "";
		
		#Error variable initialization
		$debit_success = $debit_error = "";
		$date_error = $lr_error = $company_id_error = $gst_no_error = "";
		$truck_no_error = $destination_error = $weight_error = $booking_amount_error = "";
		$hire_amount_error= $advance_amount_error = $return_amount_error = "";
		$loading_charges_error = "";
		
		#Setting label caption
		$page_title = "Add Debit Transaction";
		$page_button = "Add Debit";
		
		# edit debit detail
		if (isset($_GET['edit'])) {
			$page_title = "Update Debit Transaction";
			$page_button = "Update Debit";
			
			$connection = create_connection();
			$debit_data = mysqli_query($connection, "SELECT * FROM company_booking_debit WHERE debit_id=". $_GET['edit']);
			while ($debit_information = mysqli_fetch_array($debit_data)) {
				$debit_id = $debit_information['debit_id'];
				$debit_date = $debit_information['debit_date'];
				$lr_no = $debit_information['lr_no'];
				$company_id = $debit_information['company_id'];
				$truck_no = $debit_information['truck_no'];
				$destination = $debit_information['destination'];
				$weight = $debit_information['weight'];
				$booking_amount = $debit_information['booking_amount'];
				$old_booking_amount = $debit_information['booking_amount'];
			}
			close_connection($connection);
		}
		
		# Form button click
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			# Add or Update company credit button click operation
			if (!empty($_POST['btn_debit'])) {
				
				if (empty($_POST["debit_date"]))
				$date_error = "Please fill the date";
				$debit_date = $_POST["debit_date"];
				
				if (empty($_POST["lr_no"]))
				$lr_error = "Please fill the lr no";
				$lr_no = $_POST["lr_no"];
				
				if (empty($_POST["company_id"]))
				$company_id_error = "Please select the company name";
				$company_id = $_POST["company_id"];
				
				if (empty($_POST["gst_no"]))
				$gst_no_error = "Please fill the gst no";
				$gst_no = $_POST["gst_no"];
				
				if (empty($_POST["truck_no"]))
				$truck_no_error = "Please fill the truck no";
				$truck_no = strtoupper($_POST["truck_no"]);
				
				if (empty($_POST["destination"]))
				$destination_error = "Please fill the destination";
				$destination = $_POST["destination"];
				
				if (empty($_POST["weight"]))
				$weight_error = "Please fill the weight";
				$weight = $_POST["weight"];
				
				if (empty($_POST["booking_amount"]))
				$booking_amount_error = "Please fill the booking amount";
				$booking_amount = $_POST["booking_amount"];
				
				
				
				if(empty($date_error) && empty($lr_error) && empty($company_id_error) && empty($gst_no_error) && empty($truck_no_error) && empty($destination_error) && empty($weight_error) && empty($booking_amount_error)  && !isset($_SESSION["refresh"])){
					$connection = create_connection();
					$debit_query = "";
					if(empty($debit_id)){
						$debit_query = "INSERT INTO `company_booking_debit`(	`debit_date`,`lr_no`,`company_id`,`truck_no`,`destination`,`weight`,`booking_amount`,`create_date`
						)
						VALUES 
						('$debit_date', '$lr_no', $company_id, '$truck_no','$destination','$weight','$booking_amount','" . getCurrentDateTime() ."')";
						} else{
						$debit_query = "UPDATE company_booking_debit SET debit_date = '$debit_date', lr_no='$lr_no', company_id=$company_id, truck_no='$truck_no', destination='$destination', weight='$weight', booking_amount='$booking_amount' WHERE debit_id=$debit_id";
					}
					
					
					$balance_query = "SELECT balance_amount FROM `balance_details` WHERE balance_type='". NBRLConstants::$Cons_Company_Transaction ."' AND company_id=$company_id";
					$balance_amount = 0;
					$balance_data = mysqli_query($connection, $balance_query);
					$balance_count = mysqli_num_rows($balance_data);
					
					while ($balance_information = mysqli_fetch_array($balance_data)) {
						$balance_amount = $balance_information['balance_amount'];
					}
					$balance_amount = $balance_amount + $booking_amount;
					if(!empty($debit_id)){
						$balance_amount = $balance_amount - $old_booking_amount;
					}
					if($balance_count == 0){
						$balance_update_query = "INSERT INTO `balance_details`(
						`balance_type`,
						`company_id`,
						`balance_amount`
						)
						VALUES('". NBRLConstants::$Cons_Company_Transaction ."', $company_id, $balance_amount)";
					} else
					$balance_update_query = "UPDATE `balance_details` SET `balance_amount` = $balance_amount WHERE balance_type='". NBRLConstants::$Cons_Company_Transaction ."' AND company_id=$company_id";
					
					
					if (mysqli_query($connection, $balance_update_query)) {
						if (mysqli_query($connection, $debit_query)) {
							if(!empty($debit_id)){
								close_connection($connection);
								navigator("view_transaction.php");
							} else 
							$debit_success = "Debit entry has be added successfully";	
							
							} else {
							$debit_delete = "DELETE FROM balance_details ORDER BY balance_id DESC LIMIT 1";
							mysqli_query($connection, $debit_delete);
							
							$debit_error = "There is an issue in adding the Debit entry. Please try again later.";
							errorLogger("Error: add_debit.php:btn_debit operation -><br>" . mysqli_error($connection));
						}						
						} else {
						$debit_error = "There is an issue in adding the Debit entry. Please try again later.";
						errorLogger("Error: add_debit.php:btn_debit balance operation -><br>" . mysqli_error($connection));
					}
					
					$debit_date  = $lr_no = $company_id = $gst_no = "";
					$truck_no = $destination = $weight = $booking_amount = "";
					$hire_amount = $advance_amount = $return_amount = "";
					$loading_charges = "";
					
					close_connection($connection);
				}
			}
		}
	?>
	
	<body>
		<!-- Navigation Bar -->
		<?php navigation_bar("add_debit.php") ?>
		
		<div class="dashboard-content">
			<div class="row">
				<div class="col-sm">
					<h4 class="d-inline"><?php echo $page_title; ?></h4>
				</div>
				<div class="col-sm text-right">
					<a class='btn btn-secondary d-inline' href='menu.php'>
						<i class='fas fa-arrow-left' aria-hidden='true'></i>
					</a>
				</div>
			</div>
			<hr>
			<div class="card">
				<form class="needs-validation" method="POST">
					<input type="hidden" name="debit_id" value="<?php echo $debit_id; ?>" />
					<input type="hidden" name="old_booking" value="<?php echo $old_booking_amount; ?>" />
					<div class="card-body">
						<?php
							if(!empty($debit_error)) {
								echo "<div class='alert alert-danger'>". $debit_error ."</div>";
								}else if (!empty($debit_success)){
								echo "<div class='alert alert-success' role='success'>".$debit_success."</div>";
							}
						?>
						<div class="form-group" >
							<label for="debit_date">Date</label>
							<input type="date" class="form-control date-field <?php validate_class($date_error); ?>"  name="debit_date" value="<?php echo $debit_date; ?>">
							<?php validate_block($date_error); ?>
						</div>
						<div class="form-group" >
							<label for="lr_no">LR Number</label>
							<input type="text" class="form-control <?php validate_class($lr_error); ?>" maxlength="4" name="lr_no" value="<?php echo $lr_no; ?>" placeholder="Enter the LR number">
							<?php validate_block($lr_error); ?>
						</div>
						<div class="form-group" >
							<label for="company_id">Company Name</label>
							<select id="company_id" name="company_id"  class="form-control <?php validate_class($company_id_error); ?>">	
								<option></option>
								<?php loadCompanyName($company_id); ?>
							</select>
							<?php validate_block($company_id_error); ?>
						</div>
						<div class="form-group" >
							<label for="gst_no">GST Number</label>
							<input type="text" id="gst_no" class="form-control" name="gst_no" value="<?php echo $gst_no; ?>"  readOnly>							
						</div>
						<div class="form-group" >
							<label for="truck_no">Truck Number</label>
							<input type="text" class="form-control <?php validate_class($truck_no_error); ?>"  name="truck_no" value="<?php echo $truck_no; ?>" placeholder="Enter the truck number">
							<?php validate_block($truck_no_error); ?>
						</div>
						<div class="form-group" >
							<label for="destination">Delivery Destination</label>
							<input type="text" class="form-control <?php validate_class($destination_error); ?>"  name="destination" value="<?php echo $destination; ?>" placeholder="Enter the delivery destination">
							<?php validate_block($destination_error); ?>
						</div>
						<div class="form-group" >
							<label for="destination">Weight</label>
							<div class="input-group <?php validate_class($weight_error); ?>">
								<input type="text" name="weight" class="form-control" value="<?php echo $weight; ?>" onkeypress="numberField()" placeholder="Enter the weight">
								<div class="input-group-append">
									<span class="input-group-text">kg</span>
								</div>
							</div>
							<?php validate_block($weight_error); ?>
						</div>
						<div class="form-group">
							<label for="booking_amount">Booking Amount</label>
							<div class="input-group <?php validate_class($booking_amount_error); ?>">
								<div class="input-group-prepend">
									<span class="input-group-text">₹</span>
								</div>
								<input type="text" name="booking_amount" class="form-control" value="<?php echo $booking_amount; ?>" onkeypress="numberField()" placeholder="Enter the booking amount">
							</div>
							<?php validate_block($booking_amount_error); ?>
						</div>
						
					</div>
					<div class="card-footer">
						<input type="submit" class="btn btn-primary" name="btn_debit" value="<?php echo $page_button; ?>">
						
					</div>
				</form>
				
			</div>
			
			<br>  
		</div>
	</body>
</html>
<script type="text/javascript">
	if ( window.history.replaceState ) {
		window.history.replaceState( null, null, window.location.href );
	}
	
	window.onload = setGstNo;
	function setGstNo(){
		var company_id = document.getElementById("company_id").value;
		var component_id = "company_id";
		
		$.post('include/database_fetch.php', {id:company_id,component_id:component_id}, function(data){
			if(data!==""){
				document.getElementById("gst_no").value = data;
			} 
			else {
				document.getElementById("gst_no").value = '';
			}
		});
	}
	$(document).on("change", "#company_id", function(event){
		setGstNo();
	}); 
</script>	