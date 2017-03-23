<?php

/**
 * This file is part of Formstack's WordPress Plugin.
 *
 * Formstack's WordPress Plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * Formstack's WordPress Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

require_once dirname(__FILE__). '/../config.php';

if(!empty($_POST['formstack_api_key'])){
    update_option('formstack_api_key', $_POST['formstack_api_key']);
}

$api_key = get_option('formstack_api_key');
$res = null;
if(!empty($api_key)){
    $res = Formstack_API::request($api_key, 'forms');
    if($res->status == "ok"){
        $res = $res->response;
    }
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>

        <title>Formstack</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>

        <script language="javascript" type="text/javascript">

            function fs_init() {

            }
            function fs_submit() {
                var e = document.getElementById('fs_form_select');
                var tag = '[Formstack id=';

                tag += e.value.split('-')[0];
                tag += ' viewkey=';
                tag += e.value.split('-')[1];
                tag += ']';

                if (window.tinyMCE) {
                    window.tinyMCE.execCommand('mceInsertContent', false, tag);
                    tinyMCEPopup.editor.execCommand('mceRepaint');
                    tinyMCEPopup.close();
                }

                return;
            }
        </script>
        <style type="text/css">

            input, select {
                font-size:14px;
            }
        </style>

        <base target="_self" />
    </head>
    <body onload="tinyMCEPopup.executeOnLoad('fs_init();');">

        <?php

            if (empty($api_key) || !empty($res->error)) {
                include dirname(__FILE__). '/../tmpl/empty_api_key.php';
            } elseif ($res == null) {
                include dirname(__FILE__). '/../tmpl/api_error.php';
            } else {

        ?>

        <h3 class="media-title">Embed a Formstack form</h3>
        <br />
        <form method="post" action="">
            <label for="fs_form">Choose a form to embed</label>
            &nbsp;&nbsp;
            <select id="fs_form_select" style="width:100%;">
            <?php foreach($res->forms as $form) { ?>
                <option value='<?php echo "{$form->id}-{$form->viewkey}"; ?>'><?php echo htmlspecialchars($form->name); ?></option>
            <?php } ?>
            </select>

            <br />
            <br />
            <input type="submit" value="Insert Form" class="button" onclick="fs_submit(); return false;" />
        </form>

        <?php } ?>

    </body>
</html>
