const $ = require('jquery');

$(function() {
    $.ajax({  
        url: 'http://api.specialites.vlabs/specialities_filter',
        type: 'GET',   
        dataType: 'json',  
        async: true,
        success: function(data, status) {  
            data.forEach(element => {
                var image = element.media ? "images/"+element.media : "https://fr.gilson.com/pub/media/catalog/product/placeholder/default/NoImg.jpeg";

                var tags = '';
                (element.tags).forEach(tag => {
                    tags += tag + ' / ';
                });

                var specialityHtml = '<div class="col-12 col-md-4"><img src="'+image+'" class="img-fluid"/><p class="tags">'+tags.substring(0, tags.length - 2)+'</p><h2 class="speciality-title">'+element.name+'</p></div>';

                $('#specialities-wrapper').append(specialityHtml);
            });
        },  
        error : function(xhr, textStatus, errorThrown) {  
           alert('Ajax request failed.');  
        }  
     }); 
})