<?php
/**
 * @var string $method
 * @var string $user_login
 * @var boolean $have_phone
 * @var boolean $have_backup_codes
 * @var array $emails
 * @var string $email_ending
 * @var string $error_message
 * @var boolean $can_recovery
 * @var boolean $is_trusted
 * @var string $recovery_key
 * @var string $recovery_file
 * @var string $emails_left The number of email can be send
 * @var boolean $has_emails_limit
 */
?>
<div class="form">
    <form name="verifyForm" method="post" action="" id="verify-form">
        <input type="hidden" name="wp2sv_nonce" value="<?php echo wp_create_nonce('wp2sv_nonce'); ?>"/>
        <input type="hidden" name="wp2sv_type" value="<?php echo $method; ?>"/>
        <input type="hidden" name="wp2sv_action" value="check-code"/>
        <?php switch($method){
            case 'phone':
                $img_src=wp2sv_assets('images/authenticator.png');
                break;
            case 'email':
                $img_src=wp2sv_assets('images/email.png');
                break;
            case 'backup-codes':
                $img_src=wp2sv_assets('images/backup.png');
                break;
            default:
                $img_src=wp2sv_assets('images/authenticator.png');

        }?>
        <img alt="method icon" class="mobile" src="<?php echo $img_src;?>">

        <div class="title">
            <?php if ($method == 'backup-codes') {
                _e('Enter your backup code', 'wordpress-2-step-verification');
            } else {
                _e('Enter a verification code', 'wordpress-2-step-verification');
            } ?>
        </div>

        <div class="desc" id="verifyText">
            <?php
            $max_code_length = 6;
            if ($method == 'mobile') {
                _e('Get a verification code from the <strong>Google Authenticator</strong> app.', 'wordpress-2-step-verification');
            } elseif (in_array($method, array('email', 'user_email'))) {
				/* translators: %s is replaced with email ending */
                printf(__('An email with a verification code was just sent to <strong>%s</strong>.', 'wordpress-2-step-verification'), $email_ending);
                if(!$error_message && $emails_left!=='' && $has_emails_limit){
					/* translators: %s is replaced with number of emails left */
					printf(' '.__('%s email(s) left', 'wordpress-2-step-verification'), $emails_left);
                }
            } elseif ($method == 'backup-codes') {
                $max_code_length = 8;
            }
            $placeholder = sprintf(__('Enter the %d-digit code', 'wordpress-2-step-verification'), $max_code_length);
            $inputTitle=__('Verification codes contain only numbers.', 'wordpress-2-step-verification');
            ?>
        </div>
        <div class="theinput">
            <input type="text" title="<?php echo $inputTitle;?>" pattern="[0-9 ]*" dir="ltr"
                   class="input" placeholder="<?php echo $placeholder; ?>" autocomplete="off" required
                   size="<?php echo $max_code_length; ?>" value="" id="code"
                   name="wp2sv_code">
        </div>
        <?php if ($error_message): ?>
            <span class="error" id="error">
                <?php echo $error_message; ?>
            </span>
        <?php endif; ?>
        <input type="submit" class="submit" value="<?php _e('Done', 'wordpress-2-step-verification'); ?>">

        <div class="padded" id="persistent-container">
            <input type="checkbox"<?php checked(true, $is_trusted) ?> value="yes"
                   id="PersistentCookie" name="wp2sv_remember">
            <label for="PersistentCookie">
                <?php _e('Remember this computer for 30 days.', 'wordpress-2-step-verification'); ?>
            </label>
        </div>

    </form>
</div>
<?php include(dirname(__FILE__).'/others-link.php');?>
