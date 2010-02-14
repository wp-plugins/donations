<?php
/*
 Plugin Name: Donations Widget
 Plugin URI: http://mohanjith.com/wordpress/donations.html
 Description: Accept donations from your readers via AlertPay, Moneybookers and/or PayPal.
 Author: S H Mohanjith
 Version: 1.0.0
 Author URI: http://mohanjith.com/
 Text Domain: donations
 License: GPL

 Copyright 2010  S H Mohanjith (email : moha@mohanjith.net)
 */

define("DONATIONS_VERSION_NUM", "1.0.0");
define("DONATIONS_RCP_TRANS_DOMAIN", "donations");

global $donations;

$donations = new Donations();

class Donations {
	private $uri;
	private $the_path;
	
	public function __construct() {

		$version = get_option('donations_version');
		$_file = "web-invoice-scheduler/" . basename(__FILE__);

		$this->path = dirname(__FILE__);
		$this->file = basename(__FILE__);
		$this->directory = basename($this->path);
		$this->uri = WP_PLUGIN_URL."/".$this->directory;
		$this->the_path = $this->the_path();
		
		register_activation_hook(__FILE__, array(&$this, 'install'));
		register_deactivation_hook(__FILE__, array(&$this, 'uninstall'));

		add_action('init',  array($this, 'init'), 0);
		
		if ( !function_exists('register_sidebar_widget') ) {
			return;
		}
    
		register_sidebar_widget(__('Donations'), array($this, 'widget_donations'));
		register_widget_control(__('Donations'), array($this, 'widget_donations_control'), 250, 470);
	}
	
	public function uninstall() {
		global $wpdb;
	}

	public function install() {
		global $wpdb;
		
		add_option('donations_widget_options', '');
		add_option('donations_alertpay_email', '');
		add_option('donations_moneybookers_email', '');
		add_option('donations_paypal_email', '');
	}
	
