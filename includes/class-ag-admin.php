<?php
/**
 * Admin class file for settings.
 *
 * @package AlliedGold
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'AG_Admin' ) ) {

	/**
	 * Allied Gold Admin Class.
	 */
	class AG_Admin {

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_filter( 'woocommerce_settings_tabs_array', array( &$this, 'ag_woocommerce_settings_tabs_option' ), 999 );
			add_action( 'woocommerce_settings_tabs_' . AG_PLUGIN_SLUG, array( &$this, 'ag_program_settings_tab' ) );
			add_action( 'woocommerce_settings_save_' . AG_PLUGIN_SLUG, array( &$this, 'thm_program_settings_save' ) );
		}

		/**
		 * Settings tab.
		 *
		 * @param array $settings_tabs Settings Tab.
		 * @return array
		 */
		public function ag_woocommerce_settings_tabs_option( $settings_tabs ) {
			$settings_tabs['ag-integration'] = esc_html__( 'Allied Gold Settings', 'allied-gold-integration' );
			return $settings_tabs;
		}

		/**
		 * This function will display the settings.
		 */
		public function ag_program_settings_tab() {
			woocommerce_admin_fields( $this->ag_get_settings() );

			/*echo '
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label>Shortcode</label>
						</th>
						<td class="forminp forminp-text">
							<p>[thm_insurance_details] - Use the following shortcode to display the insurance checkbox anywhere on the Product Page.</p>
						</td>
					</tr>
				</tbody>
			</table>';*/
		}

		/**
		 * Display the html of each sections using Setting API.
		 */
		public function ag_get_settings() {
			$settings = array(
				array(
					'title' => esc_html__( 'Allied Gold Settings', 'allied-gold-integration' ),
					'type'  => 'title',
					'id'    => 'wc_thm_settings',
				),
				array(
					'title'    => esc_html__( 'Enable Add to Cart button', 'allied-gold-integration' ),
					'type'     => 'checkbox',
					'id'       => 'wc_ag_enable',
					'class'    => 'wc_ag_enable',
					'desc_tip' => __( 'Select this option to enable add to cart button for the iFrame.', 'allied-gold-integration' ),
				),
				array(
					'title'    => esc_html__( 'Enable Contact Form', 'allied-gold-integration' ),
					'type'     => 'checkbox',
					'id'       => 'wc_ag_enable_form',
					'class'    => 'wc_ag_enable_form',
					'desc_tip' => __( 'Select this option to enable Contact Form.', 'allied-gold-integration' ),
				),
				array(
					'title'    => esc_html__( 'Email Address', 'thm-integration' ),
					'type'     => 'text',
					'id'       => 'wc_ag_email',
					'class'    => 'wc_ag_email',
					'desc_tip' => __( 'Please enter the email address to which the contact forms must be sent. This email address will be effective only if Contact Form is enabled. Multiple email addresses can be entered separated by a comma ","', 'thm-integration' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wc_ag_settings',
				),
			);
			$settings = apply_filters( 'wc_ag_settings', $settings );
			return $settings;
		}

		/**
		 * Save the data using Setting API
		 */
		public function thm_program_settings_save() {
			global $current_section;
			WC_Admin_Settings::save_fields( $this->ag_get_settings() );
		}
	}

}

return new AG_Admin();
