<?php
/**
 * Integrations Center module.
 *
 * One screen to wire the hotel up to the outside world: payment gateways,
 * analytics & pixels, maps, messaging (SMTP / SMS / WhatsApp) and developer
 * settings. All values live inside the single 'dfcc_integration_settings'
 * option group. Secret keys are stored verbatim but never echoed in full —
 * the UI shows a masked placeholder and only overwrites a secret when a fresh,
 * non-masked value is submitted.
 *
 * Front-end tag injection (GA4, GTM, Meta / TikTok pixels, Clarity, Search
 * Console verification) is driven entirely off the saved IDs and is skipped for
 * logged-in admins so the owner's own visits never pollute their analytics.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Integrations.
 */
class DFCC_Integrations extends DFCC_Module {

	/**
	 * Option group that stores every integration value.
	 */
	const OPTION = 'dfcc_integration_settings';

	/**
	 * Placeholder used to mask stored secrets in the UI.
	 */
	const MASK = '••••••••';

	/**
	 * Field keys that hold secrets and must be masked / preserved on save.
	 *
	 * @var string[]
	 */
	private $secret_keys = array(
		'stripe_secret',
		'paypal_secret',
		'paymob_api_key',
		'hyperpay_access_token',
		'moyasar_secret',
		'smtp_pass',
		'sms_api_key',
	);

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'integrations';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Integrations', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'dfcc_admin_pages', array( $this, 'register_page' ) );
		add_action( 'admin_init', array( $this, 'handle_save' ) );

		// Front-end tag injection.
		add_action( 'wp_head', array( $this, 'print_head_tags' ), 5 );
		add_action( 'wp_body_open', array( $this, 'print_body_tags' ) );
		add_action( 'wp_footer', array( $this, 'print_footer_fallback' ) );

