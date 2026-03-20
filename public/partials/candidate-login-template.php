<?php if (!defined('ABSPATH')) exit; ?>
<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Template for Candidate Login Page
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>

</head>
<body <?php body_class('xh-page-login'); ?>>

<div class="xh-standalone-wrapper">
    <?php echo do_shortcode('[xenhire_candidate_login]'); ?>
</div>




<?php wp_footer(); ?>
</body>
</html>
