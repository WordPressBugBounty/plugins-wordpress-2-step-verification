<?php
/**
 * Force Popup Template
 *
 * @since 2.6.0
 * @var $setup_url string
 * @var $dismissible bool
 */

?>
<div id="wp2sv-force-popup" class="wp2sv-force-popup">
    <div class="wp2sv-force-popup-overlay"></div>
    <div class="wp2sv-force-popup-content">
        <div class="wp2sv-force-popup-header">
            <div class="wp2sv-force-popup-title"><?php echo __('2-Step Verification', 'wordpress-2-step-verification') ?></div>
            <?php if($dismissible): ?>
            <div class="wp2sv-force-popup-close"><a href="javascript:void(0)" onclick="jQuery('#wp2sv-force-popup').hide()">&times;</a></div>
            <?php endif; ?>
        </div>
        <div class="wp2sv-force-popup-body">
            <div class="wp2sv-force-popup-message"><?php echo __('Please setup 2-Step Verification to continue.', 'wordpress-2-step-verification') ?></div>
            <div class="wp2sv-force-popup-actions">
                <a href="<?php echo $setup_url ?>" class="button button-primary"><?php echo __('Setup 2-Step Verification', 'wordpress-2-step-verification') ?></a>
            </div>
        </div>
    </div>
</div>