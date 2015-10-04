<?php
/*
Template Name: EGM Set
*/

// EGM Name refers to the EGM's vLAN ID, such as 501.
// This can be found on the EGM's name label

$chngaddr= //this is the web address of the bridging server

// SQL Connection Information
$sqlusr = //sqlpassword
$sqlpass = //sqlpassword
$sqldb = //SQL Database

// Information about the WebPower Outlets
$out1ip = // IP address of WebPower Outlet #1
$out2ip = // IP address of WebPower Outlet #2
$outun = // Username for WebPower Outlets
$outpass = //  Password for WebPower Outlets

if(isset($_POST['submitted'])) {
	$name = get_post_meta($post->ID, 'egmName', true);
	$vlan = trim($_POST['vlan']);
	$output = file_get_contents("http://"$chngaddr"/chngegm.php?egm=$name&vlan=$vlan");
	#really need to add a check to verify correctly set vlan...
	#$vlanSent = true;
}
if(isset($_POST['removed'])) {
	$name = get_post_meta($post->ID, 'egmName', true);
	$output = file_get_contents("http://"$chngaddr"/chngegm.php?egm=$name&vlan=9999");
}
if(isset($_POST['chngpwr'])) {
	$pwroutlet = get_post_meta($post->ID, 'pwroutlet', true);
	$pwrbank = get_post_meta($post->ID, 'pwrbank', true);
	readysetgo($pwrbank,$pwroutlet,"chng");
}
if(isset($_POST['cclpwr'])) {
	$pwroutlet = get_post_meta($post->ID, 'pwroutlet', true);
	$pwrbank = get_post_meta($post->ID, 'pwrbank', true);
	readysetgo($pwrbank,$pwroutlet,"cclpwr");
}
?>

<?php get_header(); ?>

<?php if (have_posts()) : ?>

	<?php while (have_posts()) : the_post(); ?>
		<STYLE type="text/css">
			p.egmtext { text-align: justify }
		</STYLE>
		<STYLE>
			p.currentowner { color: green }
		</STYLE>
		<div style="width:40%;padding:30pt 10pt 0 20pt;float:left;">
		<?php
			$egmName = get_post_meta($post->ID, 'egmName', true);
			$egmState = get_post_meta($post->ID, 'egmState', true);
			# egmstate variable, anything other then 'ready' will throw into maintenance status
			# need to add 'locked' so users can lock for a certain period of time
			# if they are logged in
			if(isset($egmState) && $egmState == "ready") {
				# connection information for the EGM/vLAN/MySQL
				$connection = new mysqli("localhost", $sqlusr, $sqlpass, $sqldb);
				$vlansq = $connection->query("SELECT vlanName, vlanId FROM vmusers_network ORDER BY vlanName");
				$currvlanIdpre = get_post_meta($post->ID, 'egmName', true);
				$currvlanIdpost = file_get_contents("http://"$chngaddr"/curregm.php?egm=$currvlanIdpre");
				$cquery = "SELECT owner FROM vmusers_network WHERE vlanId = $currvlanIdpost";
				$curruserq = $connection->query($cquery);
				# Outlet Information
				$pwroutlet = get_post_meta($post->ID, 'pwroutlet', true);
				$pwrbank = get_post_meta($post->ID, 'pwrbank', true);
				$pwrstate = readysetgo($pwrbank,$pwroutlet,"query");
				$bttnstate = array(
					'ON'=>'Power EGM OFF',
					'OFF'=>'Power EGM ON'
				);
				$pwrcolors = array(
					'ON'=>'green',
					'OFF'=>'red'
				);
		echo '<div style="padding:0 0 30pt 0"><h4>';
		echo '<p>Current EGM Status:';
		echo '</p></h4>';
		#Someday I will actually throw in a test to check for errors...
		#if(isset($vlanSent) && $vlanSent == true) {
		#	echo '<div>';
		#	echo	'<p style="color:green">Your vlan was successfully connected.</p>';
		#	echo '</div>';
		#	} else {
				$curruser = $curruserq->fetch_assoc();
				if($curruser['owner'] == "DISCONNECTED") {
				echo '<p>Connection Owner:  <span style="color:red; font-weight:bold">' . "'{$curruser['owner']}'" . '</span></br>';
				} else {
				echo "<p>Current Connection Owner: <span style=" . '"' . "font-weight:bold" . '"' . ">'{$curruser['owner']}' </span></br>";
				}
				echo 'Power State:  <span style="font-weight:bold; color:' . $pwrcolors[$pwrstate] . '">' . $pwrstate . "</span></br>";
				echo "Serial Number:  " . Get_post_meta($post->ID, 'egmSerial', true) . "</p>";
		#}
		?>

		<div class="entry">
			<p class="egmtext">The current connection is stated above.  Select an environment below and click 'Set Connection' to re-configure the EGM for another environment.  If you cannot connect to the EGM after re-configuring, the EGM may be offline or your environment may be misconfigured.  Click 'Disconnect EGM' to disconnect all current sessions to the EGM.</p>
		</div>
		<?php if(isset($hasError)) { ?>
			<p class="error">There was an error submitting the form.</p>
		<?php } ?>

		<div style="padding:25pt 10pt 0 10pt">
		<div>
		<form action="<?php the_permalink(); ?>" id="contactForm" method="post">

			<ol class="forms">
				<h4>Select An Environment:</h4>
				<li><select name="vlan" id="vlan">
					<?php while ($row = $vlansq->fetch_assoc()) {
							echo "<option value='{$row['vlanId']}'>{$row['vlanName']}</option>\n";
						}
					?>
					</select>
				</li>
				<div style="padding:20pt 0 20pt 0;float:left">
				<li class="buttons"><input type="hidden" name="submitted" id="submitted" value="true" /><button type="submit">Set Connection &raquo;</button></li>
				</div>
			</ol>
		</form>
		<div style="padding:20pt 0 20pt 10pt;float:left">
		<form action="<?php the_permalink(); ?>" id="contactForm" method="post">
			<input type="hidden" name="removed" id="removed" value="true" /><button type="submit">Disconnect EGM &raquo;</button>
		</form>
		</div>
		</div>
		<div style="float:left">
		<h4>Power Controls:</h4>
		<div style="padding:5pt 0 20pt 0;float:left">
		<form method="post" action="<?php the_permalink(); ?>">
			<input name="chngpwr" type="submit" value="<?php echo $bttnstate[$pwrstate]; ?>" />
		</form>
		</div>
		<div style="padding:5pt 0 20pt 10pt;float:left">
		<form method="post" action="<?php the_permalink(); ?>">
			<input name="cclpwr" type="submit" value="Cycle EGM Power" <?php if ($pwrstate == "OFF") {echo "disabled";} ?>>
		</form>
		</div>
		</div></div></div>
		<?php } else { ?>
			<div class="entry">
			<p class="egmtext">
				The current EGM is under maintenance.  Please contact secave@igt.com for further information.
			</p>
			</div>
		<?php } ?>
	</div>
	<div style="width:40%;padding:30pt 20pt 0 0;float:right;">
		<p>You are currently viewing the EGM named:</p>
		<h1><?php the_title(); ?></h1>
		<div style="padding:25pt 0 20pt 0">
			<?php the_post_thumbnail(); ?>
		</div>
		<div class="entry">
				<?php the_content(); ?>
		</div>
	</div>
		<?php endwhile; ?>
	<?php endif; ?>
