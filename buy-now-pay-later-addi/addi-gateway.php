<?php
/*
 * Plugin Name: Addi - Cuotas que se adaptan a ti
 * Plugin URI: https://co.addi.com/
 * Description: Ofrece a tus clientes la posibilidad de comprar a cuotas lo que quieran, cuando quieran, pagando después con <strong>Addi</strong>. En minutos y sin complicaciones.
 * Author: Addi
 * Author URI: https://co.addi.com/
 * Version: 2.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * License: GPL v2 or later
 * Text Domain: buy-now-pay-later-addi
 * Domain Path: /languages
 */

// Set secure session cookies early
function addi_set_secure_session() {
    if (!headers_sent()) {
        ini_set('session.cookie_secure', 1);
    }
}
add_action('init', 'addi_set_secure_session', 1);

/*
 * file required to use some plugin hooks functions
 */
require_once (ABSPATH . 'wp-admin/includes/plugin.php');

// Dependencies
include (plugin_dir_path(__FILE__) . 'includes/class-addi-logger.php');



/*
 * registration hook when plugin is activated 
 */
add_action('activated_plugin', 'addi_plugin_activated');

/**
 * This function is being executed when activated_plugin is fired
 *  Validations:
 * - current user can activate the plugin
 * - PHP version is compatible
 * - Wordpress version is compatible
 * - Woocommerce class exists in wordpress installation
 * - table is being created
 */
function addi_plugin_activated()
{

    global $woocommerce;
    global $wp;
    global $wpdb;
    global $wp_version;

    $php_version = '7.0';
    $wordpress_version = '5.2';

    if (!current_user_can('activate_plugins')) {
        return;
    }

    if (version_compare(PHP_VERSION, $php_version, '<')) {
        // deactivating plugin if PHP version is ot compatible
        deactivate_plugins(basename(__FILE__));
        wp_die(__('This plugin can not be activated because it requires a PHP version greater than 5.3. Your PHP version can be updated by your hosting company.', 'woocommerce-addon-slug'), 'Plugin dependency check', array('back_link' => true));
    } elseif (version_compare($wp_version, $wordpress_version, '<')) {
        // deactivating plugin if wordpress version is ot compatible
        deactivate_plugins(basename(__FILE__));
        wp_die(__('This plugin can not be activated because it requires a WordPress version greater than 3.8. Please go to Dashboard &#9656; Updates to get the latest version of WordPress .', 'woocommerce-addon-slug'), 'Plugin dependency check', array('back_link' => true));
    } elseif (!class_exists('WooCommerce')) {
        // deactivating plugin if woocommerce is not installed
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Please install and Activate WooCommerce.', 'woocommerce-addon-slug'), 'Plugin dependency check', array('back_link' => true));
    } else {

        $table_name = $wpdb->prefix . "wc_addi_gateway";
        $table_config_name = $wpdb->prefix . "wc_addi_config";

        //check if table already exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                order_id int(11) NOT NULL,
                order_status varchar(50) NOT NULL,
                date datetime NOT NULL
            ) $charset_collate;";

            require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

        }

        //check if table already exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_config_name'") != $table_config_name) {

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_config_name (
                element varchar(50) NOT NULL,
                value varchar(50) NOT NULL
            ) $charset_collate;";

            require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

        }
    }
}

/* Register activation hook. */
register_activation_hook(__FILE__, 'fx_addi_admin_notice_activation_hook');

/**
 * Runs only when the plugin is activated.
 */
function fx_addi_admin_notice_activation_hook()
{

    /* Create transient data */
    set_transient('fx_addi_admin_notice_transient', true, 5);
}


/* Add admin notice */
add_action('admin_notices', 'fx_addi_admin_notice');


/**
 * Admin Notice on Activation.
 * @since 0.1.0
 */
function fx_addi_admin_notice()
{

    /* Check transient, if available display notice */
    if (get_transient('fx_addi_admin_notice_transient')) {
        if ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
            ?>
            <div class="updated notice is-dismissible">
                <p><strong>¡Pagamento parcelado com ADDI instalado corretamente!</strong>Agora você pode oferecer aos seus clientes
                    o parcelamento na sua loja.</p>
            </div>
            <?php
            /* Delete transient, only display this notice once. */
            delete_transient('fx_addi_admin_notice_transient');
        } else {
            ?>
            <div class="updated notice is-dismissible">
                <p><strong>¡Pago a cuotas con ADDI instalado correctamente!</strong> Ya puedes ofrecer a tus clientes que paguen a
                    cuotas en tu tienda.</p>
            </div>
            <div class="updated notice is-dismissible">
                <p><strong>Para garantizar que recibes correctamente la respuesta por parte de ADDI, te invitamos a verificar lo
                        siguiente:</strong></p>
                <p>Dentro del archivo .htaccess del servidor, las siguientes reglas deben estar presentes</p>
                <p><b>CGIPassAuth On</b></p>
                <p><b>RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]</b></p>
                <p><b>SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0</b></p>
            </div>
            <?php
            /* Delete transient, only display this notice once. */
            delete_transient('fx_addi_admin_notice_transient');
        }
    }
}

/**
 * Add action links
 *
 * @param array  $links Array of plugin action links
 * @param string $file  Plugin file path
 * @return array Modified array of plugin action links
 */
function addi_plugin_action_links($links) {
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=addi')),
        esc_html__('Configuración', 'buy-now-pay-later-addi')
    );
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'addi_plugin_action_links');

add_action('woocommerce_single_product_summary', 'addi_render_widget');

