<?php
/**
 * Plugin Name: Purchase Order
 * Plugin URI: https://github.com/bhaskarkc/Woo-ajax-example
 * Description: Purchase order plugin
 * Version: 1.0.0
 * Author: Bhaskar K C
 * Author URI: http://bhaskarkc.net
 * License: GPL2
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ass_Purchase_Order' ) ) {

	class Ass_Purchase_Order {

		private static $instance = null;

		const PAGE_SLUG = 'purchase-order';

		const AJAX_CALL_ACTION_PRODUCTS = 'list_products_cb';

		const AJAX_CALL_ACTION_VARIATIONS = 'list_variations_cb';

		const PURCHASE_ORDER_TEMPLATE = 'template/view.php';

		const POST_TYPE = 'product';

		const VARIATION_POST_TYPE = 'product_variation';

		const TAXONOMY_NAME = 'product_cat';

		public function __construct() {

			add_action( 'wp_loaded', [ $this, 'ass_process_form' ] );

			add_filter( 'template_include', [ $this, 'render_purchase_order_page' ], 10, 1 );

			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			add_action( 'wp_ajax_nopriv_' . self::AJAX_CALL_ACTION_PRODUCTS, [ $this, 'ajax_cb_product_dropdown' ] );
			add_action( 'wp_ajax_' . self::AJAX_CALL_ACTION_PRODUCTS, [ $this, 'ajax_cb_product_dropdown' ] );

			add_action( 'wp_ajax_nopriv_' . self::AJAX_CALL_ACTION_VARIATIONS, [ $this, 'ajax_cb_variation_dropdown', ] );
			add_action( 'wp_ajax_' . self::AJAX_CALL_ACTION_VARIATIONS, [ $this, 'ajax_cb_variation_dropdown' ] );
		}

		/**
		 * Filters the path of the current template before including it.
		 *
		 * @since 3.0.0
		 *
		 * @param string $template The path of the template to include.
		 */
		public function render_purchase_order_page( $template ) {

			global $post;
			if ( is_page() && $post->post_name == self::PAGE_SLUG ) {
				$template = __DIR__ . '/' . self::PURCHASE_ORDER_TEMPLATE;
			}

			return $template;
		}

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				return new self;
			} else {
				return self::$instance;
			}
		}

		/**
		 * Enqueues script.
		 */
		function enqueue_scripts() {
			wp_enqueue_style( 'purchase-order-css', plugins_url( '/css/main.css', __FILE__ ) );
			wp_enqueue_script( 'purchase-order-js', plugins_url( '/js/main.js', __FILE__ ), [ 'jquery' ], '0.1', true );
			wp_localize_script(
				'purchase-order-js', 'purchaseOrderJs', [
					'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
					'product_ajax'   => self::AJAX_CALL_ACTION_PRODUCTS,
					'variation_ajax' => self::AJAX_CALL_ACTION_VARIATIONS,
				]
			);
		}

		/**
		 * Process submitted form
		 * Send email or do whatever you want
		 */
		public function ass_process_form() {
			// Bail early if doing ajax or form is empty
			if (
				wp_doing_ajax()
				|| empty( $_POST['purchase-order'] )
				|| empty( $_POST )
			) {
				return;
			} // Validate form using nonce
			elseif (
				! isset( $_POST['ass_nonce_name_purchase_order'] )
				|| ! wp_verify_nonce( $_POST['ass_nonce_name_purchase_order'], 'ass_nonce_action_purchase_order' )
			) {

				print 'Sorry, your nonce did not verify.';
				exit;
			}

			$firstname = sanitize_text_field( $_POST['first_name'] );
			$lastname  = sanitize_text_field( $_POST['last_name'] );
			$email     = sanitize_email( $_POST['email'] );
			$phone     = sanitize_text_field( $_POST['phone'] );

			$category_title       = $this->get_term_name_by_id( $_POST['prod_category_id'] );
			$product_title        = $this->get_product_title_by_id( $_POST['prod_id'] );
			$variation_title_full = $this->get_product_title_by_id( $_POST['variation_id'] );
			$variation_title      = str_replace( "{$product_title} - ", '', $variation_title_full );

			// Preparing to send email.
			ob_start();
			include 'template/email-template.php';

			$message    = ob_get_clean();
			$subject    = 'A new lead generated';
			$to         = 'xlinkerz@gmail.com';//get_option( 'admin_email' );
			$from_email = 'noreply@purchaseorder.github.io';
			$from_name  = 'Purchase Order Systems';

			if ( $this->send_html_email( $to, $from_email, $from_name, $subject, $message ) ) {
				$redirect_url = add_query_arg( 'status', 'success', site_url( '/purchase-order/') );
				wp_safe_redirect( $redirect_url );
				exit;
			}
		}

		/**
		 * Get term name by ID
		 *
		 * @param $term_id
		 *
		 * @return null|string
		 */
		function get_term_name_by_id( $term_id ) {
			global $wpdb;

			return $wpdb->get_var(
				$wpdb->prepare(
					"SELECT `name` from {$wpdb->terms} WHERE `term_id` = %d",
					$term_id
				)
			);
		}

		/**
		 * Get product title by ID
		 *
		 * @param $product_id
		 *
		 * @return null|string
		 */
		function get_product_title_by_id( $product_id ) {
			global $wpdb;

			return $wpdb->get_var(
				$wpdb->prepare(
					"SELECT `post_title` from {$wpdb->posts} WHERE `ID` = %d",
					$product_id
				)
			);

		}

		/**
		 * Send HTML email.
		 *
		 * @param $to
		 * @param $from_mail
		 * @param $from_name
		 * @param $subject
		 * @param $message
		 *
		 * @return bool
		 */
		function send_html_email( $to, $from_email, $from_name, $subject, $message ) {
			$header   = [];
			$header[] = 'MIME-Version: 1.0';
			$header[] = "From: {$from_name}<{$from_email}>";
			/* Set message content type HTML*/
			$header[] = 'Content-type:text/html; charset=iso-8859-1';
			$header[] = 'Content-Transfer-Encoding: 7bit';

			return mail( $to, $subject, $message, implode( "\r\n", $header ) );
		}

		/**
		 * Ajax callback function for variation callback.
		 */
		public function ajax_cb_variation_dropdown() {
			if ( empty( $_POST['prod_id'] ) ) {
				wp_send_json_error( 'Select valid product.' );
			}
			wp_send_json_success( $this->get_product_variations_img_list( $_POST['prod_id'] ) );
		}

		/**
		 * Ajax callback function for product callback.
		 */
		public function ajax_cb_product_dropdown() {

			if ( empty( $_POST['prod_cat_id'] ) ) {
				wp_send_json_error( 'Select valid category.' );
			}
			wp_send_json_success( $this->get_products_dropdown_by_category( $_POST['prod_cat_id'] ) );
		}

		public function get_product_variations_img_list( $product_id ) {
			$variation_post_ids = get_posts(
				[
					'post_type'      => self::VARIATION_POST_TYPE,
					'post_status'    => 'publish',
					'posts_per_page' => - 1,
					'post_parent'    => $product_id,
					'fields'         => 'ids',
				]
			);

			if ( empty( $variation_post_ids ) ) {
				return false;
			}

			ob_start(); ?>
			<ul id="select-ass-variation-list">
				<?php foreach ( $variation_post_ids as $variation_post_id ) { ?>
					<li>
						<img
							src="<?php echo wp_get_attachment_image_url( get_post_thumbnail_id( $variation_post_id ), 'thumbnail' ); ?> ">
						<input type="radio" name="variation_id" value="<?php echo $variation_post_id; ?>">
					</li>
				<?php } ?>
			</ul>
			<?php
			return ob_get_clean();
		}

		/**
		 * @param $product_cat_id
		 *
		 * @return bool|string
		 */
		public function get_products_dropdown_by_category( $product_cat_id ) {
			$product_posts = get_posts(
				[
					'post_type'      => self::POST_TYPE,
					'post_status'    => 'publish',
					'posts_per_page' => - 1,
					'tax_query'      => [
						[
							'taxonomy' => self::TAXONOMY_NAME,
							'field'    => 'term_id',
							'terms'    => [ $product_cat_id ],
						],
					],
				]
			);

			if ( empty( $product_posts ) ) {
				return false;
			}

			ob_start();
			?>
			<select name="prod_id" id="select-ass-prod-dropdown">
				<option>--Select Product--</option>
				<?php foreach ( $product_posts as $product_post ) { ?>
					<option value="<?php echo $product_post->ID; ?>"><?php echo $product_post->post_title; ?></option>
				<?php } ?>
			</select>
			<?php
			return ob_get_clean();
		}

		/**
		 * Return woo prod category dropdown
		 *
		 * @return bool|string
		 */
		public function get_product_category_dropdown() {

			$terms = get_terms(
				[
					'taxonomy' => self::TAXONOMY_NAME,
					'hide_empty' => true,
				]
			);
			if ( empty( $terms ) ) {
				return false;
			}

			ob_start();
			?>
			<select name="prod_category_id" id="select-ass-cat-dropdown">
				<option>--Select Category--</option>
				<?php foreach ( $terms as $term ) { ?>
					<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
				<?php } ?>
			</select>
			<?php
			return ob_get_clean();
		}
	}
}

Ass_Purchase_Order::get_instance();
