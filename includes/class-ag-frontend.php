<?php
/**
 * Frontend class file.
 *
 * @package THMarch
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'AG_Frontend' ) ) {

	/**
	 * Frontend Class.
	 */
	class AG_Frontend {

		/**
		 * Constructor function.
		 */
		public function __construct() {
			add_shortcode( 'ag_product_form', array( &$this, 'ag_product_form' ), 10, 1 );

			add_action( 'ag_before_add_to_cart_button', array( &$this, 'ag_hidden_fields' ) );

			add_action( 'ag_after_add_to_cart_form', array( &$this, 'ag_add_contact_form' ), 10, 2 );

			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'ag_add_cart_item_data' ), 25, 2 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'ag_get_cart_item_from_session' ), 25, 2 );
			add_filter( 'woocommerce_get_item_data', array( $this, 'ag_get_item_data' ), 25, 2 );

			add_filter( 'woocommerce_cart_item_thumbnail', array( &$this, 'ag_change_thumbnail' ), 10, 3 );
			//add_filter( 'woocommerce_cart_item_price', array( &$this, 'ag_change_price' ), 10, 3 );

			add_action( 'woocommerce_before_calculate_totals', array( &$this, 'ag_calculate_price' ) );

			add_action( 'woocommerce_checkout_create_order_line_item', array( &$this, 'ag_add_product_details' ), 10, 4 );

			add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( &$this, 'ag_unset_meta' ), 10, 2 );

			add_action( 'wp_ajax_ag_send_form', array( &$this, 'ag_send_form' ) );
			add_action( 'wp_ajax_nopriv_ag_send_form', array( &$this, 'ag_send_form' ) );
		}

		/**
		 * Shortcode for showing the product Add to cart button.
		 *
		 * @param array $args Args passed to Shortcode.
		 * @return string
		 */
		public function ag_product_form( $args ) {
			global $post, $product;

			$this->ag_load_scripts();

			$product = wc_get_product( $args['id'] );
			ob_start();
			wc_get_template(
				'add_to_cart.php',
				array(
					'product'        => $product,
					'page_permalink' => get_permalink( $post ),
				),
				'allied_gold_integration/',
				AG_TEMPLATE_PATH
			);
			$output = ob_get_clean();
			return $output;
		}

		/**
		 * This function is used to add the hidden fields for information coming from iFrame.
		 *
		 * @return void
		 */
		public function ag_hidden_fields() {
			?>
				<h3 class='ag_product_name'></h3>
				<h3 class='ag_gold_final_price'></h3>

				<input type='hidden' name='ag_price' id='ag_price' class='ag_price'/>
				<input type='hidden' name='ag_product_id' id='ag_product_id' class='ag_product_id'/>
				<input type='hidden' name='ag_product_name' id='ag_product_name' class='ag_product_name'/>
				<input type='hidden' name='ag_product_img360' id='ag_product_img360' class='ag_product_img360'/>
				<input type='hidden' name='ag_product_imgURL' id='ag_product_imgURL' class='ag_product_imgURL'/>
				<input type='hidden' name='ag_size_code' id='ag_size_code' class='ag_size_code'/>
				<input type='hidden' name='ag_size_id' id='ag_size_id' class='ag_size_id'/>
				<input type='hidden' name='ag_size_name' id='ag_size_name' class='ag_size_name'/>

				<input type='hidden' name='ag_polishing_texture' id='ag_polishing_texture' class='ag_polishing_texture'/>
				<input type='hidden' name='ag_eng_font' id='ag_eng_font' class='ag_eng_font'/>
				<input type='hidden' name='ag_eng_text' id='ag_eng_text' class='ag_eng_text'/>
			<?php
		}

		/**
		 * Load frontend JS files.
		 *
		 * @return void
		 */
		public function ag_load_scripts() {
			wp_enqueue_style(
				'ag-styles',
				AG_PLUGIN_URL . '/assets/css/ag_styles.css',
				array(),
				AG_PLUGIN_VERSION,
				'all'
			);

			wp_register_script(
				'ag-frontend',
				AG_PLUGIN_URL . '/assets/js/ag_scripts.js',
				array(),
				AG_PLUGIN_VERSION,
				true
			);

			wp_localize_script(
				'ag-frontend',
				'ag_vars',
				array(
					'ajax_url'        => get_admin_url() . 'admin-ajax.php',
					'currency_symbol' => get_woocommerce_currency_symbol(),
				)
			);

			wp_enqueue_script( 'ag-frontend' );

			wp_enqueue_script(
				'ag-plugin_script',
				AG_PLUGIN_URL . '/assets/js/ag_plugin.js',
				array(),
				AG_PLUGIN_VERSION,
				true
			);
		}

		/**
		 * Add meta information passed from the page.
		 *
		 * @param array $cart_item_meta Cart Item Meta Array.
		 * @param int   $product_id     Product ID.
		 * @return array
		 */
		public function ag_add_cart_item_data( $cart_item_meta, $product_id ) {
			if ( isset( $_POST ) && isset( $_POST['ag_price'] ) && '' !== $_POST['ag_price'] ) { // phpcs:ignore
				$ag_product_details = array();

				$ag_items = array(
					'ag_price',
					'ag_product_id',
					'ag_product_name',
					'ag_product_img360',
					'ag_product_imgURL',
					'ag_size_code',
					'ag_size_id',
					'ag_size_name',
					'ag_polishing_texture',
					'ag_eng_font',
					'ag_eng_text',
				);

				foreach ( $ag_items as $item ) {
					$ag_product_details[ $item ] = ( isset( $_POST[ $item ] ) && '' !== $_POST[ $item ] ) ? $_POST[ $item ] : ''; // phpcs:ignore;
				}

				$cart_item_meta['ag_product_details'] = $ag_product_details;
			}

			return $cart_item_meta;
		}

		/**
		 * Get Cart Items from Session.
		 *
		 * @param mixed $cart_item Cart Item.
		 * @param mixed $values    Values.
		 * @return mixed
		 */
		public function ag_get_cart_item_from_session( $cart_item, $values ) {
			if ( isset( $values['ag_product_details'] ) ) {
				$cart_item['ag_product_details'] = $values['ag_product_details'];
			}

			return $cart_item;
		}

		/**
		 * Get Item Data.
		 *
		 * @param mixed $other_data Meta Data.
		 * @param mixed $cart_item  Cart Item.
		 * @return mixed
		 */
		public function ag_get_item_data( $other_data, $cart_item ) {
			if ( isset( $cart_item['ag_product_details'] ) ) {
				$other_data[] = array(
					'name'    => 'Product Name',
					'display' => $cart_item['ag_product_details']['ag_product_name'],
				);

				$other_data[] = array(
					'name'    => 'Product ID',
					'display' => $cart_item['ag_product_details']['ag_product_id'],
				);

				$other_data[] = array(
					'name'    => 'Size ID',
					'display' => $cart_item['ag_product_details']['ag_size_id'],
				);

				if ( isset( $cart_item['ag_product_details']['ag_eng_text'] ) && '' !== $cart_item['ag_product_details']['ag_eng_text'] ) {
					$other_data[] = array(
						'name'    => 'Engraving Text',
						'display' => $cart_item['ag_product_details']['ag_eng_text'],
					);
				}
			}

			return $other_data;
		}

		/**
		 * Modify Thumbnail on cart page.
		 *
		 * @param string $product_image Product Image.
		 * @param mixed  $cart_item     Cart Item.
		 * @param string $cart_item_key Cart Item Key.
		 * @return string
		 */
		public function ag_change_thumbnail( $product_image, $cart_item, $cart_item_key ) {
			if ( isset( $cart_item['ag_product_details'] ) && isset( $cart_item['ag_product_details']['ag_product_imgURL'] ) ) {
				$product_image = '<img width="300" height="300" src="' . $cart_item['ag_product_details']['ag_product_imgURL'] . '" class="woocommerce-placeholder wp-post-image" alt="Placeholder" loading="lazy" sizes="(max-width: 300px) 100vw, 300px">';
			}
			return $product_image;
		}

		/**
		 * Modify Product Price.
		 *
		 * @param string $product_price Price.
		 * @param mixed  $cart_item     Item.
		 * @param string $cart_item_key Key.
		 * @return string
		 */
		public function ag_change_price( $product_price, $cart_item, $cart_item_key ) {
			if ( isset( $cart_item['ag_product_details'] ) && isset( $cart_item['ag_product_details']['ag_price'] ) ) {
				$product_price = wc_price( $cart_item['ag_product_details']['ag_price'] );
			}
			return $product_price;
		}

		/**
		 * Modify the item price.
		 *
		 * @param mixed $cart_object Cart Object.
		 * @return void
		 */
		public function ag_calculate_price( $cart_object ) {
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				return;
			}

			foreach ( $cart_object->get_cart() as $hash => $value ) {
				if ( isset( $value['ag_product_details'] ) && isset( $value['ag_product_details']['ag_price'] ) ) {
					$value['data']->set_price( $value['ag_product_details']['ag_price'] );
				}
			}
		}

		/**
		 * Add Product Details to item meta
		 *
		 * @param mixed $item           Item.
		 * @param string $cart_item_key Item Key.
		 * @param mixed $values         Values if present.
		 * @param WC_Order $order       Woo Order.
		 * @return void
		 */
		public function ag_add_product_details( $item, $cart_item_key, $values, $order ) {
			if ( empty( $values['ag_product_details'] ) ) {
				return;
			}

			$ag_details = $values['ag_product_details'];

			$item->add_meta_data( 'Product Name', $ag_details['ag_product_name'] );
			$item->add_meta_data( 'Product ID', $ag_details['ag_product_id'] );
			$item->add_meta_data( 'Product Price', $ag_details['ag_price'] );
			$item->add_meta_data( 'Size Code', $ag_details['ag_size_code'] );
			$item->add_meta_data( 'Size ID', $ag_details['ag_size_id'] );
			$item->add_meta_data( 'Size Name', $ag_details['ag_size_name'] );
			$item->add_meta_data( 'Polishing Texture', $ag_details['ag_polishing_texture'] );

			if ( isset( $ag_details['ag_eng_font'] ) && '' !== $ag_details['ag_eng_font'] ) {
				$item->add_meta_data( 'Engraving Font', $ag_details['ag_eng_font'] );
			}

			if ( isset( $ag_details['ag_eng_text'] ) && '' !== $ag_details['ag_eng_text'] ) {
				$item->add_meta_data( 'Engraving Text', $ag_details['ag_eng_text'] );
			}
		}

		/**
		 * Unset specific not needed in the emails
		 *
		 * @param array $formatted_meta Formatted Meta.
		 * @param array $item           Item.
		 * @return array
		 */
		public function ag_unset_meta( $formatted_meta, $item ) {
			// Only on emails notifications.
			if ( is_admin() ) {
				return $formatted_meta;
			}

			foreach ( $formatted_meta as $key => $meta ){
				if ( in_array( $meta->key, array( 'Product Price', 'Size Code', 'Size Name', 'Polishing Texture', 'Engraving Font' ), true ) ) {
					unset( $formatted_meta[ $key ] );
				}
			}
			return $formatted_meta;
		}

		/**
		 * Add contact form after add to cart button.
		 *
		 * @return void
		 */
		public function ag_add_contact_form() {
			?>

				<?php if ( 'no' !== get_option( 'wc_ag_enable', 'no' ) && 'no' !== get_option( 'wc_ag_enable_form', 'no' ) ) : ?>
				<i class='ag_separator'><p class='ag_separator_text'>———————————or——————————</p></i>
				<?php endif; ?>

				<?php if ( 'no' !== get_option( 'wc_ag_enable_form', 'no' ) ) : ?>
				<h3 class='ag_form_title'>Not ready to buy online? Fill out the form below and we’ll be in touch to arrange a consultation</h3>

				<form id='ag_contact_form' name='ag_contact_form' class='ag_contact_form' type='post'>
					<label for='ag_name' class='ag_margin'>Name:*</label>
					<input type='text' name='ag_name' id='ag_name' class='ag_name' required/>

					<label for='ag_email' class='ag_margin'>Email:*</label>
					<input type='email' name='ag_email' id='ag_email' class='ag_email' required/>

					<label for='ag_phone' class='ag_margin'>Phone Number:</label>
					<input type='phone' name='ag_phone' id='ag_phone' class='ag_phone'/>

					<label for='ag_message' class='ag_margin'>Message:</label>
					<input type='text' name='ag_message' id='ag_message' class='ag_message'/>

					<input type='submit' class='button ag_submit_form ag_margin' id='ag_submit_form'/>
				</form>
				<?php endif; ?>
			<?php
		}

		/**
		 * Send Forms.
		 *
		 * @return void
		 */
		public function ag_send_form() {
			if ( isset( $_POST['name'] ) ) {
				$name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
			}

			if ( isset( $_POST['email'] ) ) {
				$email = sanitize_email( wp_unslash( $_POST['email'] ) );
			}

			if ( isset( $_POST['phone'] ) ) {
				$phone = wc_sanitize_phone_number( wp_unslash( $_POST['phone'] ) );
			}

			if ( isset( $_POST['message'] ) ) {
				$desc = sanitize_text_field( wp_unslash( $_POST['message'] ) );
			}

			$subject = 'Bespoke Wedding Ring Enquiry';

			$message  = 'A new inquiry has been submitted with the following details:</br>';
			$message .= 'Name: ' . $name . '</br>';
			$message .= 'Email: ' . $email . '</br>';
			$message .= 'Phone: ' . $phone . '</br>';
			$message .= 'Message: ' . $desc . '</br>';
			if ( 'none' === $_POST['prod_name'] ) {
				$message .= 'Product: No product selected';
			} else {
				$message .= 'Product: ' . $_POST['prod_name'];
			}

			$admin_email = get_option( 'wc_ag_email', get_bloginfo( 'admin_email' ) );

			$site_email = get_bloginfo( 'admin_email' );

			$headers  = 'From: ' . $site_name . ' <' . $site_email . '>' . "\r\n";
			$headers .= 'Content-Type: text/html' . "\r\n";
			$headers .= 'Reply-To:  ' . $email . ' ' . "\r\n";

			wp_mail( $admin_email, $subject, $message, $headers );

			$headers_return  = 'From: ' . $site_name . ' <' . $email . '>' . "\r\n";
			$headers_return .= 'Content-Type: text/html' . "\r\n";
			$headers_return .= 'Reply-To:  ' . $admin_email . ' ' . "\r\n";

			wp_mail( $email, $subject, 'Thank you for submitting the form. We will get back to you at the earliest', $headers_return );

			wp_die();
		}
	}
}

return new AG_Frontend();
