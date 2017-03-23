<div style="background-color:#FFFFE0;border: 1px solid #E6DB55;padding:4px;-moz-border-radius: 3px;-webkit-border-radius: 3px;">
<p><strong>
   <?php
   if(!empty($res->error)){
       print $res->error;
   }else{?>
        <div style="padding-bottom:5px;">
            This widget requires a valid Formstack API key.
        </div>
        <div style="padding-bottom:5px;">
            Don't have an API Key? <a href="../../../../wp-admin/admin.php?page=FormstackAPI&new=1" target="parent">Click here</a> to create one.
        </div>
    <?php
   }
   ?></strong></p>

<form method="post" action="dialog.php">

    <table cellpadding="3" cellspacing="0" border="0">
        <tr>
        <td style="width:50px;" nowrap="nowrap" align="right"><strong>API Key:</strong></td>
        <td><input type="text" name="formstack_api_key" style="width:300px;" value="<?php echo get_option('formstack_api_key'); ?>" /></td>
        </tr>
    </table>

    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
