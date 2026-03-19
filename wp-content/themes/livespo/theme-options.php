<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * TypeRocket Theme Options Form
 */

$form = tr_form();

// Bạn có thể đổi tên group thành bất cứ gì (mặc định lấy tên theme options option_name)
echo $form->useJson()->setGroup( 'livespo_options' );

?>

<h1>Cấu hình giao diện (Theme Options)</h1>

<div class="typerocket-container">
	<?php
	echo tr_tabs()->setSidebar([
		'Thông tin chung' => function() use ($form) {
			echo $form->image('Logo Website');
			echo $form->text('Hotline');
			echo $form->text('Email liên hệ');
			echo $form->textarea('Địa chỉ công ty');
		},
		'Mạng Xã Hội' => function() use ($form) {
			echo $form->text('Link Facebook');
			echo $form->text('Link Youtube');
			echo $form->text('Link Zalo');
		},
		'Chèn Code (Scripts)' => function() use ($form) {
			echo '<p>Dán mã nguồn Google Analytics, Facebook Pixel vào đây</p>';
			echo $form->textarea('Header Code')->setHelp('Code chèn trước thẻ &lt;/head&gt;');
			echo $form->textarea('Footer Code')->setHelp('Code chèn trước thẻ &lt;/body&gt;');
		}
	]);

	?>
</div>