function addi_render_widget($argPosition)
{
    global $product;
    global $wpdb;

    if (!$argPosition)
        $argPosition = 'some value';

    // LOAD THE WC LOGGER
    $logger = wc_get_logger();
    $styles_to_json = "";
    $result = null;

    try {
        // Get widget styles from custom table
        $table_config_name = $wpdb->prefix . "wc_addi_config";
        
        // Check if table exists
        $is_table_missing = !$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(1) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $wpdb->prefix . 'wc_addi_config'
        ));

        if ($is_table_missing) {
            $logger->error('Addi config table does not exist: ' . $table_config_name);
            return;
        }

        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_config_name} WHERE element = %s",
            "widget"
        ));
        
        if ($wpdb->last_error) {
            $logger->error('Error fetching widget styles: ' . $wpdb->last_error);
        }
        
    } catch (Exception $e) {
        $logger->error('Exception fetching widget styles: ' . $e->getMessage());
    }

    // Get widget position from WooCommerce settings
    $gateways = WC()->payment_gateways->payment_gateways();
    $addi_gateway = isset($gateways['addi']) ? $gateways['addi'] : null;

    if ($addi_gateway->get_option('widget_enabled') !== 'yes') {
        return;
    }

    $position = $addi_gateway ? $addi_gateway->getConfWidgetPosition() : 'woocommerce_single_product_summary';

    if ($position === $argPosition) {

        // verifying the integrity of the resulset, otherwise could throw an error.
        if (isset ($result) && count($result) > 0) {

            try {
                //variables for widget css
                $widgetBorderColor_ = '';
                $widgetBorderRadius_ = '';
                $widgetFontColor_ = '';
                $widgetFontFamily_ = '';
                $widgetFontSize_ = '';
                $widgetBadgeBackgroundColor_ = '';
                $widgetInfoBackgroundColor_ = '';
                $widgetMargin_ = '';
                //varibles for modal css
                $modalBackgroundColor_ = '';
                $modalFontColor_ = '';
                $modalPriceColor_ = '';
                $modalBadgeBackgroundColor_ = '';
                $modalBadgeBorderRadius_ = '';
                $modalBadgeFontColor_ = '';
                $modalBadgeLogoStyle_ = '';
                $modalCardColor_ = '';
                $modalButtonBorderColor_ = '';
                $modalButtonBorderRadius_ = '';
                $modalButtonBackgroundColor_ = '';
                $modalButtonFontColor_ = '';

                foreach ($result as $item) {
                    $newValue = $item->value;

                    $getSalePriceFromPlugin = "";
                    $split = explode("|", $newValue);
                    $bol = $split[0];
                    $slug = $split[1];
                    $price = $product->get_price_html();

                    #TODO: remove this validation
                    if (
                        strpos(strip_tags($price), '-') == false &&
                        strpos(strip_tags($price), ':') == false
                    ) {
                        $parts = explode(" ", strip_tags($price));
                        $partsCount = count($parts);

                        if ($partsCount > 1) {

                            foreach ($parts as $key => &$value_if) {

                                $matches = array();
                                $value_formatted = str_replace("&#36;", "$", $value_if);
                                $value_formatted = str_replace(wc_get_price_thousand_separator(), "", $value_formatted);
                                preg_match_all("/([$][0-9]+)/", $value_formatted, $matches);

                                $first_match = $matches[0];
                                $match_in_array = null;

                                if (is_array($first_match)) {
                                    if (!empty ($first_match)) {
                                        $match_in_array = $first_match[0];
                                    }
                                }

                                if ($key == 0) {
                                    $price_regular = $match_in_array !== '' ? $match_in_array : $matches[0];
                                }
                                if ($key == 1) {
                                    $price_sale = $match_in_array !== '' ? $match_in_array : $matches[0];
                                }
                            }

                            if (isset ($price_sale) && $price_sale !== "") {

                                $price_sale = str_replace("$", "", $price_sale);
                                $getSalePriceFromPlugin = $price_sale;

                            }

                        } else {
                            foreach ($parts as &$value) {
                                $matches = array();
                                //$logger->info( '>>>> value normal :' . $value . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                $value_formatted = str_replace("&#36;", "$", $value);
                                $value_formatted = str_replace(".", "", $value_formatted);
                                //$logger->info( '>>>> value formatted :' . $value_formatted . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                preg_match_all("/([$][0-9]+)/", $value_formatted, $matches);
                                $prices_match = $matches[0];
                                //$logger->info( '>>>> match count :' . count($matches) . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                //$logger->info( '>>>> price match count :' . count($prices_match) . ' ', array( 'source' => 'addi-widget-handler-log' ) );

                                if (isset ($prices_match) && count($prices_match) > 1) {
                                    $price_regular = $prices_match[0];
                                    $price_sale = $prices_match[1];
                                    $price_sale = str_replace("$", "", $price_sale);
                                    $getSalePriceFromPlugin = $price_sale;
                                    //$logger->info( '>>>> price regular:' . $price_regular . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                    //$logger->info( '>>>> price sales:' . $price_sale . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                    //$logger->info( '>>>> get Sale price from Plugin :' . $getSalePriceFromPlugin . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                }
                            }

                        }

                    }

                    $conf_result = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element like %s", "conf_%"));

                    //print_r($conf_result);

                    if (isset ($conf_result) && count($conf_result) > 0) {
                        // LOAD THE WC LOGGER
                        $logger = wc_get_logger();
                        try {

                            foreach ($conf_result as $item) {
                                $conf_type = $item->element;
                                $conf_value = $item->value;

                                switch ($conf_type) {
                                    case "conf_widgetBorderColor":
                                        $widgetBorderColor_ = $conf_value;
                                        break;
                                    case "conf_widgetBorderRadius":
                                        $widgetBorderRadius_ = $conf_value;
                                        break;
                                    case "conf_widgetFontColor":
                                        $widgetFontColor_ = $conf_value;
                                        break;
                                    case "conf_widgetFontFamily":
                                        $widgetFontFamily_ = $conf_value;
                                        break;
                                    case "conf_widgetFontSize":
                                        $widgetFontSize_ = $conf_value;
                                        break;
                                    case "conf_widgetBadgeBackgroundColor":
                                        $widgetBadgeBackgroundColor_ = $conf_value;
                                        break;
                                    case "conf_widgetInfoBackgroundColor":
                                        $widgetInfoBackgroundColor_ = $conf_value;
                                        break;
                                    case "conf_widgetMargin":
                                        $widgetMargin_ = $conf_value;
                                        break;
                                    case "conf_modalBackgroundColor":
                                        $modalBackgroundColor_ = $conf_value;
                                        break;
                                    case "conf_modalFontColor":
                                        $modalFontColor_ = $conf_value;
                                        break;
                                    case "conf_modalPriceColor":
                                        $modalPriceColor_ = $conf_value;
                                        break;
                                    case "conf_modalBadgeBackgroundColor":
                                        $modalBadgeBackgroundColor_ = $conf_value;
                                        break;
                                    case "conf_modalBadgeBorderRadius":
                                        $modalBadgeBorderRadius_ = $conf_value;
                                        break;
                                    case "conf_modalBadgeFontColor":
                                        $modalBadgeFontColor_ = $conf_value;
                                        break;
                                    case "conf_modalBadgeLogoStyle":
                                        $modalBadgeLogoStyle_ = $conf_value;
                                        break;
                                    case "conf_modalCardColor":
                                        $modalCardColor_ = $conf_value;
                                        break;
                                    case "conf_modalButtonBorderColor":
                                        $modalButtonBorderColor_ = $conf_value;
                                        break;
                                    case "conf_modalButtonBorderRadius":
                                        $modalButtonBorderRadius_ = $conf_value;
                                        break;
                                    case "conf_modalButtonBackgroundColor":
                                        $modalButtonBackgroundColor_ = $conf_value;
                                        break;
                                    case "conf_modalButtonFontColor":
                                        $modalButtonFontColor_ = $conf_value;
                                        break;
                                }
                            }
                        } catch (Exception $e) {
                            $logger->info('Error getting data from database: ' . $e . ' ', array('source' => 'addi-error-handler-log'));
                        }
                    }

                    $styles_to_json = '{"widget": {"borderColor": "' . $widgetBorderColor_ . '","borderRadius": "' . $widgetBorderRadius_ . '","fontColor": "' . $widgetFontColor_ . '","fontFamily": "' . $widgetFontFamily_ . '","fontSize": "' . $widgetFontSize_ . '","badgeBackgroundColor": "' . $widgetBadgeBackgroundColor_ . '","infoBackgroundColor": "' . $widgetInfoBackgroundColor_ . '","margin": "' . $widgetMargin_ . '","whiteLogo":' . $modalBadgeLogoStyle_ . '},"modal":{"backgroundColor": "' . $modalBackgroundColor_ . '","fontColor": "' . $modalFontColor_ . '","fontFamily": "system-ui","priceColor": "' . $modalPriceColor_ . '","badgeBorderRadius": "' . $modalBadgeBorderRadius_ . '","badgeBackgroundColor": "' . $modalBadgeBackgroundColor_ . '","badgeFontColor":"' . $modalBadgeFontColor_ . '","cardColor": "' . $modalCardColor_ . '","buttonBorderColor": "' . $modalButtonBorderColor_ . '",	"buttonBorderRadius": "' . $modalButtonBorderRadius_ . '","buttonBackgroundColor": "' . $modalButtonBackgroundColor_ . '","buttonFontColor": "' . $modalButtonFontColor_ . '"}}';

                    //print_r($styles_to_json);
                    $tax_display_mode = get_option('woocommerce_tax_display_shop', 'excl');

                    if ($bol == 'yes') {
                        $country = (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? "br" : 'co';
                        if ($product->is_on_sale() && $product->get_sale_price() !== "") {
                            //print_r('step 1');
                            $product_price = 'incl' === $tax_display_mode ? wc_get_price_including_tax($product, array('price' => $product->get_sale_price())) : wc_get_price_excluding_tax($product, array('price' => $product->get_sale_price()));
                            echo "<div style='float: none;margin:10px 0'><addi-product-widget country='" . $country . "' custom-widget-styles='" . $styles_to_json . "' price='" . $product_price . "' ally-slug='" . $slug . "'></addi-product-widget></div>";
                        } elseif ($getSalePriceFromPlugin !== "") {
                            //print_r('step 2');
                            //print_r($getSalePriceFromPlugin);
                            echo "<div style='float: none;margin:10px 0'><addi-product-widget country='" . $country . "' custom-widget-styles='" . $styles_to_json . "' price='" . $getSalePriceFromPlugin . "' ally-slug='" . $slug . "'></addi-product-widget></div>";
                        } else {
                            //print_r('step 3');
                            $product_price = 'incl' === $tax_display_mode ? wc_get_price_including_tax($product) : wc_get_price_excluding_tax($product);
                            echo "<div style='float: none;margin:10px 0'><addi-product-widget country='" . $country . "' custom-widget-styles='" . $styles_to_json . "' price='" . $product_price . "' ally-slug='" . $slug . "'></addi-product-widget></div>";
                        }
                    }

                    break;
                }
            } catch (Exception $e) {

            }
        }

    }
}

/**
 * Register all available widget positions for Addi
 */
function addi_register_widget_positions() {
    $positions = array(
        'woocommerce_before_add_to_cart_form',
        'woocommerce_single_product_summary',
        'woocommerce_after_add_to_cart_form',
        'woocommerce_product_meta_start',
        'woocommerce_product_meta_end'
    );

    foreach ($positions as $position) {
        add_action($position, function() use ($position) {
            addi_render_widget($position);
        }, 10);
    }
}
add_action('init', 'addi_register_widget_positions');

/**
 * Load custom CSS and JavaScript.
 */
add_action('wp_enqueue_scripts', 'addi_my_enqueue_scripts');

function addi_my_enqueue_scripts()
{
    // Get gateway instance to check settings
    $gateways = WC()->payment_gateways->payment_gateways();
    $addi_gateway = isset($gateways['addi']) ? $gateways['addi'] : null;

    // Only proceed if gateway exists, is enabled, and widget is enabled
    if ($addi_gateway && $addi_gateway->get_option('enabled') == 'yes' && ($addi_gateway->get_option('widget_enabled') == 'yes' || $addi_gateway->get_option('home_banner_enabled') == 'yes')) {

        // Get test mode setting
        $test_mode = $addi_gateway->getTestmode();

        // Enqueue my scripts.
        $home_url = wp_make_link_relative(home_url()) . '/';
        $country = (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'br' : 'co';
        $is_product = json_encode(is_product());

        // Choose script URL based on test mode
        $script_url = $test_mode
            ? 'https://s3.amazonaws.com/statics.addi.com/woocommerce/woocommerce-widget-wrapper.bundle-stag.min.js'
            : 'https://s3.amazonaws.com/statics.addi.com/woocommerce/woocommerce-widget-wrapper-new.bundle.min.js';
        
        //TODO: Add test mode support when we finish the script for woocommerce in staging
        //$script_url = 'https://s3.amazonaws.com/statics.addi.com/woocommerce/woocommerce-widget-wrapper-new.bundle.min.js';

        wp_register_script('widget-addi', $script_url, array(), null, true);
        wp_enqueue_script('widget-addi', $script_url, array(), null, true);
        wp_localize_script('widget-addi', 'addiParams', array(
                'country' => $country,
                'home_url' => $home_url,
                'is_product' => $is_product,
                'testmode' => $test_mode
            )
        );
    }

    // Amplitude
    wp_enqueue_script('frontend-functions', plugins_url('/js/frontend-functions.js', __FILE__), array('jquery'), null, true);
    
    // Localize the script with plugin URL for asset loading
    wp_localize_script('frontend-functions', 'addiPlugin', array(
        'url' => plugins_url('/', __FILE__)
    ));
    
    // Enqueue styles.
    wp_enqueue_style('widget-addi-style', plugins_url('/css/style.css', __FILE__));
    // Add filters to catch and modify the styles and scripts as they're loaded.

    // Running this filter after everything to prevent conflicts with other plugins
    add_filter('script_loader_tag', __NAMESPACE__ . '\addi_add_home_banner', 100, 2);
}


/**
 * Custom status styles in admin site
 */
add_action('admin_head', function () { ?>
    <style>
        mark.order-status.status-addi-approved {
            background: #c6e1c6 !important;
            color: #5b841b !important;
        }
    </style>
<?php });

/**
 * Add the home banner script
 */
function addi_add_home_banner($html, $handle): string
{
    $gateways = WC()->payment_gateways->payment_gateways();
    $addi_options = isset($gateways['addi']) ? $gateways['addi'] : null;
    if ($addi_options->get_option('enabled') == 'yes' && $addi_options->get_option('widget_slug') != '' && $addi_options->get_option('home_banner_enabled') == 'yes') {
        switch ($addi_options->get_option('field_home_banner_position')) {
            case 'on_header':
                $data_element_reference = "header";
                break;
            case 'on_footer':
                $data_element_reference = "#site-content";
                break;
            case 'custom':
                $data_element_reference = $addi_options->get_option('element_reference');
                break;
        }
        switch ($handle)     {
            case 'widget-addi':
                $html = str_replace('></script>', ' data-ally-slug="' . $addi_options->get_option('widget_slug') . '" data-name="wooAddiHomeBanner" data-show-banner="true" data-banner-element-reference=' . $data_element_reference . ' data-banner-id="' . $addi_options->get_option('field_home_banner_type') . '"></script>', $html);
                break;
        }
    }
    return $html;
}

add_action('admin_enqueue_scripts', 'addi_selectively_enqueue_admin_script');

/**
 * Enqueue a script in the WordPress admin on edit.php.
 *
 * @param int $hook Hook suffix for the current admin page.
 */
function addi_selectively_enqueue_admin_script()
{
    global $wp;
    $current_url = home_url($_SERVER['REQUEST_URI']);

    if (strpos($current_url, 'page=wc-settings&tab=checkout&section=addi') == false) {
        return;
    }

    // loading js
    wp_register_script('addi-js', plugins_url('/js/functions.js', __FILE__), array(), null, true);
    wp_enqueue_script('addi-js');
    // localize script
    wp_localize_script('addi-js', 'addiPlugin', array(
        'url' => plugins_url('/', __FILE__)
    ));
    // Enqueue styles.
    wp_enqueue_style('addi-admin-style', plugins_url('/css/admin-style.css', __FILE__));

}

add_action('init', 'addi_load_textdomain');
/**
 * Load plugin textdomain.
 */
function addi_load_textdomain()
{
    load_plugin_textdomain('buy-now-pay-later-addi', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

add_action('init', 'verify_database');
/**
 * Verify if table exists or not in database
 */
function verify_database()
{
    global $wp;
    global $wpdb;

    if (!isset ($_COOKIE['database_validation'])) {

        $table_config_name = $wpdb->prefix . "wc_addi_config";
        //check if table already exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_config_name'") != $table_config_name) {

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_config_name (
                element varchar(50) NOT NULL,
                value varchar(50) NOT NULL
            ) $charset_collate;";

            require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            $wpdb->insert($table_config_name, array('element' => 'widget', 'value' => 'no|'));

        }

        if (!headers_sent()) {
            $secure = is_ssl();
            $expire = time() + (30 * DAY_IN_SECONDS);
            setcookie(
                'database_validation',
                'true',
                [
                    'expires' => $expire,
                    'path' => COOKIEPATH,
                    'domain' => COOKIE_DOMAIN,
                    'secure' => $secure,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        }
    }
}

add_filter('woocommerce_available_payment_gateways', 'woocommerce_available_payment_gateways');
function woocommerce_available_payment_gateways($available_gateways)
{
    if (!is_checkout())
        return $available_gateways;
    if (array_key_exists('addi', $available_gateways)) {
        if ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
            $available_gateways['addi']->order_button_text = __('Pagar com Addi', 'woocommerce');
        } else {
            $available_gateways['addi']->order_button_text = __('Paga con Addi', 'woocommerce');
        }
    }
    return $available_gateways;
}

add_action('init', 'intercept_checkout_url');
/**
 * Function to intercept checkout url with specific query param
 */
function intercept_checkout_url()
{

    global $woocommerce;
    global $wp;
    global $wpdb;

    // LOAD THE WC LOGGER
    $logger = wc_get_logger();

    $table_name = $wpdb->prefix . "wc_addi_gateway";

    if (isset ($_GET['wc-order-id'])) {

        $orderId = $_GET['wc-order-id'];

        // This id is registered on database, so it's needed to see its status.
        $result = $wpdb->get_results($wpdb->prepare("select * from {$table_name} where order_id = %d", $orderId));

        // verifying the integrity of the resulset, otherwise could throw an error.
        if (isset ($result) && count($result) > 0) {
            try {

                foreach ($result as $item) {
                    $order_id = $item->order_id;
                    $order_status = $item->order_status;
                }
            } catch (Exception $e) {
                $logger->info('Error getting data from database: ' . $e . ' ', array('source' => 'addi-error-handler-log'));
            }

            if (isset ($order_id) && isset ($order_status)) {
                if ($order_status == 'APPROVED') {

                    $wpdb->delete($table_name, array('order_id' => $orderId));

                    // get woocommerce order object
                    $order = wc_get_order($order_id);

                    $return_url = $order->get_checkout_order_received_url();
                    // Redirect to the thank you page
                    echo "<script>window.location = '" . $return_url . "';</script>";
                } else {

                    // get woocommerce order object
                    $order = wc_get_order($orderId);

                    // Get and Loop Over Order Items
                    foreach ($order->get_items() as $item_id => $item) {

                        //Get the WC_Product object
                        $product = $item->get_product();
                        $product_id = $product->get_id();
                        WC()->cart->generate_cart_id($product_id);
                        WC()->cart->add_to_cart($product_id);
                    }

                }
            }
        }
    }
}

add_action('init', 'register_custom_statuses_as_order_status');
function register_custom_statuses_as_order_status()
{
    register_post_status('wc-addi-approved', array(
        'label' => get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Transação Aprovada' : 'Transacción Aprobada',//__('Transacción Aprobada','buy-now-pay-later-addi'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? _n_noop('Transação Aprovada <span class="count">(%s)</span>', 'Transação Aprovada <span class="count">(%s)</span>') : _n_noop('Transaccion Aprobada <span class="count">(%s)</span>', 'Transaccion Aprobada <span class="count">(%s)</span>')
    ));

    register_post_status('wc-addi-declined', array(
        'label' => get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Transação Não Aprovada' : 'Transacción No Aprobada',//__('Transacción No Aprobada','buy-now-pay-later-addi'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? _n_noop('Transação Não Aprovada <span class="count">(%s)</span>', 'Transação Não Aprovada <span class="count">(%s)</span>') : _n_noop('Transaccion no Aprobada <span class="count">(%s)</span>', 'Transaccion no Aprobada <span class="count">(%s)</span>')
    ));
}

// Add to list of WC Order statuses
add_filter('wc_order_statuses', 'add_additional_custom_statuses_to_order_statuses');
function add_additional_custom_statuses_to_order_statuses($order_statuses)
{
    $new_order_statuses = array();
    // add new order status after processing
    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-addi-approved'] = get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Transação Aprovada' : 'Transacción Aprobada';//__('Transacción Aprobada','buy-now-pay-later-addi');
            $new_order_statuses['wc-addi-declined'] = get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Transação Não Aprovada' : 'Transacción No Aprobada';//__('Transacción No Aprobada','buy-now-pay-later-addi');
        }
    }
    return $new_order_statuses;
}

// Adding new custom status to admin order list bulk dropdown
add_filter('bulk_actions-edit-shop_order', 'addi_custom_dropdown_bulk_actions_shop_order', 50, 1);
function addi_custom_dropdown_bulk_actions_shop_order($actions)
{
    $new_actions = array();

    // add new order status before processing
    foreach ($actions as $key => $action) {
        if ('mark_processing' === $key)
            $new_actions['mark_addi-approved'] = get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Alterar status para Transação Aprovada' : 'Cambiar estado a Transacción Aprobada';//__( 'Change status to Transacción Aprobada', 'woocommerce' );
        $new_actions['mark_addi-declined'] = get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Alterar status para Transação Não Aprovada' : 'Cambiar estado a Transacción No Aprobada';//__( 'Change status to Transacción No Aprobada', 'woocommerce' );
        $new_actions[$key] = $action;
    }
    return $new_actions;
}

/*
 * This filter hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter('woocommerce_payment_gateways', 'addi_add_gateway_class');
function addi_add_gateway_class($gateways)
{
    $gateways[] = 'WC_Addi_Gateway';
    return $gateways;
}

$brazilCheckoutFieldspluginPath = 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php';
$checkoutFieldEditorAndManagerForWoocommercePath = 'checkout-field-editor-and-manager-for-woocommerce/start.php';
$fieldEditorForWoocommercePluginPath = 'woo-checkout-field-editor-pro/checkout-form-designer.php';
$yithWoocommerceCheckoutManagerPath = 'yith-woocommerce-checkout-manager/init.php';


//check if id field exists in wp_options
$id_field_exists = false;

$addi_settings = get_option('woocommerce_addi_settings', array());

// Check if field_id exists and is not empty in the settings
$id_field_exists = !empty($addi_settings['field_id']) && trim($addi_settings['field_id']) !== '';

if (
    is_plugin_active($fieldEditorForWoocommercePluginPath) ||
    is_plugin_active($checkoutFieldEditorAndManagerForWoocommercePath) ||
    is_plugin_active($brazilCheckoutFieldspluginPath) ||
    is_plugin_active($yithWoocommerceCheckoutManagerPath) ||
    $id_field_exists == true
) {
    /**
     * Denial do not work for this hook, so it is necessary this piece of code to avoid errors.
     */
} else {
    /* Register activation hook. */
    add_filter('woocommerce_checkout_fields', 'addi_custom_woocommerce_billing_fields');

}

/**
 * Insert new billing cedula field if it does not exists
 *
 * @param string $fields     array of objects containing checkout fields.
 *
 * @return array             new array of checkout fields.
 */
function addi_custom_woocommerce_billing_fields($fields)
{
    $newFields = $fields;

    $fieldFound = false;

    // new array based on billings array
    $newFields['billing'] = array();

    //loop in array to verify if billing_cedula field exists or not
    foreach ($fields['billing'] as $key1 => $billing) {

        if ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {

            if (!class_exists('Extra_Checkout_Fields_For_Brazil')) {
                if (strpos($key1, 'billing_cpf') !== false || strpos($key1, 'billing_id') !== false) {

                    $fieldFound = true;
                    break;

                }
            } else {
                $fieldFound = true;
            }

        } else {

            if (
                strpos($key1, 'billing_cedula') !== false ||
                strpos($key1, 'billing_id') !== false ||
                strpos($key1, 'billing_nmero') !== false ||
                strpos($key1, 'billing_numero') !== false
            ) {

                $fieldFound = true;
                break;

            }

        }
    }

    // if the field does not exists, then insert it before billing_last_name field
    if (!$fieldFound) {

        foreach ($fields['billing'] as $key1 => $billing) {
            $newFields['billing'][$key1] = $billing;
            if (strpos($key1, 'billing_last_name') !== false) {

                if ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
                    $newFields['billing']['billing_cpf'] = array(
                        'label' => __('CPF', 'buy-now-pay-later-addi'), // Add custom field label
                        'placeholder' => _x('', 'placeholder', 'buy-now-pay-later-addi'), // Add custom field placeholder
                        'required' => true, // if field is required or not
                        'clear' => false, // add clear or not
                        'type' => 'text', // add field type
                        'class' => array('form-row-wide')   // add class name
                    );
                } else {
                    $newFields['billing']['billing_id'] = array(
                        'label' => __('Cédula', 'buy-now-pay-later-addi'), // Add custom field label
                        'placeholder' => _x('', 'placeholder', 'buy-now-pay-later-addi'), // Add custom field placeholder
                        'required' => true, // if field is required or not
                        'clear' => false, // add clear or not
                        'type' => 'text', // add field type
                        'class' => array('form-row-wide')   // add class name
                    );
                }

            }
        }
        ;
    }

    return $newFields;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action('plugins_loaded', 'addi_init_gateway_class');

/**
 * Init plugin class
 */
function addi_init_gateway_class()
{

    // verify if woocommerce was installed, this plugin can´t extends its components if there are not classes in wordpress installation.
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Please install and Activate WooCommerce.', 'woocommerce-addon-slug'), 'Plugin dependency check', array('back_link' => true));
    }

    if (!class_exists('WC_Payment_Gateway'))
        return; // if the WC payment gateway class is not available, do nothing

    include (plugin_dir_path(__FILE__) . 'includes/class-wc-addi-gateway.php');

}

