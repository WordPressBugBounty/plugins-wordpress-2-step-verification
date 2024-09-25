<div id="others">
    <form method="post">
        <input type="hidden" name="wp2sv_nonce" value="<?php echo wp_create_nonce('wp2sv_nonce'); ?>"/>
        <input type="hidden" name="wp2sv_type" value="others"/>
        <input class="other-link" type="submit" value="<?php echo esc_attr(__('Try another way to sign in','wordpress-2-step-verification'));?>">
    </form>
</div>
