(function($) {
    var $pbmAdmin = $('.pbm-admin-section');    
    $pbmAdmin.hide();
    $('#pbm-activity').show();
        
    $('#pbm-tabs li').on('click', function(){
        $(this).parent().find('.active').removeClass('active');
        $(this).addClass('active');
        var index = $(this).index();
        if(index === 0) {
            $pbmAdmin.hide();
            $('#pbm-activity').show();
        } else if (index === 1) {
            $pbmAdmin.hide();
            $('#pbm-manual-push').show();
        } else {
            $pbmAdmin.hide();            
            $('#pbm-settings').show();
        }
    });

    var pbmInput = $('#pbmManualNote');
    var pbmCount = $('#pbmManualNoteCountInt');
    var pbmLimit = 70;

    pbmInput.keyup(function() {
        var n = this.value.replace(/{.*?}/g, '').length;
        if ( n > ( pbmLimit - 11 ) ){
            if(!pbmCount.hasClass('pbmWarning')){
                pbmCount.addClass('pbmWarning');   
            }
        } else if ( n < pbmLimit - 10 ) {
            if(pbmCount.hasClass('pbmWarning')){
                pbmCount.removeClass('pbmWarning');   
            }
        }
        pbmCount.text( 0 + n );
    }).triggerHandler('keyup');
})(jQuery);
