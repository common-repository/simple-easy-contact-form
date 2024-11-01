<?php
/**
 * @package Simple Easy Contact Form
 * @version 1.0
 */
/*
  Plugin Name: Simple Easy Contact Form
  Description: Simple easy contact form create contact form in your site.
  Author: ifourtechnolab
  Version: 1.0
  Author URI: http://www.ifourtechnolab.com/
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

define('SECF_URL', plugin_dir_url(__FILE__));

global $wpdb, $wp_version;
define("WP_SECF_TABLE", $wpdb->prefix . "simpleeasy_contactform");

/*
 * Main class
 */
class Simple_Easy_Contact_Form {

    /**
     * @global type $wp_version
     */
    public function __construct() {
        global $wp_version;
        
        /*
         *  Front-Side
         */
        /* Run scripts and shortcode */
        add_action('wp_enqueue_scripts', array($this, 'secf_frontend_scripts'));
        add_shortcode('simple-easy-contact-form-plugin', array($this, 'simple_easy_contact_form_shortcode'));
        
        /* Send mail contact form */
        add_action('admin_action_sendmail-contactform',array($this, 'sendmailcontactform'));
        
        
        /* 
         * Admin-Side 
         * */
        /* Setup menu and run scripts */
        add_action('admin_menu', array($this, 'secf_plugin_setup_menu'));
        add_action('admin_enqueue_scripts', array($this, 'secf_backend_scripts'));
        
        /* Save contact form in database */
        add_action('admin_action_save-contact-form',array($this, 'savecontactform'));
        
        add_filter('widget_text','do_shortcode');
    }
    
    
    /** Create table and Insert default data */
    function my_plugin_create_db() {
		
		global $wpdb;
		    
		$sql = "CREATE TABLE " . WP_SECF_TABLE . " (
			`form_id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`fieldname` tinytext NOT NULL,
			`labelname` tinytext NOT NULL,
			`placeholder` tinytext NOT NULL,
			`status` char(3) NOT NULL default 'YES',
			PRIMARY KEY (form_id)
			);";
				  
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
		
		$query = ("INSERT INTO ".WP_SECF_TABLE."
            (`fieldname`, `labelname`, `placeholder`, `status`)
            VALUES
            ('toemailaddress', '', '', 'YES'),
            ('fullname', 'Full Name', 'Please enter your full name', 'YES'),
            ('firstname', 'First Name', 'Please enter your first name', 'NO'),
            ('lastname', 'Last Name', 'Please enter your last name', 'NO'),
            ('subject', 'Subject', 'Please enter your subject', 'YES'),
            ('emailaddress', 'Email Address', 'Please enter your email', 'YES'),
            ('phonenumber', 'Phone Number', 'Please enter your phone number', 'NO'),
            ('companyname', 'Company Name', 'Please enter your company name', 'NO'),
            ('website', 'Website', 'Please enter your website', 'NO'),
            ('comments', 'Comments', 'Please enter your comments', 'YES'),
            ('butname', 'Submit', '', 'YES')");
         dbDelta($query);
    }



/** 
 * 
 * ---------------------------------ADMIN SIDE----------------------------------- 
 * 
**/
    
    /**
     * Setup simple easy contact form to Admin Menu.
     * @global type $user_ID
     */
    public function secf_plugin_setup_menu() {
		global $user_ID;
		$title		 = apply_filters('secf_menu_title', 'Simple Easy Contact Form');
		$capability	 = apply_filters('secf_capability', 'edit_others_posts');
		$page		 = add_menu_page($title, $title, $capability, 'secf',
			array($this, 'admin_simpleeasycontactforms'), "", 9501);
		add_action('load-'.$page, array($this, 'help_tab'));
    }
	
	/**
     * Admin simple easy contact form
     */
    public function admin_simpleeasycontactforms() {
		global $wpdb;
		
		$query = $wpdb->get_results("SELECT * FROM " . WP_SECF_TABLE . " order by form_id");
		foreach ($query as $data) :
			
			$formid[] = $wpdb->_escape(trim($data->form_id));
			$lname[] = $wpdb->_escape(trim($data->labelname));
			$pholder[] = $wpdb->_escape(trim($data->placeholder));
			$status[] = $wpdb->_escape(trim($data->status));
			
		endforeach; 
		?>
	
		<div class="wrap">

			<div id="icon-options-general" class="icon32"></div>
			<h1><?php esc_attr_e( 'Simple easy contact form', 'wp_admin_style' ); ?></h1>

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-2">

					<!-- main content -->
					<div id="post-body-content">

						<div class="meta-box-sortables ui-sortable">

							<div class="postbox">

								<div class="inside">
									
									<form method="post" action="<?php echo admin_url( 'admin.php' ); ?>" enctype="multipart/form-data">
										
										<input type="hidden" name="action" value="save-contact-form" />
										
										<table style="width:100%;" id="simpleeasycontactform">
											<tr>
												<td valign="top">
													<label for="first_name">To Email Address</label>
												</td>
												<td valign="top">
													<input  type="text" name="label[]" value="<?php echo $lname[0]; ?>">
													<input  type="hidden" name="pholder[]" value="">
													<input  type="hidden" name="status[]" value="<?php echo $formid[0]; ?>">
												</td>
											</tr>
										</table>
										
										<table style="width:100%;" id="simpleeasycontactform">
										  
										  <tr>
											<th style="width: 30%;">Field</th>
											<th style="width: 30%;">Label</th>
											<th style="width: 30%;">PlaceHolder</th>
											<th style="width: 10%;">Status</th>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="first_name">Full Name </label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[1]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[1]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="checkbox" name="status[]" value="<?php echo $formid[1]; ?>" <?php if($status[1]=='YES') { echo 'checked="checked"'; } else { ?> value="NO" <?php } ?>>
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="first_name">First Name </label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[2]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[2]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="checkbox" name="status[]" value="<?php echo $formid[2]; ?>" <?php if($status[2]=='YES') { echo 'checked="checked"'; } ?>>
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top"">
												<label for="last_name">Last Name </label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[3]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[3]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="checkbox" name="status[]" value="<?php echo $formid[3]; ?>" <?php if($status[3]=='YES') { echo 'checked="checked"'; } ?>>
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="email">Subject </label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[4]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[4]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="hidden" name="status[]" value="<?php echo $formid[4]; ?>">
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="email">Email Address *</label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[5]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[5]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="hidden" name="status[]" value="<?php echo $formid[5]; ?>">
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="telephone">Telephone/Mobile Number</label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[6]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[6]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="checkbox" name="status[]" value="<?php echo $formid[6]; ?>" <?php if($status[6]=='YES') { echo 'checked="checked"'; } ?>>
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="telephone">Company Name</label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[7]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[7]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="checkbox" name="status[]" value="<?php echo $formid[7]; ?>" <?php if($status[7]=='YES') { echo 'checked="checked"'; } ?>>
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="telephone">Website</label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[8]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[8]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="checkbox" name="status[]" value="<?php echo $formid[8]; ?>" <?php if($status[8]=='YES') { echo 'checked="checked"'; } ?>>
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="comments">Comments/Message *</label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[9]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[9]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="hidden" name="status[]" value="<?php echo $formid[9]; ?>">
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="buttonname">Button Name *</label>
											 </td>
											 <td valign="top" colspan="3">
												<input  type="text" name="label[]" value="<?php echo $lname[10]; ?>">
												<input  type="hidden" name="pholder[]" value="">
												<input  type="hidden" name="status[]" value="<?php echo $formid[10]; ?>">
											 </td>
									       </tr>
										  
									   </table>
									   
										<table style="width:100%;" id="simpleeasycontactform">
											<tr>
												<td colspan="4" style="text-align:center">
													<input type="submit" value="Submit" id="btnsaveform">
												</td>
											</tr>
										</table>

									</form>
									
								</div>
								<!-- .inside -->

							</div>
							<!-- .postbox -->

						</div>
						<!-- .meta-box-sortables .ui-sortable -->

					</div>
					<!-- post-body-content -->


					<!-- sidebar -->
					<div id="postbox-container-1" class="postbox-container">

						<div class="meta-box-sortables">

							<div class="postbox">

								<h2><span><?php esc_attr_e(
											'Sidebar', 'wp_admin_style'
										); ?></span></h2>

								<div class="inside">
									<p>Add <strong><code>[simple-easy-contact-form-plugin]</code></strong> shortcode for use.</p>
								</div>
								<!-- .inside -->

							</div>
							<!-- .postbox -->

						</div>
						<!-- .meta-box-sortables -->

					</div>
					<!-- #postbox-container-1 .postbox-container -->

				</div>
				<!-- #post-body .metabox-holder .columns-2 -->

				<br class="clear">
			</div>
			<!-- #poststuff -->

		</div> <!-- .wrap -->
	<?php
    }
    
    // Contact form save in Database
    public function savecontactform() {
		
		global $wpdb;
		
		$label = $wpdb->_escape($_REQUEST['label']);
		$pholder = $wpdb->_escape($_REQUEST['pholder']);
		$status = $wpdb->_escape($_REQUEST['status']);
		
		$wpdb->query($wpdb->prepare("UPDATE ".WP_SECF_TABLE." SET status='NO'"));
		
		for($i=0;$i<=10;$i++) {
			$formid = $i+1;
			
			$wpdb->query($wpdb->prepare("UPDATE ".WP_SECF_TABLE." SET 
			labelname='".$label[$i]."',placeholder='".$pholder[$i]."' WHERE form_id=$formid"));
			
			if(!empty($status[$i])) {
				$wpdb->query($wpdb->prepare("UPDATE ".WP_SECF_TABLE." SET status='YES' WHERE form_id=$status[$i]"));
			}
		}
		
		header("location:".$_SERVER['HTTP_REFERER']);
    }
    
    /**
     * css and javascript scripts.
     */
    public function secf_backend_scripts() {
		wp_enqueue_style('secf-css-handler-backend', SECF_URL.'assets/css/simple-easy-contact-form.css');
		//wp_enqueue_script('secf-js-handler-backend', SECF_URL.'assets/js/simple-easy-contact-form.js',array('jquery'),'1.0.0',true);
    }
    
    
    
    
/** 
 * 
 * ---------------------------------FRONT END----------------------------------- 
 * 
**/
    
    /** Create form and Add short code */
	function simple_easy_contact_form_shortcode( $atts ) {
		add_action('wp_enqueue_scripts', array($this, 'secf_frontend_scripts'));
		
		global $wpdb;
		
		$query = $wpdb->get_results("SELECT * FROM " . WP_SECF_TABLE . " order by form_id");
		foreach ($query as $data) :
			
			$fname[] = $wpdb->_escape(trim($data->fieldname));
			$lname[] = $wpdb->_escape(trim($data->labelname));
			$pholder[] = $wpdb->_escape(trim($data->placeholder));
			$status[] = $wpdb->_escape(trim($data->status));
			
		endforeach;	
	?>
	
		<form method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
			
			<input type="hidden" name="action" value="sendmail-contactform" />
			<input  type="hidden" name="<?php echo $fname[0]; ?>" value="<?php echo $lname[0]; ?>">
			
			<table class="front-simpleeasycontactform">
				
				<tr>
					<th colspan="2"><h2>Contact Form</h2></th>
				</tr>
				
				
				<!-- Enter full name -->
				<?php if($status[1] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[1]; ?>"><?php echo $lname[1]; ?> *</label>
						</td>
						<td valign="top">
							<input  type="text" name="<?php echo $fname[1]; ?>" placeholder="<?php echo $pholder[1]; ?>" required="required">
						</td>
					</tr>
				<?php } ?>
				
				<!-- Enter first name -->
				<?php if($status[2] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[2]; ?>"><?php echo $lname[2]; ?> *</label>
						</td>
						<td valign="top">
							<input  type="text" name="<?php echo $fname[2]; ?>" placeholder="<?php echo $pholder[2]; ?>" required="required">
						</td>
					</tr>
				<?php } ?>
				
				<!-- Enter last name -->
				<?php if($status[3] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[3]; ?>"><?php echo $lname[3]; ?> *</label>
						</td>
						<td valign="top">
							<input  type="text" name="<?php echo $fname[3]; ?>" placeholder="<?php echo $pholder[3]; ?>" required="required">
						</td>
					</tr>
				<?php } ?>
				
				<!-- Enter subject name -->
				<?php if($status[4] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[4]; ?>"><?php echo $lname[4]; ?> *</label>
						</td>
						<td valign="top">
							<input  type="text" name="<?php echo $fname[4]; ?>" placeholder="<?php echo $pholder[4]; ?>" required="required">
						</td>
					</tr>
				<?php } ?>
				
				<!-- Enter email address -->
				<?php if($status[5] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[5]; ?>"><?php echo $lname[5]; ?> *</label>
						</td>
						<td valign="top">
							<input  type="email" name="<?php echo $fname[5]; ?>" placeholder="<?php echo $pholder[5]; ?>" required="required">
						</td>
					</tr>
				<?php } ?>
				
				<!-- Enter phone number -->
				<?php if($status[6] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[6]; ?>"><?php echo $lname[6]; ?> *</label>
						</td>
						<td valign="top">
							<input  type="text" name="<?php echo $fname[6]; ?>" placeholder="<?php echo $pholder[6]; ?>" required="required">
						</td>
					</tr>
				<?php } ?>
				
				<!-- Enter company name -->
				<?php if($status[7] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[7]; ?>"><?php echo $lname[7]; ?> *</label>
						</td>
						<td valign="top">
							<input  type="text" name="<?php echo $fname[7]; ?>" placeholder="<?php echo $pholder[7]; ?>" required="required">
						</td>
					</tr>
				<?php } ?>
				
				<!-- Enter website -->
				<?php if($status[8] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[8]; ?>"><?php echo $lname[8]; ?> *</label>
						</td>
						<td valign="top">
							<input  type="url" name="<?php echo $fname[8]; ?>" placeholder="<?php echo $pholder[8]; ?>" required="required">
						</td>
					</tr>
				<?php } ?>
				
				<!-- Enter comments -->
				<?php if($status[9] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[9]; ?>"><?php echo $lname[9]; ?> *</label>
						</td>
						<td valign="top">
							<textarea  name="<?php echo $fname[9]; ?>" placeholder="<?php echo $pholder[9]; ?>" maxlength="1000" cols="25" rows="6" required="required"></textarea>
						</td>
					</tr>
				<?php } ?>
				
				<tr>
					<td colspan="2" style="text-align:center">
						<input type="submit" value="<?php if($status[10] == 'YES') { echo $lname[10]; }?>">
					</td>
				</tr>
				
			</table>
		</form>
	
	<?php
	}
	
	/** Front end - send mail contact form  */
	function sendmailcontactform() {
		
		global $wpdb;
		
		$contact_errors = false;
		
		$toemail = $wpdb->_escape(trim($_POST['toemailaddress']));
		
		$fullname = $wpdb->_escape(trim($_POST['fullname']));
		if(!empty($fullname)) {
			$fullname = 'Full Name :- '.$fullname."\n<br />\n";
		}
		$firstname = $wpdb->_escape(trim($_POST['firstname']));
		if(!empty($firstname)) {
			$firstname = 'First Name :- '.$firstname."\n<br />\n";
		}
		$lastname = $wpdb->_escape(trim($_POST['lastname']));
		if(!empty($lastname)) {
			$lastname = 'Last Name :- '.$lastname."\n<br />\n";
		}
		
		$subject = $wpdb->_escape(trim($_POST['subject']));
		$fromemail = $wpdb->_escape(trim($_POST['emailaddress']));
		$headers = "";
		if(!empty($fromemail)) {
			$headers = "From: ".$fromemail. " \r\n";
		}
		
		$phonenumber = $wpdb->_escape(trim($_POST['phonenumber']));
		if(!empty($phonenumber)) {
			$phonenumber = 'Phone Number :- '.$phonenumber."\n<br />\n";
		}
		$companyname = $wpdb->_escape(trim($_POST['companyname']));
		if(!empty($companyname)) {
			$companyname = 'Company Name :- '.$companyname."\n<br />\n";
		}
		$website = $wpdb->_escape(trim($_POST['website']));
		if(!empty($website)) {
			$website = 'Website :- '.$website."\n<br />\n";
		}
		$comments = $wpdb->_escape(trim($_POST['comments']));
		if(!empty($comments)) {
			$var = nl2br($comments);
			$comments = 'Comments :- '.$var;
		}
		
		$contents = $fullname."".$firstname."".$lastname."".$phonenumber."".$companyname."".$website."".$comments."";
		
        if(is_email($fromemail)) {
			add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
			
			if(!wp_mail($toemail, $subject, $contents, $headers)) {
				$contact_errors = true;
			}
			remove_filter( 'wp_mail_content_type',array($this,'set_html_content_type') );
			
        } else {
			echo "Mail failed";
		}
        
        header("location:".$_SERVER['HTTP_REFERER']);
		exit();
	}
	
	/**
     * Content html type
     */
    public function set_html_content_type() {
		return 'text/html';
	}	
    
    /**
     * Front-end Css and Javascript initialize.
     */
    public function secf_frontend_scripts() {
		wp_enqueue_style('secf-css-handler', SECF_URL.'assets/css/simple-easy-contact-form.css');
		//wp_enqueue_script('secf-js-handler', SECF_URL.'assets/js/simple-easy-contact-form.js',array('jquery'),'1.0.0',true);
    }



    
    
    /**
     * Add the help tab to the screen.
     */
    public function help_tab()
    {
		$screen = get_current_screen();

		// documentation tab
		$screen->add_help_tab(array(
			'id' => 'documentation',
			'title' => __('Documentation', 'secf'),
			'content' => "<p><a href='http://www.ifourtechnolab.com/documentation/' target='blank'>Simple Easy Contact Form</a></p>",
			)
		);
    }

    /**
     * Deactivation hook.
     */
    public function secf_deactivation_hook() {
		if (function_exists('update_option')) {
			global $wpdb;
			$sql = "DROP TABLE IF EXISTS $table_name".WP_SECF_TABLE;
			$wpdb->query($sql);
		}
    }

    /**
     * Uninstall hook
     */
    public function secf_uninstall_hook() {
		if (current_user_can('delete_plugins')) {
			
		}
    }
}

$contactforms = new Simple_Easy_Contact_Form();

register_activation_hook( __FILE__, array('Simple_Easy_Contact_Form', 'my_plugin_create_db') );

register_deactivation_hook(__FILE__, array('Simple_Easy_Contact_Form', 'secf_deactivation_hook'));

register_uninstall_hook(__FILE__, array('Simple_Easy_Contact_Form', 'secf_uninstall_hook'));