// Adding cancellation / refund hooks

function get_addi_auth()
{
    $addi_options = get_option('woocommerce_addi_settings');

    $api_selected = $addi_options['testmode'] ? ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://api.addi-staging-br.com' : 'https://api.staging.addi.com') : ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://api.addi.com.br' : 'https://api.addi.com');

    $body_auth = [
        'audience' => $api_selected,
        'grant_type' => 'client_credentials',
        'client_id' => $addi_options['prod_client_id'],
        'client_secret' => $addi_options['prod_client_secret'],
    ];

    $body_auth = wp_json_encode($body_auth);

    $options_auth = [
        'body' => $body_auth,
        'headers' => [
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
        ],
        'timeout' => 60,
        'data_format' => 'body',
    ];

    // getting api url based on test mode checkbox
    $auth_app_url = $addi_options['testmode'] ? ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')
        ? 'https://auth.addi-staging-br.com/oauth/token' : 'https://auth.addi-staging.com/oauth/token')
        : ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://auth.addi.com.br/oauth/token'
            : 'https://auth.addi.com/oauth/token');

    return wp_remote_post($auth_app_url, $options_auth);
}

function get_addi_base_url()
{
    $addi_options = get_option('woocommerce_addi_settings');
    $base_url = $addi_options['testmode'] ?
        ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ?
            'https://api.addi-staging-br.com/v1/' : 'https://api.addi-staging.com/v1/') :
        ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ?
            'https://api.addi.com.br/v1/' : 'https://api.addi.com/v1/');
    return $base_url;
}

