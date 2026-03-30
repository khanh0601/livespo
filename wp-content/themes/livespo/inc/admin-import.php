<?php
// Thêm Submenu Page "Import CSV" vào dưới menu "Điểm bán"
add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=pharmacy',
        'Import Điểm Bán',
        'Import CSV Điểm Bán',
        'manage_options',
        'import-pharmacy-csv',
        'livespo_render_import_page'
    );
});

// Hàm dọn dẹp chuỗi (Fuzzy Match)
function livespo_fuzzy_clean($string)
{
    if (empty($string))
        return '';
    $string = mb_strtolower(trim($string), 'UTF-8');
    $string = remove_accents($string);
    $prefixes = [
        'thanh pho ',
        'tp. ',
        'tp ',
        'tinh ',
        'quan ',
        'q. ',
        'q ',
        'huyen ',
        'thi xa ',
        'phuong ',
        'p. ',
        'p ',
        'xa ',
        'thi tran ',
        'tt. ',
        'tt '
    ];
    foreach ($prefixes as $prefix) {
        if (strpos($string, $prefix) === 0) {
            $string = substr($string, strlen($prefix));
        }
    }
    $string = trim(preg_replace('/\s+/', ' ', $string));
    return $string;
}

// Hàm tìm Term ID
function livespo_find_location_term($raw_name, $parent_id = 0)
{
    if (empty($raw_name))
        return 0;

    $clean_input = livespo_fuzzy_clean($raw_name);
    $terms = get_terms([
        'taxonomy' => 'pharmacy_location',
        'hide_empty' => false,
        'parent' => $parent_id,
    ]);

    if (is_wp_error($terms))
        return 0;

    foreach ($terms as $term) {
        $clean_term_name = livespo_fuzzy_clean($term->name);
        if ($clean_input === $clean_term_name) {
            return $term->term_id;
        }
    }
    return 0;
}

// Giải quyết danh mục Chuỗi hệ thống
function livespo_resolve_chain($raw_chain)
{
    if (empty($raw_chain))
        return [];

    $term = term_exists($raw_chain, 'pharmacy_chain');
    if ($term !== 0 && $term !== null) {
        return [(int) $term['term_id']];
    }

    $insert = wp_insert_term($raw_chain, 'pharmacy_chain');
    if (!is_wp_error($insert)) {
        return [(int) $insert['term_id']];
    }
    return [];
}

