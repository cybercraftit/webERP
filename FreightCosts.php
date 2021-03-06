<?php

/* $Id: FreightCosts.php 5785 2012-12-29 04:47:42Z daintree $*/

include('includes/session.inc');
$Title = _('Freight Costs Set Up');
include('includes/header.inc');

if (isset($_GET['LocationFrom'])){
	$LocationFrom = $_GET['LocationFrom'];
} elseif (isset($_POST['LocationFrom'])){
	$LocationFrom = $_POST['LocationFrom'];
}
if (isset($_GET['ShipperID'])){
	$ShipperID = $_GET['ShipperID'];
} elseif (isset($_POST['ShipperID'])){
	$ShipperID = $_POST['ShipperID'];
}
if (isset($_GET['SelectedFreightCost'])){
	$SelectedFreightCost = $_GET['SelectedFreightCost'];
} elseif (isset($_POST['SelectedFreightCost'])){
	$SelectedFreightCost = $_POST['SelectedFreightCost'];
}

	echo '<div class="centre"><p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
		_('Freight Costs') . '" alt="" />' . ' ' . $Title . '</p></div>';

if (!isset($LocationFrom) OR !isset($ShipperID)) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$sql = "SELECT shippername, shipper_id FROM shippers";
	$ShipperResults = DB_query($sql,$db);

	echo '<table class="selection">
		<tr>
			<td>' . _('Select A Freight Company to set up costs for') . '</td>
			<td><select name="ShipperID">';

	while ($myrow = DB_fetch_array($ShipperResults)){
		echo '<option value="' . $myrow['shipper_id'] . '">' . $myrow['shippername'] . '</option>';
	}
	echo '</select></td></tr>
			<tr>
				<td>' . _('Select the warehouse') . ' (' . _('ship from location') . ')</td>
				<td><select name="LocationFrom">';

	$sql = "SELECT loccode,
					locationname
			FROM locations";
	$LocationResults = DB_query($sql,$db);

	while ($myrow = DB_fetch_array($LocationResults)){
		echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}

	echo '</select></td></tr>
			</table>
			<br /><div class="centre"><input type="submit" value="' . _('Accept') . '" name="Accept" /></div>
            </div>
			</form>';

} else {

	$sql = "SELECT shippername FROM shippers WHERE shipper_id = '".$ShipperID."'";
	$ShipperResults = DB_query($sql,$db);
	$myrow = DB_fetch_row($ShipperResults);
	$ShipperName = $myrow[0];
	$sql = "SELECT locationname FROM locations WHERE loccode = '".$LocationFrom."'";
	$LocationResults = DB_query($sql,$db);
	$myrow = DB_fetch_row($LocationResults);
	$LocationName = $myrow[0];

}


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	//first off validate inputs sensible
	if (mb_strlen($_POST['Destination'])<2){
		$InputError=1;
		prnMsg(_('The entry for the destination must be at least two characters long') . '. ' . _('These entries are matched against the town names entered for customer delivery addresses'),'warn');
	}


	if (trim($_POST['CubRate']) == '' ) {
		$_POST['CubRate'] = 0;
	}
	if (trim($_POST['KGRate']) == '' ) {
		$_POST['KGRate'] = 0;
	}
	if (trim($_POST['MAXKGs']) == '' ) {
		$_POST['MAXKGs'] = 0;
	}
	if (trim($_POST['MAXCub']) == '' ) {
		$_POST['MAXCub'] = 0;
	}
	if (trim($_POST['FixedPrice']) == '' ){
		$_POST['FixedPrice'] = 0;
	}
	if (trim($_POST['MinimumChg']) == '' ) {
		$_POST['MinimumChg'] = 0;
	}

	if (!is_double((double) $_POST['CubRate']) OR !is_double((double) $_POST['KGRate']) OR !is_double((double) $_POST['MAXKGs']) OR !is_double((double) $_POST['MAXCub']) OR !is_double((double) $_POST['FixedPrice']) OR !is_double((double) $_POST['MinimumChg'])) {
		$InputError=1;
		prnMsg(_('The entries for Cubic Rate, KG Rate, Maximum Weight, Maximum Volume, Fixed Price and Minimum charge must be numeric'),'warn');
	}



	if (isset($SelectedFreightCost) AND $InputError !=1) {

		$sql = "UPDATE freightcosts
				SET	locationfrom='".$LocationFrom."',
					destination='" . $_POST['Destination'] . "',
					shipperid='" . $ShipperID . "',
					cubrate='" . $_POST['CubRate'] . "',
					kgrate ='" . $_POST['KGRate'] . "',
					maxkgs ='" . $_POST['MAXKGs'] . "',
					maxcub= '" . $_POST['MAXCub'] . "',
					fixedprice = '" . $_POST['FixedPrice'] . "',
					minimumchg= '" . $_POST['MinimumChg'] . "'
			WHERE shipcostfromid='" . $SelectedFreightCost . "'";

		$msg = _('Freight cost record updated');

	} elseif ($InputError !=1) {

	/*Selected freight cost is null cos no item selected on first time round so must be adding a record must be submitting new entries */

		$sql = "INSERT INTO freightcosts (locationfrom,
											destination,
											shipperid,
											cubrate,
											kgrate,
											maxkgs,
											maxcub,
											fixedprice,
											minimumchg)
										VALUES (
											'".$LocationFrom."',
											'" . $_POST['Destination'] . "',
											'" . $ShipperID . "',
											'" . $_POST['CubRate'] . "',
											'" . $_POST['KGRate'] . "',
											'" . $_POST['MAXKGs'] . "',
											'" . $_POST['MAXCub'] . "',
											'" . $_POST['FixedPrice'] ."',
											'" . $_POST['MinimumChg'] . "'
										)";

		$msg = _('Freight cost record inserted');

	}
	//run the SQL from either of the above possibilites


	$ErrMsg = _('The freight cost record could not be updated because');
	$result = DB_query($sql,$db,$ErrMsg);

	prnMsg($msg,'success');

	unset($SelectedFreightCost);
	unset($_POST['CubRate']);
	unset($_POST['KGRate']);
	unset($_POST['MAXKGs']);
	unset($_POST['MAXCub']);
	unset($_POST['FixedPrice']);
	unset($_POST['MinimumChg']);

} elseif (isset($_GET['delete'])) {

	$sql = "DELETE FROM freightcosts WHERE shipcostfromid='" . $SelectedFreightCost . "'";
	$result = DB_query($sql,$db);
	prnMsg( _('Freight cost record deleted'),'success');
	unset ($SelectedFreightCost);
	unset($_GET['delete']);
}

