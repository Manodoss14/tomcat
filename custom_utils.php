<?php
	
	## Indian Currency
	$INR = "₹ ";
	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	  if (!empty($_POST['btn_logout'])) {
		  if (isset($_SESSION['user_name']))
			session_destroy();
		  exit(navigator("index.php"));
		}
	}
	
	# Navigator method is used for redirection from one page to another.
	function navigator($page){
	  header("Location: " . $page);
	  //echo("<script>location.href = 'http://nbrl.atwebpages.com/" . $page . "';</script>");
	}
	
	# navigation_bar method is used to load the navigation bar.
	function navigation_bar($page){
		
		# Variable in initialization
		$dashboard_active = $addDebit_active = "bg-dark";
		
		if ($page === "dashboard.php")
		$dashboard_active = "bg-info";
		else if ($page === "add_debit.php")
		$addDebit_active = "bg-info";
		echo "
		<nav class='navbar fixed-top navbar-expand-sm bg-dark navbar-dark'>
		<a class='navbar-brand' href='menu.php'>
		NBRL
		</a>
		<!-- Toggle / Collapse button -->
		<button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarSupportedContent'>
		<span class='navbar-toggler-icon'></span>
		</button>
		<div class='collapse navbar-collapse ml-auto flex-grow-0' id='navbarSupportedContent'>
		<ul class='navbar-nav' style='height:36px'>
		<li class='nav-item'>
		<form  method='POST'>
		<input type='submit' value='Logout' class='btn btn-primary' name='btn_logout'>
		</form>
		</li>
		</ul>
		<ul>
		</ul>
		</div>
		</nav>
		";
	}
	
	# getCurrentDateTime method is used to get the current date time based on timezone Asia Calcutta
	function getCurrentDateTime(){
	  date_default_timezone_set("Asia/Calcutta");
		return date("Y-m-d H:i:s");
	}
	
	#Select option load
	function loadCompanyName($fieldName){
		$connection = create_connection();
		$company_data = mysqli_query($connection, "SELECT * FROM company_details order by company_name ASC");
		while($company_information = mysqli_fetch_array($company_data)) {
			if(!empty($fieldName) && $fieldName ==$company_information['company_id'])
			$selected = "selected";
			else
      $selected = '';
			echo "<option value='" . $company_information['company_id']."' ". $selected .">" . $company_information['company_name'] . "</option>";
		}
		close_connection($connection);
	}
	
	##Indian Currency Formator.
	function moneyFormat($amount) {
		$amount_array = explode('.',round($amount,2,PHP_ROUND_HALF_UP));
		$amount_value = $amount_array[0];
    $explrestunits = "" ;
    if(strlen($amount_value)>3) {
			$lastthree = substr($amount_value, strlen($amount_value)-3, strlen($amount_value));
			$restunits = substr($amount_value, 0, strlen($amount_value)-3); // extracts the last three digits
			$restunits = (strlen($restunits)%2 == 1)?"0".$restunits:$restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
			$expunit = str_split($restunits, 2);
			for($i=0; $i<sizeof($expunit); $i++) {
				// creates each of the 2's group and adds a comma to the end
				if($i==0) {
					$explrestunits .= (int)$expunit[$i].","; // if is first value , convert into integer
					} else {
					$explrestunits .= $expunit[$i].",";
				}
			}
			$converted_amount = $explrestunits.$lastthree;
			} else {
			$converted_amount = $amount_value;
		}
		if(isset($amount_array[1]))
		return $converted_amount.".".$amount_array[1]; // writes the final format where $currency is the currency symbol.
	  else
		return $converted_amount;
	}
	
	##endsWith function used to check whether the string ends substring or not.
	function endsWith($string, $endString) { 
		$len = strlen($endString); 
		if ($len == 0) { 
			return true; 
		} 
		if(substr($string, -$len) === $endString)
		return true; 
		else
		return false;
	}
	
	##removeString function is used to remove removeString from dataString
	function removeString($dataString, $removeString){
		if(endsWith($dataString, $removeString)){
			$lastOccurance = strrpos($dataString, $removeString);
			return substr_replace($dataString, " ", $lastOccurance);
		}
		return $dataString;
	}
?>