<?php
/**
 * Admin settings page and option registration (multiple modals).
 *
 * @package WP_Leave_Modal
 */

namespace WP_Leave_Modal;

defined( 'ABSPATH' ) || exit;

/**
 * Registers settings under Settings → Leave Modal.
 */
class Admin_Settings {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Option group name for Settings API.
	 */
	const OPTION_GROUP = 'wp_leave_modal_option_group';

	/**
	 * Settings page slug.
	 */
	const PAGE_SLUG = 'wp-leave-modal';

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
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Load admin JS on the plugin settings screen only.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}
		wp_enqueue_script(
			'wp-leave-modal-admin',
			WP_LEAVE_MODAL_PLUGIN_URL . 'assets/js/admin-modals.js',
			array(),
			WP_LEAVE_MODAL_VERSION,
			true
		);
		wp_localize_script(
			'wp-leave-modal-admin',
			'wpLeaveModalAdmin',
			array(
				'optionKey' => WP_LEAVE_MODAL_OPTION_KEY,
				'i18n'      => array(
					'needOne' => __( 'At least one modal is required.', 'wp-leave-modal' ),
				),
			)
		);
	}

	/**
	 * Add submenu under Settings.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Leave Modal', 'wp-leave-modal' ),
			__( 'Leave Modal', 'wp-leave-modal' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register option and fields.
	 */
	public function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			WP_LEAVE_MODAL_OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => self::default_settings(),
			)
		);

		add_settings_section(
			'wp_leave_modal_modals',
			__( 'Modals', 'wp-leave-modal' ),
			array( $this, 'section_modals_description' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'modals_repeater',
			'',
			array( $this, 'field_modals_repeater' ),
			self::PAGE_SLUG,
			'wp_leave_modal_modals'
		);
	}

	/**
	 * Section description.
	 */
	public function section_modals_description() {
		echo '<p>' . esc_html__( 'Define one or more modals. Use the slug in data attributes or shortcodes to connect a trigger to a modal.', 'wp-leave-modal' ) . '</p>';
	}

	/**
	 * Default fields for a single modal row.
	 *
	 * @return array<string, string>
	 */
	public static function default_modal_entry() {
		return array(
			'slug'                 => 'default',
			'modal_title'          => __( 'You are leaving this site', 'wp-leave-modal' ),
			'section_1_message'    => __( '<p>You are about to visit an external website. Please confirm to continue.</p>', 'wp-leave-modal' ),
			'section_2_label'      => __( 'You will be redirected to:', 'wp-leave-modal' ),
			'redirect_url'         => '',
			'button_cancel_text'   => __( 'Cancel', 'wp-leave-modal' ),
			'button_continue_text' => __( 'Continue', 'wp-leave-modal' ),
		);
	}

	/**
	 * Default option shape (array of modals).
	 *
	 * @return array{modals: array<int, array<string, string>>}
	 */
	public static function default_settings() {
		return array(
			'modals' => array( self::default_modal_entry() ),
		);
	}

	/**
	 * Upgrade legacy flat option keys to modals[] once.
	 */
	public static function maybe_upgrade_legacy() {
		$opt = get_option( WP_LEAVE_MODAL_OPTION_KEY );
		if ( ! is_array( $opt ) ) {
			return;
		}
		if ( isset( $opt['modals'] ) && is_array( $opt['modals'] ) && count( $opt['modals'] ) > 0 ) {
			return;
		}
		$legacy_keys = array( 'modal_title', 'section_1_message', 'section_2_label', 'redirect_url', 'button_cancel_text', 'button_continue_text' );
		$has_legacy  = false;
		foreach ( $legacy_keys as $k ) {
			if ( array_key_exists( $k, $opt ) ) {
				$has_legacy = true;
				break;
			}
		}
		if ( ! $has_legacy ) {
			return;
		}

		$base = self::default_modal_entry();
		$row  = array(
			'slug'                 => 'default',
			'modal_title'          => isset( $opt['modal_title'] ) ? $opt['modal_title'] : $base['modal_title'],
			'section_1_message'    => isset( $opt['section_1_message'] ) ? $opt['section_1_message'] : $base['section_1_message'],
			'section_2_label'      => isset( $opt['section_2_label'] ) ? $opt['section_2_label'] : $base['section_2_label'],
			'redirect_url'         => isset( $opt['redirect_url'] ) ? $opt['redirect_url'] : $base['redirect_url'],
			'button_cancel_text'   => isset( $opt['button_cancel_text'] ) ? $opt['button_cancel_text'] : $base['button_cancel_text'],
			'button_continue_text' => isset( $opt['button_continue_text'] ) ? $opt['button_continue_text'] : $base['button_continue_text'],
		);

		update_option(
			WP_LEAVE_MODAL_OPTION_KEY,
			array(
				'modals' => array( $row ),
			)
		);
	}

	/**
	 * Sanitize slug: lowercase letters, numbers, hyphens.
	 *
	 * @param string $slug Raw slug.
	 * @return string
	 */
	public static function sanitize_slug( $slug ) {
		$s = sanitize_title( (string) $slug );
		$s = strtolower( $s );
		$s = preg_replace( '/[^a-z0-9-]+/', '', $s );
		$s = trim( $s, '-' );
		if ( $s === '' ) {
			$s = 'modal';
		}
		return $s;
	}

	/**
	 * Sanitize all settings on save.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array{modals: array<int, array<string, string>>}
	 */
	public function sanitize_settings( $input ) {
		$out = array( 'modals' => array() );

		if ( ! isset( $input['modals'] ) || ! is_array( $input['modals'] ) ) {
			return self::default_settings();
		}

		$used_slugs = array();

		foreach ( $input['modals'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$slug = self::sanitize_slug( isset( $row['slug'] ) ? $row['slug'] : '' );
			$base = $slug;
			$n    = 2;
			while ( in_array( $slug, $used_slugs, true ) ) {
				$slug = $base . '-' . $n;
				++$n;
			}
			$used_slugs[] = $slug;

			$entry = array(
				'slug'                 => $slug,
				'modal_title'          => isset( $row['modal_title'] ) ? sanitize_text_field( wp_unslash( $row['modal_title'] ) ) : '',
				'section_1_message'    => isset( $row['section_1_message'] ) ? wp_kses_post( wp_unslash( $row['section_1_message'] ) ) : '',
				'section_2_label'      => isset( $row['section_2_label'] ) ? sanitize_text_field( wp_unslash( $row['section_2_label'] ) ) : '',
				'redirect_url'         => '',
				'button_cancel_text'   => isset( $row['button_cancel_text'] ) ? sanitize_text_field( wp_unslash( $row['button_cancel_text'] ) ) : '',
				'button_continue_text' => isset( $row['button_continue_text'] ) ? sanitize_text_field( wp_unslash( $row['button_continue_text'] ) ) : '',
			);

			$raw_url = isset( $row['redirect_url'] ) ? wp_unslash( $row['redirect_url'] ) : '';
			if ( $raw_url !== '' ) {
				$entry['redirect_url'] = esc_url_raw( $raw_url );
			}

			$defaults = self::default_modal_entry();
			foreach ( array( 'modal_title', 'section_1_message', 'section_2_label', 'button_cancel_text', 'button_continue_text' ) as $k ) {
				if ( $entry[ $k ] === '' ) {
					$entry[ $k ] = $defaults[ $k ];
				}
			}

			$out['modals'][] = $entry;
		}

		if ( count( $out['modals'] ) === 0 ) {
			$out['modals'][] = self::default_modal_entry();
		}

		return $out;
	}

	/**
	 * Render settings page wrapper.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post" id="wp-leave-modal-settings-form">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
			<hr />
			<h2><?php esc_html_e( 'Triggers', 'wp-leave-modal' ); ?></h2>
			<ul class="description">
				<li><?php esc_html_e( 'HTML: add data-wp-leave-modal="your-slug" to any button or link (value must match a modal slug above).', 'wp-leave-modal' ); ?></li>
				<li><?php esc_html_e( 'Shortcode: [leave_modal_button modal="your-slug" label="Button text"] or [leave_modal_trigger modal="your-slug"].', 'wp-leave-modal' ); ?></li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Repeater UI for modals.
	 */
	public function field_modals_repeater() {
		$settings = self::get_settings();
		$modals   = isset( $settings['modals'] ) && is_array( $settings['modals'] ) ? $settings['modals'] : self::default_settings()['modals'];
		$name     = WP_LEAVE_MODAL_OPTION_KEY . '[modals]';
		?>
		<div id="wp-leave-modal-rows" class="wp-leave-modal-admin-rows">
			<?php
			foreach ( $modals as $index => $modal ) {
				$this->render_modal_row( (int) $index, $modal, $name );
			}
			?>
		</div>
		<p>
			<button type="button" class="button" id="wp-leave-modal-add-row"><?php esc_html_e( 'Add modal', 'wp-leave-modal' ); ?></button>
		</p>
		<p class="description"><?php esc_html_e( 'Section 1 accepts basic HTML; content is filtered on save.', 'wp-leave-modal' ); ?></p>
		<?php
	}

	/**
	 * Render one modal row in the repeater.
	 *
	 * @param int    $index Row index.
	 * @param array  $modal Modal data.
	 * @param string $name  Base option name.
	 */
	private function render_modal_row( $index, array $modal, $name ) {
		$prefix = $name . '[' . $index . ']';

		$slug_val = isset( $modal['slug'] ) ? $modal['slug'] : 'default';
		?>
		<div class="postbox wp-leave-modal-row" style="padding:12px;margin-bottom:16px;">
			<h3 class="hndle" style="margin-top:0;">
				<?php esc_html_e( 'Modal', 'wp-leave-modal' ); ?>
				<button type="button" class="button-link wp-leave-modal-remove-row" style="float:right;"><?php esc_html_e( 'Remove', 'wp-leave-modal' ); ?></button>
			</h3>
			<p>
				<label><strong><?php esc_html_e( 'Slug', 'wp-leave-modal' ); ?></strong>
					<input type="text" class="regular-text" name="<?php echo esc_attr( $prefix ); ?>[slug]" value="<?php echo esc_attr( $slug_val ); ?>" required pattern="[a-z0-9-]+" title="<?php esc_attr_e( 'Lowercase letters, numbers, hyphens only.', 'wp-leave-modal' ); ?>" />
				</label>
			</p>
			<p>
				<label><strong><?php esc_html_e( 'Modal title', 'wp-leave-modal' ); ?></strong><br />
					<input type="text" class="large-text" name="<?php echo esc_attr( $prefix ); ?>[modal_title]" value="<?php echo esc_attr( isset( $modal['modal_title'] ) ? $modal['modal_title'] : '' ); ?>" />
				</label>
			</p>
			<p>
				<strong><?php esc_html_e( 'Section 1 — message', 'wp-leave-modal' ); ?></strong><br />
				<textarea class="large-text" rows="8" name="<?php echo esc_attr( $prefix ); ?>[section_1_message]"><?php echo esc_textarea( isset( $modal['section_1_message'] ) ? $modal['section_1_message'] : '' ); ?></textarea>
			</p>
			<p>
				<label><strong><?php esc_html_e( 'Section 2 — label before URL', 'wp-leave-modal' ); ?></strong><br />
					<input type="text" class="large-text" name="<?php echo esc_attr( $prefix ); ?>[section_2_label]" value="<?php echo esc_attr( isset( $modal['section_2_label'] ) ? $modal['section_2_label'] : '' ); ?>" />
				</label>
			</p>
			<p>
				<label><strong><?php esc_html_e( 'Redirect URL', 'wp-leave-modal' ); ?></strong><br />
					<input type="url" class="large-text" name="<?php echo esc_attr( $prefix ); ?>[redirect_url]" value="<?php echo esc_attr( isset( $modal['redirect_url'] ) ? $modal['redirect_url'] : '' ); ?>" placeholder="https://example.com" />
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'Cancel button label', 'wp-leave-modal' ); ?>
					<input type="text" class="regular-text" name="<?php echo esc_attr( $prefix ); ?>[button_cancel_text]" value="<?php echo esc_attr( isset( $modal['button_cancel_text'] ) ? $modal['button_cancel_text'] : '' ); ?>" />
				</label>
				&nbsp;
				<label><?php esc_html_e( 'Continue button label', 'wp-leave-modal' ); ?>
					<input type="text" class="regular-text" name="<?php echo esc_attr( $prefix ); ?>[button_continue_text]" value="<?php echo esc_attr( isset( $modal['button_continue_text'] ) ? $modal['button_continue_text'] : '' ); ?>" />
				</label>
			</p>
		</div>
		<?php
	}

	/**
	 * Get merged settings from the database.
	 *
	 * @return array{modals: array<int, array<string, string>>}
	 */
	public static function get_settings() {
		self::maybe_upgrade_legacy();

		$stored = get_option( WP_LEAVE_MODAL_OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$defaults = self::default_settings();
		if ( ! isset( $stored['modals'] ) || ! is_array( $stored['modals'] ) || count( $stored['modals'] ) === 0 ) {
			return $defaults;
		}

		$merged_modals = array();
		foreach ( $stored['modals'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$merged_modals[] = array_merge( self::default_modal_entry(), $row );
		}

		if ( count( $merged_modals ) === 0 ) {
			return $defaults;
		}

		return array( 'modals' => $merged_modals );
	}

	/**
	 * Build keyed map slug => modal for front-end JSON.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function get_modals_map_for_js() {
		$settings = self::get_settings();
		$map        = array();

		if ( empty( $settings['modals'] ) || ! is_array( $settings['modals'] ) ) {
			return $map;
		}

		foreach ( $settings['modals'] as $modal ) {
			if ( empty( $modal['slug'] ) ) {
				continue;
			}
			$slug = self::sanitize_slug( $modal['slug'] );
			$map[ $slug ] = array(
				'title'          => isset( $modal['modal_title'] ) ? $modal['modal_title'] : '',
				'section1Html'   => isset( $modal['section_1_message'] ) ? wp_kses_post( $modal['section_1_message'] ) : '',
				'section2Label'  => isset( $modal['section_2_label'] ) ? $modal['section_2_label'] : '',
				'redirectUrl'    => isset( $modal['redirect_url'] ) ? $modal['redirect_url'] : '',
				'buttonCancel'   => isset( $modal['button_cancel_text'] ) ? $modal['button_cancel_text'] : '',
				'buttonContinue' => isset( $modal['button_continue_text'] ) ? $modal['button_continue_text'] : '',
			);
		}

		return $map;
	}
}