function addi_order_cancelled($order_id)
{
    // Order cancellation hook

    // Check if the feature is enabled
    $addi_options = get_option('woocommerce_addi_settings');
    if ($addi_options['allow_refunds'] == 'no') {
        return;
    }

    $order = new WC_Order($order_id);
    $payment_method = $order->get_payment_method();
    if ($payment_method == 'addi') {
        // Should call our cancellation API here
        $auth_response = get_addi_auth();
        $body_auth_response = json_decode($auth_response['body'], true);
        if (!is_array($body_auth_response)) {
            $body_auth_response = array();
        }

        $denied = in_array("access_denied", $body_auth_response) || in_array("Unauthorized", $body_auth_response);
        if (!is_wp_error($auth_response) && !$denied) {
            cancel_addi_order($order, $body_auth_response, $order->get_total());
        }
    }
}

function addi_order_refunded($order_id, $refund_id)
{
    // Order cancellation hook

    $addi_options = get_option('woocommerce_addi_settings');
    if ($addi_options['allow_refunds'] == 'no') {
        return;
    }

    $order = new WC_Order($order_id);
    $refund = new WC_Order_Refund($refund_id);
    $payment_method = $order->get_payment_method();

    if ($payment_method == 'addi') {
        // Should call our cancellation API here
        $auth_response = get_addi_auth();
        $body_auth_response = json_decode($auth_response['body'], true);
        if (!is_array($body_auth_response)) {
            $body_auth_response = array();
        }

        $denied = in_array("access_denied", $body_auth_response) || in_array("Unauthorized", $body_auth_response);
        if (!is_wp_error($auth_response) && !$denied) {
            cancel_addi_order($order, $body_auth_response, $refund->get_amount());
        }
    }
}

