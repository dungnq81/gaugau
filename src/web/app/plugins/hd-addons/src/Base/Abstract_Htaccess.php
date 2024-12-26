<?php

namespace Addons\Base;

\defined( 'ABSPATH' ) || die;

/**
 * Htaccess Class
 *
 * @author SiteGround Security
 * Modified by NTH
 */
abstract class Abstract_Htaccess {

	/**
	 * WordPress filesystem.
	 *
	 * @var ?\WP_Filesystem_Base
	 */
	protected ?\WP_Filesystem_Base $wp_filesystem = null;

	/**
	 * Path to htaccess file.
	 *
	 * @var ?string
	 */
	public ?string $path = null;

	/**
	 * Rules for enabling and disabling htaccess.
	 *
	 * @var array<string, string> Array of regex patterns.
	 */
	protected array $rules = [];

	/**
	 * Template file name.
	 *
	 * @var ?string
	 */
	protected ?string $template = null;

	// --------------------------------------------------

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->wp_filesystem = $this->_wp_filesystem();
	}

	// --------------------------------------------------

	/**
	 * Initialize WordPress filesystem.
	 *
	 * @return ?\WP_Filesystem_Base
	 */
	private function _wp_filesystem(): ?\WP_Filesystem_Base {
		global $wp_filesystem;

		// Initialize the WP filesystem, no more using the 'file-put-contents' function.
		// Front-end only in the back-end, it's already included
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem ?: null;
	}

	// --------------------------------------------------

	/**
	 * Get the filepath to the htaccess.
	 *
	 * @return string
	 */
	public function get_filepath(): string {
		return $this->wp_filesystem->abspath() . '.htaccess';
	}

	// --------------------------------------------------

	/**
	 * Set the htaccess path.
	 *
	 * @return $this
	 */
	public function set_filepath(): self {
		if ( null === $this->path ) {
			$filepath = $this->get_filepath();

			// Create the htaccess if it doesn't exist.
			if ( ! $this->wp_filesystem->exists( $filepath ) ) {
				$this->wp_filesystem->touch( $filepath );
			}

			// Ensure the file is writable.
			if ( $this->wp_filesystem->is_writable( $filepath ) ) {
				$this->path = $filepath;
			}
		}

		return $this;
	}

	// --------------------------------------------------

	/**
	 * Disable the rule and remove it from the htaccess.
	 *
	 * @return bool
	 */
	public function disable(): bool {
		if ( $this->path && $this->is_enabled() ) {
			$content = $this->wp_filesystem->get_contents( $this->path );

			if ( $content ) {
				$new_content = preg_replace( $this->rules['disabled'], '', $content );

				return $this->lock_and_write( $new_content );
			}
		}

		return false;
	}

	// --------------------------------------------------

	/**
	 * Add rule to htaccess and enable it.
	 *
	 * @return bool
	 */
	public function enable(): bool {
		if ( $this->path && ! $this->is_enabled() ) {
			$content = $this->wp_filesystem->get_contents( $this->path );

			if ( $content ) {
				$content  = preg_replace( $this->rules['disable_all'], '', $content );
				$new_rule = $this->wp_filesystem->get_contents( ADDONS_PATH . 'tpl/' . $this->template );

				if ( $new_rule ) {
					$content .= PHP_EOL . $new_rule;
					$content = $this->do_replacement( $content );

					return $this->lock_and_write( $content );
				}
			}
		}

		return false;
	}

	// --------------------------------------------------

	/**
	 * Lock a file and write something in it.
	 *
	 * @param string $content Content to write.
	 *
	 * @return bool
	 */
	protected function lock_and_write( string $content ): bool {
		return $this->_do_lock_write( $this->path, $content );
	}

	// --------------------------------------------------

	/**
	 * Lock a file and write content into it.
	 *
	 * @param string $path Filepath.
	 * @param string $content Content to write.
	 *
	 * @return bool
	 */
	private function _do_lock_write( string $path, string $content = '' ): bool {
		$fp = fopen( $path, 'wb+' );

		if ( $fp ) {
			if ( flock( $fp, LOCK_EX ) ) {
				fwrite( $fp, $content );
				flock( $fp, LOCK_UN );
				fclose( $fp );

				return true;
			}

			fclose( $fp );
		}

		return false;
	}

	// --------------------------------------------------

	/**
	 * Check if the rule is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		$content = $this->wp_filesystem->get_contents( $this->path );
		return $content && (bool) preg_match( $this->rules['enabled'], $content );
	}

	// --------------------------------------------------

	/**
	 * Perform replacements in htaccess content.
	 *
	 * @param string $content The htaccess content.
	 *
	 * @return string
	 */
	public function do_replacement( string $content ): string {
		return $content;
	}

	// --------------------------------------------------

	/**
	 * Toggle specific rules in htaccess.
	 *
	 * @param bool|int $rule Whether to enable or disable the rules.
	 */
	public function toggle_rules( bool|int $rule = 1 ): void {
		$this->set_filepath();
		$rule ? $this->enable() : $this->disable();
	}
}
