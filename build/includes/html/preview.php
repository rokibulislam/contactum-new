<?php
// Fallback template used by Form_Preview when the active theme has no
// page.php/single.php/index.php to hijack (e.g. a block/FSE theme like
// Twenty Twenty-Five, which ships only block templates) — without this,
// template_include() has nothing to return and WordPress outputs nothing.
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
?>
<?php wp_footer(); ?>
</body>
</html>
