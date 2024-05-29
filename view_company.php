<?php
	# creating user session
	include("include/session.php");
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>View Company - NBRL</title>
	</head>
	
	<?php
		# Import
		include("include/bootstrap.html");
		include("custom_utils.php");
		include("connection.php");
	?>
	
	<body>
		<!-- Navigation Bar -->
		<?php navigation_bar("view_company.php") ?>
		
		<div class="dashboard-content">
			
			<div class="card">
				<div class="card-header">
					<div class="row">
						<div class="col-sm">
							<h4 class="d-inline">View Company Detials</h4>
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
					
					<div class="btn-toolbar mb-3" role="toolbar">
						<div class="btn-group mr-2" role="group">
							<button type="button" class="btn btn-outline-dark border-0" disabled>Export Data</button>
						</div>
						<div class="btn-group mr-2" role="group">
							<a href="report/company_report.php" target="_blank" class="btn btn-outline-danger"><i class='fas fa-file-pdf' aria-hidden='true'></i></a>
							<!-- <button type="button" class="btn btn-outline-success"><i class='fas fa-file-excel' aria-hidden='true'></i></button> -->
						</div>
					</div>
					
					<div class="overflow-auto table-div" style="height:43em; width:100%;">
						<table class="table table-bordered table-hover">
							<thead class="thead-light">
								<tr class="border border-success">
									<th>#</th>
									<th>Company Name</th>
									<th>GST No</th>
									<th>Company Address</th>
									<th>Contact Number</th>
									<th>Operation</th>
								</tr>
							</thead>
							<tbody>
								<?php
                  $connection = create_connection();
                  $company_data = mysqli_query($connection, "SELECT * FROM company_details order by company_name ASC");
                  while ($company_information = mysqli_fetch_array($company_data)) {
                    static $company_number = 1; 
                    echo "
                    <tr>
										<td class='text-center'>$company_number</td>
										<td>". $company_information['company_name'] ."</td>
										<td>". $company_information['gst_number'] ."</td>
										<td>". $company_information['company_address'] ."</td>
										<td>". $company_information['company_contactno'] ."</td>
										<td class='text-nowrap text-center'>
										<a class='btn btn-outline-success btn-sm' href='add_company.php?edit=". $company_information['company_id'] ."' >
										<i class='fas fa-pencil-alt' aria-hidden='true'></i>
										</a>
										<button class='btn btn-outline-danger btn-sm'data-toggle='modal' data-target='#dialog' data-id='". $company_information['company_id'] ."' data-title='Confirmation' data-message='Do you want to delete the company detail?' data-type='company_details' >
										<i class='fas fa-trash-alt' aria-hidden='true'></i>
										</button>
										</td>
                    </tr>
                    ";
                    $company_number++;
									}
									close_connection($connection);
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			
			<?php include("include/modal.php"); ?> 
		</div>
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
		var company_id = document.getElementById("id").value;
		var operation_type = document.getElementById("type").value;
		
		$.post('include/delete_operation.php', {id:company_id,operation_type:operation_type}, function(data){
			var errorDiv = document.getElementById("alert-error");
			if(data==="success"){
				errorDiv.style.display = "none";
				location.reload();
				} else {
				errorDiv.innerHTML = data;
				errorDiv.style.display = "block";
				$('#dialog').modal('hide')
			}
		});
	}); 
</script>	
