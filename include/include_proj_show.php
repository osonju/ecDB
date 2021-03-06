<?php
class ProjectShow {
	public function ProjectShowComponents()
	{
		require_once('login/auth.php');
		include('mysql_connect.php');

		$project_id = (int)mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_GET["proj_id"]);

		$project_owner_idRow = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT project_owner FROM projects WHERE project_id = ".$project_id."");
		$project_owner_id = mysqli_fetch_assoc($project_owner_idRow);
		$project_owner_id = $project_owner_id['project_owner'];

		if(isset($_GET['by']))
		{
			$by			=	strip_tags(mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_GET["by"]));
			$order_q	=	strip_tags(mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_GET["order"]));

			if($order_q == 'desc' or $order_q == 'asc'){
				$order = $order_q;
			}
			else{
				$order = 'asc';
			}

			if($by == 'price' or $by == 'quantity' or $by == 'order_quantity') {
				$GetDataComponentsAll = "SELECT * FROM projects_data, data WHERE projects_data.projects_data_component_id = data.id AND projects_data.projects_data_project_id = ".$project_id." ORDER by ".$by." +0 ".$order."";
			}
			elseif($by == 'name' or $by == 'category' or $by == 'manufacturer' or $by =='package' or $by =='smd') {
				$GetDataComponentsAll = "SELECT * FROM projects_data, data WHERE projects_data.projects_data_component_id = data.id AND projects_data.projects_data_project_id = ".$project_id." ORDER by ".$by." ".$order."";
			}
			else {
				$GetDataComponentsAll = "SELECT * FROM projects_data, data WHERE projects_data.projects_data_component_id = data.id AND projects_data.projects_data_project_id = ".$project_id." ORDER by name ASC";
			}
		}
		else {
			$GetDataComponentsAll = "SELECT * FROM projects_data, data WHERE projects_data.projects_data_component_id = data.id AND projects_data.projects_data_project_id = ".$project_id." ORDER by name ASC";
		}

		$sql_exec = mysqli_query($GLOBALS["___mysqli_ston"], $GetDataComponentsAll);
		while($showDetails = mysqli_fetch_array($sql_exec))
		{
			echo "<tr>";

			if(isset($_SESSION['SESS_MEMBER_ID']) == true)
			{
				echo '<td class="edit"><a href="edit_component.php?edit=';
				echo $showDetails['id'];
				echo '"><span class="icon medium pencil"></span></a></td>';

				echo '<td><a href="component.php?view=';
				echo $showDetails['id'];
				echo '">';

				echo $showDetails['name'];
				echo "</a></td>";
			}
			else
			{
				echo '<td></td>';
				echo '<td>'.$showDetails['name'].'</td>';
			}



			echo "<td>";
				$sql_exec_catname = mysqli_query($GLOBALS["___mysqli_ston"], "select c.name h, c.id cid, cs.subcategory s, cs.id csid from category c, category_sub cs where c.id = cs.category_id and cs.id = ".$showDetails['category']."");

				while($showDetailsCat = mysqli_fetch_array($sql_exec_catname))
				{
					$catname = $showDetailsCat['h'];
				}

				echo $catname;
			echo "</td>";

			echo "<td>";
				$manufacturer = $showDetails['manufacturer'];
				if ($manufacturer == ""){
					echo "-";
				}
				else{
					echo $manufacturer;
				}
			echo "</td>";

			echo "<td>";
				$package = $showDetails['package'];
				if ($package == ""){
					echo "-";
				}
				else{
					echo $package;
				}
			echo "</td>";

			echo "<td>";
				$smd = $showDetails['smd'];
				if ($smd == "No"){
					echo '<span class="icon medium checkboxUnchecked"></span>';
				}
				else{
					echo '<span class="icon medium checkboxChecked"></span>';
				}
			echo "</td>";

			echo "<td class='priceCol'>";
				$price = $showDetails['price'];
				if ($price == ""){
					echo "-";
				}
				else{
					echo $price;
				}
			echo "</td>";

			echo "<td>";
				$quantity = $showDetails['quantity'];
				if ($quantity == ""){
					echo "-";
				}
				else{
					echo $quantity;
				}
			echo "</td>";

			echo "<td>";
				$quantity = $showDetails['order_quantity'];
				if ($quantity == ""){
					echo "-";
				}
				else{
					echo $quantity;
				}
			echo "</td>";


			echo "<td>";

			$comp_id = $showDetails['id'];
			$ShowQuant = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT projects_data_quantity FROM projects_data WHERE projects_data_component_id = '$comp_id' AND projects_data_project_id = '$project_id'");
			$quant = mysqli_fetch_assoc($ShowQuant);

			$quantity = $quant['projects_data_quantity'];
				if ($quantity == ""){
					echo "-";
				}
				else{
					echo $quantity;
				}
			echo "</td>";

				if(isset($_SESSION['SESS_MEMBER_ID']) && trim($_SESSION['SESS_MEMBER_ID']) == $project_owner_id )
				{
					echo "<td>";
						$bin_location = $showDetails['bin_location'];
						if ($bin_location == ""){
							echo "-";
						}
						else{
							echo $bin_location;
						}
					echo "</td>";
				}

			echo "</tr>";
		}
	}



	public function ProjectShowComponentsBOM($out, $project_id)
	{
		//require_once('login/auth.php');
		//include('mysql_connect.php');
		$GetDataComponentsAll = "SELECT * FROM projects_data, data WHERE projects_data.projects_data_component_id = data.id AND projects_data.projects_data_project_id = ".$project_id." ORDER by name ASC";

		$sql_exec = mysqli_query($GLOBALS["___mysqli_ston"], $GetDataComponentsAll);
		while($showDetails = mysqli_fetch_array($sql_exec))
		{
			$arr = array();

			$arr[] = $showDetails['name'];

			$sql_exec_catname = mysqli_query($GLOBALS["___mysqli_ston"], "select c.name h, c.id cid, cs.subcategory s, cs.id csid from category c, category_sub cs where c.id = cs.category_id and cs.id = ".$showDetails['category']."");
			$showDetailsCat = mysqli_fetch_array($sql_exec_catname);
			$catname = $showDetailsCat['h'];

			$arr[] = $showDetailsCat['h']." / ".$showDetailsCat['s'];
			$arr[] = $showDetails['manufacturer'];
			$arr[] = $showDetails['package'];
			$arr[] = $showDetails['projects_data_quantity'];
			$arr[] = $showDetails['bin_location'];

			//fputcsv($out, $arr, ",", "\"");
			$first = 0;

			foreach ($arr as $fields)
			{
				if($first > 0)
					fwrite($out, ",");

				$first += 1;

				fwrite($out, '"'.str_replace('"', '""', $fields).'"');
			}
			fwrite($out, "\r\n");

		}
	}
}
?>
