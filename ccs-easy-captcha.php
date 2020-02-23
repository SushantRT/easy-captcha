<?php
/**
 * Plugin Name:       Easy Captcha
 * Plugin URI:        https://croitresoftwares.com/plugins/easy-captcha/
 * Description:       Easy Captcha plugin is basic captcha plugin to prevent spam submissions.
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Croitre Softwares
 * Author URI:        https://croitresoftwares.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */


/**
* On Activation of Plugin
*/
function ccs_easy_captcha_on_activate(){

	//This token is used just to randomize input names
	$ccs_easycap_app_secret = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 10); //This is used to append before answer
	$ccs_easycap_tok_secret = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 10);
	$ccs_easycap_ans_secret = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 10);

	//Make sure both are unique
	while ($ccs_easycap_tok_secret == $ccs_easycap_ans_secret) {
		$ccs_easycap_ans_secret = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 10);
	}

	add_option( 'ccs_easycap_app_secret', $ccs_easycap_app_secret);
	add_option( 'ccs_easycap_tok_secret', $ccs_easycap_tok_secret);
	add_option( 'ccs_easycap_ans_secret', $ccs_easycap_ans_secret);

}
register_activation_hook( __FILE__, 'ccs_easy_captcha_on_activate' );



/**
* After Deactivate
*/
function ccs_easy_captcha_on_deactivate(){
	
}
register_deactivation_hook( __FILE__, 'ccs_easy_captcha_on_deactivate' );


/**
* After Uninstallation
*/
function ccs_easy_captcha_on_uninstall(){
	unregister_setting( 'ccs_easycap_options_group','ccs_easycap_math_ops' );
	delete_option('ccs_easycap_app_secret');
	delete_option('ccs_easycap_tok_secret');
	delete_option('ccs_easycap_ans_secret');
	delete_option('ccs_easycap_math_ops');
}
register_uninstall_hook( __FILE__, 'ccs_easy_captcha_on_uninstall' );



/**
* Register a custom menu page.
*/
function register_ccs_easy_cap_settings_page() {
	add_options_page(
	    __( 'Easy Captcha Settings', 'textdomain' ),
	    'Easy Captcha',
	    'manage_options',
	    'ccs-easy-cap-settings',
	    'render_ccs_easy_cap_settings_page',
	    7
	);
}
add_action( 'admin_menu', 'register_ccs_easy_cap_settings_page' );


/**
 * Display a custom menu page
 */
function render_ccs_easy_cap_settings_page(){
	?>
	<h1>Easy Captcha Settings <small style="font-size: 12px; color: #777;">(v.0.0.1)</small></h1>
	<p>These are the basic settings for easy captcha plugin. Even if untouched plugin will work fine.</p>
	<hr>
	<br><br>
	<div>
		<form method="post" action="options.php">
			<?php settings_fields( 'ccs_easycap_options_group' ); ?>
			<table>
				<?php
					$ops = get_option('ccs_easycap_math_ops');
					if(!is_array($ops)) $ops = array();
				?>
				<tr valign="top">
				  	<th scope="row" colspan="4" style="text-align: left;"><label>Allowed Math Operations:</label></th>
				</tr>
				<tr valign="top">
				  	<td>
				  	  Addition: <input type="checkbox" name="ccs_easycap_math_ops[]" value="add" <?= in_array('add', $ops) ? 'checked' : '' ?> />
				  	</td>
				  	<td>
				  	  Subtraction: <input type="checkbox" name="ccs_easycap_math_ops[]" value="sub" <?= in_array('sub', $ops) ? 'checked' : '' ?> />
				  	</td>
				  	<td>
				  	  Multiplication: <input type="checkbox" name="ccs_easycap_math_ops[]" value="mul" <?= in_array('mul', $ops) ? 'checked' : '' ?> />
				  	</td>
				</tr>
			</table>
			<?php  submit_button(); ?>
		</form>
	</div>
	<br>
	<hr>
	<p>
		<b>To display captcha on your form you can use one of following methods:</b><br>
		1) Using Shortcode : <code>[ccs_easy_captcha]</code> (Recommended)<br>
		2) Using PHP function : <code><?= htmlspecialchars("<?php echo do_shortcode('[ccs_easy_captcha]'); ?>") ?></code> (For Developers)<br>
	</p>
	<?php
}


/**
 * Adding Plugin Links on Plugin Page
 */
