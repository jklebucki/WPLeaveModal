<?php
/**
 * Front-end assets and modal markup.
 *
 * @package WP_Leave_Modal
 */

namespace WP_Leave_Modal;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues CSS/JS and prints the dialog markup in the footer.
 */
class Assets {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Whether styles/scripts were registered for this request.
	 *
	 * @var bool
	 */
	private $enqueued = false;

	/**
	 * Script handle for wp_localize_script.
	 */
	const SCRIPT_HANDLE = 'wp-leave-modal';

	/**
	 * Get singleton instance.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook into WordPress.
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_from_content' ), 20 );
		// Print the modal shell before core footer scripts so modal.js can find it on first run.
		add_action( 'wp_footer', array( $this, 'print_modal' ), 5 );
	}

	/**
	 * Early enqueue when content suggests a modal is needed.
	 *
	 * Page builders (Elementor, Divi, etc.) often store layout outside raw post_content, so shortcodes
	 * are not visible during wp_enqueue_scripts. When at least one modal is configured, load assets on
	 * normal frontend requests unless disabled via filter.
	 */
	public function maybe_enqueue_from_content() {
		if ( apply_filters( 'wp_leave_modal_enqueue', false ) ) {
			$this->register_assets();
			return;
		}
		if ( Shortcode::content_should_enqueue_assets() ) {
			$this->register_assets();
			return;
		}

		/**
		 * When true (default) and at least one modal exists in settings, enqueue on public frontend views.
		 * Set to false to only load when a trigger is detected in post_content (saves bytes on sites that
		 * use builders — then rely on shortcode callbacks calling ensure_enqueued(), or use wp_leave_modal_enqueue).
		 *
		 * @since 1.1.2
		 */
		if ( apply_filters( 'wp_leave_modal_enqueue_if_configured', true ) && Admin_Settings::has_any_modal() && $this->is_normal_frontend_view() ) {
			$this->register_assets();
		}
	}

	/**
	 * Skip admin, feeds, REST, and (by default) AJAX so assets load where visitors actually see the page.
	 *
	 * @return bool
	 */
	private function is_normal_frontend_view() {
		if ( is_admin() ) {
			return false;
		}
		if ( is_feed() ) {
			return false;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}
		if ( wp_doing_ajax() && ! apply_filters( 'wp_leave_modal_enqueue_on_ajax', false ) ) {
			return false;
		}
		if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
			return false;
		}
		return true;
	}

	/**
	 * Called from shortcode when it renders (covers widgets and late content).
	 */
	public function ensure_enqueued() {
		$this->register_assets();
	}

	/**
	 * Register and enqueue stylesheet and script, localize config once.
	 */
	public function register_assets() {
		if ( $this->enqueued ) {
			return;
		}
		$this->enqueued = true;

		$version = WP_LEAVE_MODAL_VERSION;
		$base    = WP_LEAVE_MODAL_PLUGIN_URL . 'assets/';

		wp_register_style(
			'wp-leave-modal',
			$base . 'css/modal.css',
			array(),
			$version
		);
		wp_enqueue_style( 'wp-leave-modal' );

		wp_register_script(
			self::SCRIPT_HANDLE,
			$base . 'js/modal.js',
			array(),
			$version,
			true
		);

		$modals = Admin_Settings::get_modals_map_for_js();

		wp_localize_script(
			self::SCRIPT_HANDLE,
			'wpLeaveModal',
			array(
				'modals'    => $modals,
				'noUrlHint' => __( 'No valid redirect URL is configured for this modal.', 'wp-leave-modal' ),
			)
		);

		wp_enqueue_script( self::SCRIPT_HANDLE );
	}

	/**
	 * Print a single empty dialog shell; content is filled from localized config in JS.
	 */
	public function print_modal() {
		if ( ! $this->enqueued ) {
			return;
		}

		$title_id = 'wp-leave-modal-title';
		?>
		<div
			id="wp-leave-modal"
			class="wp-leave-modal"
			hidden
			role="dialog"
			aria-modal="true"
			aria-labelledby="<?php echo esc_attr( $title_id ); ?>"
		>
			<div class="wp-leave-modal__backdrop" data-wp-leave-modal-close tabindex="-1" aria-hidden="true"></div>
			<div class="wp-leave-modal__panel" role="document">
				<header class="wp-leave-modal__header">
					<h2 id="<?php echo esc_attr( $title_id ); ?>" class="wp-leave-modal__title"></h2>
					<button type="button" class="wp-leave-modal__close" data-wp-leave-modal-close aria-label="<?php esc_attr_e( 'Close dialog', 'wp-leave-modal' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</header>
				<div class="wp-leave-modal__body">
					<section class="wp-leave-modal__section wp-leave-modal__section--message" aria-label="<?php esc_attr_e( 'Message', 'wp-leave-modal' ); ?>">
						<div class="wp-leave-modal__section-content wp-leave-modal__section-content--html" id="wp-leave-modal-section-1"></div>
					</section>
					<section class="wp-leave-modal__section wp-leave-modal__section--destination" aria-label="<?php esc_attr_e( 'Destination', 'wp-leave-modal' ); ?>">
						<p class="wp-leave-modal__destination-label" id="wp-leave-modal-destination-label"></p>
						<p class="wp-leave-modal__destination-url">
							<a href="#" class="wp-leave-modal__url-link" tabindex="-1" id="wp-leave-modal-url-link"></a>
						</p>
					</section>
				</div>
				<footer class="wp-leave-modal__footer">
					<button type="button" class="wp-leave-modal__btn wp-leave-modal__btn--secondary button" data-wp-leave-modal-cancel></button>
					<button type="button" class="wp-leave-modal__btn wp-leave-modal__btn--primary button button-primary" data-wp-leave-modal-continue></button>
				</footer>
			</div>
		</div>
		<?php
	}
}
