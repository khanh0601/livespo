<?php
/**
 * Theme functions and definitions
 */

// Load TypeRocket (Bỏ comment khi bạn đã copy lại thư mục typerocket vào theme)
require get_template_directory() . '/typerocket/init.php';

// Đăng ký trang Theme Options cho TypeRocket
add_filter('tr_theme_options_page', function() {
    return get_template_directory() . '/theme-options.php';
});
add_filter('tr_theme_options_name', function() {
    return 'livespo_options'; //Tên options key trong Database
});

// Load file Tối ưu hoá tốc độ (Performance Configs)
require get_template_directory() . '/inc/performance.php';

// Khởi tạo Custom Post Types (Trang 2: Tìm Điểm bán Pharmacy)
require get_template_directory() . '/inc/cpt-pharmacy.php';

if (!function_exists('livespo_setup')):
  function livespo_setup()
  {
    // Khai báo thư mục chứa file dịch thuật (Bắt buộc cho web đa ngôn ngữ)
    load_theme_textdomain('livespo', get_template_directory() . '/languages');

    // Thêm hỗ trợ quản lý Title
    add_theme_support('title-tag');

    // Thêm hỗ trợ ảnh đại diện cho Post/Page
    add_theme_support('post-thumbnails');

    // Đăng ký menu
    register_nav_menus(array(
      'primary' => esc_html__('Primary Menu', 'livespo'),
    ));
  }
endif;
add_action('after_setup_theme', 'livespo_setup');

/**
 * Enqueue scripts and styles.
 */
function livespo_scripts() {
	// Khởi tạo tính năng Auto Cache-Busting (Tự động load lại cache khi file bị thay đổi)
	$theme_version = wp_get_theme()->get('Version');
	$css_version = file_exists(get_stylesheet_directory() . '/style.css') ? filemtime(get_stylesheet_directory() . '/style.css') : $theme_version;
	$js_version = file_exists(get_template_directory() . '/js/main.js') ? filemtime(get_template_directory() . '/js/main.js') : $theme_version;

	// CSS Load
	wp_enqueue_style( 'swiper-style', get_template_directory_uri() . '/css/swiper-bundle.min.css', array(), '11.0.0' );
	wp_enqueue_style( 'livespo-style', get_stylesheet_uri(), array(), $css_version );

	// JS Libraries
	wp_enqueue_script( 'gsap', get_template_directory_uri() . '/js/gsap.min.js', array(), '3.12.5', true );
	wp_enqueue_script( 'gsap-scrolltrigger', get_template_directory_uri() . '/js/ScrollTrigger.min.js', array('gsap'), '3.12.5', true );
	wp_enqueue_script( 'lenis', get_template_directory_uri() . '/js/lenis.min.js', array(), '1.1.2', true );
	wp_enqueue_script( 'swiper', get_template_directory_uri() . '/js/swiper-bundle.min.js', array(), '11.0.0', true );

	// Main JS (Tự động load cuối cùng)
	wp_enqueue_script( 'livespo-main', get_template_directory_uri() . '/js/main.js', array('gsap', 'gsap-scrolltrigger', 'lenis', 'swiper'), $js_version, true );

	// JS Chuyên biệt (Page-specific Scripts)
	// Tránh load Google Maps ở trang khác. Chỉ nạp khi vào CPT Mạng lưới điểm bán
	if ( is_post_type_archive('pharmacy') || is_singular('pharmacy') ) {
		$map_js_version = file_exists(get_template_directory() . '/js/pharmacy-map.js') ? filemtime(get_template_directory() . '/js/pharmacy-map.js') : $theme_version;
		wp_enqueue_script( 'livespo-pharmacy-map', get_template_directory_uri() . '/js/pharmacy-map.js', array(), $map_js_version, true );
		
		// Truyền biến PHP sang JS một cách bảo mật
		wp_localize_script( 'livespo-pharmacy-map', 'livespoMapData', array(
			// Lấy API Key từ Theme Options của TypeRocket. (Bạn có thể thêm 1 ô Google Map API Key vào theme-options.php nếu muốn).
			'apiKey'  => function_exists('tr_options_field') ? tr_options_field('livespo_options', 'Google Maps API Key') : 'NHAP_API_KEY_MAP_VAO_DAY',
			'ajaxUrl' => admin_url('admin-ajax.php')
		));
	}
}
add_action( 'wp_enqueue_scripts', 'livespo_scripts' );