if (!isset($SelectedFreightCost) AND isset($LocationFrom) AND isset($ShipperID)){


	$sql = "SELECT shipcostfromid,
					destination,
					cubrate,
					kgrate,
					maxkgs,
					maxcub,
					fixedprice,
					minimumchg
				FROM freightcosts
				WHERE freightcosts.locationfrom = '".$LocationFrom. "'
				AND freightcosts.shipperid = '" . $ShipperID . "'
				ORDER BY destination";

	$result = DB_query($sql,$db);

	echo '<br /><table class="selection">';
	$TableHeader = '<tr>
					<th>' . _('Destination') . '</th>
					<th>' . _('Cubic Rate') . '</th>
					<th>' . _('KG Rate') . '</th>
					<th>' . _('MAX KGs') . '</th>
					<th>' . _('MAX Volume') . '</th>
					<th>' . _('Fixed Price') . '</th>
					<th>' . _('Minimum Charge') . '</th>
					</tr>';

	echo $TableHeader;

	$k = 0; //row counter to determine background colour
	$PageFullCounter=0;

	while ($myrow = DB_fetch_row($result)) {
		$PageFullCounter++;
		if ($PageFullCounter==15){
				$PageFullCounter=0;
				echo $TableHeader;

		}
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}


		printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td><a href="%s&amp;SelectedFreightCost=%s&amp;LocationFrom=%s&amp;ShipperID=%s">' . _('Edit') . '</a></td>
			<td><a href="%s&amp;SelectedFreightCost=%s&amp;LocationFrom=%s&amp;ShipperID=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this freight cost') . '\');">' . _('Delete') . '</a></td></tr>',
			$myrow[1],
			$myrow[2],
			$myrow[3],
			$myrow[4],
			$myrow[5],
			$myrow[6],
			$myrow[7],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow[0],
			$LocationFrom,
			$ShipperID,
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow[0],
			$LocationFrom,
			$ShipperID);

	}

	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedFreightCost)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?LocationFrom=' . $LocationFrom . '&amp;ShipperID=' . $ShipperID . '">' . _('Show all freight costs for') . ' ' . $ShipperName  . ' ' . _('from') . ' ' . $LocationName . '</a></div>';
}

