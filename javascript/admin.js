(function ($) {
    $.entwine('ss', function ($) {
        $(".ss-gridfield-paste-session a").entwine({
            onclick: function () {
                var me = this,
                    link = this.data('href');

                if(me.parent().hasClass('ui-state-disabled')) {
                    return false;
                }

                $.ajax({
                    type: "POST",
                    url: link,
                    data: {
                        pageId: this.data('pageid')
                    }
                })
                    .always(function () {
                        me.getGridField().reload();
                    });

                return false;
            }
        });
    });
}(jQuery));
