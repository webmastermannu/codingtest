<?php
/**
 * Twenty Twenty-Two functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Two
 * @since Twenty Twenty-Two 1.0
 */


if ( ! function_exists( 'twentytwentytwo_support' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since Twenty Twenty-Two 1.0
	 *
	 * @return void
	 */
	function twentytwentytwo_support() {

		// Add support for block styles.
		add_theme_support( 'wp-block-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style.css' );

	}

endif;

add_action( 'after_setup_theme', 'twentytwentytwo_support' );

if ( ! function_exists( 'twentytwentytwo_styles' ) ) :

	/**
	 * Enqueue styles.
	 *
	 * @since Twenty Twenty-Two 1.0
	 *
	 * @return void
	 */
	function twentytwentytwo_styles() {
		// Register theme stylesheet.
		$theme_version = wp_get_theme()->get( 'Version' );

		$version_string = is_string( $theme_version ) ? $theme_version : false;
		wp_register_style(
			'twentytwentytwo-style',
			get_template_directory_uri() . '/style.css',
			array(),
			$version_string
		);

		// Enqueue theme stylesheet.
		wp_enqueue_style( 'twentytwentytwo-style' );

	}

endif;

add_action( 'wp_enqueue_scripts', 'twentytwentytwo_styles' );

// Add block patterns
require get_template_directory() . '/inc/block-patterns.php';

// Custom Post Type For Portfolios
function portfolio_init() {
    $labels = array(
        'name' => 'Portfolios',
        'singular_name' => 'Portfolios',
        'add_new' => 'Add New Portfolios',
        'add_new_item' => 'Add New Portfolios',
        'edit_item' => 'Edit Portfolios',
        'new_item' => 'New Portfolios',
        'all_items' => 'All Portfolios',
        'view_item' => 'View Portfolios',
        'search_items' => 'Search Portfolios',
        'not_found' =>  'No Portfolios Found',
        'not_found_in_trash' => 'No Portfolios found in Trash', 
        'parent_item_colon' => '',
        'menu_name' => 'Portfolios',
    );
    
    // register post type
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array('slug' => 'portfolios'),
        'query_var' => true,
        'menu_icon' => 'dashicons-portfolio',
        'supports' => array(
            'title',
            'editor',
            'excerpt',
            'trackbacks',
            'custom-fields',
            'comments',
            'revisions',
            'thumbnail',
            'author',
            'page-attributes'
        )
    );
    register_post_type( 'portfolios', $args );
    register_taxonomy('portfolios_category', 'portfolios', array('hierarchical' => true, 'label' => 'Category', 'query_var' => true, 'rewrite' => array( 'slug' => 'portfolios-category' )));
}
add_action( 'init', 'portfolio_init' );
## Create Custom post Type Code End

/* Wallet Balance & Meta Fields Code */
function add_wallet_balance_field() {
    $args = array(
        'type'         => 'number',
        'label'        => 'Wallet Balance',
        'description'  => 'User Wallet Balance',
        'show_in_rest' => true,
        'single'       => true,
    );
    register_meta('user', 'wallet_balance', $args);
}
add_action('init', 'add_wallet_balance_field');

function add_wallet_balance_user_profile($user) {
    $wallet_balance = get_user_meta($user->ID, 'wallet_balance', true); ?>
    <table class="form-table">
        <tr>
            <th><label for="wallet_balance">Wallet Balance</label></th>
            <td>
                <input type="number" name="wallet_balance" id="wallet_balance" value="<?php echo esc_attr($wallet_balance); ?>" class="regular-text">
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'add_wallet_balance_user_profile');
add_action('edit_user_profile', 'add_wallet_balance_user_profile');

function save_wallet_balance_user_profile($user_id) {
    if (current_user_can('edit_user', $user_id)) {
        $wallet_balance = isset($_POST['wallet_balance']) ? sanitize_text_field($_POST['wallet_balance']) : '';
        update_user_meta($user_id, 'wallet_balance', $wallet_balance);
    }
}
add_action('personal_options_update', 'save_wallet_balance_user_profile');
add_action('edit_user_profile_update', 'save_wallet_balance_user_profile');

function add_wallet_payment_gateway($gateways) {
    $gateways[] = 'WC_Gateway_Wallet';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'add_wallet_payment_gateway');

class WC_Gateway_Wallet extends WC_Payment_Gateway {
    public function __construct() {
        $this->id = 'wallet';
        $this->method_title = 'Wallet';
        $this->method_description = 'Pay using your wallet balance.';
        $this->has_fields = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable Wallet Payment',
                'default' => 'yes',
            ),
            'title' => array(
                'title'       => 'Title',
                'type'        => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default'     => 'Wallet',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => 'Description',
                'type'        => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default'     => 'Pay using your wallet balance.',
                'desc_tip'    => true,
            ),
        );
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        $wallet_balance = get_user_meta($user_id, 'wallet_balance', true);

        // Check if wallet payment is enabled and user has sufficient balance
        if ($this->enabled === 'yes' && $wallet_balance >= $order->get_total()) {
            // Deduct order amount from wallet balance
            $new_wallet_balance = $wallet_balance - $order->get_total();
            update_user_meta($user_id, 'wallet_balance', $new_wallet_balance);

            // Mark the order as paid
            $order->payment_complete();
            $order->reduce_order_stock();

            // Empty the cart
            WC()->cart->empty_cart();

            // Redirect to the thank you page
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            );
        } else {
            // Display an error message if wallet payment is not enabled or user has insufficient balance
            wc_add_notice('Wallet payment is not available or your wallet balance is insufficient.', 'error');
            return;
        }
    }
}
/* Wallet Balance & Meta Fields Code */