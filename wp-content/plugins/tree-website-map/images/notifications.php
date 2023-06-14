<?php
if ( ! defined( 'ABSPATH' ) ) {
  // Exit if accessed directly.
  exit;
}

$icon_path = plugins_url( '/images/icon-128x128.png' , __FILE__);
$rating_url = "https://wordpress.org/support/plugin/visitors-traffic-real-time-statistics/reviews/?filter=5&rate=5#new-post";
$activation_time = 604800; // 7 days in seconds
$file_version = 2.4;
$development_mode = false; // Put yes to allow development mode, you will see the rating notice without timers

/**
* @since  1.9
* @version 1.9
* @class vtrts_free_Notification
*/

if ( ! class_exists( 'vtrts_free_Notification' ) ) :

  class vtrts_free_Notification {
	
	/* * * * * * * * * *
    * Class constructor
    * * * * * * * * * */
    public function __construct() {

      $this->_hooks();
    }

    /**
    * Hook into actions and filters
    * @since  1.0.0
    * @version 1.2.1
    */
    private function _hooks() {
      add_action( 'admin_init', array( $this, 'vtrts_free_review_notice' ) );
    }
	
	/**
  	 * Ask users to review our plugin on wordpress.org
  	 *
  	 * @since 1.0.11
  	 * @return boolean false
  	 * @version 1.1.3
  	 */
  	public function vtrts_free_review_notice() {
		
		global $file_version, $activation_time, $development_mode;
		
		$this->vtrts_free_review_dismissal();
		
  		$this->vtrts_free_review_pending();
		
		$activation_time 	= get_site_option( 'vtrts_free_active_time' );
		
  		$review_dismissal	= get_site_option( 'vtrts_free_review_dismiss' );
		
		if ($review_dismissal == 'yes' && !$development_mode) return;
		
		if ( !$activation_time && !$development_mode ) :

  			$activation_time = time(); // Reset Time to current time.
  			add_site_option( 'vtrts_free_active_time', $activation_time );
			
  		endif;
		if ($development_mode) $activation_time = 432001; //This variable used to show the message always for testing purposes only
  		// 432000 = 5 Days in seconds.
  		if ( time() - $activation_time > 432000 ) :
		
			wp_enqueue_style( 'vtrts_free_review_stlye', plugins_url( '/css/css-review.css', __FILE__ ), array(), $file_version );
			add_action( 'admin_notices' , array( $this, 'vtrts_free_review_notice_message' ) );
		
		endif;
  	}

    /**
  	 *	Check and Dismiss review message.
  	 *
  	 *	@since 1.9
  	 */
  	private function vtrts_free_review_dismissal() {

  		if ( ! is_admin() ||
  			! current_user_can( 'manage_options' ) ||
  			! isset( $_GET['_wpnonce'] ) ||
  			! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'vtrts_free_review-nonce' ) ||
  			! isset( $_GET['vtrts_free_review_dismiss'] ) ) :

  			return;
  		endif;

  		add_site_option( 'vtrts_free_review_dismiss', 'yes' );
  	}

    /**
  	 * Set time to current so review notice will popup after 14 days
  	 *
  	 * @since 1.9
  	 */
  	private function vtrts_free_review_pending() {

  		if ( ! is_admin() ||
  			! current_user_can( 'manage_options' ) ||
  			! isset( $_GET['_wpnonce'] ) ||
  			! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'vtrts_free_review-nonce' ) ||
  			! isset( $_GET['vtrts_free_review_later'] ) ) :

  			return;
  		endif;

  		// Reset Time to current time.
  		update_site_option( 'vtrts_free_active_time', time() );
  	}

    /**
  	 * Review notice message
  	 *
  	 * @since  1.0.11
  	 */
  	public function vtrts_free_review_notice_message() {

  		$scheme      = ( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) ? '&' : '?';
  		$url         = $_SERVER['REQUEST_URI'] . $scheme . 'vtrts_free_review_dismiss=yes';
  		$dismiss_url = wp_nonce_url( $url, 'vtrts_free_review-nonce' );

  		$_later_link = $_SERVER['REQUEST_URI'] . $scheme . 'vtrts_free_review_later=yes';
  		$later_url   = wp_nonce_url( $_later_link, 'vtrts_free_review-nonce' );
		
		global $icon_path;
		
		global $rating_url;
      ?>

  		<div class="vtrts_free_review-notice">
  			<div class="vtrts_free_review-thumbnail">
  				<img src="<?php echo $icon_path; ?>" alt="">
  			</div>
  			<div class="vtrts_free_review-text">
  				<h3><?php _e( 'Leave A Review?', 'visitors-traffic-real-time-statistics' ) ?></h3>
  				<p><?php _e( 'We hope you\'ve enjoyed using visitor traffic real time statistics :) Would you mind taking a few minutes to write a review on WordPress.org?<br>Just writing simple "thank you" will make us happy!', 'visitors-traffic-real-time-statistics' ) ?></p>
  				<ul class="vtrts_free_review-ul">
            <li><a href="<?php echo $rating_url; ?>" target="_blank"><span class="dashicons dashicons-external"></span><?php _e( 'Sure! I\'d love to!', 'visitors-traffic-real-time-statistics' ) ?></a></li>
            <li><a href="<?php echo $dismiss_url ?>"><span class="dashicons dashicons-smiley"></span><?php _e( 'I\'ve already left a review', 'visitors-traffic-real-time-statistics' ) ?></a></li>
            <li><a href="<?php echo $later_url ?>"><span class="dashicons dashicons-calendar-alt"></span><?php _e( 'Will Rate Later', 'visitors-traffic-real-time-statistics' ) ?></a></li>
            <li><a href="<?php echo $dismiss_url ?>"><span class="dashicons dashicons-dismiss"></span><?php _e( 'Hide Forever', 'visitors-traffic-real-time-statistics' ) ?></a></li></ul>
  			</div>
  		</div>
  	<?php
  	}
}

endif;
$admincore = '';
	if (isset($_GET['page'])) $admincore = sanitize_text_field($_GET['page']);
	if($admincore != 'wccpoptionspro') {
		new vtrts_free_Notification();
	}
?>