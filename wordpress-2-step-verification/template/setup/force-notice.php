<?php
/**
 * Notice template
 * @since 2.6.0
 */
?>
<div id="wp2sv-force-notice" class="wp2sv-force-popup">
    <div class="wp2sv-force-popup-overlay"></div>
    <div class="wp2sv-force-popup-content">
        <div class="wp2sv-force-popup-header">
            <div class="wp2sv-force-popup-title"><?php echo __('2-Step Verification', 'wordpress-2-step-verification') ?></div>
            <div class="wp2sv-force-popup-close"><a href="javascript:void(0)" onclick="jQuery('#wp2sv-force-notice').hide()">&times;</a></div>
        </div>
        <div class="wp2sv-force-popup-body">
            <div class="wp2sv-force-popup-message"><?php echo __('Please setup 2-Step Verification to continue.', 'wordpress-2-step-verification') ?></div>
            <div class="wp2sv-force-popup-actions">

            </div>
        </div>
    </div>
</div>