define(['jquery','jquery_nestable'],function($){

var component = {
    getName : function(){
        return "images@1.0.0";
    },
    getAuthKey : function(){
        return "a5771bce93e200c36f7c-842f3298f90f91ca8f70-b5b147bc52282873111a-3e82ed46969b8845a86cb9af85dfcd9e"; // 检测是授权组件
    },
	createObj : function(){
		var obj = {};
		var o_document = $(document);
        var WebUploader = null;

        var config = {
                "builderDiv"       : "",
                "formItemName"     : "",
                "tellInputId"      : "",
                "WebUploader_swf"  : "bower_components/fex-webuploader/dist/Uploader.swf",
                "image_upload_url" : "/api/file/webUploader",
        };

		obj.init = function(userconfig){
			console.log("builder image");
			config       = $.extend({}, config, userconfig);
            require(['webuploader'],function(webuploader){
                WebUploader = webuploader;
                _init_something();
                _onDocumentBtn();
            });
		}

		function _init_something(){
            _order_file_ids();
		}

        // 对$file_ids_input内的id,进行排序，按当前图片位置排
        function _order_file_ids(){
            var $input_file       = $('#j_webuploader_'+config.formItemName);
            var $input_file_name  = $input_file.data('name');
            var $file_ids_input   = $('#j_'+$input_file_name+'_ids_input');
            var $file_list        = $('#j_file_list_' + $input_file_name);
            var file_ids_arr = [];
            $file_list.find('.file-item').each(function(i,val){
                file_ids_arr.push($(val).attr('data-id'));
            });
            $file_ids_input.val(file_ids_arr.join(','));
        }

		function _onDocumentBtn(){

            // 图片上传
                var $input_file       = $('#j_webuploader_'+config.formItemName);
                var $input_file_name  = $input_file.data('name');
                var $file_ids_input   = $('#j_'+$input_file_name+'_ids_input');
                // 是否多图片上传
                var $multiple         = $input_file.data('multiple');
                // 允许上传的后缀
                var $ext              = $input_file.data('ext');
                // 图片限制大小
                var $size             = $input_file.data('size');
                // 图片列表
                var $file_list        = $('#j_file_list_' + $input_file_name);
                // 优化retina, 在retina下这个值是2
                var ratio             = window.devicePixelRatio || 1;
                // 缩略图大小
                var thumbnailWidth    = 100 * ratio;
                var thumbnailHeight   = 100 * ratio;
                // 实例化上传
                var uploader = WebUploader.create({
                    // 选完图片后，是否自动上传。
                    auto: true,
                    // 去重
                    duplicate: true,
                    // 不压缩图片
                    resize: false,
                    compress: false,
                    // swf图片路径
                    swf: config.WebUploader_swf,
                    // 图片接收服务端。
                    server: config.image_upload_url,
                    // 选择图片的按钮。可选。
                    // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                    pick: {
                        id: '#picker_' + $input_file_name,
                        multiple: $multiple
                    },
                    // 图片限制大小
                    fileSingleSizeLimit: $size,
                    // 只允许选择图片文件。
                    accept: {
                        title: 'Images',
                        extensions: $ext,
                        mimeTypes: 'image/jpg,image/jpeg,image/bmp,image/png,image/gif'
                    }
                });

                // 当有文件添加进来的时候
                uploader.on( 'fileQueued', function( file ) {
                    var $li = $(
                            '<li id="' + file.id + '" class="file-item js-gallery thumbnail  dd-item " data-todo="后面这个 id:todo" data-id="' + file.id + '" >' +
                            '<a class="img-link" href="">'+
                            '<img>' +
                            '</a>'+
                            '<div class="info">' + file.name + '</div>' +
                            '<i class="fa fa-times-circle remove-picture"></i>' +
                            ($multiple ? '<i class="fa fa-fw fa-arrows move-picture dd-handle"></i>' : '') +
                            '<div class="progress progress-mini remove-margin active" style="display: none">' +
                            '<div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>' +
                            '</div>' +
                            '<div class="file-state img-state"><div class="bg-info">正在读取...</div>' +
                            '</li>'
                        ),
                        $img = $li.find('img');

                    $file_list.append( $li );
                    // 创建缩略图
                    // 如果为非图片文件，可以不用调用此方法。
                    // thumbnailWidth x thumbnailHeight 为 100 x 100
                    uploader.makeThumb( file, function( error, src ) {
                        if ( error ) {
                            $img.replaceWith('<span>不能预览</span>');
                            return;
                        }
                        $img.attr( 'src', src );
                    }, thumbnailWidth, thumbnailHeight );

                    // 设置当前上传对象
                    curr_uploader = uploader;
                });

                // 文件上传过程中创建进度条实时显示。
                uploader.on( 'uploadProgress', function( file, percentage ) {
                    var $percent = $( '#'+file.id ).find('.progress-bar');
                    $percent.css( 'width', percentage * 100 + '%' );
                });

                // 文件上传成功
                uploader.on( 'uploadSuccess', function( file, response ) {
                    var $li = $( '#'+file.id );

                    if (response.code) {
                        if ($file_ids_input.val()) {
                            $file_ids_input.val($file_ids_input.val() + ',' + response.data.fileid);
                        } else {
                            $file_ids_input.val(response.data.fileid);
                        }
                        $li.find('.remove-picture').attr('data-id', response.data.fileid);
                        $li.attr('data-id', response.data.fileid);
                        _order_file_ids();
                    }

                    $li.find('.file-state').html('<div class="bg-'+response.class+'">'+response.msg+'</div>');
                    $li.find('a.img-link').attr('href', response.path);

                    setTimeout(function () {
                        $li.find('.file-state').remove();
                    }, 1500);

                    // 文件上传成功后的自定义回调函数
                    // todo
                });

                // 文件上传失败，显示上传出错。
                uploader.on( 'uploadError', function( file ) {
                    var $li = $( '#'+file.id );
                    $li.find('.file-state').html('<div class="bg-danger">服务器错误</div>');

                    // 文件上传出错后的自定义回调函数
                    // todo
                });

                // 文件验证不通过
                uploader.on('error', function (type) {
                    switch (type) {
                        case 'Q_TYPE_DENIED':
                            Dolphin.notify('图片类型不正确，只允许上传后缀名为：'+$ext+'，请重新上传！', 'danger');
                            break;
                        case 'F_EXCEED_SIZE':
                            Dolphin.notify('图片不得超过'+ ($size/1024) +'kb，请重新上传！', 'danger');
                            break;
                    }
                });

                // 完成上传完了，成功或者失败，先删除进度条。
                uploader.on( 'uploadComplete', function( file ) {
                    setTimeout(function(){
                        $( '#'+file.id ).find('.progress').remove();
                    }, 500);

                    // 文件上传完成后的自定义回调函数
                    // todo
                });

                // 删除图片
                $file_list.delegate('.remove-picture', 'click', function(){
                    $(this).closest('.file-item').remove();
                    _order_file_ids();
                });

                $('.dd').nestable({ maxDepth: 1 }).on('change', function(e){
                    _order_file_ids();
                }.bind(this));
		}

		return obj;
	}
}

return component;

});