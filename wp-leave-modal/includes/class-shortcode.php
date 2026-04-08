<?php
/**
 * Shortcodes that render modal triggers (button or link).
 *
 * @package WP_Leave_Modal
 */

namespace WP_Leave_Modal;

defined( 'ABSPATH' ) || exit;

/**
 * Registers shortcodes for modal triggers.
 */
class Shortcode {

	const TAG_BUTTON = 'leave_modal_button';

	const TAG_TRIGGER = 'leave_modal_trigger';

	const TAG_LINK = 'leave_modal_link';

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

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
		add_shortcode( self::TAG_BUTTON, array( $this, 'render_trigger' ) );
		add_shortcode( self::TAG_TRIGGER, array( $this, 'render_trigger' ) );
		add_shortcode( self::TAG_LINK, array( $this, 'render_link' ) );
	}

	/**
	 * Render trigger button. Requires modal slug. Ensures assets load when shortcode runs late.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render_trigger( $atts, $content = null, $tag = '' ) {
		$tag = $tag !== '' ? $tag : self::TAG_BUTTON;

		$atts = shortcode_atts(
			array(
				'modal' => '',
				'id'    => '',
				'label' => __( 'Leave site', 'wp-leave-modal' ),
				'class' => '',
			),
			$atts,
			$tag
		);

		$raw = $atts['modal'] !== '' ? $atts['modal'] : $atts['id'];
		$raw = trim( (string) $raw );
		if ( $raw === '' ) {
			return '';
		}

		$slug = Admin_Settings::sanitize_slug( $raw );

		Assets::instance()->ensure_enqueued();

		$label = sanitize_text_field( $atts['label'] );

		$extra_classes = array();
		foreach ( preg_split( '/\s+/', (string) $atts['class'], -1, PREG_SPLIT_NO_EMPTY ) as $part ) {
			$c = sanitize_html_class( $part );
			if ( $c !== '' ) {
				$extra_classes[] = $c;
			}
		}

		$classes = array_merge( array( 'wp-leave-modal__trigger', 'button' ), $extra_classes );

		return sprintf(
			'<button type="button" class="%s" data-wp-leave-modal="%s" aria-haspopup="dialog">%s</button>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $slug ),
			esc_html( $label )
		);
	}

	/**
	 * Render trigger as an anchor. Uses href/url for the link target (and as redirect fallback in JS when modal has no Redirect URL).
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render_link( $atts, $content = null, $tag = '' ) {
		$atts = shortcode_atts(
			array(
				'modal' => '',
				'id'    => '',
				'href'  => '',
				'url'   => '',
				'label' => __( 'Leave site', 'wp-leave-modal' ),
				'class' => '',
			),
			$atts,
			self::TAG_LINK
		);

		$raw = $atts['modal'] !== '' ? $atts['modal'] : $atts['id'];
		$raw = trim( (string) $raw );
		if ( $raw === '' ) {
			return '';
		}

		$slug = Admin_Settings::sanitize_slug( $raw );

		$href_raw = $atts['href'] !== '' ? $atts['href'] : $atts['url'];
		$href_raw = trim( (string) $href_raw );
		$href     = $href_raw !== '' ? esc_url( $href_raw ) : '#';

		Assets::instance()->ensure_enqueued();

		$label = sanitize_text_field( $atts['label'] );

		$extra_classes = array();
		foreach ( preg_split( '/\s+/', (string) $atts['class'], -1, PREG_SPLIT_NO_EMPTY ) as $part ) {
			$c = sanitize_html_class( $part );
			if ( $c !== '' ) {
				$extra_classes[] = $c;
			}
		}

		$classes = array_merge( array( 'wp-leave-modal__trigger' ), $extra_classes );

		return sprintf(
			'<a href="%s" class="%s" data-wp-leave-modal="%s" aria-haspopup="dialog">%s</a>',
			esc_attr( $href ),
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $slug ),
			esc_html( $label )
		);
	}

	/**
	 * Whether the singular post content suggests loading modal assets early.
	 *
	 * @return bool
	 */
	public static function content_should_enqueue_assets() {
		if ( ! is_singular() ) {
			return false;
		}
		$post = get_post();
		if ( ! $post || ! isset( $post->post_content ) ) {
			return false;
		}
		$content = $post->post_content;

		if ( strpos( $content, 'data-wp-leave-modal' ) !== false ) {
			return true;
		}

		foreach ( array( self::TAG_BUTTON, self::TAG_TRIGGER, self::TAG_LINK ) as $tag ) {
			if ( has_shortcode( $content, $tag ) ) {
				return true;
			}
			if ( strpos( $content, '[' . $tag ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
