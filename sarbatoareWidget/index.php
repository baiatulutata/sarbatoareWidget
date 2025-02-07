<?php
/**
 * Plugin Name: Sarbatoare Azi Widget
 * Description: Displays links and titles from sarbatoare.ro in a widget or via shortcode.
 * Version: 1.0.0
 * Author: Ionut Baldazar
 * Author URI:
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sarbatoare-azi
 * Domain Path: /languages
 */

// Include the settings page
require_once plugin_dir_path(__FILE__) . 'includes/sarbatoare-azi-settings.php';

// Widget
class Sarbatoare_Azi_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'sarbatoare_azi_widget',
            __('Sarbatoare Azi', 'sarbatoare-azi'),
            array('description' => __('Displays links and titles from sarbatoare.ro', 'sarbatoare-azi'))
        );
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $css = $instance['css'];

        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        echo '<div class="sarbatoare-azi-widget" style="' . esc_attr($css) . '">';
        $this->display_data();
        echo '</div>';

        echo $args['after_widget'];
    }
    private function get_cache_expiration() {
        $tomorrow8am = strtotime("tomorrow 8:00");
        $seconds_until_8am = $tomorrow8am - time();
        return $seconds_until_8am > 0 ? $seconds_until_8am : DAY_IN_SECONDS; // Cache for 24h if time already passed
    }
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $css = !empty($instance['css']) ? $instance['css'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'sarbatoare-azi'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('css'); ?>"><?php _e('Custom CSS:', 'sarbatoare-azi'); ?></label>
            <textarea class="widefat" id="<?php echo $this->get_field_id('css'); ?>" name="<?php echo $this->get_field_name('css'); ?>"><?php echo esc_attr($css); ?></textarea>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['css'] = (!empty($new_instance['css'])) ? $new_instance['css'] : '';
        return $instance;
    }

    public function display_data() {

        echo "<div class='sarbatoare'>
<h2>Sarbatori ".date("j M Y")."</h2>";
        echo "<ul>";
                $cache_key = 'sarbatoare_azi_data';
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            // Use cached data
            foreach ($cached_data as $item) {
                echo '<li><a href="' . esc_url("https://sarbatoare.ro/sinaxar/".$item['lnk']) . '" target="_blank" alt="sarbatoare azi">' . esc_html($item['titlu']) . '</a></li>';
            }
            return; // Exit early
        }

        $url = 'https://sarbatoare.ro/sarbatoare_azi.json';
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            echo __('Error fetching data.', 'sarbatoare-azi');
            return;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            echo __('Error decoding JSON data.', 'sarbatoare-azi');
            return;
        }

        if($data){
            set_transient($cache_key, $data, $this->get_cache_expiration()); // Cache the data


            foreach ($data as $item) {
                echo '<li><a href="' . esc_url("https://sarbatoare.ro/sinaxar/".$item['lnk']) . '" target="_blank" alt="sarbatoare azi">' . esc_html($item['titlu']) . '</a></li>';
            }
        } else {
            echo __('No data found.', 'sarbatoare-azi');
        }


        echo "</ul></div>";
    }
}

function register_sarbatoare_azi_widget() {
    register_widget('Sarbatoare_Azi_Widget');
}
add_action('widgets_init', 'register_sarbatoare_azi_widget');


// Shortcode
function sarbatoare_azi_shortcode($atts) {
    $atts = shortcode_atts( array(
        'class' => '',
    ), $atts );

    ob_start();
    echo '<div class="sarbatoare-azi-shortcode ' . esc_attr($atts['class']) . '">';
    $widget = new Sarbatoare_Azi_Widget();
    $widget->display_data();
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('sarbatoare_azi', 'sarbatoare_azi_shortcode');


// Load text domain
function sarbatoare_azi_load_textdomain() {
    load_plugin_textdomain('sarbatoare-azi', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'sarbatoare_azi_load_textdomain');

?>