	public function init() {
		global $wp_version;

		if (version_compare($wp_version, '2.6', '<')) // Using old WordPress
			load_plugin_textdomain(WEB_INVOICE_rcp_TRANS_DOMAIN, PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/languages');
		else
			load_plugin_textdomain(WEB_INVOICE_rcp_TRANS_DOMAIN, PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/languages', dirname(plugin_basename(__FILE__)).'/languages');
	}
	
	public function the_path() {
		$path =	WP_PLUGIN_URL."/".basename(dirname(__FILE__));
		return $path;
	}

	public function frontend_path() {
		$path =	WP_PLUGIN_URL."/".basename(dirname(__FILE__));
		if(get_option('web_invoice_force_https') == 'true') $path = str_replace('http://','https://',$path);
		return $path;
	}
	
	public function widget_donations($args) {
		global $wpdb;
		
		$options = get_option('donations_widget_options');
		$alertpay_email = get_option('donations_alertpay_email');
		$moneybookers_email = get_option('donations_moneybookers_email');
		$paypal_email = get_option('donations_paypal_email');
		
		$item_name = get_option('donations_item_name');
		$item_code = get_option('donations_item_code');
		$currency = get_option('donations_currency');
		$amount = get_option('donations_amount');
		
		if ( !is_array($options) ) {
			$options = array('title'=>'Donations');
		}
		
		extract($args);
		echo $before_widget;
		
		echo $before_title; echo $options['title']; echo $after_title;
		
		if (!empty($alertpay_email)) {
			?>
			<p><form method="post" action="https://www.alertpay.com/PayProcess.aspx" target="_blank" >
				<input type="hidden" name="ap_purchasetype" value="item"/>
				<input type="hidden" name="ap_merchant" value="<?php print $alertpay_email; ?>"/>
				<input type="hidden" name="ap_itemname" value="<?php print $item_name; ?>"/>
				<input type="hidden" name="ap_itemcode" value="<?php print $item_code; ?>"/> 
				<input type="hidden" name="ap_quantity" value="1"/>
				<input type="hidden" name="ap_returnurl" value=""/>
				<input type="hidden" name="ap_currency" value="<?php print $currency; ?>"/>
				<label ><?php print $currency; ?> <input type="text" name="ap_amount" value="<?php print $amount; ?>" size="3" /></label><br/>
				<input type="image" name="ap_image" src="<?php print $this->uri; ?>/images/alertpay_logo.png" width="90" height="60" alt="Donate with AlertPay" style="border: none; background: none;" />
			</form></p> 
			<?php 
		}
		
		if (!empty($moneybookers_email)) {
			?>
			<p></o><form action="https://www.moneybookers.com/app/payment.pl" method="post" target="_blank" >
				<input type="hidden" name="pay_to_email" value="<?php print $moneybookers_email; ?>" />
				<input type="hidden" name="language" value="EN" />
				<input type="hidden" name="rid" value="5413099" />
				<label ><?php print $currency; ?> <input type="text" name="amount" value="<?php print $amount; ?>" size="3" /></label><br/>
				<input type="hidden" name="currency" value="<?php print $currency; ?>" />
				<input type="hidden" name="detail1_description" value="<?php print $item_code; ?>" />
				<input type="hidden" name="detail1_text" value="<?php print $item_name; ?>" />
				<input type="image" src="<?php print $this->uri; ?>/images/mb_orange_donate_with.gif" width="90" height="60" border="0" name="submit" alt="Donate with Moneybookers" style="border: none; background: none;" />
			</form></p>
			<?php 
		}
		
		if (!empty($paypal_email)) {
			?>
			<p><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" >
				<input type="hidden" name="cmd" value="_donations" />
				<input type="hidden" name="business" value="<?php print $paypal_email; ?>" />
				<input type="hidden" name="item_name" value="<?php print $item_name; ?>" />
				<input type="hidden" name="item_number" value="<?php print $item_code; ?>" />
				<input type="hidden" name="currency_code" value="<?php print $currency; ?>" />
				<label ><?php print $currency; ?> <input type="text" name="amount" value="<?php print $amount; ?>" size="3" /></label><br/>
				<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHostedGuest" />
				<input type="image" src="<?php print $this->uri; ?>/images/pp_donate_LG.gif" width="92" height="26" border="0" name="submit" alt="Donate with PayPal" style="border: none; background: none;" />
				<img alt="" border="0" src="<?php print $this->uri; ?>/images/pixel.gif" width="1" height="1" />
			</form></p>
		<?php 
		}
		
		echo $after_widget;
	}
	
	public function widget_donations_control() {
		$errors = array();
		if ( $_POST['donations-widget-submit'] ) {
			$options['title'] = trim(strip_tags(stripslashes($_POST['title'])));
			update_option('donations_widget_options', $options);
			
			update_option('donations_item_code', trim(strip_tags(stripslashes($_POST['item_code']))));
			update_option('donations_item_name', trim(strip_tags(stripslashes($_POST['item_name']))));
			update_option('donations_currency', trim(strip_tags(stripslashes($_POST['currency']))));
			update_option('donations_amount', trim(strip_tags(stripslashes($_POST['amount']))));
			
			if (preg_match(
				"/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i",
				trim($_POST['alertpay_email']))) {
				update_option('donations_alertpay_email', trim(strip_tags(stripslashes($_POST['alertpay_email']))));
			} else if (trim($_POST['alertpay_email']) != "") {
				$errors['alertpay_email'] = true;
			} else {
				update_option('donations_alertpay_email', '');
			}
			if (preg_match(
				"/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i",
				trim($_POST['moneybookers_email']))) {
				update_option('donations_moneybookers_email', trim(strip_tags(stripslashes($_POST['moneybookers_email']))));
			} else if (trim($_POST['moneybookers_email']) != "") {
				$errors['moneybookers_email'] = true;
			} else {
				update_option('donations_moneybookers_email', '');
			}
			if (preg_match(
				"/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i",
				trim($_POST['paypal_email']))) {
				update_option('donations_paypal_email', trim(strip_tags(stripslashes($_POST['paypal_email']))));
			} else if (trim($_POST['paypal_email']) != "") {
				$errors['paypal_email'] = true;
			} else {
				update_option('donations_paypal_email', '');
			}
		}
		
		$options = get_option('donations_widget_options');
		$alertpay_email = get_option('donations_alertpay_email');
		$moneybookers_email = get_option('donations_moneybookers_email');
		$paypal_email = get_option('donations_paypal_email');
		
		$item_code = get_option('donations_item_code');
		$item_name = get_option('donations_item_name');
		$currency = get_option('donations_currency');
		$amount = get_option('donations_amount');
		
		if ( !is_array($options) ) {
			$options = array('title'=>'Donations');
		}
		
		$title = $options['title'];
		
		?>
		<p><strong><?php _e("Widget Title", DONATIONS_RCP_TRANS_DOMAIN); ?></strong></p>
    	<p>
	    	<label for="donations_title"><?php _e("Title text (optional)", DONATIONS_RCP_TRANS_DOMAIN); ?></label><br />
	    	<input type="text" name="title" id="donations_title" value="<?php echo $title; ?>" class="widefat" />
	    </p>
	    <p><strong><?php _e("Donation details", DONATIONS_RCP_TRANS_DOMAIN); ?></strong></p>
    	<p>
	    	<label for="donations_item_code"><?php _e("Item code", DONATIONS_RCP_TRANS_DOMAIN); ?></label><br />
	    	<input type="text" name="item_code" id="donations_item_code" value="<?php echo $item_code; ?>" class="widefat" /><br />
	    	<label for="donations_item_name"><?php _e("Item name", DONATIONS_RCP_TRANS_DOMAIN); ?></label><br />
	    	<input type="text" name="item_name" id="donations_item_name" value="<?php echo $item_name; ?>" class="widefat" /><br />
	    	<label for="donations_currency"><?php _e("Currency", DONATIONS_RCP_TRANS_DOMAIN); ?></label><br />
	    	<select name="currency" id="donations_currency" value="<?php echo $currency; ?>">
	    	<?php foreach ($this->_currency_array() as $key=>$val) { ?>
	    		<option value="<?php print $key ?>" <?php print ($currency == $key)?'selected="selected"':''; ?> ><?php print $val; ?></option>
	    	<?php } ?>
	    	</select><br/>
	    	<label for="donations_amount"><?php _e("Suggested amount", DONATIONS_RCP_TRANS_DOMAIN); ?></label><br />
	    	<input type="text" name="amount" id="donations_amount" value="<?php echo $amount; ?>" class="widefat" /><br />
	    </p>
	    <p><strong><?php _e("Payment options", DONATIONS_RCP_TRANS_DOMAIN); ?></strong></p>
	    <p>
	    	<label for="donations_alertpay_email"><?php _e("AlertPay e-mail (optional)", DONATIONS_RCP_TRANS_DOMAIN); ?></label><br />
	    	<input type="text" name="alertpay_email" id="donations_alertpay_email" value="<?php echo $alertpay_email; ?>" 
	    		class="widefat" style="<?php print isset($errors['alertpay_email'])?'border-color:#CC0000;':''; ?>" /><br />
	    	<label for="donations_moneybookers_email"><?php _e("Moneybookers e-mail (optional)", DONATIONS_RCP_TRANS_DOMAIN); ?></label><br />
	    	<input type="text" name="moneybookers_email" id="donations_moneybookers_email" value="<?php echo $moneybookers_email; ?>" 
	    		class="widefat" style="<?php print isset($errors['moneybookers_email'])?'border-color:#CC0000;':''; ?>" /><br />
	    	<label for="donations_paypal_email"><?php _e("PayPal e-mail (optional)", DONATIONS_RCP_TRANS_DOMAIN); ?></label><br />
	    	<input type="text" name="paypal_email" id="donations_paypal_email" value="<?php echo $paypal_email; ?>" 
	    		class="widefat" style="<?php print isset($errors['paypal_email'])?'border-color:#CC0000;':''; ?>" /><br />
    	</p>
    	<input type="hidden" name="donations-widget-submit" value="1" />
    	<?php 
	}
	

	private function _currency_array() {
		$currency_list = array(
		"AUD"=> __("Australian Dollars"),
		"CAD"=> __("Canadian Dollars"),
		"EUR"=> __("Euros"),
		"GBP"=> __("Pounds Sterling"),
		"JPY"=> __("Yen"),
		"USD"=> __("U.S. Dollars"),
		"NZD"=> __("New Zealand Dollar"),
		"CHF"=> __("Swiss Franc"),
		"HKD"=> __("Hong Kong Dollar"),
		"SGD"=> __("Singapore Dollar"),
		"SEK"=> __("Swedish Krona"),
		"DKK"=> __("Danish Krone"),
		"PLN"=> __("Polish Zloty"),
		"NOK"=> __("Norwegian Krone"),
		"HUF"=> __("Hungarian Forint"),
		"CZK"=> __("Czech Koruna"),
		"ILS"=> __("Israeli Shekel"),
		"MXN"=> __("Mexican Peso"),
		"BRL"=> __("Brazilian real"));
	
		return $currency_list;
	}
}

