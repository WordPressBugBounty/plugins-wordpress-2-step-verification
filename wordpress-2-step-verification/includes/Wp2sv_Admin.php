<?php

class Wp2sv_Admin extends Wp2sv_Abstract
{

    function _construct()
    {
        add_action('edit_user_profile', array($this, 'editUserProfile'));
        add_action('edit_user_profile_update', array($this, 'editUserProfileUpdate'));
        add_filter('manage_users_columns', [$this, 'addUsersTableColumn']);
        add_filter('manage_users_custom_column', [$this, 'showUsersTableColumn'],10,3);
        new Wp2sv_Admin_Settings();
    }

    function showUsersTableColumn($val, $column_name, $user_id)
    {
        switch ($column_name) {
            case 'wp2sv_status' :
                return Wp2sv_Model::forUser($user_id)->status(false);
                break;
            default:
        }
        return $val;
    }

    function addUsersTableColumn($column)
    {
        $column['wp2sv_status'] = __('2-Step Verification','wordpress-2-step-verification');
        return $column;
    }

    function isEnabled($user_id)
    {
        $model = Wp2sv_Model::forUser($user_id);
        return $model->enabled;
    }

    function editUserProfileUpdate($user_id)
    {
        if (!current_user_can('edit_users'))
            return false;
        if ($this->post('wp2sv-turn-off')) {
            Wp2sv_Model::forUser($user_id)->disable();
        }
        return true;
    }

    function editUserProfile($user)
    {
        ?>
        <h3><?php _e('2-Step Verification', 'wordpress-2-step-verification'); ?></h3>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><?php _e('Status:', 'wordpress-2-step-verification'); ?></th>
                <td><label><?php echo Wp2sv_Model::forUser($user)->status(); ?></label>
                    <?php if ($this->isEnabled($user->ID)): ?>
                        <input type="hidden" name="wp2sv-turn-off" id="wp2sv-turn-off"/>
                        <input type="button" class="button" id="wp2sv-turn-off-button"
                               value="<?php _e('Turn off 2-step verification', 'wordpress-2-step-verification'); ?>"/>

                    <?php else:
                        ?>
                        <span class="description">
                <?php
                _e('Only the user can turn on 2-step verification', 'wordpress-2-step-verification');
                ?>
                </span>
                    <?php
                    endif; ?>
            </tr>
            </tbody>
        </table>
        <script type="text/javascript">
            if (typeof jQuery == 'function') {
                jQuery('#your-profile').ready(function ($) {
                    var turnOff;
                    turnOff = $("#wp2sv-turn-off").val('');
                    $("#wp2sv-turn-off-button").click(function () {
                        turnOff.val('');
                        if (!confirm('<?php _e('Are you sure to turn off 2-step verification? Only the user can turn it on again!', 'wordpress-2-step-verification')?>'))
                            return false;
                        turnOff.val('turn-off');
                        $("#submit").click();
                        return false;
                    })
                });
            }
        </script>
        <?php
    }


}
