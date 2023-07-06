/* jQuery Dropdown plugin */
!function($){"use strict";function Dropdown(element){$(element).on("click.bs.dropdown",this.toggle)}var toggle='[data-toggle="dropdown"]';function clearMenus(e){e&&3===e.which||($(".dropdown-backdrop").remove(),$(toggle).each(function(){var $parent=getParent($(this)),relatedTarget={relatedTarget:this};$parent.hasClass("open")&&($parent.trigger(e=$.Event("hide.bs.dropdown",relatedTarget)),e.isDefaultPrevented()||$parent.removeClass("open").trigger("hidden.bs.dropdown",relatedTarget))}))}function getParent($this){var selector=$this.attr("data-target"),selector=(selector=selector||(selector=$this.attr("href"))&&/#[A-Za-z]/.test(selector)&&selector.replace(/.*(?=#[^\s]*$)/,""))&&$(selector);return selector&&selector.length?selector:$this.parent()}Dropdown.VERSION="3.1.1",Dropdown.prototype.toggle=function(e){var $this=$(this);if(!$this.is(".disabled, :disabled")){var $parent=getParent($this),isActive=$parent.hasClass("open");if(clearMenus(),!isActive){"ontouchstart"in document.documentElement&&!$parent.closest(".navbar-nav").length&&$('<div class="dropdown-backdrop"/>').insertAfter($(this)).on("click",clearMenus);isActive={relatedTarget:this};if($parent.trigger(e=$.Event("show.bs.dropdown",isActive)),e.isDefaultPrevented())return;$this.trigger("focus"),$parent.toggleClass("open").trigger("shown.bs.dropdown",isActive)}return!1}},Dropdown.prototype.keydown=function(e){if(/(38|40|27)/.test(e.keyCode)){var $this=$(this);if(e.preventDefault(),e.stopPropagation(),!$this.is(".disabled, :disabled")){var $parent=getParent($this),isActive=$parent.hasClass("open");if(!isActive||27==e.keyCode)return 27==e.which&&$parent.find(toggle).trigger("focus"),$this.trigger("click");isActive=" li:not(.divider):visible a",$this=$parent.find('[role="menu"]'+isActive+', [role="listbox"]'+isActive);$this.length&&($parent=$this.index($this.filter(":focus")),38==e.keyCode&&0<$parent&&$parent--,40==e.keyCode&&$parent<$this.length-1&&$parent++,$this.eq($parent=~$parent?$parent:0).trigger("focus"))}}};var old=$.fn.dropdown;$.fn.dropdown=function(option){return this.each(function(){var $this=$(this),data=$this.data("bs.dropdown");data||$this.data("bs.dropdown",data=new Dropdown(this)),"string"==typeof option&&data[option].call($this)})},$.fn.dropdown.Constructor=Dropdown,$.fn.dropdown.noConflict=function(){return $.fn.dropdown=old,this},$(document).on("click.bs.dropdown.data-api",clearMenus).on("click.bs.dropdown.data-api",".dropdown form",function(e){e.stopPropagation()}).on("click.bs.dropdown.data-api",toggle,Dropdown.prototype.toggle).on("keydown.bs.dropdown.data-api",toggle+', [role="menu"], [role="listbox"]',Dropdown.prototype.keydown)}(jQuery);

// Enter Key detect
jQuery.fn.enterKey = function (fnc) {
    return this.each(function () {
        jQuery(this).keypress(function (ev) {
            const keycode = (ev.keyCode ? ev.keyCode : ev.which);
            if (keycode === 13) {
                fnc.call(this, ev);
            }
        })
    })
};

let editTaskDialog = false;
const taskDescriptionEditorId = 'edit-task-description';
let isDragging = false;
let isRefreshing = false;
let isListInlineEditing = false;
let reorderingTaskId = 0;

jQuery(function(){
    jQuery('.task-board').addClass('loading-board');
    isRefreshing = true;

    if(btargs.group_id === '0') {
        btargs.group_id = '';
    }

    renderDialogs();

    attachEditTaskFormEvents();

    const data = {
        'action' : 'get_board',
        '_wpnonce': jQuery("input#_wpnonce_get_board").val(),
    };

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        success: function(data){
            const board = JSON.parse(data);
            renderBoard(board);
            attachAutoRefresh();
        },
        error: function(data){
        }
    });

    function renderTasks(tasks){
        let html = "";
        if (tasks){
            tasks.filter(task => task !== null).forEach(task => {
                let dueEpoch = '';
                let due = '';
                if(task.due_to !== null){
                    dueEpoch = task.due_to;
                    due = jQuery.datepicker.formatDate('d M y',new Date(dueEpoch * 1000));
                } else {
                    due = btargs.lang.due_date;
                }

                let assignTo = task.owners ? renderAssignedUsers(task.owners) : '';

                html += `<li class="task">
                               <div class="task-progress">
                                    <div class="task-progress-bar">
                                        <div class="color-line task-progress-bar-current" style="width: `+task.done_percent+`%;"></div>
                                    </div>
                                </div>
                                <div class = 'task-wrapper' id="` + task.uuid + `">
                                    <div class="task-title-block">
                                        <div class="task-title">` + task.title + `</div>
                                        <div class="task-menu dropdown">
                                            <button type="submit" class="task-menu-button dropdown-toggle" data-toggle="dropdown"><i class="dashicons dashicons-ellipsis"></i></button>
                                            <ul class="dropdown-menu drop-left">`;

                html += `<li><a href="#" class="edit-task-button editable"><i class="dashicons dashicons-edit"></i>` + btargs.lang.edit + `</a></li>`;
                html += `<li class="divider"></li><li><a href="#" class="delete-task-button"><i class="dashicons dashicons-no"></i>` + btargs.lang.delete + `</a></li>`;
                html += `</ul>
                            </div>
                            </div>
                            <div class="task-created-by" style="display: none">` + task.created_by + `</div>
                            <div class="task-description editable">` + task.description + `</div>
                            <div class="task-users task-info-block">
                                <div class="user-avatars">` + assignTo + `</div>
                                <div class="task-date">` + due + `</div>
                                <input type="hidden" class="task-date-epoch" name="task-date-epoch" value="`+dueEpoch+`"/>
                            </div>
                        </div>
                    </li>`;
            })
        }
        return html;
    }

    function renderAssignedUsers(users){
        let html =  '';
        users.forEach(user => {
            html += `<div id="uid-` + user.user_id + `" class="user-avatar" title="` + user.display_name + `">
                        <a href="/`+btargs.user_profile_path+`/`+user.username+`">
                            <img  src="` + user.avatar_url + `"/>
                        </a>`;

            html += `<i class="dashicons dashicons-no delete-assigned-user" style="display: none"></i>`;

            html += `</div>`;
        });
        return html;
    }

    function renderBoard(data){
        if(isListInlineEditing){
            return;
        }

        let html = "";
        const lists = data.lists;
        lists.forEach(list => {
            let heading = "";
            heading += `<h2 class="tasks-list-heading">` + list.name + `
                            <button type="submit" class="add-new-task-button"><i class="dashicons dashicons-plus"></i></button>
                        </h2>`;
            html += `<div class="tasks-list-wrapper">
                        <div class="tasks-list" id="` + list.uuid + `">
                             <div class = 'color-line-big'></div>` + heading +
                            `<div class="tasks-list-body">
                                <ul class="tasks">` + renderTasks(list.tasks) + `<ul> 
                            </div> 
                        </div>
                    </div>`;
        });

        jQuery('.task-board').removeClass('loading-board');

        jQuery('.task-board').html(html);

        jQuery('.tasks-list h2').on('click', function (e) {
            const isInlineEditList = jQuery(e.target).is('h2');
            const isAddTaskButton = jQuery(e.target).is('i');
            if (isInlineEditList) {
                const list = jQuery(this).closest('.tasks-list');
                inlineEditList(list);
            } else if (isAddTaskButton) {
                jQuery(this).addClass('autocomplete-loading');
                isRefreshing = true;

                const list_id = jQuery(e.target).closest('.tasks-list').attr('id');
                const position = jQuery('#' + list_id).find('.task').length;
                const data = {
                    'action': 'add_new_task',
                    'list_id': list_id,
                    'position': position,
                    '_wpnonce': jQuery("input#_wpnonce_add_new_task").val(),
                };

                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: data,
                    success: function (data) {
                        const board = JSON.parse(data);
                        renderBoard(board);
                        jQuery('.add-new-task-button').removeClass('autocomplete-loading');
                    },
                    error: function (data) {
                    }
                });

                e.preventDefault();
            }
        });

        jQuery('.delete-task-button').on( 'click', function(e) {
            const target = e.target;
            const task_id = jQuery(target).closest('.task-wrapper').attr('id');
            openDeleteTaskConfirmationDialog(task_id);
            e.preventDefault();
        });

        jQuery('[data-toggle="dropdown"]').dropdown();

        jQuery( ".edit-task-button" ).on( "click", openEditTaskDialog);
        jQuery( ".view-task-button" ).on( "click", openEditTaskDialog);
        jQuery( ".task-title" ).on( "mouseup", openEditTaskDialog);
        jQuery( ".task-info-block" ).on( "mouseup", openEditTaskDialog);

        let listBorderColors = [
            '#000963', '#375980', '#679999', '#fe641d'
        ];
        let i = 0;
        jQuery('.tasks-list').each(function() {
            jQuery(this).css('border-color', listBorderColors[i]);
            i = (i + 1) % listBorderColors.length;
        });

        jQuery('h2.tasks-list-heading').each(function() {
            jQuery(this).css('color', listBorderColors[i]);
            i = (i + 1) % listBorderColors.length;
        });

        jQuery('.tasks').sortable({
            connectWith: ['.tasks'],
            placeholder: "tasks-highlight",
            start: function (e, ui) {
                isDragging = true;
                // creates a temporary attribute on the element with the old index
                jQuery(this).attr('data-previndex', ui.item.index());
                const list = jQuery(ui.item).closest('.tasks-list').attr('id');
                jQuery(this).attr('data-prevlist', list);
            },
            stop: function (e, ui) {
                isDragging = false;

                const task_id = ui.item.find('.task-wrapper').attr('id');
                const task_index = jQuery(ui.item).parent().children('li').index(ui.item);
                const newList = jQuery(ui.item).closest('.tasks-list').attr('id');
                const oldList = jQuery(this).attr('data-prevlist');
                const oldIndex = parseInt(jQuery(this).attr('data-previndex'));

                if (oldIndex !== task_index || oldList !== newList) {
                    reorderTask(newList, task_id, task_index);
                }

                // gets the new and old index then removes the temporary attribute
                jQuery(this).removeAttr('data-previndex');
                jQuery(this).removeAttr('data-prevlist');

            },
            revert: 100
        });

        let buttons = {};
        buttons[btargs.lang.delete] = function() {
            const task_id = jQuery('#edit-task-form #edit-task-id').val();
            openDeleteTaskConfirmationDialog(task_id);
            editTaskDialog.dialog( "close" );
        }
        buttons[btargs.lang.submit] = editTask;
        buttons[btargs.lang.cancel] = function() {
            editTaskDialog.dialog( "close" );
        }

        editTaskDialog = jQuery( "#edit-task-dialog" ).dialog({
            autoOpen: false,
            height: 'auto',
            width: 700,
            modal: true,
            buttons: buttons,
            classes: {
                "ui-dialog": "task-board-dialog"
            },
            open: function( event, ui ) {
                jQuery("body").css({ overflow: 'hidden' });
                const task_id = jQuery('#edit-task-form #edit-task-id').val();

                fetchTodos(task_id);

                jQuery('#todo-list').sortable({
                    placeholder: "ui-state-highlight"
                });

                jQuery('.delete-assigned-user').on( 'click', function(e) {
                    e.target.closest('.user-avatar').remove();
                });

                const description = jQuery('#edit-task-form #edit-task-description').html();
                tinyMCE.get(taskDescriptionEditorId).setContent(description);

                buddytask_refresh_buttons_state();
            },
            close: function() {
                jQuery("body").css({ overflow: 'inherit' });
                jQuery('#edit-task-form #edit-task-list').val('');
                jQuery('#edit-task-form #edit-task-id').val('');
                jQuery('#edit-task-form #edit-task-created-by').val('');
                jQuery('#edit-task-form #edit-task-title').val('');
                jQuery('#edit-task-due-date-epoch').val('');
                jQuery('#edit-task-form #edit-task-description').html('');
                jQuery('#users-invite-list').find('div').remove();

                jQuery('.checklist-progress-bar-current').width('0%');
                jQuery('.checklist-progress-percentage').text('0%');

                jQuery('#todo-list').html('');
            }
        });

        isRefreshing = false;
    }

    jQuery("#edit-task-description").click(function () {
        if (jQuery(this).text().includes("Click to edit")) {
            jQuery(this).text('')
        }
    });

    jQuery("#edit-task-title").click(function () {
        if (jQuery(this).val().includes("Click to edit")) {
            jQuery(this).val('')
        }
    });

    function openDeleteTaskConfirmationDialog(task_id) {
        jQuery( "#dialog-delete-confirm" ).dialog({
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            classes: {
                "ui-dialog": "task-board-dialog"
            },
            buttons: {
                [btargs.lang.delete]: function() {
                    deleteTask(task_id);
                    jQuery( this ).dialog( "close" );
                },
                [btargs.lang.cancel]: function() {
                    jQuery( this ).dialog( "close" );
                }
            }
        });
    }

    function openEditTaskDialog(e) {
        e.preventDefault();
        if(isDragging){
            return;
        }

        const target = e.target;
        const list_id = jQuery(target).closest('.tasks-list').attr('id');
        const task_id = jQuery(target).closest('.task-wrapper').attr('id');
        const created_by = jQuery(target).closest('.task-wrapper').find('.task-created-by').text();
        const title = jQuery(target).closest('.task-wrapper').find('.task-title').text();
        const description = jQuery(target).closest('.task-wrapper').find('.task-description').html();
        const due_date_epoch = jQuery(target).closest('.task-wrapper').find('.task-date-epoch').val();
        let usersList = jQuery(target).closest('.task-wrapper').find('.user-avatars').html();

        jQuery('#edit-task-form #edit-task-list').val(list_id);
        jQuery('#edit-task-form #edit-task-id').val(task_id);
        jQuery('#edit-task-form #edit-task-created-by').val(created_by);
        jQuery('#edit-task-form #edit-task-title').val(title);
        jQuery('#edit-task-form #edit-task-description').html(description);

        jQuery('#add-todo').enterKey(function(e){
            const title = jQuery(this).val();
            if(title.trim() === ''){
                return;
            }
            const lastSibling = jQuery('#todo-list > .todo-wrap:last-of-type > input').attr('id');
            let newId = lastSibling !== undefined ? Number(lastSibling.split('_')[1]) + 1 : 1;
            newId = 'task_' + newId;

            addTodoIntoList(newId, title, true, false);
            updatePercentage();

            jQuery(this).val('');
        });

        if(due_date_epoch !== null && due_date_epoch !== undefined && due_date_epoch !== '') {
            jQuery('#edit-task-due-date-epoch').val(due_date_epoch);
            const due_date = jQuery.datepicker.formatDate('d M y', new Date(jQuery('#edit-task-due-date-epoch').val() * 1000));
            jQuery('#edit-task-due-date').val(due_date);
        }

        if(usersList === undefined){
            usersList = '';
        }
        jQuery('#users-invite-list').html(usersList);
        jQuery('#users-invite-list').find('.delete-assigned-user').show();


        jQuery('#edit-task-dialog :input').prop("disabled", false);
        tinyMCE.get(taskDescriptionEditorId).setMode('design');
        jQuery('.mce-floatpanel').show();
        jQuery('#edit-task-description').removeClass('mce-edit-focus');

        editTaskDialog.dialog( "open" );
    }

    function editTask(){
        isRefreshing = true;

        const list_id = jQuery('#edit-task-form #edit-task-list').val();
        const task_id = jQuery('#edit-task-form #edit-task-id').val();
        const task_title = jQuery('#edit-task-form #edit-task-title').val();
        const task_description = tinyMCE.get(taskDescriptionEditorId).getContent();
        const task_due_date = jQuery('#edit-task-due-date').val();
        const task_due_date_epoch = task_due_date !== '' ? jQuery('#edit-task-due-date-epoch').val() : null;
        const task_assign_to = [];
        const task_todos = [];

        jQuery('#users-invite-list div').each(function(index,value) {
            task_assign_to.push( jQuery(this).attr('id').split('-')[1] );
        });

        jQuery('#todo-list .todo-wrap').each(function(index,value){
            const task = jQuery(this);
            const label = task.find('label');
            const task_id = label.attr('id');
            const task_title = label.text();
            const done = label.hasClass('done');
            const isNew = task.hasClass('new-todo');

            task_todos.push({uuid: task_id, title: task_title, isDone: done, isNew: isNew});
        });

        // set ajax data
        const data = {
            'action' : 'edit_task',
            '_wpnonce': jQuery("input#_wpnonce_edit_task").val(),
            'list_id': list_id,
            'task_id': task_id,
            'task_title': task_title,
            'task_description' : task_description,
            'task_assign_to': task_assign_to,
            'task_due_date': task_due_date_epoch,
            'task_todos' : JSON.stringify(task_todos)
        };

        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function(data){
                if(data) {
                    const board = JSON.parse(data);
                    renderBoard(board);
                }
            },
            error: function(data){
            }
        });

        displayBusyTaskIndicator(task_id);

        editTaskDialog.dialog( "close" );
    }

    function reorderTask(list_id, task_id, task_index){
        isRefreshing = true;
        reorderingTaskId = task_id;

        const data = {
            'action' : 'reorder_task',
            'list_id': list_id,
            'task_id': task_id,
            'task_index': task_index,
            '_wpnonce': jQuery("input#_wpnonce_reorder_task").val(),
        };

        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            dataType : "json",
            success: function(data){
                if(data && reorderingTaskId === task_id) {
                    renderBoard(data);
                }
            },
            error: function(data){
            }
        });

        displayBusyTaskIndicator(task_id);
    }

    function deleteTask(task_id){
        isRefreshing = true;

        const data = {
            'action' : 'delete_task',
            'task_id': task_id,
            '_wpnonce': jQuery("input#_wpnonce_delete_task").val(),
        };

        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function(data){
                const board = JSON.parse(data);
                renderBoard(board);
            },
            error: function(data){
            }
        });

        displayBusyTaskIndicator(task_id);
    }

    function autocomplete(){
        const options = {
            minLength: 3,
            select: buddytask_on_autocomplete_select,
            source: function( request, response ) {
                jQuery('#edit-task-assign-to').addClass('autocomplete-loading');
                const task_id = jQuery('#edit-task-form #edit-task-id').val();
                const task_assign_to = [];

                jQuery('#users-invite-list div').each(function(index,value) {
                    task_assign_to.push( jQuery(this).attr('id').split('-')[1] );
                });

                const data =  {
                    'action': 'users_autocomplete',
                    '_wpnonce': jQuery("input#_wpnonce_users_autocomplete").val(),
                    'term':  request.term,
                    'task_id': task_id,
                    'task_assign_to': task_assign_to
                };

                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: data,
                    success: function(data){
                        jQuery('#edit-task-assign-to').removeClass('autocomplete-loading');
                        if(data){
                            response(JSON.parse(data));
                        }
                    },
                    error: function(data){}
                });
            }
        };

        const elem = jQuery('#edit-task-assign-to').autocomplete(options).autocomplete( "instance" );
        if(elem){
           elem._renderItem = function( ul, item ) {
               return jQuery( "<li>" )
                   .append( "<div>" + item.label + "</div>" )
                   .appendTo( ul );
           };
        }
    }

    function buddytask_on_autocomplete_select( event, ui) {
        const user_id = ui.item.value;
        // Put the item in the invite list
        jQuery('#edit-task-assign-to').addClass('autocomplete-loading');

        const data =  {
            'action': 'add_users_to_assign_list',
            '_wpnonce': jQuery("input#_wpnonce_add_users_to_assign_list").val(),
            'user_id': user_id
        };

        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function(response){
                jQuery('.ajax-loader').toggle();

                if(response) {
                    jQuery('#users-invite-list').append(response);

                    jQuery('.action a').click(function(){
                        jQuery(this).closest('li').remove();
                        buddytask_refresh_buttons_state();
                    });
                }

                jQuery('#edit-task-assign-to').removeClass('autocomplete-loading');

                // Refresh the submit button state
                buddytask_refresh_buttons_state();
            },
            error: function(data){
            }
        });

        // Remove the value from the input element
        jQuery('#edit-task-assign-to').val('');

        return false;
    }

    function buddytask_refresh_buttons_state(){
        const hasInvites = jQuery( '#users-invite-list li' ).length;
        if ( hasInvites) {
            jQuery( '#submit' ).prop( 'disabled', false ).removeClass( 'submit-disabled' );
        } else {
            jQuery( '#submit' ).prop( 'disabled', true ).addClass( 'submit-disabled' );
        }
    }

    function initializeRichEditor(){
        const settings = {
            tinymce: {
                height : "250",
                wpautop  : true,
                theme    : 'modern',
                resize: false,
                inline:true,
                skin     : 'lightgray',
                language : 'en',
                relative_urls       : false,
                fix_list_elements   : true,
                paste_webkit_styles : 'font-weight font-style color',
                preview_styles      : 'font-family font-size font-weight font-style text-decoration text-transform',
                tabfocus_elements   : ':prev,:next',
                plugins    : 'charmap,hr,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview',
                menubar    : false,
                indent     : false,
                toolbar1   : 'bold,italic,strikethrough,underline,bullist,numlist,hr,alignleft,aligncenter,alignright,alignjustify,formatselect,forecolor,removeformat,outdent,indent,undo,redo',
                toolbar2   : '',
                toolbar3   : '',
                toolbar4   : '',
                content_css : ''
            },
            quicktags   : false,
            mediaButtons: false
        };
        wp.editor.initialize(taskDescriptionEditorId, settings);
    }

    function initializeDatePicker(){
        jQuery('#edit-task-due-date').datepicker({
            dateFormat: 'd M y',
            classes: {
                "ui-datepicker": "task-board-datepicker"
            },
            onSelect: function(date) {
                const epoch = jQuery.datepicker.formatDate('@', jQuery(this).datepicker('getDate')) / 1000;
                jQuery('#edit-task-due-date-epoch').val(epoch);
            },
            beforeShow: function(input, inst) {
                inst.dpDiv.css({
                    marginLeft: '-9px'
                });
            }
        });
    }

    function displayBusyTaskIndicator(task_id){
        jQuery('#' + task_id + ' .task-menu-button').hide();
        jQuery('#' + task_id + ' .task-menu').addClass('autocomplete-loading')
            .css('paddingBottom', '15px').css('paddingLeft', '16px');
    }

    function attachAutoRefresh(){
        let pulse = btargs.heartbeat.interval;
        window.heartbeatSettings = {};
        window.heartbeatSettings.nonce = btargs.heartbeat.nonce;

        // Set the interval and the namespace event
        if (typeof wp !== 'undefined' && typeof wp.heartbeat !== 'undefined' && typeof pulse !== 'undefined') {
            wp.heartbeat.interval(Number(pulse));
            jQuery.fn.extend({
                'heartbeat-send': function () {
                    return this.bind('heartbeat-send.buddytask');
                }
            });
        }

        // Set the last id to request after
        jQuery(document).on('heartbeat-send.buddytask', function (e, data) {
            if(!isRefreshing) {
                //Return true to indicate we are expecting data
                data.refresh_board = true;
                return true;
            }
        });

        jQuery(document).on('heartbeat-tick', function (e, data) {
            if(data && data.board && !isRefreshing){
                renderBoard(data.board);
            }
        });
    }

    function fetchTodos(task_id){
        jQuery('#todo-list').addClass('autocomplete-loading');

        const parent_id = jQuery('#edit-task-id').val();
        const data = {
            'action' : 'get_tasks',
            'parent_id': parent_id,
            '_wpnonce': jQuery("input#_wpnonce_get_tasks").val(),
        };

        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function(data){
                const edit_task_id = jQuery('#edit-task-form #edit-task-id').val();
                if(task_id === edit_task_id){
                    const todo_list = jQuery('#todo-list');
                    todo_list.removeClass('autocomplete-loading');
                    todo_list.html('');
                    if(data){
                        const todos = JSON.parse(data);
                        if(todos && Array.isArray(todos)) {
                            todos.forEach(todo => {
                                addTodoIntoList(todo.uuid, todo.title, false, todo.done === "1");
                            });
                            updatePercentage();
                        }
                    }
                }
            },
            error: function(data){
            }
        });
    }

    function updatePercentage(){
        console.log('updatePercentage');
        const todoList = jQuery('#todo-list');
        const done = todoList.find('.done').length;
        const total = todoList.find('.todo').length;

        let percentage = Math.round((done * 100) / total);
        if(Number.isNaN(percentage)){
            percentage = 0;
        }

        jQuery('.checklist-progress-bar-current').width(percentage + '%');
        jQuery('.checklist-progress-percentage').text(percentage + '%');
    }

    function addTodoIntoList(id, title, isNew, isDone){
        const todoList = jQuery('#todo-list');
        const todo = createTodo(id, title, isNew, isDone);
        todoList.append(todo);
        attachTodoEvents(todo, id);
    }

    function createTodo(id, description, isNew, isDone){
        const done = isDone ? 'done' : '';
        const extraClass = isNew ? 'new-todo' : '';
        const deleteTodo = `<span class="delete-item"><i class="dashicons dashicons-no"></i></span>`;
        let task = `<li><span class="todo-wrap ` + extraClass + `">
                    <label id="` + id + `" class="todo ` + done + `"><i class="dashicons dashicons-saved todo-checkbox"></i>`+ description +`</label>`;
        task += deleteTodo;
        task += `</span></li>`;
        return jQuery(task);
    }

    function attachTodoEvents(todo, id){
        todo.find('.delete-item').on('click', function(e){
            const parentItem = jQuery(this).parent();

            jQuery( "#dialog-delete-confirm" ).dialog({
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                classes: {
                    "ui-dialog": "task-board-dialog"
                },
                buttons: {
                    [btargs.lang.delete]: function() {
                        jQuery(parentItem).remove();
                        updatePercentage();
                        jQuery( this ).dialog( "close" );
                    },
                    [btargs.lang.cancel]: function() {
                        jQuery( this ).dialog( "close" );
                    }
                }
            });
        });

        todo.on('click', function(e){
            const wrapper = todo.find('.todo-wrap');
            const isInlineEditEvent = jQuery(e.target).is('label') || jQuery(e.target).is('span');
            const isDoneToggle = jQuery(e.target).hasClass('todo-checkbox');
            if(isInlineEditEvent) {
                inlineEditTodo(wrapper);
            } else if(isDoneToggle){
                const label = todo.find('label');
                label.toggleClass('done');
                updatePercentage();
            }
        });
    }

    function inlineEditTodo(todo){
        const label = todo.find('label');
        const id = label.attr('id');

        todo.addClass('editing');

        const inline = jQuery('<input type="text" class="input-todo" id="input-todo' + id + '"/>');
        inline.val(label.text());
        todo.append(inline);

        label.text('');
        inline.focus();
        inline.enterKey(function(){inline.trigger('enterEvent');});

        inline.on('blur enterEvent',function(){
            const todoTitle = jQuery('#input-todo'+id+'').val();
            const todoTitleLength = todoTitle.length;
            if (todoTitleLength > 0) {
                label.append('<i class="dashicons dashicons-saved todo-checkbox"></i>' + todoTitle);
                inline.remove();
                todo.removeClass('editing');
            }
        });
    }

    function inlineEditList(list){
        isListInlineEditing = true;
        list.addClass('editing');

        const id = list.attr('id');
        const label = list.find('h2');
        const inline = jQuery('<input type="text" class="input-list" id="input-list' + id + '"/>');
        const oldTitle = label.text().trim();

        inline.val(oldTitle);
        label.html(inline);
        inline.focus();
        inline.enterKey(function(){inline.trigger('enterEvent');});

        inline.on('blur enterEvent',function(){
            const newTitle = jQuery('#input-list'+id+'').val();
            const newTitleLength = newTitle.length;

            label.append(newTitle + '<button type="submit" class="add-new-task-button"><i class="dashicons dashicons-plus"></i></button>');
            inline.remove();
            list.removeClass('editing');

            if (newTitleLength > 0 && newTitle !== oldTitle) {
                isRefreshing = true;

                label.addClass('autocomplete-loading');
                const data = {
                    'action' : 'edit_list',
                    'id' : id,
                    'name':  newTitle,
                    '_wpnonce': jQuery("input#_wpnonce_edit_list").val(),
                };

                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: data,
                    success: function(data){
                        label.removeClass('autocomplete-loading');
                        isRefreshing = false;
                        isListInlineEditing = false;
                    },
                    error: function(data){
                        label.removeClass('autocomplete-loading');
                        isRefreshing = false;
                        isListInlineEditing = false;
                    }
                });
            }
        });
    }

    function attachEditTaskFormEvents(){
        //attach the event to add new tasks in the list
        autocomplete();

        initializeRichEditor();
        initializeDatePicker();
    }

    function renderDialogs(){
        let html = '';

        html += `<div id="edit-task-dialog" style="display: none" title="`+btargs.lang.edit_task+`">
            <form id="edit-task-form">`;
        html +=  `<fieldset style="float: left; width: 100%">
                    <label htmlFor="edit-task-title">`+btargs.lang.title+`</label>
                    <input type="text" name="edit-task-title" id="edit-task-title"
                           class="text ui-widget-content ui-corner-all">
                </fieldset>
                <fieldset style="float: left; width: 100%">
                    <label htmlFor="edit-task-description">`+btargs.lang.description+`</label>
                    <div type="text" name="edit-task-description" id="edit-task-description"
                         class="text ui-widget-content ui-corner-all"></div>
                </fieldset>
                <fieldset style="float: left; width: 73%">
                    <label htmlFor="edit-task-assign-to">`+btargs.lang.assign_to+`</label>
                    <input type="text" name="edit-task-assign-to" id="edit-task-assign-to"
                           class="text ui-widget-content ui-corner-all">
                </fieldset>
                <fieldset style="float: left; width: 27%;">
                    <label htmlFor="edit-task-due-date">`+btargs.lang.due_date+`</label>
                    <input type="text" name="edit-task-due-date" id="edit-task-due-date"
                           class="text ui-widget-content ui-corner-all">
                        <input type="hidden" name="edit-task-due-date-epoch" id="edit-task-due-date-epoch"
                               class="text ui-widget-content ui-corner-all">
                </fieldset>

                <div id="users-invite-list" class="item-list"></div>

                <fieldset style="clear: left; padding-top: 20px">
                    <label htmlFor="add-todo">`+btargs.lang.tasks+`</label>
                    <div class="checklist-progress">
                        <span class="checklist-progress-percentage">0%</span>
                        <div class="checklist-progress-bar">
                            <div class="checklist-progress-bar-current" style="width: 0%;"></div>
                        </div>
                    </div>
                    <input type="text" name="add-todo" id="add-todo" class="text ui-widget-content ui-corner-all"
                           placeholder="`+btargs.lang.add_task_press_enter+`">
                </fieldset>

                <ul id="todo-list"></ul>

                <input type="hidden" id="edit-task-id">
                <input type="hidden" id="edit-task-list">
                <input type="hidden" id="edit-task-created-by">
            </form>
        </div>`;

        html += `<div id="dialog-delete-confirm" style="display: none" title="`+btargs.lang.delete_task+`">
            <p><i class="dashicons dashicons-warning delete-confirmation-message"></i>
                `+btargs.lang.delete_warning+`
            </p>
        </div>`;

        jQuery('.task-dialog').html(html);
    }
});