<?php# } ?>
<?php

	function readysetgo($bank,$pwroutlet,$pwraction) {
		$powerbanks = array(
			'1'=>'http://'$out1ip'/',
			'2'=>'http://'$out2ip'/'
		);
		$pwrbank = $powerbanks[$bank];
		$pwrun = $outun;
		$pwrpwd = $outpw;
		$pwrcred = sprintf('Authorization: Basic %s',base64_encode("$pwrun:$pwrpwd"));
		$pwropts = array(
			'http'=>array(
				'method'=>'GET',
				'header'=>$pwrcred
			)
		);
		$pwrctx = stream_context_create($pwropts);
		$pwrhtml = file_get_contents($pwrbank . 'index.htm',false,$pwrctx);
		$PWRDOM = new DOMDocument;
		$PWRDOM->loadHTML($pwrhtml);
		$olists = $PWRDOM->getElementsByTagName('tr');
		foreach ($olists as $olist) {
			$otest = $olist->getAttribute('bgcolor');
			if ($otest == "#F4F4F4") {
				$otest2 = $olist->childNodes->item(0)->nodeValue;
				if ($otest2 == $pwroutlet) {
					$rtn1 = trim($olist->childNodes->item(3)->nodeValue);
					$rtn2a = $olist->getElementsByTagName('a')->item(0)->getAttribute('href');
					$rtn2 = $pwrbank . $rtn2a;
					if ($rtn1 == "ON") {
						$rtn3a = $olist->getElementsByTagName('a')->item(1)->getAttribute('href');
						$rtn3 = $pwrbank . $rtn3a;
					} else {
						$rtn3 = "notavailable";
					}
				}
			}
		}
		$pwrarray = array(
			'query'=>$rtn1,
			'chng'=>$rtn2,
			'cclpwr'=>$rtn3
		);
		if ($pwraction == "query") {
			return $rtn1;
		} else {
			file_get_contents($pwrarray[$pwraction],false,$pwrctx);
		}
	}
?>

<?php get_footer(); ?>