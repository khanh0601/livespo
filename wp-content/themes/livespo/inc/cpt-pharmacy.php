<?php
/**
 * Custom Post Type: Điểm Bán (Pharmacy)
 * Requirement B: Tìm điểm bán tích hợp Google Maps API
 */

if (!defined('ABSPATH')) {
	exit;
}

// 1. Tạo Taxonomy (Danh mục): Tỉnh / Thành phố
$pharmacy_location = tr_taxonomy('pharmacy_location');
$pharmacy_location->setHierarchical();
$pharmacy_location->setArgument('labels', [
    'name' => 'Tỉnh/Thành',
    'singular_name' => 'Tỉnh/Thành',
    'menu_name' => 'Tỉnh/Thành',
    'all_items' => 'Tất cả Tỉnh/Thành'
]);
$pharmacy_location->setArgument('meta_box_cb', false); // Tắt native category checkbox metabox để tránh crash trình duyệt


// 2. Tạo Taxonomy (Danh mục): Chuỗi nhà thuốc (Long Châu, An Khang...)
$pharmacy_chain = tr_taxonomy('pharmacy_chain');
$pharmacy_chain->setHierarchical();
$pharmacy_chain->setArgument('labels', [
    'name' => 'Chuỗi hệ thống',
    'singular_name' => 'Chuỗi hệ thống',
    'menu_name' => 'Chuỗi hệ thống',
    'all_items' => 'Tất cả Chuỗi'
]);

// 3. Khởi tạo Post Type: Pharmacy (Điểm Bán)
$pharmacy = tr_post_type('pharmacy');
$pharmacy->setIcon('dashicons-location-alt');
$pharmacy->setArgument('supports', ['title']); // Tắt the_content Editor cơ bản
$pharmacy->setArgument('labels', [
    'name' => 'Điểm bán',
    'singular_name' => 'Điểm bán',
    'menu_name' => 'Điểm bán',
    'all_items' => 'Tất cả Điểm bán',
    'add_new' => 'Thêm mới',
    'add_new_item' => 'Thêm Điểm bán mới'
]);
$pharmacy->addTaxonomy($pharmacy_location);
$pharmacy->addTaxonomy($pharmacy_chain);

