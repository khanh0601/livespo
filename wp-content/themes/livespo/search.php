<?php get_header(); ?>
<main id="main" class="site-main">
	<?php if ( have_posts() ) : ?>
		<header class="page-header">
			<h1 class="page-title">Kết quả tìm kiếm cho: <?php echo get_search_query(); ?></h1>
		</header>
		<?php
		while ( have_posts() ) :
			the_post();
			the_title( '<h2><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
			the_excerpt();
		endwhile;
	else :
		echo '<p>Không tìm thấy kết quả nào phù hợp.</p>';
	endif;
	?>
</main>
<?php get_footer(); ?>