// Custom function to declare compatibility with cart_checkout_blocks feature
function declare_cart_checkout_blocks_compatibility_addi() {
    // Check if the required class exists
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        // Declare compatibility for 'cart_checkout_blocks'
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
}

//Register addi as payment method on blocks
function register_addi_payment_block() {
    // Check if the required class exists
    if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        return;
    }
    // Include the custom Blocks Checkout class
    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-addi-gateway-block.php';
    // Hook the registration function to the 'woocommerce_blocks_payment_method_type_registration' action
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            // Register an instance of the addi block
            $payment_method_registry->register( new WC_Addi_Gateway_Blocks );
        }
    );
}

function addi_register_id_number_field() {
    $field_options = array(
        'id'            => 'addi/cedula-id',
        'label'         => 'Número de documento (Cédula)',
        'location'      => 'address',
        'required'      => true,
        'attributes'    => array(
            'autocomplete'     => 'cedula-id',
            'pattern'          => '^(?:[89]\d{8}|[12]\d{9}|\d{6,8})$',
            'title'            => 'Número de documento (Cédula)',
        ),
    );

    // Check if we can use the latest WooCommerce checkout field registration
    if ( version_compare( WC_VERSION, '8.9.0', '>=' ) ) {
        woocommerce_register_additional_checkout_field( $field_options );
        return;
    }

    // Add admin warnings for older WooCommerce versions
    add_action('admin_notices', 'addi_wc_version_warning');
    add_filter('plugin_row_meta', 'addi_plugin_row_meta', 10, 2);

    // Check if experimental function is available
    if ( function_exists( '__experimental_woocommerce_blocks_register_checkout_field' ) ) {
        __experimental_woocommerce_blocks_register_checkout_field( $field_options );
        return;
    }

    // Fallback for very old versions - add the field using WooCommerce hooks
    add_filter( 'woocommerce_checkout_fields', function( $fields ) use ( $field_options ) {
        $fields['billing']['billing_cedula'] = array(
            'type'          => 'text',
            'label'         => $field_options['label'],
            'required'      => $field_options['required'],
            'class'         => array('form-row-wide'),
            'autocomplete'  => $field_options['attributes']['autocomplete'],
            'custom_attributes' => array(
                'pattern' => $field_options['attributes']['pattern'],
                'title'   => $field_options['attributes']['title'],
            ),
        );
        return $fields;
    });
}

