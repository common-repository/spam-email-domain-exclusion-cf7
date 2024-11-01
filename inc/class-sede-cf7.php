<?php
namespace SEDE_CF7;

/**
 * Define the Spam Email Domain Exclusion for CF7 futures.
 *
 * @package         SEDE_CF7
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'SEDE_CF7' ) ) {

	/**
	 * This class describes All features of this add-on.
	 */
	class SEDE_CF7 {

		/**
		 * Constructs a new instance and register all hooks.
		 */
		public function __construct() {
			add_filter( 'wpcf7_editor_panels', array( $this, 'sede_cf7_tab_register' ), 10, 1 );
			add_action( 'wpcf7_after_save', array( $this, 'sede_cf7_submision_store' ) );
			add_filter( 'wpcf7_validate_email*', array( $this, 'sede_cf7_email_validation' ), 10, 2 );
		}

		/**
		 * Register Spam filter tab on Contact form admin panel.
		 *
		 * @param      array $panels  The panels of Contact from 7.
		 *
		 * @return     array  $panels  The panels of Contact from 7.
		 */
		public function sede_cf7_tab_register( $panels ) {
			$panels['exclude-domains'] = array(
				'title'    => __( 'Exclude Domains', 'spam-email-domain-exclusion-cf7' ),
				'callback' => array( $this, 'sede_cf7_submision_view' ),
			);
			return $panels;
		}

		/**
		 * Display HTML to add filter validation rules.
		 *
		 * @param      array $post   The $_post array.
		 */
		public function sede_cf7_submision_view( $post ) {
			$description = __( 'Add Blacklisted contact from submission\'s email domain.', 'spam-email-domain-exclusion-cf7' );

			?>
			<h2><?php echo esc_html( __( 'Excluded Domains', 'spam-email-domain-exclusion-cf7' ) ); ?></h2>
			<fieldset>
				<legend><?php echo esc_html( $description ); ?></legend>
				<p class="description">
					<label for="spam-email-excluded-domains-cf7"><?php echo esc_html( 'Excluded Domains' ); ?><br />
						<input type="text" id="spam-email-excluded-domains-cf7" name="spam-email-excluded-domains-cf7" class="large-text" size="70" value="<?php echo esc_attr( get_post_meta( $post->id(), 'spam-email-excluded-domains-cf7', true ) ); ?>" data-config-field="<?php echo sprintf( 'messages.%s', esc_attr( 'spam-email-excluded-domains-cf7' ) ); ?>" />
						<i><?php echo esc_html( __( 'The domains should be comma-separated.', 'spam-email-domain-exclusion-cf7' ) ); ?></i>
					</label>
				</p>
			</fieldset>
			<?php
		}

		/**
		 * Stores a spam filter submision.
		 *
		 * @param      Object $args   The arguments of CF7.
		 */
		public function sede_cf7_submision_store( $args ) {

			if ( current_user_can( 'wpcf7_submit', $args->id() ) ) {
				check_admin_referer( 'wpcf7-save-contact-form_' . $args->id() );

				if ( isset( $_POST['spam-email-excluded-domains-cf7'] ) && ! empty( $_POST['spam-email-excluded-domains-cf7'] ) ) {
					$sede_cf7_blacklisted_domain = preg_replace( '/,+/', ',', sanitize_text_field( wp_unslash( $_POST['spam-email-excluded-domains-cf7'] ) ) );
					update_post_meta( $args->id(), 'spam-email-excluded-domains-cf7', ( trim( $sede_cf7_blacklisted_domain, ',' ) ) );
				}
			}
		}

		/**
		 * Validate email if does it belong to spam or not
		 *
		 * @param      Object $result  The result Object of CF7.
		 * @param      Object $tag     The tag Object of CF7.
		 *
		 * @return     Object|Array  Validated array.
		 */
		public function sede_cf7_email_validation( $result, $tag ) {

			$wpcf7 = \WPCF7_ContactForm::get_current();

			$form_id = $wpcf7->id;

			$sede_cf7_blacklisted_domain = apply_filters( 'sede_cf7_blacklisted_domain', get_post_meta( $form_id, 'spam-email-excluded-domains-cf7', true ), $form_id );

			$sede_cf7_blacklisted_domain = array_filter( explode( ',', $sede_cf7_blacklisted_domain ) );

			if ( ! empty( $sede_cf7_blacklisted_domain ) ) {

				// phpcs:ignore WordPress.Security -- Nonce verified upstream, value only used for comparison.
				if ( ! isset( $_POST[ $tag->name ] ) ) {
					return $result;
				}

				// phpcs:ignore WordPress.Security -- value only used for comparison.
				$current_value = sanitize_email( wp_unslash( $_POST[ $tag->name ] ) );

				$has_blacklisted = array_filter(
					$sede_cf7_blacklisted_domain,
					function( $val ) use ( $current_value ) {
						return strrpos( $current_value, trim( $val ) ) === strlen( $current_value ) - strlen( trim( $val ) );
					}
				);

				if ( array_filter( $has_blacklisted ) ) {
					$result['valid'] = false;
					$result->invalidate(
						$tag,
						apply_filters(
							'sede_cf7_blacklisted_domain_message',
							__( 'The domain of the email address is not allowed.', 'spam-email-domain-exclusion-cf7' ),
							$form_id,
							$has_blacklisted
						)
					);
				}
			}
			return $result;
		}
	}
}
