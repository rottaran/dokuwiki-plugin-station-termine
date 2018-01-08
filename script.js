jQuery(function() {

    function init() {

        // TODO add inline struct editor
        if ( jQuery("#global__tools a.logout").length ) {
            // jQuery("station-termin-table").contextMenu({
            //     selector: '.editable',
            //     trigger: 'hover',
            //     delay: 500,
            //     autoHide: true,
            //     callback: function(key, options) {
            //         var m = "clicked: " + key + " on " + $(this).text();
            //         window.console && console.log(m) || alert(m);
            //     },
            //     items: {
            //         "edit": {name: "Edit", icon: "edit"},
            //     }
            // });
        }


        jQuery(".station-termin-foldable").each(function(idx) {
            jQuery(this).addClass("folded");
            // TODO not working, conflict with click: jQuery(this).dblclick(function(){ });
            jQuery(this).click(function(){
                jQuery(this).toggleClass("folded unfolded");
                jQuery(this).find('.station-termin-body').slideToggle('fast');
            });

            // not necessary because done by conditional CSS based on body.js
            // jQuery(this).find(".station-termin-body").hide();
        });
    }

    jQuery(init);

    // jQuery(window).on('fastwiki:afterSwitch', function(evt, viewMode, isSectionEdit, prevViewMode) {
    //     if (viewMode=="edit" || isSectionEdit) {
    //         init();
    //     }
    // });
});
