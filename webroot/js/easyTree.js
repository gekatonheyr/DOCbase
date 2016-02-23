(function ($) {
    $.fn.EasyTree = function (options) {
        var defaults = {
            i18n: {
                collapseTip: 'collapse',
                expandTip: 'expand'
            }
        };

        options = $.extend(defaults, options);

        this.each(function () {
            var easyTree = $(this);
            $.each($(easyTree).find('ul > li'), function() {
                var text;
                    text = $(this).text();
                    $(this).html('<span><span class="glyphicon"></span><a href="javascript: void(0);"></a> </span>');
                    $(this).find(' > span > span').addClass('glyphicon-folder-close');
                    $(this).addClass('parent_li');
                    $(this).find(' > span > a').text(text);
            });

            $(easyTree).delegate('li.parent_li > span', 'click', function (e) {
                parent_id = e.target.parentNode.parentNode.id;
                parent_obj = $('#'+parent_id);
                child_class = 'parent_li';
                span_icon = "glyphicon glyphicon-folder-close";
                url = '/ajax/pages/getStructure/' + parent_id + '?type=plain';
                if(parent_obj.find('ul').length == 0){
                    $.getJSON(url, function(data){
                        parent_obj.append('<ul/>');
                        $.each(data, function(key, value){
                            if(value['has_subnodes'] == 'n'){
                                child_class = 'tree_leaf';
                                span_icon = "glyphicon glyphicon-file";
                            }
                            if(value['current_table'] != undefined){
                                item_id = parent_id + '-' + value['current_table']+ '-' + value['successor_table'];
                            }else{
                                item_id = parent_id + '-' + value['alias'] + '-' + value['successor_table'];
                            }

                            html = '<span><span class="' + span_icon + '"></span><a href="javascript: void(0);">'+value['title']+'</a> </span>';
                            list_item = $('<li/>', {
                                class : child_class,
                                id : item_id,
                                html : html});
                            $('#'+parent_id+' > ul').append(list_item);
                        });
                    });
                }else{
                    var children = $(this).parent('li.parent_li').find(' > ul > li');
                    if (children.is(':visible')) {
                        children.hide('fast');
                        $(this).attr('title', options.i18n.expandTip)
                            .find(' > span.glyphicon')
                            .addClass('glyphicon-folder-close')
                            .removeClass('glyphicon-folder-open');
                    }else{
                        children.show('fast');
                        $(this).attr('title', options.i18n.collapseTip)
                            .find(' > span.glyphicon')
                            .addClass('glyphicon-folder-open')
                            .removeClass('glyphicon-folder-close');
                    }
                }
                e.stopPropagation();
            });

            $(easyTree).delegate('li.tree_leaf > span', 'click', function (e) {
                enterprise_alias = $('.active').filter('.active').attr('id');
                branch_id = $(this).parent().attr('id');
                enterprise_alias_parts = branch_id.split('-');
                table_suffix = enterprise_alias_parts[enterprise_alias_parts.length - 1];
                table_name = enterprise_alias + '-' + table_suffix;
                url = '/ajax/pages/getData/' + table_name + '?type=table';
                $.ajax(url).done(function(data){
                    $('#main_content').html(data);
                    //alert('we have it!!!');
                });
                e.stopPropagation();
            });
        });
    };
})(jQuery);