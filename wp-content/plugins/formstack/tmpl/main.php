<?php
$fs_url = 'https://www.formstack.com/admin';
if($_GET['page'] == 'FormstackNewForm'){
    $fs_url .= '/form/add?steps=1';
}elseif($_GET['page'] == 'FormstackAPI'){
    if($_GET['new']==1){
        $fs_url .= '/apiKey/add';
    }else{
        $fs_url .= '/apiKey/main';
    }
}else{
    $fs_url .= '/form/dashboard';
}
?>
<iframe id="formstack_iframe" src="<?php print $fs_url;?>" style="width:95%; min-height:600px;"></iframe>

<script language="javascript">
document.getElementById('formstack_iframe').style.height = screen.height*.8 + 'px';
</script>
