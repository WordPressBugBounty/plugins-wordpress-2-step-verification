<?php
/**
 * @var string $method
 * @var string $user_login
 * @var boolean $have_phone
 * @var boolean $have_backup_codes
 * @var array $emails
 * @var string $error_message
 * @var boolean $can_recovery
 * @var boolean $is_trusted
 * @var string $recovery_key
 * @var string $recovery_file
 *
 */
?>
<div class="form">
    <form name="recoverForm" method="post" action="" id="recovery-form">
        <input type="hidden" name="wp2sv_nonce" value="<?php echo wp_create_nonce('wp2sv_nonce'); ?>"/>
        <input type="hidden" name="wp2sv_recovery_key" value="<?php echo $recovery_key ?>"/>
        <input type="hidden" name="wp2sv_type" value="<?php echo $method; ?>"/>
        <div class="title"><?php _e('Upload an HTML file to your site','wordpress-2-step-verification');?></div>
        <div class="desc">
            <ol>
                <li><b><?php _e('Download','wordpress-2-step-verification');?></b> <input class="link" type="submit" name="wp2sv_recovery_download" value="<?php _e('this HTML verification file','wordpress-2-step-verification');?>"> <span class="help">[<?php echo $recovery_file;?>]</span></li>
                <li><?php
					/* translators: %s is replaced with wp-config.php */
                    printf(__('<b>Upload</b> the file to Wordpress directory (same directory with %s)','wordpress-2-step-verification'),'<span>wp-config.php</span>');?></li>
                <li><?php _e('<b>Click</b> Verify below','wordpress-2-step-verification');?></li>
            </ol>
        </div>
        <?php if ($error_message): ?>
            <span class="error" id="error">
                <?php echo $error_message; ?>
            </span>
        <?php endif; ?>
        <input type="submit" name="wp2sv_recovery_verify" class="submit" value="<?php _e('Verify','wordpress-2-step-verification')?>">


    </form>

</div>
<?php include(dirname(__FILE__).'/others-link.php');?>
