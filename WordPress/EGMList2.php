<?php
/*
Template Name: Listzz
*/

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

?>

<?php get_header(); ?>

<?php
	$egm_pages_args = array(
		'meta_key' => '_wp_page_template',
		'meta_value' => 'EGM2.php'
	);
	$egm_pages = get_pages( $egm_pages_args );
?>
	<div style="padding:0 0 30pt 0"><h4>
	</h4></div>
	<div class="entry">
	<ul>
	<?php
		Echo '<div style="padding: 10px 10px 10px 10px">';
		foreach($egm_pages as $egm_page) {
			$title = get_permalink($egm_page->ID);
			$pwroutlet = get_post_meta($egm_page->ID, 'pwroutlet', true);
			$pwrbank = get_post_meta($egm_page->ID, 'pwrbank', true);
			# connection information for the EGM/vLAN/MySQL
			$connection = new mysqli("localhost", $sqlusr, $sqlpass, $sqldb);
			$vlansq = $connection->query("SELECT vlanName, vlanId FROM vmusers_network ORDER BY vlanName");
			$currvlanIdpre = get_post_meta($egm_page->ID, 'egmName', true);
			$currvlanIdpost = file_get_contents("http://"$chngaddr"/curregm.php?egm=$currvlanIdpre");
			$cquery = "SELECT owner FROM vmusers_network WHERE vlanId = $currvlanIdpost";
			$curruserq = $connection->query($cquery);
			$curruser = $curruserq->fetch_assoc();
			$egmstate = get_post_meta($egm_page->ID, 'egmState', true); 
			#$pwrstate = readysetgo($pwrbank,$pwroutlet,"query");
			#$connection = 
			#Echo $title;
			Echo '<div style="padding:10px 10px 10px 10px">';
			Echo '<div class="egmlistbox">';
			Echo '<li><a href="' . get_permalink( $egm_page->ID ) . '">' . $egm_page->post_title . '</a>' . $curruser['owner'] . $egmstate . '</li>';
			#Echo '<a href="' . get_permalink( $egm_page->ID ) . '">' . $egm_page->post_title . '</a>' . $curruser['owner'] . $egmstate .;
			Echo '</div>';
			Echo '</div>';
			
		}
		Echo '</div>';
	?>
	

<?php
	
	function readysetgo($bank,$pwroutlet,$pwraction) {
		$powerbanks = array(
			'1'=>$out1ip,
			'2'=>$out2ip
		);
		$pwrbank = $powerbanks[$bank];
		$pwrun = $outun;
		$pwrpwd = $outpass;
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
<?php

