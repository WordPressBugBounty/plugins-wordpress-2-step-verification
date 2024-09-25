<?php
/**
 * @var Wp2sv_Setup $this
 */
?>
<?php do_action('wp2sv_setup_header');?>
<div class="wrap wp2sv-setup wp2sv" id="wp2sv-setup">
    <?php do_action('wp2sv_setup_before_title') ?>
    <h1 class="wp2sv-setup-title"><?php _e('Wordpress 2-step verification', 'wordpress-2-step-verification') ?></h1>
    <?php do_action('wp2sv_setup_before') ?>
    <wp2sv-route></wp2sv-route>
    <?php do_action('wp2sv_setup_after') ?>
</div>
<?php do_action('wp2sv_setup_footer');?>