function livespo_render_import_page()
{
    if (!current_user_can('manage_options'))
        return;

    // Xử lý upload form
    $message = '';
    $import_details = [];
    $success_count = 0;
    $failed_count = 0;

    if (isset($_POST['submit_import']) && check_admin_referer('livespo_import_csv_action')) {
        if (!empty($_FILES['csv_file']['tmp_name'])) {

            $file = $_FILES['csv_file']['tmp_name'];
            if (($handle = fopen($file, "r")) !== FALSE) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;
                    if ($row === 1)
                        continue; // Bỏ qua tiêu đề

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
                        $failed_count++;
                        $import_details[] = "<span style='color:red;'>[Lỗi Dòng $row]</span> Thiếu thông tin bắt buộc (Tên nhà thuốc / Tỉnh / Huyện).";
                        continue;
                    }

                    $tinh_id = livespo_find_location_term($tinh_str, 0);
                    $huyen_id = ($tinh_id > 0) ? livespo_find_location_term($huyen_str, $tinh_id) : 0;
                    $xa_id = ($huyen_id > 0) ? livespo_find_location_term($xa_str, $huyen_id) : 0;

                    if ($tinh_id === 0 || $huyen_id === 0) {
                        $failed_count++;
                        $import_details[] = "<span style='color:red;'>[Lỗi Dòng $row]</span> Không nhận diện được khu vực: <b>$tinh_str - $huyen_str</b>. Hãy kiểm tra lại lỗi chính tả.";
                        continue;
                    }

                    // Tạo bài viết
                    $post_data = array(
                        'post_title' => wp_strip_all_tags($ten_nha_thuoc),
                        'post_status' => 'publish',
                        'post_type' => 'pharmacy'
                    );
                    $post_id = wp_insert_post($post_data);

                    if (!is_wp_error($post_id)) {
                        update_post_meta($post_id, 'ten_nha_thuoc', $ten_nha_thuoc);
                        update_post_meta($post_id, 'so_dien_thoai_lien_he', $sdt);
                        update_post_meta($post_id, 'dia_chi_cu_the', $dia_chi);
                        update_post_meta($post_id, 'link_zalo', $zalo);
                        update_post_meta($post_id, 'vi_do', $lat);
                        update_post_meta($post_id, 'kinh_do', $lng);
                        update_post_meta($post_id, 'khu_vuc_tinh', $tinh_id);
                        update_post_meta($post_id, 'khu_vuc_huyen', $huyen_id);
                        update_post_meta($post_id, 'khu_vuc_xa', $xa_id);

                        $locations_to_assign = array_filter([$tinh_id, $huyen_id, $xa_id]);
                        wp_set_object_terms($post_id, $locations_to_assign, 'pharmacy_location', false);

                        $chains_to_assign = livespo_resolve_chain($chuoi_str);
                        if (!empty($chains_to_assign)) {
                            wp_set_object_terms($post_id, $chains_to_assign, 'pharmacy_chain', false);
                        }

                        $success_count++;
                        $import_details[] = "<span style='color:green;'>[THÀNH CÔNG] Dòng $row</span>: Đã import điểm bán '<b>$ten_nha_thuoc</b>'.";
                    } else {
                        $failed_count++;
                        $import_details[] = "<span style='color:red;'>[Lỗi Dòng $row]</span> Lỗi hệ thống khi chèn bài viết.";
                    }
                }
                fclose($handle);
                $message = "<div class='notice notice-success is-dismissible'><p><b>Hoàn tất cấu trình Import!</b> Thành công: $success_count | Lỗi/Bỏ qua: $failed_count</p></div>";
            } else {
                $message = "<div class='notice notice-error is-dismissible'><p>Không thể đọc file đã tải lên.</p></div>";
            }
        } else {
            $message = "<div class='notice notice-error is-dismissible'><p>Vui lòng chọn file CSV trước khi bấm Import.</p></div>";
        }
    }

    // Lấy URL file CSV mẫu
    $sample_csv_url = get_site_url() . '/pharmacies_sample.csv';

    // Hiển thị UI
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Công cụ Import Điểm Bán Hàng Loạt (CSV)</h1>
        <hr class="wp-header-end">

        <?php echo $message; ?>

        <div style="background: #fff; padding: 25px; border: 1px solid #ccd0d4; max-width: 800px; margin-top: 20px;">
            <h2>Tải lên File dữ liệu</h2>
            <p>Chọn file CSV chứa danh sách các cửa hàng để import. File phải tuân thủ đúng các cột tiêu chuẩn.</p>
            <p><a href="<?php echo esc_url($sample_csv_url); ?>" class="button button-secondary" download>📥 Tải về File CSV
                    Mẫu</a></p>

            <form method="post" enctype="multipart/form-data"
                style="margin: 20px 0; padding: 20px; border: 2px dashed #b4b9be; background: #f9f9f9; text-align: center;">
                <?php wp_nonce_field('livespo_import_csv_action'); ?>
                <input type="file" name="csv_file" accept=".csv" required style="margin-bottom: 15px;">
                <br>
                <button type="submit" name="submit_import" class="button button-primary button-hero">Tiến hành Import Dữ
                    liệu</button>
            </form>

            <?php if (!empty($import_details)): ?>
                <div
                    style="margin-top: 30px; background: #f0f0f1; padding: 15px; border-left: 4px solid #0073aa; max-height: 400px; overflow-y: auto;">
                    <h3 style="margin-top:0;">Chi tiết tiến trình:</h3>
                    <ul style="list-style: none; padding: 0; margin: 0; line-height: 1.8;">
                        <?php foreach ($import_details as $detail): ?>
                            <li><?php echo $detail; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

    </div>
    <?php
}