// Hook the custom function to the 'before_woocommerce_init' action
add_action('before_woocommerce_init', 'declare_cart_checkout_blocks_compatibility_addi');
// Hook the custom function to the 'woocommerce_blocks_loaded' action
add_action( 'woocommerce_blocks_loaded', 'register_addi_payment_block' );
// Hook for add the id number field for checkout blocks
add_action('woocommerce_init', 'addi_register_id_number_field');

/**
 * Handle AJAX request to dismiss the version warning
 */
function dismiss_addi_version_warning() {
    $user_id = get_current_user_id();
    update_user_meta($user_id, 'addi_wc_version_warning_dismissed', true);
    wp_die();
}
add_action('wp_ajax_dismiss_addi_version_warning', 'dismiss_addi_version_warning');

/**
 * Display admin notice for WooCommerce version compatibility
 */
function addi_wc_version_warning() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        return;
    }

    // Check if user has already dismissed the notice
    $user_id = get_current_user_id();
    if (get_user_meta($user_id, 'addi_wc_version_warning_dismissed', true)) {
        return;
    }

    echo '<div class="notice notice-warning is-dismissible" id="addi-version-warning">
        <p>' . sprintf(
            __('ADDI Payment Gateway: Tu versión de WooCommerce (%s) es anterior a la versión recomendada (8.9.0). Aunque el plugin seguirá funcionando, te recomendamos actualizar WooCommerce para tener la mejor experiencia con el metodo de pago.', 'buy-now-pay-later-addi'),
            WC_VERSION
        ) . '</p>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $(document).on("click", "#addi-version-warning .notice-dismiss", function() {
            $.ajax({
                url: ajaxurl,
                data: {
                    action: "dismiss_addi_version_warning"
                }
            });
        });
    });
    </script>';
}

