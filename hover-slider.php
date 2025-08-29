<?php
/**
 * Plugin Name: Hover Slider WordPress
 * Description: Image trail hover slider with media uploader, shortcode, font options, and drag-drop reordering. Use [hover_slider].
 * Version: 2.2
 * Author: WP DESIGN LAB
 */

if (!defined('ABSPATH')) exit;

class HoverSliderPlugin {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_shortcode('hover_slider', [$this, 'render_shortcode']);
    }

    /*-----------------------------------
     * Frontend Assets
     *-----------------------------------*/
    public function enqueue_assets() {
        $url  = plugin_dir_url(__FILE__);
        $path = plugin_dir_path(__FILE__);

        wp_enqueue_style('hs-base', $url . 'css/main-css-hover.css', [], filemtime($path . 'css/main-css-hover.css'));
        wp_enqueue_script('hs-imagesloaded', $url . 'js/imagesloaded.pkgd.min.js', [], null, true);
        wp_enqueue_script('hs-tweenmax', $url . 'js/TweenMax.min.js', [], null, true);
        wp_enqueue_script('hs-demo6', $url . 'js/hover-slider.js', ['hs-imagesloaded','hs-tweenmax'], filemtime($path . 'js/hover-slider.js'), true);

        // Load Google Fonts only if needed
        $font = get_option('hs_slider_font', 'inherit');
        $google_fonts = [
            'playfair'   => 'Playfair+Display',
            'roboto'     => 'Roboto',
            'lora'       => 'Lora',
            'oswald'     => 'Oswald',
            'montserrat' => 'Montserrat'
        ];
        if ($font !== 'inherit' && isset($google_fonts[$font])) {
            wp_enqueue_style('hs-font-'.$font, 'https://fonts.googleapis.com/css2?family='.$google_fonts[$font].':wght@400;700&display=swap', [], null);
        }

        // Add body classes (demo-6 loading)
        add_filter('body_class', function($classes) {
            $classes[] = 'hover-6';
            $classes[] = 'loading';
            return $classes;
        });
    }

    /*-----------------------------------
     * Admin Assets
     *-----------------------------------*/
    public function admin_assets($hook) {
        if ($hook !== 'toplevel_page_hover-slider') return;
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable'); // For drag-drop
        wp_enqueue_script('hs-admin', plugin_dir_url(__FILE__) . 'js/admin.js', ['jquery', 'jquery-ui-sortable'], false, true);
        wp_enqueue_style('hs-admin-style', plugin_dir_url(__FILE__) . 'css/admin.css');
    }

    /*-----------------------------------
     * Shortcode
     *-----------------------------------*/
    public function render_shortcode($atts) {
        $slides = get_option('hs_slides', []);
        $title  = get_option('hs_slider_title', 'My Slider');
        $font   = get_option('hs_slider_font', 'inherit');

        if (empty($slides)) return '<p>No images added yet.</p>';

        // Map font key to CSS font-family
        $font_map = [
            'inherit'    => 'inherit',
            'playfair'   => "'Playfair Display', serif",
            'roboto'     => "'Roboto', sans-serif",
            'lora'       => "'Lora', serif",
            'oswald'     => "'Oswald', sans-serif",
            'montserrat' => "'Montserrat', sans-serif"
        ];
        $font_family = isset($font_map[$font]) ? $font_map[$font] : 'inherit';

        ob_start(); ?>
        <main class="hover-me">
            <div class="content">
                <?php foreach ($slides as $slide):
                    $url = wp_get_attachment_url($slide['id']);
                    if (!$url) continue; ?>
                    <div class="content__img content__img--full" style="background-image:url('<?php echo esc_url($url); ?>');"></div>
                <?php endforeach; ?>
                <h3 class="content__title" style="font-family:<?php echo esc_attr($font_family); ?>">
                    <?php echo esc_html($title); ?>
                </h3>
            </div><!--/content-->
        </main>
        <?php
        return ob_get_clean();
    }

    /*-----------------------------------
     * Admin Page
     *-----------------------------------*/
    public function admin_menu() {
        add_menu_page('Hover Slider', 'Hover Slider', 'manage_options', 'hover-slider', [$this, 'admin_page'], 'dashicons-images-alt2');
    }

    public function admin_page() {
        $slides = get_option('hs_slides', []);
        $title  = get_option('hs_slider_title', '');
        $font   = get_option('hs_slider_font', 'inherit'); ?>
        <div class="wrap">
            <h1>Hover Slider Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('hs_settings_group'); ?>
                <?php do_settings_sections('hs_settings_group'); ?>

                <h2>Slider Title</h2>
                <input type="text" name="hs_slider_title" value="<?php echo esc_attr($title); ?>" placeholder="Enter slider title" style="width:300px;">
				
				
				<div class="notice notice-info" style="padding:15px; margin-bottom:20px;">
            <p><strong>Shortcode:</strong> Use <code>[hover_slider]</code> in any page, post, or widget to display your slider.</p>
        </div>
				

                <h2>Title Font</h2>
                <select name="hs_slider_font">
                    <option value="inherit" <?php selected($font, 'inherit'); ?>>Inherit Body Font</option>
                    <option value="playfair" <?php selected($font, 'playfair'); ?>>Playfair Display</option>
                    <option value="roboto" <?php selected($font, 'roboto'); ?>>Roboto</option>
                    <option value="lora" <?php selected($font, 'lora'); ?>>Lora</option>
                    <option value="oswald" <?php selected($font, 'oswald'); ?>>Oswald</option>
                    <option value="montserrat" <?php selected($font, 'montserrat'); ?>>Montserrat</option>
                </select>

                <h2>Images (Drag to Reorder)</h2>
                <div id="hs-images" class="hs-sortable">
                    <?php if (!empty($slides)): ?>
                        <?php foreach ($slides as $index => $slide): 
                            $url = wp_get_attachment_url($slide['id']); ?>
                            <div class="hs-image-row">
                                <input type="hidden" name="hs_slides[<?php echo $index; ?>][id]" value="<?php echo esc_attr($slide['id']); ?>">
                                <img src="<?php echo esc_url($url); ?>" style="width:80px;height:auto;">
                                <button type="button" class="button hs-remove">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <button type="button" class="button hs-add">Add Images</button>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting('hs_settings_group', 'hs_slides'); 
        register_setting('hs_settings_group', 'hs_slider_title'); 
        register_setting('hs_settings_group', 'hs_slider_font'); 
    }
}

new HoverSliderPlugin();
