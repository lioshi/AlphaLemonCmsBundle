$(document).ready(function() {
    $(document).on("popoverShow", function(event, element){
        if (element.attr('data-type') != 'Image') {
            return;
        }
    
        $('#al_json_block_src').ShowExternalFilesManager('images', function(){
            $('<div/>').dialogelfinder({
                    url : frontController + 'backend/' + $('#al_available_languages option:selected').val() + '/al_elFinderMediaConnect',
                    lang : 'en',
                    width : 840,
                    destroyOnClose : true,
                    commandsOptions : {
                        getfile : {
                            onlyURL  : false
                        }
                    },
                    getFileCallback : function(file, fm) {
                        var image = '/' + $('#al_assets_path').val() + '/' + file.path;
                        $('#al_json_block_src').val(image);
                        
                        $('body').showAlert('Image has been selected');
                    }
            }).dialogelfinder('instance');
        });
    });
}); 
