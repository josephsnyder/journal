// When page ready
$(document).ready(function(){

  if($('#disclaimerWrapper').length != 0)
  {
    $.fancybox.open([
    {
      href : '#disclaimerWrapper',
      closeBtn:false,
      helpers: {
        overlay: {closeClick: false}
      },
      keys : {
        close  : null
      }
    }]);
  }
});

