<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://hparsa.ir
 * @since      1.1.0
 *
 * @author    Hosein Parsa
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
    <h2>تنظیم حافظه کش</h2>
    <form method="POST" action="<?php echo esc_url(admin_url().'/admin-post.php') ?>">
        <input type="hidden" name="action" value="delete_combine_style">

        <p class="submit"><input type="submit" name="deletestyle" id="deletestyle" class="button button-primary" value="پاکسازی کش"></p>
    </form>
    <form method="POST" action="<?php echo esc_url(site_url().'/wp-admin/admin-post.php') ?>">

        <input type="hidden" name="action" value="save_changes_combine_style">

        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row"><label for="timedeletestyle">زمان پاکسازی</label></th>
                <td>
                    <input name="timedeletestyle" type="number" id="timedeletestyle" aria-describedby="tagline-description" value="<?php if(isset($plugin_settings['time_clean_style'])){echo $plugin_settings['time_clean_style'];}else{echo '4000';}; ?>" class="regular-text" max="43200">
                    <p class="description" id="timedeletestyle">زمان را به دقیقه وارد نمایید.MAX:43200</p></td>
            </tr>
            <tr>
                <th scope="row"><label for="pluginstatus">فعالسازی</label></th>
                <td>
                    <fieldset><legend class="screen-reader-text"><span>فعالسازی</span></legend><label for="pluginstatus">
                            <input name="pluginstatus" type="checkbox" id="pluginstatus" value="1" <?php echo $plugin_settings['plugin_status'] == 1 ? 'checked' : '' ?> >فعالسازی</label>
                            <p class="description" id="pluginstatus">فعالسازی و غیر فعالسازی</p>
                    </fieldset>
                </td>

            </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="savechanges" id="savechanges" class="button button-primary" value="ذخیرهٔ تغییرات"></p>
    </form>
</div>

