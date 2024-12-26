<?php

namespace Addons\Smtp;

use Addons\Base\Singleton;
use PHPMailer\PHPMailer\Exception;

\defined( 'ABSPATH' ) || die;

final class SMTP {
	use Singleton;

	// ------------------------------------------------------

	private function init(): void {
		add_action( 'admin_notices', [ $this, 'options_admin_notice' ] );

		if ( $this->smtpConfigured() && check_smtp_plugin_active() && $this->_check_smtp_menu() ) {
			add_filter( 'pre_wp_mail', [ $this, 'pre_wp_mail' ], 99, 2 );
		}
	}

	// ------------------------------------------------------

	/**
	 * @return bool
	 */
	private function _check_smtp_menu(): bool {
		$menu_options_page = apply_filters( 'addon_menu_options_page_filter', [] );

		return isset( $menu_options_page['smtp'] );
	}

	// ------------------------------------------------------

	/**
	 * @param $null
	 * @param $atts
	 *
	 * @return void
	 * @throws Exception
	 */
	public function pre_wp_mail( $null, $atts ): void {
		$this->_smtp_mailer_pre_wp_mail( $null, $atts, 'smtp__options' );
	}

	// ------------------------------------------------------

	/**
	 * SMTP Mailer plugin - https://vi.wordpress.org/plugins/smtp-mailer/
	 *
	 * @param $null
	 * @param $atts
	 * @param string|null $option_name
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _smtp_mailer_pre_wp_mail( $null, $atts, ?string $option_name = null ): bool {

		//------------------------------------------------
		$option_name = $option_name ?: 'smtp__options';
		$options     = get_option( $option_name );
		//------------------------------------------------

		if ( isset( $atts['to'] ) ) {
			$to = $atts['to'];
		}

		if ( ! is_array( $to ) ) {
			$to = explode( ',', $to );
		}

		if ( isset( $atts['subject'] ) ) {
			$subject = $atts['subject'];
		}

		if ( isset( $atts['message'] ) ) {
			$message = $atts['message'];
		}

		if ( isset( $atts['headers'] ) ) {
			$headers = $atts['headers'];
		}

		if ( isset( $atts['attachments'] ) ) {
			$attachments = $atts['attachments'];
			if ( ! is_array( $attachments ) ) {
				$attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
			}
		}

		global $phpmailer;

		// (Re)create it, if it's gone missing.
		if ( ! ( $phpmailer instanceof \PHPMailer\PHPMailer\PHPMailer ) ) {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			$phpmailer = new \PHPMailer\PHPMailer\PHPMailer( true );

			$phpmailer::$validator = static function ( $email ) {
				return (bool) is_email( $email );
			};
		}

		// Headers.
		$cc       = [];
		$bcc      = [];
		$reply_to = [];

		if ( empty( $headers ) ) {
			$headers = [];
		} else {
			if ( ! is_array( $headers ) ) {
				/*
				 * Explode the headers out, so this function can take
				 * both string headers and an array of headers.
				 */
				$tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
			} else {
				$tempheaders = $headers;
			}
			$headers = [];

			// If it's actually got contents.
			if ( ! empty( $tempheaders ) ) {
				// Iterate through the raw headers.
				foreach ( (array) $tempheaders as $header ) {
					if ( ! str_contains( $header, ':' ) ) {
						if ( false !== stripos( $header, 'boundary=' ) ) {
							$parts    = preg_split( '/boundary=/i', trim( $header ) );
							$boundary = trim( str_replace( [ "'", '"' ], '', $parts[1] ) );
						}
						continue;
					}
					// Explode them out.
					[ $name, $content ] = explode( ':', trim( $header ), 2 );

					// Cleanup crew.
					$name    = trim( $name );
					$content = trim( $content );

					switch ( strtolower( $name ) ) {
						// Mainly for legacy -- process a "From:" header if it's there.
						case 'from':
							$bracket_pos = strpos( $content, '<' );
							if ( false !== $bracket_pos ) {
								// Text before the bracketed email is the "From" name.
								if ( $bracket_pos > 0 ) {
									$from_name = substr( $content, 0, $bracket_pos );
									$from_name = str_replace( '"', '', $from_name );
									$from_name = trim( $from_name );
								}

								$from_email = substr( $content, $bracket_pos + 1 );
								$from_email = str_replace( '>', '', $from_email );
								$from_email = trim( $from_email );

								// Avoid setting an empty $from_email.
							} elseif ( '' !== trim( $content ) ) {
								$from_email = trim( $content );
							}
							break;
						case 'content-type':
							if ( str_contains( $content, ';' ) ) {
								[ $type, $charset_content ] = explode( ';', $content );
								$content_type = trim( $type );
								if ( false !== stripos( $charset_content, 'charset=' ) ) {
									$charset = trim( str_replace( [ 'charset=', '"' ], '', $charset_content ) );
								} elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
									$boundary = trim( str_replace( [ 'BOUNDARY=', 'boundary=', '"' ], '', $charset_content ) );
									$charset  = '';
								}

								// Avoid setting an empty $content_type.
							} elseif ( '' !== trim( $content ) ) {
								$content_type = trim( $content );
							}
							break;
						case 'cc':
							$cc = array_merge( (array) $cc, explode( ',', $content ) );
							break;
						case 'bcc':
							$bcc = array_merge( (array) $bcc, explode( ',', $content ) );
							break;
						case 'reply-to':
							$reply_to = array_merge( (array) $reply_to, explode( ',', $content ) );
							break;
						default:
							// Add it to our grand headers array.
							$headers[ trim( $name ) ] = trim( $content );
							break;
					}
				}
			}
		}

		// Empty out the values that may be set.
		$phpmailer->clearAllRecipients();
		$phpmailer->clearAttachments();
		$phpmailer->clearCustomHeaders();
		$phpmailer->clearReplyTos();
		$phpmailer->Body    = '';
		$phpmailer->AltBody = '';

		// Set "From" name and email.

		// If we don't have a name from the input headers.
		if ( ! isset( $from_name ) ) {
			$from_name = 'WordPress';

			//------------------------------------------------
			if ( $options['smtp_from_name'] ) {
				$from_name = $options['smtp_from_name'];
			}
			//------------------------------------------------
		}

		/*
		 * If we don't have an email from the input headers, default to wordpress@$sitename
		 * Some hosts will block outgoing mail from this address if it doesn't exist,
		 * but there's no easy alternative. Defaulting to admin_email might appear to be
		 * another option, but some hosts may refuse to relay mail from an unknown domain.
		 * See https://core.trac.wordpress.org/ticket/5007.
		 */
		if ( ! isset( $from_email ) ) {
			// Get the site domain and get rid of www.
			$sitename   = wp_parse_url( network_home_url(), PHP_URL_HOST );
			$from_email = 'wordpress@';

			if ( null !== $sitename ) {
				if ( str_starts_with( $sitename, 'www.' ) ) {
					$sitename = substr( $sitename, 4 );
				}

				$from_email .= $sitename;
			}

			//------------------------------------------------
			if ( $options['smtp_from_email'] ) {
				$from_email = $options['smtp_from_email'];
			}
			//------------------------------------------------
		}

		/**
		 * Filters the email address to send from.
		 *
		 * @param string $from_email Email address to send from.
		 *
		 * @since 2.2.0
		 *
		 */
		$from_email = apply_filters( 'wp_mail_from', $from_email );

		/**
		 * Filters the name to associate with the "from" email address.
		 *
		 * @param string $from_name Name associated with the "from" email address.
		 *
		 * @since 2.3.0
		 */
		$from_name = apply_filters( 'wp_mail_from_name', $from_name );

		//-----------------------------------------------
		if ( ! empty( $options['smtp_force_from_address'] ) ) {
			$from_name  = $options['smtp_from_name'];
			$from_email = $options['smtp_from_email'];
		}
		//------------------------------------------------

		try {
			$phpmailer->setFrom( $from_email, $from_name, false );
		} catch ( Exception $e ) {
			$mail_error_data                             = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
			$mail_error_data['phpmailer_exception_code'] = $e->getCode();

			/** This filter is documented in wp-includes/pluggable.php */
			do_action( 'wp_mail_failed', new \WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_error_data ) );

			return false;
		}

		//------------------------------------------------
		$smtp_mailer_reply_to = '';
		$smtp_mailer_reply_to = apply_filters( 'smtp_mailer_reply_to', $smtp_mailer_reply_to );
		if ( ! empty( $smtp_mailer_reply_to ) ) {
			$temp_reply_to_addresses = explode( ",", $smtp_mailer_reply_to );
			$reply_to                = [];
			foreach ( $temp_reply_to_addresses as $temp_reply_to_address ) {
				$reply_to[] = trim( $temp_reply_to_address );
			}
		}
		//------------------------------------------------

		// Set mail's subject and body.
		$phpmailer->Subject = $subject;
		$phpmailer->Body    = $message;

		// Set destination addresses, using appropriate methods for handling addresses.
		$address_headers = compact( 'to', 'cc', 'bcc', 'reply_to' );

		foreach ( $address_headers as $address_header => $addresses ) {
			if ( empty( $addresses ) ) {
				continue;
			}

			foreach ( (array) $addresses as $address ) {
				try {
					// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>".
					$recipient_name = '';

					if ( preg_match( '/(.*)<(.+)>/', $address, $matches ) ) {
						if ( count( $matches ) === 3 ) {
							$recipient_name = $matches[1];
							$address        = $matches[2];
						}
					}

					switch ( $address_header ) {
						case 'to':
							$phpmailer->addAddress( $address, $recipient_name );
							break;
						case 'cc':
							$phpmailer->addCc( $address, $recipient_name );
							break;
						case 'bcc':
							$phpmailer->addBcc( $address, $recipient_name );
							break;
						case 'reply_to':
							$phpmailer->addReplyTo( $address, $recipient_name );
							break;
					}
				} catch ( Exception $e ) {
					continue;
				}
			}
		}

		// Set to use PHP's mail().
		//$phpmailer->isMail();

		//------------------------------------------------
		$phpmailer->isSMTP(); // Tell PHPMailer to use SMTP
		$phpmailer->Host        = $options['smtp_host']; // Set the hostname of the mail server
		$phpmailer->Port        = $options['smtp_port']; // SMTP port
		$phpmailer->SMTPAutoTLS = false; // Whether to enable TLS encryption automatically if a server supports it

		// Whether to use SMTP authentication
		if ( isset( $options['smtp_auth'] ) && (string) $options['smtp_auth'] === "true" ) {
			$phpmailer->SMTPAuth = true;
			$phpmailer->Username = $options['smtp_username']; // SMTP username
			$phpmailer->Password = base64_decode( $options['smtp_password'] ); // SMTP password
		}

		// Whether to use encryption
		$smtp_encryption = $options['smtp_encryption'];
		if ( (string) $smtp_encryption === "none" ) {
			$smtp_encryption = '';
		}
		$phpmailer->SMTPSecure = $smtp_encryption;

		//enable debug when sending a test mail
		if ( isset( $_POST['smtp_mailer_send_test_email'] ) ) {
			$phpmailer->SMTPDebug   = 4;
			$phpmailer->Debugoutput = 'html'; // Ask for HTML-friendly debug output
		}

		//disable ssl certificate verification if checked
		if ( ! empty( $options['smtp_disable_ssl_verification'] ) ) {
			$phpmailer->SMTPOptions = [
				'ssl' => [
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				],
			];
		}
		//------------------------------------------------

		// Set Content-Type and charset.

		// If we don't have a Content-Type from the input headers.
		if ( ! isset( $content_type ) ) {
			$content_type = 'text/plain';
		}

		/**
		 * Filters the wp_mail() content type.
		 *
		 * @param string $content_type Default wp_mail() content type.
		 *
		 * @since 2.3.0
		 *
		 */
		$content_type = apply_filters( 'wp_mail_content_type', $content_type );

		$phpmailer->ContentType = $content_type;

		// Set whether it's plaintext, depending on $content_type.
		if ( 'text/html' === $content_type ) {
			$phpmailer->isHTML( true );
		}

		// If we don't have a charset from the input headers.
		if ( ! isset( $charset ) ) {
			$charset = get_bloginfo( 'charset' );
		}

		/**
		 * Filters the default wp_mail() charset.
		 *
		 * @param string $charset Default email charset.
		 *
		 * @since 2.3.0
		 *
		 */
		$phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );

		// Set custom headers.
		if ( ! empty( $headers ) ) {
			foreach ( (array) $headers as $name => $content ) {
				// Only add custom headers not added automatically by PHPMailer.
				if ( ! in_array( $name, [ 'MIME-Version', 'X-Mailer' ], true ) ) {
					try {
						$phpmailer->addCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
					} catch ( Exception $e ) {
						continue;
					}
				}
			}

			if ( false !== stripos( $content_type, 'multipart' ) && ! empty( $boundary ) ) {
				$phpmailer->addCustomHeader( sprintf( 'Content-Type: %s; boundary="%s"', $content_type, $boundary ) );
			}
		}

		if ( ! empty( $attachments ) ) {
			foreach ( $attachments as $filename => $attachment ) {
				$filename = is_string( $filename ) ? $filename : '';

				try {
					$phpmailer->addAttachment( $attachment, $filename );
				} catch ( Exception $e ) {
					continue;
				}
			}
		}

		/**
		 * Fires after PHPMailer is initialized.
		 *
		 * @param \PHPMailer $phpmailer The PHPMailer instance (passed by reference).
		 *
		 * @since 2.2.0
		 *
		 */
		do_action_ref_array( 'phpmailer_init', [ &$phpmailer ] );

		$mail_data = compact( 'to', 'subject', 'message', 'headers', 'attachments' );

		// Send!
		try {
			$send = $phpmailer->send();

			/**
			 * Fires after PHPMailer has successfully sent an email.
			 *
			 * The firing of this action does not necessarily mean that the recipient(s) received the
			 * email successfully. It only means that the `send` method above was able to
			 * process the request without any errors.
			 *
			 * @param array $mail_data {
			 *     An array containing the email recipient(s), subject, message, headers, and attachments.
			 *
			 * @type string[] $to Email addresses to send message.
			 * @type string $subject Email subject.
			 * @type string $message Message contents.
			 * @type string[] $headers Additional headers.
			 * @type string[] $attachments Paths to files to attach.
			 * }
			 * @since 5.9.0
			 */
			do_action( 'wp_mail_succeeded', $mail_data );

			return $send;
		} catch ( Exception $e ) {
			$mail_data['phpmailer_exception_code'] = $e->getCode();

			/**
			 * Fires after a \PHPMailer\PHPMailer\Exception is caught.
			 *
			 * @param \WP_Error $error A WP_Error object with the PHPMailer\PHPMailer\Exception message, and an array
			 *                        containing the mail recipient, subject, message, headers, and attachments.
			 *
			 * @since 4.4.0
			 *
			 */
			do_action( 'wp_mail_failed', new \WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_data ) );

			return false;
		}
	}

	// ------------------------------------------------------

	/**
	 * SMTP notices
	 *
	 * @return void
	 */
	public function options_admin_notice(): void {
		if ( ! $this->smtpConfigured() && check_smtp_plugin_active() && $this->_check_smtp_menu() ) {
			$class   = 'notice notice-error';
			$message = __( 'You need to configure your SMTP credentials in the settings to send emails.', ADDONS_TEXT_DOMAIN );

			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
		}
	}

	// -------------------------------------------------------------

	/**
	 * @return bool
	 */
	public function smtpConfigured(): bool {
		$smtp_options    = get_option( 'smtp__options' );
		$smtp_configured = true;

		if ( isset( $smtp_options['smtp_auth'] ) && $smtp_options['smtp_auth'] === "true" ) {
			if ( empty( $smtp_options['smtp_username'] ) || empty( $smtp_options['smtp_password'] ) ) {
				$smtp_configured = false;
			}
		}

		if ( empty( $smtp_options['smtp_host'] ) ||
		     empty( $smtp_options['smtp_auth'] ) ||
		     empty( $smtp_options['smtp_encryption'] ) ||
		     empty( $smtp_options['smtp_port'] ) ||
		     empty( $smtp_options['smtp_from_email'] ) ||
		     empty( $smtp_options['smtp_from_name'] )
		) {
			$smtp_configured = false;
		}

		return $smtp_configured;
	}
}
