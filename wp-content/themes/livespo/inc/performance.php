<?php
/**
 * Tối ưu hóa hiệu suất (Performance Optimization)
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Thêm thuộc tính defer/async vào thẻ script để không bị chặn render HTML
function livespo_defer_scripts($tag, $handle, $src)
{
    // Các file JS nặng cần load ngầm (defer)
    $defer_scripts = array('gsap', 'gsap-scrolltrigger', 'lenis', 'swiper', 'livespo-main');

    if (in_array($handle, $defer_scripts)) {
        // Trả về thẻ script có chứa thuộc tính defer
        return '<script src="' . esc_url($src) . '" defer="defer"></script>' . "\n";
    }

    return $tag;
}
add_filter('script_loader_tag', 'livespo_defer_scripts', 10, 3);

// 2. Xóa bỏ các mã rác/tracking mặc định của WordPress trên thẻ <head> làm trang bị chậm
function livespo_cleanup_wp_head()
{
    // Tắt Emoji JS/CSS (Làm nặng trang vô ích nếu không dùng emoji)
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');

    // Tắt các thẻ meta rác (Giúp HTML <head> cực kỳ sạch và tiết kiệm băng thông)
    remove_action('wp_head', 'wp_generator'); // Ẩn WP version
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
    remove_action('wp_head', 'rest_output_link_wp_head', 10);
    remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
    remove_action('wp_head', 'wp_oembed_add_host_js');
}
add_action('init', 'livespo_cleanup_wp_head');

// 3. Tắt CSS mặc định của Gutenberg Blocks. 
// Nếu bạn tự code UI hoàn toàn từ đầu và không dùng khối kéo thả của WP. Bỏ comment bên dưới:

add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-blocks-style');
    wp_dequeue_style('global-styles');
}, 100);
