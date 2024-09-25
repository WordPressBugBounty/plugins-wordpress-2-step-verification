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
 */
?>
<!doctype html>
<html>
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php _e('Enter verification code', 'wordpress-2-step-verification'); ?></title>
    <link href="<?php echo wp2sv_assets('css/wp2sv.css') ?>" rel="stylesheet">
    <?php do_action('wp2sv_head');?>
</head>
<body>
<?php

?>
<div id="wp2sv">
    <div class="header">
        <div class="logo-wrapper">
            <div class="logo"></div>
        </div>
        <div class="heading">
            <div class="h1">
                <h1><?php _e('2-Step Verification', 'wordpress-2-step-verification'); ?></h1>
            </div>
            <div class="h2">
                <?php switch ($method) {
                    case 'others':
                        $h2 = __('To sign in to your Wordpress Account, choose a task from the list below.', 'wordpress-2-step-verification');
                        break;
                    case 'recovery':
                        $h2 = __('To recover access to your Wordpress Account, complete the task below.', 'wordpress-2-step-verification');
                        break;
                    default:
                        $h2 = __('To help keep your account safer, complete the task below.', 'wordpress-2-step-verification');
                } ?>
                <h2><?php echo $h2; ?></h2>
            </div>
        </div>

    </div>
    <div class="container container-<?php echo $method;?>">
        <?php
        switch ($method) {
            case 'others':
                include(dirname(__FILE__) . '/form-others.php');
                break;
            case 'recovery':
                include(dirname(__FILE__) . '/form-recovery.php');
                break;
            default:
                include(dirname(__FILE__) . '/form-verify.php');
        }
        ?>
        <div class="current-user">
            <span class="tac"><?php echo $user_login; ?></span>
            <a href="<?php echo wp_logout_url(); ?>"
               class="logout"><?php _e('Use a different account', 'wordpress-2-step-verification'); ?></a>
        </div>

    </div>
	<?php do_action('wp2sv_footer');?>
</div>
</body>
</html>