// 4. Tạo Custom Meta Box nhập liệu cho Điểm Bán (Toạ độ, ĐC, SĐT)
$pharmacy_meta = tr_meta_box('Thông tin điểm bán')->apply($pharmacy);
$pharmacy_meta->setCallback(function () {
	$form = tr_form();

	echo '<style>
        /* Card Layout */
        .livespo-pharmacy-card {
            background: #ffffff;
            border: 1px solid #dcdde1;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .livespo-section-title {
            font-size: 15px;
            font-weight: 600;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f1f2f6;
            padding-bottom: 12px;
            margin-top: 0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .livespo-section-title span.dashicons {
            margin-right: 8px;
            color: #0984e3;
            font-size: 22px;
            width: 22px;
            height: 22px;
        }
        .livespo-help-text {
            color: #636e72;
            font-size: 13.5px;
            margin-bottom: 20px;
            background: #f8fbff;
            padding: 12px 16px;
            border-left: 4px solid #0984e3;
            border-radius: 4px;
            line-height: 1.5;
        }

        /* Tối ưu Input Fields (Nhìn xịn hơn) */
        .typerocket-container .control-label {
            font-weight: 600;
            color: #34495e;
            font-size: 13px;
            margin-bottom: 8px;
            display: block;
        }
        .typerocket-container input[type="text"],
        .typerocket-container input[type="number"],
        .typerocket-container .tr-link-search-input {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            color: #2d3436;
            background-color: #fcfcfc;
            border: 1px solid #ced6e0;
            border-radius: 6px;
            transition: all 0.25s ease-in-out;
            box-shadow: none !important;
        }
        .typerocket-container input[type="text"]:hover,
        .typerocket-container input[type="number"]:hover,
        .typerocket-container .tr-link-search-input:hover {
            border-color: #a4b0be;
            background-color: #ffffff;
        }
        .typerocket-container input[type="text"]:focus,
        .typerocket-container input[type="number"]:focus,
        .typerocket-container .tr-link-search-input:focus {
            border-color: #0984e3;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(9, 132, 227, 0.15) !important;
            outline: none;
        }
        
        /* Chỉnh lại margin cho field row của TypeRocket */
        .typerocket-container .tr-control-section {
            padding-bottom: 10px;
        }
    </style>';

	echo '<div style="padding: 5px;">';
	
	// 1. Thông tin cơ bản
	echo '<div class="livespo-pharmacy-card">';
	echo '<h3 class="livespo-section-title"><span class="dashicons dashicons-store"></span> Thông trang</h3>';
	echo '<div style="display: flex; gap: 20px; flex-wrap: wrap;">';
		echo '<div style="flex: 1 1 45%; min-width: 300px;">' . $form->text('ten_nha_thuoc')->setLabel('Tên nhà thuốc')->setAttribute('required', 'required') . '</div>';
		echo '<div style="flex: 1 1 45%; min-width: 300px;">' . $form->text('so_dien_thoai_lien_he')->setLabel('Số điện thoại liên hệ') . '</div>';
	echo '</div>';
	echo '<div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 15px;">';
		echo '<div style="flex: 2 1 60%; min-width: 300px;">' . $form->text('dia_chi_cu_the')->setLabel('Địa chỉ cụ thể') . '</div>';
		echo '<div style="flex: 1 1 35%; min-width: 300px;">' . $form->text('link_zalo')->setLabel('Link Zalo') . '</div>';
	echo '</div>';
	echo '</div>';

	// 2. Khu vực
	global $post;
	$saved_tinh = get_post_meta($post->ID, 'khu_vuc_tinh', true);
	$saved_huyen = get_post_meta($post->ID, 'khu_vuc_huyen', true);
	$saved_xa = get_post_meta($post->ID, 'khu_vuc_xa', true);

	$tinh_terms = get_terms(['taxonomy' => 'pharmacy_location', 'hide_empty' => false, 'parent' => 0]);

	echo '<div class="livespo-pharmacy-card">';
	echo '<h3 class="livespo-section-title"><span class="dashicons dashicons-location-alt"></span> Khu vực phân bổ (Bắt buộc)</h3>';
	echo '<div class="livespo-help-text">Vui lòng chọn lần lượt từ Tỉnh/Thành đến Phường/Xã. Hệ thống sẽ tự động tải các danh sách tương ứng.</div>';
	
	echo '<div style="display: flex; gap: 20px; flex-wrap: wrap;">';
		// Tỉnh/Thành
		echo '<div style="flex: 1 1 30%; min-width: 200px;">';
		echo '<label class="control-label">Tỉnh / Thành phố</label>';
		echo '<select name="tr[khu_vuc_tinh]" id="sel_tinh" style="width:100%; padding:8px; border-radius:4px; border: 1px solid #ced6e0;">';
		echo '<option value="">-- Chọn Tỉnh/Thành --</option>';
		foreach ($tinh_terms as $t) {
			$sel = ($saved_tinh == $t->term_id) ? 'selected' : '';
			echo '<option value="'.$t->term_id.'" '.$sel.'>'.$t->name.'</option>';
		}
		echo '</select></div>';

		// Quận/Huyện
		echo '<div style="flex: 1 1 30%; min-width: 200px;">';
		echo '<label class="control-label">Quận / Huyện</label>';
		echo '<select name="tr[khu_vuc_huyen]" id="sel_huyen" style="width:100%; padding:8px; border-radius:4px; border: 1px solid #ced6e0;">';
		echo '<option value="">-- Chọn Quận/Huyện --</option>';
		if ($saved_tinh) {
			$huyen_terms = get_terms(['taxonomy' => 'pharmacy_location', 'hide_empty' => false, 'parent' => $saved_tinh]);
			foreach ($huyen_terms as $h) {
				$sel = ($saved_huyen == $h->term_id) ? 'selected' : '';
				echo '<option value="'.$h->term_id.'" '.$sel.'>'.$h->name.'</option>';
			}
		}
		echo '</select></div>';

		// Phường/Xã
		echo '<div style="flex: 1 1 30%; min-width: 200px;">';
		echo '<label class="control-label">Phường / Xã</label>';
		echo '<select name="tr[khu_vuc_xa]" id="sel_xa" style="width:100%; padding:8px; border-radius:4px; border: 1px solid #ced6e0;">';
		echo '<option value="">-- Chọn Phường/Xã --</option>';
		if ($saved_huyen) {
			$xa_terms = get_terms(['taxonomy' => 'pharmacy_location', 'hide_empty' => false, 'parent' => $saved_huyen]);
			foreach ($xa_terms as $x) {
				$sel = ($saved_xa == $x->term_id) ? 'selected' : '';
				echo '<option value="'.$x->term_id.'" '.$sel.'>'.$x->name.'</option>';
			}
		}
		echo '</select></div>';
	echo '</div>';

	// JS logic cho AJAX Dropdowns
	echo '<script>
	jQuery(document).ready(function($) {
		var ajaxurl = "'.admin_url('admin-ajax.php').'";
		$("#sel_tinh").on("change", function() {
			var pid = $(this).val();
			$("#sel_huyen").html("<option>Đang tải...</option>");
			$("#sel_xa").html("<option value=\"\">-- Chọn Phường/Xã --</option>");
			if(!pid) { $("#sel_huyen").html("<option value=\"\">-- Chọn Quận/Huyện --</option>"); return; }
			
			$.post(ajaxurl, { action: "get_location_children", parent_id: pid }, function(res) {
				if(res.success) {
					var html = "<option value=\"\">-- Chọn Quận/Huyện --</option>";
					$.each(res.data, function(i, v) { html += "<option value=\""+v.term_id+"\">"+v.name+"</option>"; });
					$("#sel_huyen").html(html);
				}
			});
		});
		
		$("#sel_huyen").on("change", function() {
			var pid = $(this).val();
			$("#sel_xa").html("<option>Đang tải...</option>");
			if(!pid) { $("#sel_xa").html("<option value=\"\">-- Chọn Phường/Xã --</option>"); return; }
			
			$.post(ajaxurl, { action: "get_location_children", parent_id: pid }, function(res) {
				if(res.success) {
					var html = "<option value=\"\">-- Chọn Phường/Xã --</option>";
					$.each(res.data, function(i, v) { html += "<option value=\""+v.term_id+"\">"+v.name+"</option>"; });
					$("#sel_xa").html(html);
				}
			});
		});
	});
	</script>';
	
	echo '</div>';

	// 3. Bản đồ
	echo '<div class="livespo-pharmacy-card">';
	echo '<h3 class="livespo-section-title"><span class="dashicons dashicons-location"></span> Toạ độ Bản đồ (GPS)</h3>';
	echo '<div class="livespo-help-text">Hệ thống dùng Vĩ độ/Kinh độ để định vị Pin trên Google Maps API. Có thể tra cứu toạ độ trên Google Maps.</div>';
	echo '<div style="display: flex; gap: 20px; flex-wrap: wrap;">';
		echo '<div style="flex: 1 1 45%; min-width: 300px;">' . $form->text('vi_do')->setLabel('Vĩ độ (Latitude)')->setType('number')->setAttribute('step', 'any')->setSetting('help', 'Ví dụ: 21.028511') . '</div>';
		echo '<div style="flex: 1 1 45%; min-width: 300px;">' . $form->text('kinh_do')->setLabel('Kinh độ (Longitude)')->setType('number')->setAttribute('step', 'any')->setSetting('help', 'Ví dụ: 105.804817') . '</div>';
	echo '</div>';
	echo '</div>';
	
	echo '</div>';
});

// 5. Hook: Đồng bộ khu vực (Search field meta -> Taxonomy terms)
add_action('save_post_pharmacy', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    // TypeRocket lưu dữ liệu các field name="tr[TÊN]" thành post meta. Lấy các meta này ra:
    $tinh = get_post_meta($post_id, 'khu_vuc_tinh', true);
    $huyen = get_post_meta($post_id, 'khu_vuc_huyen', true);
    $xa = get_post_meta($post_id, 'khu_vuc_xa', true);
    
    $terms_to_assign = [];
    if (!empty($tinh)) $terms_to_assign[] = (int) $tinh;
    if (!empty($huyen)) $terms_to_assign[] = (int) $huyen;
    if (!empty($xa)) $terms_to_assign[] = (int) $xa;
    
    // Lưu các relationships (Gắn Tỉnh, Huyện, Xã vào Điểm bán)
    wp_set_object_terms($post_id, $terms_to_assign, 'pharmacy_location', false);
    
    // Dọn dẹp trường meta search cũ để đỡ trùng logic
    delete_post_meta($post_id, 'khu_vuc');
});

// 6. Hook: API lấy danh sách Huyện/Xã con dựa vào parent_id
add_action('wp_ajax_get_location_children', function() {
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    
    if ($parent_id > 0) {
        $terms = get_terms([
            'taxonomy' => 'pharmacy_location', 
            'hide_empty' => false, 
            'parent' => $parent_id
        ]);
        
        $data = [];
        if (!is_wp_error($terms)) {
            foreach ($terms as $t) {
                $data[] = [
                    'term_id' => $t->term_id, 
                    'name' => $t->name
                ];
            }
        }
        wp_send_json_success($data); // Phản hồi AJAX
    }
    
    wp_send_json_error();
});
