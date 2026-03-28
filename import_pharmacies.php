<?php
// Tên file: import_pharmacies.php
// Hướng dẫn: php import_pharmacies.php

if (php_sapi_name() !== 'cli') {
    die("Script này chỉ được chạy qua cửa sổ dòng lệnh (CLI). Vui lòng mở Terminal và dùng: php import_pharmacies.php\n");
}

require_once(dirname(__FILE__) . '/wp-load.php');

// Hàm chuẩn hoá chuỗi để fuzzy match (xoá dấu, xoá từ khoá hành chính, chuyển viết thường)
function livespo_fuzzy_clean($string) {
    if (empty($string)) return '';
    $string = mb_strtolower(trim($string), 'UTF-8');
    
    // Bỏ dấu Tiếng Việt
    $string = remove_accents($string);
    
    // Các tiền tố hành chính phổ biến cần loại bỏ để so khớp chính xác phần lõi
    $prefixes = [
        'thanh pho ', 'tp. ', 'tp ', 'tinh ',
        'quan ', 'q. ', 'q ', 'huyen ', 'thi xa ',
        'phuong ', 'p. ', 'p ', 'xa ', 'thi tran ', 'tt. ', 'tt '
    ];
    
    foreach ($prefixes as $prefix) {
        if (strpos($string, $prefix) === 0) {
            $string = substr($string, strlen($prefix));
        }
    }
    
    // Loại bỏ khoảng trắng thừa
    $string = trim(preg_replace('/\s+/', ' ', $string));
    return $string;
}

// Hàm tìm Term ID dựa vào fuzzy match & parent_id
function livespo_find_location_term($raw_name, $parent_id = 0) {
    if (empty($raw_name)) return 0;
    
    $clean_input = livespo_fuzzy_clean($raw_name);
    
    // Lấy tất cả term của cấp hiện tại (có cùng parent_id)
    $terms = get_terms([
        'taxonomy' => 'pharmacy_location',
        'hide_empty' => false,
        'parent' => $parent_id,
    ]);
    
    if (is_wp_error($terms)) return 0;
    
    foreach ($terms as $term) {
        $clean_term_name = livespo_fuzzy_clean($term->name);
        if ($clean_input === $clean_term_name) {
            return $term->term_id;
        }
    }
    return 0; // Không tìm thấy
}

// Giải quyết danh mục Chuỗi hệ thống
function livespo_resolve_chain($raw_chain) {
    if (empty($raw_chain)) return [];
    
    $term = term_exists($raw_chain, 'pharmacy_chain');
    if ($term !== 0 && $term !== null) {
        return [(int) $term['term_id']];
    }
    
    // Nếu chưa có, tạo mới
    $insert = wp_insert_term($raw_chain, 'pharmacy_chain');
    if (!is_wp_error($insert)) {
        return [(int) $insert['term_id']];
    }
    return [];
}

$csv_file = dirname(__FILE__) . '/pharmacies_sample.csv';

if (!file_exists($csv_file)) {
    die("Lỗi: Không tìm thấy file 'pharmacies_sample.csv' tại thư mục cùng cấp.\n");
}

echo "Bắt đầu đọc file CSV...\n";
if (($handle = fopen($csv_file, "r")) !== FALSE) {
    $row = 0;
    $success = 0;
    $failed = 0;
    
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $row++;
        if ($row === 1) continue; // Bỏ qua dòng tiêu đề Header
        
        // CSV columns mapping:
        // [0] ten_nha_thuoc, [1] so_dien_thoai, [2] dia_chi, [3] zalo_link
        // [4] tinh_thanh, [5] quan_huyen, [6] phuong_xa
        // [7] chuoi_he_thong, [8] latitude, [9] longitude
        
        $ten_nha_thuoc = $data[0] ?? '';
        $sdt = $data[1] ?? '';
        $dia_chi = $data[2] ?? '';
        $zalo = $data[3] ?? '';
        
        $tinh_str = $data[4] ?? '';
        $huyen_str = $data[5] ?? '';
        $xa_str = $data[6] ?? '';
        
        $chuoi_str = $data[7] ?? '';
        $lat = $data[8] ?? '';
        $lng = $data[9] ?? '';
        
        if (empty($ten_nha_thuoc) || empty($tinh_str) || empty($huyen_str)) {
            echo "[Bỏ qua Dòng $row] Thiếu thông tin bắt buộc (Tên nhà thuốc hoặc Tỉnh/Huyện).\n";
            $failed++;
            continue;
        }
        
        // Map Taxonomy Khu vực bằng Fuzzy matching tinh vi
        $tinh_id = livespo_find_location_term($tinh_str, 0);
        $huyen_id = ($tinh_id > 0) ? livespo_find_location_term($huyen_str, $tinh_id) : 0;
        $xa_id = ($huyen_id > 0) ? livespo_find_location_term($xa_str, $huyen_id) : 0;
        
        if ($tinh_id === 0 || $huyen_id === 0) {
            echo "[Lỗi Dòng $row] Không thể Map (so khớp) được Tỉnh hoặc Huyện dưới Database cho chuỗi: '$tinh_str - $huyen_str'\n";
            $failed++;
            continue;
        }
        
        // Khởi tạo bài viết CPT pharmacy
        $post_data = array(
            'post_title'    => wp_strip_all_tags($ten_nha_thuoc),
            'post_status'   => 'publish',
            'post_type'     => 'pharmacy'
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (!is_wp_error($post_id)) {
            // Lưu Post Meta (Format chuẩn TypeRocket)
            update_post_meta($post_id, 'ten_nha_thuoc', $ten_nha_thuoc);
            update_post_meta($post_id, 'so_dien_thoai_lien_he', $sdt);
            update_post_meta($post_id, 'dia_chi_cu_the', $dia_chi);
            update_post_meta($post_id, 'link_zalo', $zalo);
            update_post_meta($post_id, 'vi_do', $lat);
            update_post_meta($post_id, 'kinh_do', $lng);
            
            update_post_meta($post_id, 'khu_vuc_tinh', $tinh_id);
            update_post_meta($post_id, 'khu_vuc_huyen', $huyen_id);
            update_post_meta($post_id, 'khu_vuc_xa', $xa_id);
            
            // Set Taxonomies
            $locations_to_assign = array_filter([$tinh_id, $huyen_id, $xa_id]);
            wp_set_object_terms($post_id, $locations_to_assign, 'pharmacy_location', false);
            
            $chains_to_assign = livespo_resolve_chain($chuoi_str);
            if (!empty($chains_to_assign)) {
                wp_set_object_terms($post_id, $chains_to_assign, 'pharmacy_chain', false);
            }
            
            echo "[THÀNH CÔNG] Dòng $row: Đã tạo điểm bán '$ten_nha_thuoc'\n";
            $success++;
        } else {
            echo "[Lỗi Dòng $row] Lỗi hệ thống khi tạo bài viết DB.\n";
            $failed++;
        }
    }
    fclose($handle);
    
    echo "\n=== TIẾN TRÌNH IMPORT HOÀN TẤT ===\n";
    echo "Thành công: $success Điểm bán.\n";
    echo "Thất bại/Bỏ qua: $failed Điểm bán.\n";
}
