(function(){

  tinymce.create('tinymce.plugins.formstack', {

    init : function(ed, url) {

      ed.addCommand('fs_embed_window', function() {
          var dialogUrl = url + '/dialog.php';
          ed.windowManager.open(
            {
              width: 400,
              height: 160,
              url: dialogUrl
            }
          );
        }
      );

      ed.addButton('formstack', {
          title : 'Formstack',
          cmd : 'fs_embed_window',
          image : url + '/stack.gif'
        }
      );
    }
  });

  tinymce.PluginManager.add('formstack', tinymce.plugins.formstack);

})()
