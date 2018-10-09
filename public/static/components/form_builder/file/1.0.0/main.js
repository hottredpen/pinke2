define(['jquery'], function ($) {

    return {
        getName: function () {
            return "file@1.0.0";
        },
        getAuthKey: function () {
            return "e369853df766fa44e1ed-51eee1e2b42d45493cec-b4b147bc523828731f1a-15239883801bf857a0a6388e6b2d5543"; // 检测是授权组件
        },
        createObj: function () {
            let obj = {};
            // let o_document = $(document);
            let WebUploader = null;

            let config = {
                "builderDiv": "",
                "formItemName": "",
                "tellInputId": "",
                "WebUploader_swf": "bower_components/fex-webuploader/dist/Uploader.swf",
                "image_upload_url": "/file/fileUploader/upload",
                "uploadtype": "",
            };

            obj.init = function (userconfig) {
                console.log("builder file");
                config = $.extend({}, config, userconfig);
                require(['webuploader'], function (webuploader) {
                    WebUploader = webuploader;
                    _init_something();
                    _onDocumentBtn();
                });
            };

            function _init_something() {

            }

            function _onDocumentBtn() {

                // 图片上传
                $('.js-upload-image,.js-upload-images').each(function () {
                    let $input_file = $(this).find('input');
                    let $input_file_name = $input_file.data('name');
                    // 允许上传的后缀
                    let $ext = $input_file.data('ext');
                    let $type = $input_file.data('type');
                    // 限制大小
                    let $size = $input_file.data('size');
                    // 列表
                    let $file_list = $('#file_list_' + $input_file_name);
                    // 优化retina, 在retina下这个值是2
                    // let ratio = window.devicePixelRatio || 1;

                    // 实例化上传
                    let uploader = WebUploader.create({
                        // 选完图片后，是否自动上传。
                        auto: true,
                        // 去重
                        duplicate: true,
                        // 不压缩图片
                        // resize: false,
                        compress: false,
                        // swf图片路径
                        swf: config.WebUploader_swf,
                        // 图片接收服务端。
                        server: config.image_upload_url + '?uploadtype=' + config.uploadtype,
                        // 选择图片的按钮。可选。
                        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                        pick: {
                            id: '#picker_' + $input_file_name,
                            multiple: false
                        },
                        // 图片限制大小
                        fileSingleSizeLimit: $size,
                        // 只允许选择图片文件。
                        accept: {
                            title: 'File',
                            extensions: $ext,
                            mimeTypes: $type,
                        }
                    });

                    // 当有文件添加进来的时候
                    uploader.on('fileQueued', function (file) {
                        let $li = '' +
                            '                        <li id="' + file.id + '">\n' +
                            '                            <div class="file-name">' + file.name + '</div>\n' +
                            '                            <span id="upload_statu">上传中</span>' +
                            '                            <a style="display: none;" href="" download="" target="_blank" id="download_file"> 查看 </a>\n' +
                            '                            <a style="display: none;" href="" id="delete_file">删除</a>\n' +
                            '                        </li>';


                        $file_list.find('.file-list').html($li);
                        $input_file.val('');

                    });


                    // 文件上传成功
                    uploader.on('uploadSuccess', function (file, response) {
                        // let $li = $('#' + file.id);

                        if (response.code === 200) {
                            let download_file = $('#download_file');
                            $('#upload_statu').text('上传成功');
                            $(download_file).show();
                            $(download_file).attr('href', response.data.url);
                            $('#delete_file').show();
                            $('#j_' + $input_file_name + '_id_input').val(response.data.fileid);


                        } else {
                            $('#upload_statu').text('上传服务器失败');
                            $('#j_' + $input_file_name + '_id_input').val('');
                        }


                    });

                    // 文件上传失败，显示上传出错。
                    uploader.on('uploadError', function () {
                        $('#upload_statu').text('上传服务器失败');
                        $('#j_' + $input_file_name + '_id_input').val('');

                        // 文件上传出错后的自定义回调函数
                    });

                    // 文件验证不通过
                    uploader.on('error', function (type) {
                        switch (type) {
                            case 'Q_TYPE_DENIED':
                                alert('图片类型不正确，只允许上传后缀名为：' + $ext + '，请重新上传！');
                                break;
                            case 'F_EXCEED_SIZE':
                                alert('图片不得超过' + ($size / 1024) + 'kb，请重新上传！');
                                break;
                        }
                    });


                    // 删除
                    $file_list.delegate('#delete_file', 'click', function () {

                        $input_file.val('');

                        $(this).closest('li').remove();
                    });
                });
            }

            return obj;
        }
    };

});