if (isset($LocationFrom) AND isset($ShipperID)) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedFreightCost)) {
		//editing an existing freight cost item

		$sql = "SELECT locationfrom,
					destination,
					shipperid,
					cubrate,
					kgrate,
					maxkgs,
					maxcub,
					fixedprice,
					minimumchg
				FROM freightcosts
				WHERE shipcostfromid='" . $SelectedFreightCost ."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$LocationFrom  = $myrow['locationfrom'];
		$_POST['Destination']	= $myrow['destination'];
		$ShipperID  = $myrow['shipperid'];
		$_POST['CubRate']  = $myrow['cubrate'];
		$_POST['KGRate'] = $myrow['kgrate'];
		$_POST['MAXKGs'] = $myrow['maxkgs'];
		$_POST['MAXCub'] = $myrow['maxcub'];
		$_POST['FixedPrice'] = $myrow['fixedprice'];
		$_POST['MinimumChg'] = $myrow['minimumchg'];

		echo '<input type="hidden" name="SelectedFreightCost" value="' . $SelectedFreightCost . '" />';

	} else {
		$_POST['FixedPrice'] = 0;
		$_POST['MinimumChg'] = 0;

	}
	echo '<input type="hidden" name="LocationFrom" value="' . $LocationFrom . '" />';
	echo '<input type="hidden" name="ShipperID" value="' . $ShipperID . '" />';

	if (!isset($_POST['Destination'])) {$_POST['Destination']='';}
	if (!isset($_POST['CubRate'])) {$_POST['CubRate']='';}
	if (!isset($_POST['KGRate'])) {$_POST['KGRate']='';}
	if (!isset($_POST['MAXKGs'])) {$_POST['MAXKGs']='';}
	if (!isset($_POST['MAXCub'])) {$_POST['MAXCub']='';}

	echo '<br /><table class="selection">';
	echo '<tr><th colspan="2">' . _('For Deliveries From') . ' ' . $LocationName . ' ' . _('using') . ' ' .
		$ShipperName . '</th></tr>';
	echo'<tr><td>' . _('Destination') . ':</td>
		<td><input type="text" maxlength="20" size="20" name="Destination" value="' . $_POST['Destination'] . '" /></td></tr>';
	echo '<tr><td>' . _('Rate per Cubic Metre') . ':</td>
		<td><input type="text" name="CubRate" class="number" size="6" maxlength="5" value="' . $_POST['CubRate'] . '" /></td></tr>';
	echo '<tr><td>' . _('Rate Per KG') . ':</td>
		<td><input type="text" name="KGRate" class="number" size="6" maxlength="5" value="' . $_POST['KGRate'] . '" /></td></tr>';
	echo '<tr><td>' . _('Maximum Weight Per Package (KGs)') . ':</td>
		<td><input type="text" name="MAXKGs" class="number" size="8" maxlength="7" value="' . $_POST['MAXKGs'] . '" /></td></tr>';
	echo '<tr><td>' . _('Maximum Volume Per Package (cubic metres)') . ':</td>
		<td><input type="text" name="MAXCub" class="number" size="8" maxlength="7" value="' . $_POST['MAXCub'] . '" /></td></tr>';
	echo '<tr><td>' . _('Fixed Price (zero if rate per KG or Cubic)') . ':</td>
		<td><input type="text" name="FixedPrice" class="number" size="6" maxlength="5" value="' . $_POST['FixedPrice'] . '" /></td></tr>';
	echo '<tr><td>' . _('Minimum Charge (0 is N/A)') . ':</td>
		<td><input type="text" name="MinimumChg" class="number" size="6" maxlength="5" value="' . $_POST['MinimumChg'] . '" /></td></tr>';

	echo '</table><br />';

	echo '<div class="centre"><input type="submit" name="submit" value="' . _('Enter Information') . '" /></div>';
    echo '</div>';
	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>