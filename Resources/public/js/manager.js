$(function () {

    var $renameModal = $('#js-confirm-rename');
    var $deleteModal = $('#js-confirm-delete');
    var callback = function (key, opt) {
        switch (key) {
            case 'edit':
                var $renameModalButton = opt.$trigger.find(".js-rename-modal")
                renameFile($renameModalButton)
                $renameModal.modal("show");
                break;
            case 'delete':
                var $deleteModalButton = opt.$trigger.find(".js-delete-modal")
                deleteFile($deleteModalButton)
                $deleteModal.modal("show");
                break;
            case 'download':
                var $downloadButton = opt.$trigger.find(".js-download")
                downloadFile($downloadButton)
                break;
        }
    };

    $.contextMenu({
        selector: '.file',
        callback: callback,
        items: {
            "delete": {name: deleteMessage, icon: "fa-trash"},
            "edit": {name: renameMessage, icon: "fa-edit"},
            "download": {name: downloadMessage, icon: "fa-download"},
        }
    });
    $.contextMenu({
        selector: '.dir',
        callback: callback,
        items: {
            "delete": {name: deleteMessage, icon: "fa-trash"},
            "edit": {name: renameMessage, icon: "fa-edit"},
        }
    });


    function ajaxMoveFile(fileName, newPath){
        $.ajax({
            data: {
                json: 'true',
                conf: 'basic_user',
                fileName: fileName,
                newPath: newPath
            },
            url: urlmove,
            success: function(data){
                window.location.reload();
            },
            error: function (a, b, c) {
            }
        });
    }


    function moveFile(destFolder, file){

        let newPath = '';
        let dataHref = destFolder.attr('href').split('&');
        dataHref.forEach(function (queryFragment, index) {
            let queryNVP = queryFragment.split('=');
            if (queryNVP[0] === 'route') {
                newPath = queryNVP[1];
            }
        });
        if(newPath == '') {
            newPath = '/';
        }
        let fileName = file.attr('data-name');
        ajaxMoveFile(fileName, newPath);
    }


    function ajaxMoveFolder(origin, destination) {
        $.ajax({
            data: {
                json: 'true',
                conf: 'basic_user',
                origin: origin,
                destination: destination
            },
            url: urlmovefolder,
            success:function(data){
                window.location.reload();
            },
            error:function(a,b,c){
            }
        });
    }


    function moveFolder(destFolder, origFolder) {
        let destPath = '';
        let dataHref = destFolder.attr('href').split('&');
        dataHref.forEach(function (queryFragment, index) {
            let queryNVP = queryFragment.split('=');
            if (queryNVP[0] === 'route') {
                destPath = queryNVP[1];
            }
        });
        if(destPath == '') {
            destPath = '/';
        }

        let origPath = '';
        let dataHref2 = origFolder.attr('href').split('&');
        dataHref2.forEach(function (queryFragment, index) {
            let queryNVP2 = queryFragment.split('=');
            if (queryNVP2[0] === 'route') {
                origPath = queryNVP2[1];
            }
        });
        if(origPath != '') {
            ajaxMoveFolder(origPath, destPath);
        }

    }


    function setDroppables() {
        $('.dir').droppable({
            tolerance: "pointer",
            over: function(event, ui) {
                ui.helper.find('.jstree-icon').removeClass('jstree-er').addClass('jstree-ok');
            },
            out: function(event, ui){
                ui.helper.find('.jstree-icon').removeClass('jstree-ok').addClass('jstree-er');
            },
            drop: function (event, ui) {
                let file = $(event.target);
                if($(ui.draggable).hasClass('dir')) {
                    if($(ui.draggable).is('tr')) {
                        moveFolder(file.find('td > a'), $(ui.draggable).find('td > a'))
                    }else{
                        moveFolder(file.find('p > a'), $(ui.draggable).find('p > a'))
                    }
                }else if($(ui.draggable).hasClass('file')) {
                    if($(ui.draggable).is('tr')) {
                        moveFile(file.find('td > a'), $(ui.draggable))
                    }else {
                        moveFile(file.find('p > a'), $(ui.draggable))

                    }
                }
            }
        });
    }

    function getHelper($element) {
        let item = $("<div>", {
            id: "jstree-dnd",
            class: "jstree-default"
        });
        $("<i>", {
            class: "jstree-icon jstree-er"
        }).appendTo(item);
        item.append($element.text());
        return item;
    }

    function setDraggables() {

        let draggableOpts = {
            revert: "invalid",
            iframeFix: true,
            cursor: "crosshair",
            cursorAt: { top: 5, left: 5 },
            opacity: 0.7,
            helper:"clone",
            start: function (event, ui){
                $(this).addClass('drag-active');
                if($('#tree').length){
                    let item = getHelper($(this));
                    return $.vakata.dnd.start(
                        event,
                        {
                            jstree: true,
                            obj: ui.helper,
                            nodes: [{
                                id: true,
                                text: $(this).text(),
                                icon: "fa fa-flag-o"
                            }]
                        },
                        item
                    );
                }
            },
            stop: function(event, ui){
                $('.drag-active').removeClass('drag-active');
            }
        };
        if($('#tree').length){
            draggableOpts.helper = "clone";
        } else {
            draggableOpts.helper = function() {
                return getHelper($(this));
            }
        }
        $('.file').draggable(draggableOpts);
        $('.dir').draggable(draggableOpts);
    }

    function renameFile($renameModalButton) {
        $('#form_name').val($renameModalButton.data('name'));
        $('#form_extension').val($renameModalButton.data('extension'));
        $renameModal.find('form').attr('action', $renameModalButton.data('href'))
    }

    function deleteFile($deleteModalButton) {
        $('#js-confirm-delete').find('form').attr('action', $deleteModalButton.data('href'));
    }

    function downloadFile($downloadButton) {
        $downloadButton[0].click();
    }

    function initTree(treedata) {
        $('#tree').jstree({
            'plugins':["dnd"],
            'core': {
                'data': treedata,
                "check_callback": function (operation, node, node_parent, node_position, more) {
                    if (more) {
                        if (more.dnd) {
                            return false;
                        }
                    }
                    return true;
                }
            },
        }).bind("changed.jstree", function (e, data) {
            if (data.node) {
                document.location = data.node.a_attr.href;
            }
            setDraggables();
        });
        $(document).on('dnd_stop.vakata', function(ev, data) {
            ev.stopPropagation();
            ev.preventDefault();

            let target = $(data.event.target).closest('a');
            let file = $(data.element);

            if(file.attr('id') === 'j1_1_anchor') {
                return false;
            }

            if (vakataDndStopFolder(ev, data)){
                moveFolder(target, file.find('a'));
            }else if(vakataDndStopFile(ev, data)) {
                moveFile(target, file);
            }
            $('.drag-active').removeClass('drag-active');
            return false;
        }).on('dnd_move.vakata', function(e, data) {
            let file = $(data.element);
            if(file.hasClass('file')) {
                vakataDndMoveFile(e, data);
            } else {
                vakataDndMoveFolder(e, data);
            }
        });
    }


    function vakataDndStopFolder(ev, data) {
        let target = $(data.event.target).closest('a');
        let file = $(data.element);

        if(file.attr('id') === 'j1_1') { //cannot drag the main folder
            return false;
        }

        let conditions =
            file.closest('.dir').length //dragged must be of class dir
            && target.closest('.dir').length // target must be of class dir
            && !target.closest('.dir').hasClass('drag-active') // but not the dragged one
            && !$.contains(file.closest('.dir').get(0), target.closest('.dir').get(0)); //and the target must not be descedant of the dragged

        return conditions;
    }

    function vakataDndStopFile(ev, data) {
        let target = $(data.event.target).closest('a');
        let file = $(data.element);

        let conditions = target.closest('.jstree-hovered:not(.jstree-clicked)').length
            && file.hasClass('file');

        return conditions;
    }

    function vakataDndMoveFile(ev, data) {
        let target = $(data.event.target);
        let file = $(data.element);

        if(target.closest('.jstree-hovered:not(.jstree-clicked)').length && file.hasClass('file')) {
            data.helper.find('.jstree-icon').removeClass('jstree-er').addClass('jstree-ok');
        } else {
            data.helper.find('.jstree-icon').removeClass('jstree-ok').addClass('jstree-er');
        }
    }

    function vakataDndMoveFolder(ev, data) {
        let target = $(data.event.target);
        let file = $(data.element);

        if(file.attr('id') === 'j1_1') { //cannot drag the main folder
            $(data.helper).hide();
        }
        if(target.closest('.dir').length // target must be of class dir
            && !target.closest('.dir').hasClass('drag-active') //but not the dragged one
            && !$.contains(file.closest('.dir').get(0), target.closest('.dir').get(0)) //and the target must not be descedant of the dragged
        ) {
            data.helper.find('.jstree-icon').removeClass('jstree-er').addClass('jstree-ok');
        } else {
            data.helper.find('.jstree-icon').removeClass('jstree-ok').addClass('jstree-er');
        }

    }

    if (tree === true) {

        // sticky kit
        $("#tree-block").stick_in_parent();

        initTree(treedata);

    } else {

        setDraggables();
        setDroppables();
    }
    $(document)
    // checkbox select all
        .on('click', '#select-all', function () {
            var checkboxes = $('#form-multiple-delete').find(':checkbox')
            if ($(this).is(':checked')) {
                checkboxes.prop('checked', true);
            } else {
                checkboxes.prop('checked', false);
            }
        })
        // delete modal buttons
        .on('click', '.js-delete-modal', function () {
                deleteFile($(this));
            }
        )
        // rename modal buttons
        .on('click', '.js-rename-modal', function () {
                renameFile($(this));
            }
        )
        // multiple delete modal button
        .on('click', '#js-delete-multiple-modal', function () {
            var $multipleDelete = $('#form-multiple-delete').serialize();
            if ($multipleDelete) {
                var href = urldelete + '&' + $multipleDelete;
                $('#js-confirm-delete').find('form').attr('action', href);
            }
        })
        // checkbox
        .on('click', '#form-multiple-delete :checkbox', function () {
            var $jsDeleteMultipleModal = $('#js-delete-multiple-modal');
            if ($(".checkbox").is(':checked')) {
                $jsDeleteMultipleModal.removeClass('disabled');
            } else {
                $jsDeleteMultipleModal.addClass('disabled');
            }
        });





    // preselected
    $renameModal.on('shown.bs.modal', function () {
        $('#form_name').select().mouseup(function () {
            $('#form_name').unbind("mouseup");
            return false;
        });
    });
    $('#addFolder').on('shown.bs.modal', function () {
        $('#rename_name').select().mouseup(function () {
            $('#rename_name').unbind("mouseup");
            return false;
        });
    });


    // Module Tiny
    if (moduleName === 'tiny') {

        $('#form-multiple-delete').on('click', '.select', function () {
            var args = top.tinymce.activeEditor.windowManager.getParams();
            var input = args.input;
            var document = args.window.document;
            var divInputSplit = document.getElementById(input).parentNode.id.split("_");

            // set url
            document.getElementById(input).value = $(this).attr("data-path");

            // set width and height
            var baseId = divInputSplit[0] + '_';
            var baseInt = parseInt(divInputSplit[1], 10);

            divWidth = baseId + (baseInt + 3);
            divHeight = baseId + (baseInt + 5);

            document.getElementById(divWidth).value = $(this).attr("data-width");
            document.getElementById(divHeight).value = $(this).attr("data-height");

            top.tinymce.activeEditor.windowManager.close();
        });
    }

    // Global functions
    // display error alert
    function displayError(msg) {
        displayAlert('danger', msg)
    }

    // display success alert
    function displaySuccess(msg) {
        displayAlert('success', msg)
    }

    // file upload
    $('#fileupload').fileupload({
        dataType: 'json',
        processQueue: false,
        dropZone: $('#dropzone'),
        progressall: function (e, data) {
            let progress = parseInt(data.loaded / data.total * 100, 10);
            $('.progress-bar-info').css(
                'width',
                progress + '%'
            );
        }
    }).on('fileuploaddone', function (e, data) {
        uploadProgress.close();
        $.each(data.result.files, function (index, file) {
            if (file.url) {
                displaySuccess('<strong>' + file.name + '</strong> ' + successMessage)
                // Ajax update view
                $.ajax({
                    dataType: "json",
                    url: url,
                    type: 'GET'
                }).done(function (data) {
                    // update file list
                    $('#form-multiple-delete').html(data.data);
                    if (tree === true) {
                        $('#tree').data('jstree', false).empty();
                        initTree(data.treeData);
                    }

                    $('#select-all').prop('checked', false);
                    $('#js-delete-multiple-modal').addClass('disabled');

                }).fail(function (jqXHR, textStatus, errorThrown) {
                    displayError('<strong>Ajax call error :</strong> ' + jqXHR.status + ' ' + errorThrown)
                });

            } else if (file.error) {
                displayError('<strong>' + file.name + '</strong> ' + file.error)
            }
        });
    }).on('fileuploadfail', function (e, data) {
        uploadProgress.close();
        $.each(data.files, function (index, file) {
            displayError('File upload failed.')
        });
    }).on('fileuploadstart', function(e) {
        uploadProgress = $.notify({
            message: uploadMessage
        }, {
            type: 'info',
            delay:0,
            timer:0,
            showProgressbar: true,
            placement: {
                from: "bottom",
                align: "left"
            },
            template: '<div data-notify="container" class="col-xs-5 col-md-4 col-lg-3 alert alert-{0}" role="alert">' +
                '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
                '<span data-notify="icon"></span> ' +
                '<span data-notify="title">{1}</span> ' +
                '<span data-notify="message">{2}</span>' +
                '<div class="progress" data-notify="progressbar">' +
                '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
                '</div>' +
                '<a href="{3}" target="{4}" data-notify="url"></a>' +
                '</div>'
        });
    });
})
;