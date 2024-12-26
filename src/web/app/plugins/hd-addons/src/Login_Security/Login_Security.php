<?php

namespace Addons\Login_Security;

use Addons\Base\Singleton;

\defined( 'ABSPATH' ) || die;

final class Login_Security {
	use Singleton;

	// --------------------------------------------------

	/**
	 * @var array|false|mixed
	 */
	public mixed $login_security_options = [];

	private function init(): void {
		$this->login_security_options = get_option( 'login_security__options' );

		( Custom_Login::get_instance() );

		$this->_illegal_users();
		$this->_login_attempts();
	}

	// ------------------------------------------------------

	/**
	 * Add username hooks.
	 *
	 * @return void
	 */
	private function _illegal_users(): void {
		if ( $this->login_security_options['illegal_users'] ?? 0 ) {
			$common_user = new Illegal_Users();
			add_action( 'illegal_user_logins', [ &$common_user, 'get_illegal_usernames' ] );
		}
	}

	// ------------------------------------------------------

	/**
	 * Add login service hooks.
	 *
	 * @return void
	 */
	private function _login_attempts(): void {
		$limit_login_attempts = $this->login_security_options['limit_login_attempts'] ?? 0;
		$security_login       = new Login_Attempts();

		// Bail if optimization is disabled.
		if ( 0 === (int) $limit_login_attempts ) {
			$security_login->reset_login_attempts();

			return;
		}

		// Check the login attempts for an ip and block the access to the login page.
		add_action( 'login_head', [ &$security_login, 'maybe_block_login_access' ], PHP_INT_MAX );

		// Add login attempts for ip.
		add_filter( 'login_errors', [ &$security_login, 'log_login_attempt' ] );

		// Reset login attempts for an ip on successful login.
		add_filter( 'wp_login', [ &$security_login, 'reset_login_attempts' ] );
	}
}
