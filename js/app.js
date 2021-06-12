jQuery(function($){
  $("#likes-dislikes a").on("click", function(){
   let state = $(this).data("val");
   let span = $(this).parent().find("span");
    $.ajax({
      url: ajax_object.url,
      method: "post",
      data:{
        action: "my_likes_dislikes_action",
        post: ajax_object.post,
        state: state,
      }
    }).success(function(e){
      data = JSON.parse(e);
     $('#likes-dislikes li').each(function(){
        let state = $(this).find('a').data('val');
        $(this).find('span').text("["+data[state]+"]");
      });
    });
  });
});

/*jQuery(function($){
  $('#likes-dislikes a').on('click', function(){
    let state = $(this).data("val");
    $.ajax({
      url: ajax_object.url,
      method:"post", 
      data:{
        action: "my_likes_dislikes_action",
        post: ajax_object.post,
        state: state,
      }
    }).success(function(e){
      data = JSON.parse(e);
      $('#likes-dislikes li').each(function(){
        let state = $(this).find('a').data('val');
        $(this).find('span').text("["+data[state]+"]");
      });
        });
      });
 });*/