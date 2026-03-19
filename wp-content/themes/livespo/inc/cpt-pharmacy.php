<?php
/**
 * Custom Post Type: Điểm Bán (Pharmacy)
 * Requirement B: Tìm điểm bán tích hợp Google Maps API
 */

if (!defined('ABSPATH')) {
	exit;
}

// 1. Tạo Taxonomy (Danh mục): Tỉnh / Thành phố
$pharmacy_location = tr_taxonomy('Tỉnh/Thành', 'pharmacy_location');
$pharmacy_location->setHierarchical()->setSearchable();

// 2. Tạo Taxonomy (Danh mục): Chuỗi nhà thuốc (Long Châu, An Khang...)
$pharmacy_chain = tr_taxonomy('Chuỗi hệ thống', 'pharmacy_chain');
$pharmacy_chain->setSearchable();

// 3. Khởi tạo Post Type: Pharmacy (Điểm Bán)
$pharmacy = tr_post_type('Điểm bán', 'pharmacy');
$pharmacy->setIcon('dashicons-location-alt');
$pharmacy->setArgument('supports', ['title']); // Tắt the_content Editor cơ bản
$pharmacy->addTaxonomy($pharmacy_location);
$pharmacy->addTaxonomy($pharmacy_chain);

// 4. Tạo Custom Meta Box nhập liệu cho Điểm Bán (Toạ độ, ĐC, SĐT)
$pharmacy_meta = tr_meta_box('Thông tin điểm bán')->apply($pharmacy);
$pharmacy_meta->setCallback(function () {
	$form = tr_form();

	echo tr_tabs()->setSidebar([
	'Thông tin trạm' => function () use ($form) {
		    echo $form->text('Địa chỉ cụ thể');
		    echo $form->text('Số điện thoại liên hệ');
	    }
	    ,
	    'Toạ độ Bản đồ (GPS)' => function () use ($form) {
		    echo '<p>Hệ thống dùng Vĩ độ/Kinh độ để định vị Pin trên Google Maps API.</p>';
		    echo $form->text('Vĩ độ (Latitude)')->setType('number')->setSetting('step', 'any');
		    echo $form->text('Kinh độ (Longitude)')->setType('number')->setSetting('step', 'any');
	    }
	    ]);
    });