		// Transactional email via SMTP.
		add_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );

		// REST API toggle.
		add_action( 'init', array( $this, 'maybe_disable_rest' ) );
	}

	/**
	 * Add the Integrations admin page.
	 *
	 * @param array $pages Existing pages.
	 * @return array
	 */
	public function register_page( $pages ) {
		$pages[] = array(
			'slug'     => 'dfcc-integrations',
			'title'    => __( 'Integrations', 'dog-father-control-center' ),
			'callback' => array( $this, 'render' ),
			'order'    => 60,
		);
		return $pages;
	}

	/**
	 * Render the settings screen.
	 *
	 * @return void
	 */
	public function render() {
		$this->view(
			'integrations',
			array(
				'settings' => get_option( self::OPTION, array() ),
				'module'   => $this,
			)
		);
	}

	/**
	 * Whether a field key is a secret.
	 *
	 * @param string $key Field key.
	 * @return bool
	 */
	public function is_secret( $key ) {
		return in_array( $key, $this->secret_keys, true );
	}

	/**
	 * Produce the value to show in an input for a given key.
	 *
	 * Secrets are replaced with a fixed mask when a value is stored so the real
	 * key is never sent to the browser. The last 4 characters are surfaced
	 * separately via masked_hint() for owner recognition.
	 *
	 * @param array  $settings Saved settings.
	 * @param string $key      Field key.
	 * @return string
	 */
	public function field_value( $settings, $key ) {
		$stored = isset( $settings[ $key ] ) ? (string) $settings[ $key ] : '';
		if ( $this->is_secret( $key ) && '' !== $stored ) {
			return self::MASK;
		}
		return $stored;
	}

	/**
	 * Human hint for a stored secret, e.g. "•••• 1234".
	 *
	 * @param array  $settings Saved settings.
	 * @param string $key      Field key.
	 * @return string Empty when nothing stored.
	 */
	public function masked_hint( $settings, $key ) {
		$stored = isset( $settings[ $key ] ) ? (string) $settings[ $key ] : '';
		if ( '' === $stored ) {
			return '';
		}
		$last4 = strlen( $stored ) > 4 ? substr( $stored, -4 ) : $stored;
		return '•••• ' . $last4;
	}

	/**
	 * Save handler.
	 *
	 * @return void
	 */
	public function handle_save() {
		if ( ! isset( $_POST['dfcc_integrations_nonce'] ) ) {
			return;
		}
		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dfcc_integrations_nonce'] ) ), 'dfcc_save_integrations' ) ) {
			return;
		}

		$existing = get_option( self::OPTION, array() );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		// Plain text / id fields → sanitize_text_field.
		$text_fields = array(
			'stripe_publishable',
			'paymob_integration_id',
			'paypal_client_id',
			'hyperpay_entity_id',
			'moyasar_publishable',
			'ga4_id',
			'gtm_id',
			'gsc_code',
			'clarity_id',
			'meta_pixel_id',
			'tiktok_pixel_id',
			'maps_api_key',
			'whatsapp_number',
			'smtp_host',
			'smtp_user',
			'smtp_encryption',
			'smtp_from_name',
			'sms_provider',
			'sms_sender_id',
		);

		$out = $existing;

		foreach ( $text_fields as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$out[ $key ] = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
			}
		}

		// Numeric.
		if ( isset( $_POST['smtp_port'] ) ) {
			$out['smtp_port'] = (int) $_POST['smtp_port'];
		}

		// Emails.
		if ( isset( $_POST['smtp_from_email'] ) ) {
			$out['smtp_from_email'] = sanitize_email( wp_unslash( $_POST['smtp_from_email'] ) );
		}

		// URLs textarea (one per line).
		if ( isset( $_POST['webhook_urls'] ) ) {
			$lines = preg_split( '/\r\n|\r|\n/', (string) wp_unslash( $_POST['webhook_urls'] ) );
			$clean = array();
			foreach ( (array) $lines as $line ) {
				$line = trim( $line );
				if ( '' !== $line ) {
					$clean[] = esc_url_raw( $line );
				}
			}
			$out['webhook_urls'] = implode( "\n", array_filter( $clean ) );
		}

		// Boolean toggles.
		$toggles = array(
			'stripe_enabled',
			'paymob_enabled',
			'paypal_enabled',
			'hyperpay_enabled',
			'moyasar_enabled',
			'smtp_enabled',
			'rest_enabled',
		);
		foreach ( $toggles as $key ) {
			$out[ $key ] = ! empty( $_POST[ $key ] ) ? 1 : 0;
		}

		// Secrets: only overwrite when a new, non-masked value is provided.
		foreach ( $this->secret_keys as $key ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$submitted = (string) wp_unslash( $_POST[ $key ] );
			if ( self::MASK === $submitted ) {
				// Owner left the mask in place — keep stored secret.
				continue;
			}
			if ( '' === $submitted ) {
				// Explicitly cleared.
				$out[ $key ] = '';
				continue;
			}
			$out[ $key ] = sanitize_text_field( $submitted );
		}

		update_option( self::OPTION, $out );

		add_settings_error( 'dfcc_integrations', 'saved', __( 'Integration settings saved.', 'dog-father-control-center' ), 'updated' );
		set_transient( 'dfcc_integrations_notice', 1, 30 );

		wp_safe_redirect( add_query_arg( array( 'page' => 'dfcc-integrations', 'updated' => 'true' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Should tracking tags be suppressed for the current request?
	 *
	 * @return bool
	 */
	private function suppress_tracking() {
		$suppress = is_user_logged_in() && current_user_can( 'manage_options' );

		/**
		 * Filter whether analytics / pixel tags are suppressed for this request.
		 *
		 * Defaults to true for logged-in admins so owner visits don't skew data.
		 *
		 * @param bool $suppress Whether to skip tag output.
		 */
		return (bool) apply_filters( 'dfcc_suppress_tracking', $suppress );
	}

	/**
	 * Read a single integration value.
	 *
	 * @param string $key     Field key.
	 * @param string $default Fallback.
	 * @return string
	 */
	private function get( $key, $default = '' ) {
		return (string) dfcc_get_setting( self::OPTION, $key, $default );
	}

	/**
	 * Print analytics, pixels and verification tags in <head>.
	 *
	 * @return void
	 */
	public function print_head_tags() {
		// Search Console verification should be printed even for admins.
		$gsc = $this->get( 'gsc_code' );
		if ( '' !== $gsc ) {
			printf( '<meta name="google-site-verification" content="%s" />' . "\n", esc_attr( $gsc ) );
		}

		if ( $this->suppress_tracking() ) {
			return;
		}

		$ga4 = $this->get( 'ga4_id' );
		if ( '' !== $ga4 ) {
			?>
<!-- DFCC: Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4 ); ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', <?php echo wp_json_encode( $ga4 ); ?>);
</script>
			<?php
		}

		$gtm = $this->get( 'gtm_id' );
		if ( '' !== $gtm ) {
			?>
<!-- DFCC: Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer',<?php echo wp_json_encode( $gtm ); ?>);</script>
			<?php
		}

		$meta_pixel = $this->get( 'meta_pixel_id' );
		if ( '' !== $meta_pixel ) {
			?>
<!-- DFCC: Meta Pixel -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', <?php echo wp_json_encode( $meta_pixel ); ?>);
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr( $meta_pixel ); ?>&ev=PageView&noscript=1"/></noscript>
			<?php
		}

		$tiktok = $this->get( 'tiktok_pixel_id' );
		if ( '' !== $tiktok ) {
			?>
<!-- DFCC: TikTok Pixel -->
<script>
!function (w, d, t) {
w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=d.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=d.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
ttq.load(<?php echo wp_json_encode( $tiktok ); ?>);
ttq.page();
}(window, document, 'ttq');
</script>
			<?php
		}

		$clarity = $this->get( 'clarity_id' );
		if ( '' !== $clarity ) {
			?>
<!-- DFCC: Microsoft Clarity -->
<script type="text/javascript">
(function(c,l,a,r,i,t,y){
c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
})(window, document, "clarity", "script", <?php echo wp_json_encode( $clarity ); ?>);
</script>
			<?php
		}
	}

	/**
	 * Print the GTM noscript fallback right after <body>.
	 *
	 * @return void
	 */
	public function print_body_tags() {
		if ( $this->suppress_tracking() ) {
			return;
		}
		$gtm = $this->get( 'gtm_id' );
		if ( '' === $gtm ) {
			return;
		}
		printf(
			'<!-- DFCC: GTM (noscript) --><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>',
			esc_attr( $gtm )
		);
	}

	/**
	 * Footer fallback for the GTM noscript when wp_body_open isn't supported
	 * by the active theme.
	 *
	 * @return void
	 */
	public function print_footer_fallback() {
		if ( did_action( 'wp_body_open' ) ) {
			return;
		}
		$this->print_body_tags();
	}

	/**
	 * Apply stored SMTP settings to PHPMailer.
	 *
	 * @param PHPMailer\PHPMailer\PHPMailer|object $phpmailer PHPMailer instance.
	 * @return void
	 */
	public function configure_smtp( $phpmailer ) {
		$settings = get_option( self::OPTION, array() );
		if ( empty( $settings['smtp_enabled'] ) || empty( $settings['smtp_host'] ) ) {
			return;
		}

		$phpmailer->isSMTP();
		$phpmailer->Host       = (string) $settings['smtp_host'];
		$phpmailer->SMTPAuth   = ! empty( $settings['smtp_user'] );
		$phpmailer->Port       = ! empty( $settings['smtp_port'] ) ? (int) $settings['smtp_port'] : 587;

		if ( ! empty( $settings['smtp_user'] ) ) {
			$phpmailer->Username = (string) $settings['smtp_user'];
		}
		if ( ! empty( $settings['smtp_pass'] ) ) {
			$phpmailer->Password = (string) $settings['smtp_pass'];
		}

		$enc = isset( $settings['smtp_encryption'] ) ? strtolower( (string) $settings['smtp_encryption'] ) : '';
		if ( in_array( $enc, array( 'ssl', 'tls' ), true ) ) {
			$phpmailer->SMTPSecure = $enc;
		} elseif ( 'none' === $enc ) {
			$phpmailer->SMTPSecure = '';
			$phpmailer->SMTPAutoTLS = false;
		}

		if ( ! empty( $settings['smtp_from_email'] ) && is_email( $settings['smtp_from_email'] ) ) {
			$from_name = ! empty( $settings['smtp_from_name'] ) ? (string) $settings['smtp_from_name'] : get_bloginfo( 'name' );
			$phpmailer->setFrom( (string) $settings['smtp_from_email'], $from_name );
		}
	}

	/**
	 * Optionally lock down the REST API to authenticated users when the toggle
	 * is off. Default WordPress behaviour (open) is kept when enabled or unset.
	 *
	 * @return void
	 */
	public function maybe_disable_rest() {
		$settings = get_option( self::OPTION, array() );
		// Only act when the key has been explicitly saved to 0.
		if ( ! is_array( $settings ) || ! isset( $settings['rest_enabled'] ) ) {
			return;
		}
		if ( (int) $settings['rest_enabled'] === 1 ) {
			return;
		}

		add_filter(
			'rest_authentication_errors',
			static function ( $result ) {
				if ( ! empty( $result ) ) {
					return $result;
				}
				if ( ! is_user_logged_in() ) {
					return new WP_Error(
						'dfcc_rest_disabled',
						__( 'The REST API is restricted to authenticated users.', 'dog-father-control-center' ),
						array( 'status' => 401 )
					);
				}
				return $result;
			}
		);
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Integrations() );
	}
);
