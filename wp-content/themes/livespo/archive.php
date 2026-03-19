<?php get_header(); ?>
<main id="main" class="site-main">
	<?php if ( have_posts() ) : ?>
		<header class="page-header">
			<?php
			the_archive_title( '<h1 class="page-title">', '</h1>' );
			the_archive_description( '<div class="archive-description">', '</div>' );
			?>
		</header>
		<?php
		while ( have_posts() ) :
			the_post();
			the_title( '<h2><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
			the_excerpt();
		endwhile;
	else :
		echo '<p>Không tìm thấy bài viết nào.</p>';
	endif;
	?>
</main>
<?php get_footer(); ?>