function ccs_easycap_action_links( $links ) {

	$links = array_merge( array(
		'<a href="' . esc_url( admin_url( '/options-general.php?page=ccs-easy-cap-settings' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>'
	), $links );

	return $links;

}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ccs_easycap_action_links' );



/**
 * Register Plugin Settings
 */
function ccs_easycap_register_settings() {
   //Setting for Secret
   add_option( 'ccs_easycap_math_ops', array('add'));
   register_setting( 
   	'ccs_easycap_options_group',
   	'ccs_easycap_math_ops' 
   );
}
add_action( 'admin_init', 'ccs_easycap_register_settings' );



/**
 * Shortcode for Display Captcha Fields
 */

function ccs_easy_captcha_show_fields($atts) {

	$app_secret = get_option( 'ccs_easycap_app_secret');
	$tok_secret = get_option( 'ccs_easycap_tok_secret');
	$ans_secret = get_option( 'ccs_easycap_ans_secret');
	$opArr = get_option( 'ccs_easycap_math_ops');
	if(!is_array($opArr)) $opArr = array('add');
	$op = $opArr[array_rand($opArr)];
	$opSym = '';
	$num1 = rand(50,100);
	$num2 = rand(1,100);
	while ($num2 > $num1) {
		//To prevenet Negative Subtstraction
		$num2 = rand(1,100);
	}
	$ans = 0;

	switch ($op) {
		case 'sub':
			$opSym = ' - ';
			$ans = $num1 - $num2;
			break;
		case 'mul':
			$opSym = ' * ';
			$ans = $num1 * $num2;
			break;
		default:
			$opSym = ' + ';
			$ans = $num1 + $num2;
			break;
	}


	$content = '<div class="form-group">
		<label class="control-label col-sm-4" for="ccs_ps_ans">'.$num1.$opSym.$num2.' = </label>
		<div class="col-sm-8">
			<input name="'.$ans_secret.'" type="text" class="form-control" placeholder="Enter Answer" style="border: 1px solid #EEE;" required>
			<input name="'.$tok_secret.'" type="hidden" value="'.password_hash($ans.$app_secret, PASSWORD_DEFAULT).'" />
			<span class="captcha-res"></span>
		</div>
	</div>';
	 
    return $content;
}
add_shortcode('ccs_easy_captcha', 'ccs_easy_captcha_show_fields');



/**
 * Captcha Verification Function on Form Submit
 */
function ccs_verify_captcha()
{
	if(!verifyCap($_POST)){
		header('location:'.$_SERVER['HTTP_REFERER']);
		exit();
	}
}
add_action('template_redirect', 'ccs_verify_captcha');



/**
 * Footer Script to Enable AJAX Captcha Verification on Form Submit
 */
function ccs_easycap_ajaxscript() {
	$tok_secret = get_option('ccs_easycap_tok_secret');
	$ans_secret = get_option('ccs_easycap_ans_secret');
	?>
	<script type="text/javascript">

		jQuery(document).ready(function(){
			jQuery("form").submit(function(e){
				e.preventDefault();
				var self = this;

				var <?= $tok_secret ?> = jQuery(this).find('input[name=<?= $tok_secret ?>]').val();
				var <?= $ans_secret ?> = jQuery(this).find('input[name=<?= $ans_secret ?>]').val();

				if(<?= $tok_secret ?> != undefined){
					jQuery(self).find('.captcha-res').html('<span class="ccs-easycap-blinking">Verifying</span>');

					jQuery.ajax({
			          type:"POST",
			          url: "<?= get_site_url() ?>/wp-admin/admin-ajax.php",
			          data: {
			              action: "ccs_verify_captcha_js",
			              <?= $tok_secret ?>: <?= $tok_secret ?>,
			              <?= $ans_secret ?>: <?= $ans_secret ?>,
			          },
			          success:function(result){       
			            if(result == 'true'){
			            	self.submit();
			            }
			            else{
			            	jQuery(self).find('.captcha-res').text('Wrong Captcha');
			            }
			          },
			          error: function(errorThrown){
			          	console.log(errorThrown);
			          }
			        });
				}else{
					self.submit();
				}
		    });
	  	});  
	</script>
	<style>
		.ccs-easycap-blinking{
		    animation:blinkingText 0.1s infinite;
		}
		@keyframes blinkingText{
		    0%{     color: #000;    }
		    49%{    color: #000; }
		    60%{    color: transparent; }
		    99%{    color:transparent;  }
		    100%{   color: #000;    }
		}
	</style>
	<?php
}
add_action( 'wp_footer', 'ccs_easycap_ajaxscript' );



/**
 * AJAX Captcha Verification Function on Form Submit
 */
add_action( 'wp_ajax_ccs_verify_captcha_js', 'ccs_verify_captcha_js' );
add_action( 'wp_ajax_nopriv_ccs_verify_captcha_js', 'ccs_verify_captcha_js' );

function ccs_verify_captcha_js() {
	if(!verifyCap($_POST)){
		echo "false";
		die();
	}
	else{
		echo "true";
		die();
	}
}

function verifyCap($data){
	$app_secret = get_option('ccs_easycap_app_secret');
	$tok_secret = get_option('ccs_easycap_tok_secret');
	$ans_secret = get_option('ccs_easycap_ans_secret');

	$easyCap = (isset($data[$tok_secret]) && isset($data[$ans_secret])) ? true : false;

	return $easyCap ? password_verify($data[$ans_secret].$app_secret, $data[$tok_secret]) : true;
}
