<?PHP

function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}

//echo $user_ip; // Output IP address [Ex: 177.87.193.134]

function geolocate_ip($user_ip) {
	$url = 'http://ip-api.com/json/' . $user_ip;
	$data = array();
	$result = wp_remote_post( $url, array( 'data' => $data ) );
	return $result['body'];
}

function geoblocking_content_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

$user_ip_address = getUserIP();

?>
<div class="wrap">
	<h1>Geoblocking content plugin</h1>
	<h2>Your IP Address is:</h2>
	<p><?php echo $user_ip_address ?></p>
	<h2>Informations about your location: (Source: <a href="http://ip-api.com/docs/api:json">http://ip-api.com/docs/api:json</a>)</h2>
	<ul>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->country; ?></li>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->countryCode; ?></li>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->region; ?></li>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->regionName; ?></li>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->city; ?></li>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->zip; ?></li>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->lat; ?></li>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->lon; ?></li>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->timezone; ?></li>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->isp; ?></li>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->org; ?></li>
		<li><?php echo json_decode(geolocate_ip($user_ip_address))->as; ?></li>
	</ul>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'country-option-group' );
		do_settings_sections( 'country-option-group' );
		?>
		<!--<label>Country to block</label>
		<input type="text" name="country" value="<?php echo esc_attr( get_option('country') ); ?>" />-->
		<label>Scope ray to blackout (KM)</label>
		<input type="text" name="blackout_ray" value="<?php echo esc_attr( get_option('blackout_ray') ); ?>" />
		<?php submit_button(); ?>
	</form>
	<p>Plugin mage by Larbizard (Larbi Gharib) <a href="http://www.larbizard.com">http://www.larbizard.com</a></p>
</div>
<?php 
}
?>