/**
 * Add version compatibility notice to plugin meta row
 */
function addi_plugin_row_meta($links, $file) {
    if (plugin_basename(__FILE__) !== $file) {
        return $links;
    }

    if (version_compare(WC_VERSION, '8.9.0', '<')) {
        $row_meta = array(
            'version_warning' => '<span style="color:rgb(129, 0, 0);">' . __('⚠️ Recomendación: Tener la versión 8.9.0 o superior de WooCommerce. Tu versión actual es: '. WC_VERSION, 'buy-now-pay-later-addi') . '</span>'
        );
        return array_merge($links, $row_meta);
    }

    return $links;
}

function cancel_addi_order($order, $auth, $amount)
{
    $cancel_order_params = [
        'orderId' => $order->get_id(),
        'amount' => number_format($amount, 1, '.', ''),
    ];


    $body_cancel_order = wp_json_encode($cancel_order_params);

    $options_online_application = [
        'body' => $body_cancel_order,
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $auth['access_token'],
        ],
        'timeout' => 100,
        'data_format' => 'body',
    ];

    // getting api url based on test mode checkbox
    $online_app_url = get_addi_base_url() . 'online-applications/cancellations';

    // request
    $cancel_application_response = wp_remote_post($online_app_url, $options_online_application);

    // verify if body response is an error or contains data
    $body_cancel_application_response = json_decode($cancel_application_response['body'], true);

    if (!is_array($body_cancel_application_response)) {
        $body_cancel_application_response = array();
    }

    //    $logger = wc_get_logger();
    //    $logger->info('Order cancellation endpoint:  ' . $online_app_url , array( 'source' => 'addi-debug-logger' ) );
    //    $logger->info('Order cancellation request:  ' . json_encode($body_cancel_order) , array( 'source' => 'addi-debug-logger' ) );
    //    $logger->info('Order cancellation response:  ' . $cancel_application_response['body'] , array( 'source' => 'addi-debug-logger' ) );
    //    $logger->info('Order cancellation response code:  ' . wp_remote_retrieve_response_code($cancel_application_response), array( 'source' => 'addi-debug-logger' ) );
}

