<?php
namespace SEDE_CF7;

/**
 * Define the Spam filter Global option futures.
 *
 * @package         SEDE_CF7_Global_Option
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}


if ( ! class_exists( 'SEDE_CF7_Global_Option' ) ) {

	/**
	 * This class describes a CF7 SEDE Global Option.
	 */
	class SEDE_CF7_Global_Option {

		/**
		 * Constructs a new instance and register all hooks.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'sede_cf7_register_global_option' ) );
			add_action( 'admin_init', array( $this, 'sede_cf7_option_settings_fields' ) );
			add_filter( 'sede_cf7_blacklisted_domain', array( $this, 'sede_cf7_blacklisted_domain_global' ) );
		}

		/**
		 * Added a sub page under the Contact menu.
		 */
		public function sede_cf7_register_global_option() {
			add_submenu_page(
				'wpcf7',
				__( 'Exclude Domains', 'spam-email-domain-exclusion-cf7' ),
				__( 'Exclude Domains', 'spam-email-domain-exclusion-cf7' ),
				'wpcf7_manage_integration',
				'spam-email-domain-exclusion-cf7',
				array( $this, 'sede_cf7_render_global_page' )
			);
		}

		/**
		 * Render Spam filter global page
		 */
		public function sede_cf7_render_global_page() {
			?>
			<div class="wrap">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'spam_email_domain_exclusion_cf7_settings' );
					do_settings_sections( 'spam_email_domain_exclusion_cf7' );
					submit_button();
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Register Option page for spam filter input.
		 */
		public function sede_cf7_option_settings_fields() {

			$page_slug    = 'spam_email_domain_exclusion_cf7';
			$option_group = 'spam_email_domain_exclusion_cf7_settings';

			add_settings_section(
				'spam_email_domain_exclusion_cf7',
				'',
				'',
				$page_slug
			);

			register_setting(
				$option_group,
				'sede_cf7_blacklisted_domain',
				array( $this, 'sanitize_fields_sede' )
			);

			add_settings_field(
				'sede_cf7_blacklisted_domain',
				__( 'Excluded Domains', 'spam-email-domain-exclusion-cf7' ),
				array( $this, 'sede_cf7_blacklisted_domain' ),
				$page_slug,
				'spam_email_domain_exclusion_cf7',
				array(
					'label_for'   => 'sede_cf7_blacklisted_domain',
					'class'       => 'spam-email-domain-exclusion_excluded-domains',
					'name'        => 'sede_cf7_blacklisted_domain',
					'description' => __( 'List all Domains with comma-separated. For Example: .abc, .xxx', 'spam-email-domain-exclusion-cf7' ),
				)
			);

		}

		/**
		 * Callback function to print field HTML
		 *
		 * @param      array $args   of add_settings_field.
		 */
		public function sede_cf7_blacklisted_domain( $args ) {
			printf(
				'<input type="text" id="%s" name="%s" value="%s" /> <br />
				<span class="%s">%s</span>
				',
				esc_attr( $args['name'] ),
				esc_attr( $args['name'] ),
				esc_attr( get_option( $args['name'] ) ),
				esc_attr( $args['name'] ),
				esc_html( $args['description'] )
			);
		}

		/**
		 * Sanitize all user input fields
		 *
		 * @param      string $input  The user input string.
		 *
		 * @return     string  sanitized input value
		 */
		public function sanitize_fields_sede( $input ) {
			if ( ! empty( $input ) ) {
				$input = preg_replace( '/,+/', ',', sanitize_text_field( wp_unslash( $input ) ) );
			}

			return $input;
		}

		/**
		 * Add Global blacklist using filter hook
		 *
		 * @param      string $domains  The domains list with comma saprated.
		 *
		 * @return     string  contcated string of previes used domain and the global
		 */
		public function sede_cf7_blacklisted_domain_global( $domains ) {
			$sede_cf7_blacklisted_domain_global = get_option( 'sede_cf7_blacklisted_domain' );
			$sede_cf7_blacklisted_domain        = implode( ',', array( $domains, $sede_cf7_blacklisted_domain_global ) );
			return $sede_cf7_blacklisted_domain;
		}
	}

}