add_action('woocommerce_order_status_cancelled', 'addi_order_cancelled');
add_action('woocommerce_order_refunded', 'addi_order_refunded', 10, 2);

function bnpn_new_title() {
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            //Function to update the label text without modify the logo
            function updatePaymentLabel() {
                const label = document.querySelector('label[for="payment_method_addi"]') || document.querySelector('span[for="payment_method_addi"]');
                if (label) {
                    //Make sure the first text node on the label is the text we want to change to avoid changes in other part of the DOM
                    const firstChild = label.childNodes[0];
                    if (firstChild && firstChild.nodeType === Node.TEXT_NODE) {
                        firstChild.textContent = 'Paga con Addi ';
                    }
                }
            }

            updatePaymentLabel();

            //Configure MutationObserver to observe changes in the body
            const observer = new MutationObserver((mutationsList) => {
                for (let mutation of mutationsList) {
                    if (mutation.type === 'childList' || mutation.type === 'subtree') {
                        updatePaymentLabel();
                    }
                }
            });

            //Observe changes in the body
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>
    <?php
}

/**
 * Encrypts a credential string using AES-256-CBC with the site's SECURE_AUTH_KEY.
 * Stores IV prepended to ciphertext, base64-encoded.
 */
function addi_encrypt_credential($value) {
    $key    = hash('sha256', SECURE_AUTH_KEY, true);
    $iv_len = openssl_cipher_iv_length('AES-256-CBC');
    $iv     = openssl_random_pseudo_bytes($iv_len);
    $encrypted = openssl_encrypt($value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypts a value previously encrypted with addi_encrypt_credential().
 * Returns false on any failure (wrong key, corrupted data, empty input).
 */
function addi_decrypt_credential($value) {
    if (empty($value)) {
        return false;
    }
    $data = base64_decode($value, true);
    if ($data === false) {
        return false;
    }
    $iv_len    = openssl_cipher_iv_length('AES-256-CBC');
    $iv        = substr($data, 0, $iv_len);
    $encrypted = substr($data, $iv_len);
    $key       = hash('sha256', SECURE_AUTH_KEY, true);
    $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return ($decrypted === false) ? false : $decrypted;
}

/**
 * Encrypts and saves callback credentials to wp_options.
 */
function addi_store_callback_credentials($user, $password) {
    update_option('addi_callback_user',     addi_encrypt_credential($user));
    update_option('addi_callback_password', addi_encrypt_credential($password));
}

/**
 * Retrieves and decrypts callback credentials from wp_options.
 * Returns ['user' => '...', 'password' => '...'] or false if unavailable or corrupt.
 */
function addi_get_callback_credentials() {
    $enc_user     = get_option('addi_callback_user',     false);
    $enc_password = get_option('addi_callback_password', false);

    if ($enc_user === false || $enc_password === false) {
        return false;
    }

    $user     = addi_decrypt_credential($enc_user);
    $password = addi_decrypt_credential($enc_password);

    if ($user === false || $password === false) {
        return false;
    }

    return array('user' => $user, 'password' => $password);
}

/**
 * Fetches callback credentials from Addi's API using a valid Bearer JWT.
 * Returns ['user' => '...', 'password' => '...'] on success, false on any failure.
 */
function addi_fetch_callback_credentials($access_token) {
    $url      = get_addi_base_url() . 'online-applications/callback-credentials';
    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Accept'        => 'application/json',
        ),
        'timeout' => 60,
    ));

    if (is_wp_error($response)) {
        return false;
    }

    if (wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['user'], $body['password'])) {
        return false;
    }

    return array('user' => $body['user'], 'password' => $body['password']);
}

add_action('admin_notices', 'addi_callback_credentials_notice');
/**
 * Shows a one-time admin warning when credential fetch failed on settings save.
 */
function addi_callback_credentials_notice() {
    if (!get_transient('addi_callback_credentials_warning')) {
        return;
    }
    if (!current_user_can('manage_woocommerce')) {
        return;
    }
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php _e('Addi: Hubo un problema al obtener las credenciales de notificación. El plugin seguirá funcionando, pero por favor contacta a soporte de Addi.', 'buy-now-pay-later-addi'); ?></p>
    </div>
    <?php
    delete_transient('addi_callback_credentials_warning');
}
