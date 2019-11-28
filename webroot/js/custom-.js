$(function(){
    $(document).on('change', '#master-categories', function () {
        getCategories();
    });    

    $(document).on('change', '#categories', function () {
        getChildCategories();
    });    

    $(document).on('change', '#child-categories', function () {
        getSubChildCategories();
    });

    getCategories();
    getChildCategories();
    getSubChildCategories();
});

function getCategories(){
    var master_category_id = $('#master-categories').val();
    var ajax_url = $('#master-categories').attr('category-ajax');        
    if(master_category_id !=''){
        $.ajax({
            type: "post",
            url: ajax_url,
            data: {master_category_id:master_category_id},
            beforeSend: function(xhr) {
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            },
            success: function(response) {
                if (response) {
                    $('#categories').html('');
                    $('#categories').html(response);
                    var sci = $('#selected_category_id').val();
                    if(sci){  
                        $('#categories').val(sci);
                    }
                }else{
                    $('#categories').html('');
                    $('#categories').html('<option value="">-- Please Select --</option>');
                }              
            },
            error: function(e) {                
                console.log(e);
            }
        });
    }else{
        $('#categories').html('<option value="">-- Please Select --</option>');
    }
}

function getChildCategories(){
    var category_id = $('#categories').val();
    var ajax_url = $('#categories').attr('child-ajax');        
    if(category_id !=''){
        $.ajax({
            type: "post",
            url: ajax_url,
            data: {category_id:category_id},
            beforeSend: function(xhr) {
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            },
            success: function(response) {
                if (response) {
                    $('#child-categories').html('');
                    $('#child-categories').html(response);
                    var scci = $('#selected_child_category_id').val();
                    if(scci){  
                        $('#child-categories').val(scci);
                    }                  
                }else{
                    $('#child-categories').html('');
                    $('#child-categories').html('<option value="">-- Please Select --</option>');
                    getSubChildCategoriesByCategoryId(category_id);
                }
                                
            },
            error: function(e) {                
                console.log(e);
            }
        });
    }else{
        $('#child-categories').html('<option value="">-- Please Select --</option>');
    }
}

function getSubChildCategories(){
    var child_category_id = $('#child-categories').val();
    var ajax_url = $('#child-categories').attr('sub-child-ajax');
    if(child_category_id !=''){
        $.ajax({
            type: "post",
            url: ajax_url,
            data: {child_category_id:child_category_id},
            beforeSend: function(xhr) {
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            },
            success: function(response) {
                if (response) {
                    $('#sub-child-categories').html('');
                    $('#sub-child-categories').html(response);
                }else{
                    $('#sub-child-categories').html('');
                    $('#sub-child-categories').html('<option value="">-- Please Select --</option>');
                }
                
            },
            error: function(e) {                
                console.log(e);
            }
        });
    }else{
        $('#sub-child-categories').html('<option value="">-- Please Select --</option>');
    }

}

function getSubChildCategoriesByCategoryId(category_id){
    var ajax_url = $('#categories').attr('sub-child-category-ajax');
    var master_category_id = $('#master-categories').val();
    if(category_id !='' && master_category_id !=''){
        $.ajax({
            type: "post",
            url: ajax_url,
            data: {category_id:category_id, master_category_id:master_category_id},
            beforeSend: function(xhr) {
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            },
            success: function(response) {
                if (response) {
                    $('#sub-child-categories').html('');
                    $('#sub-child-categories').html(response);
                                      
                }else{
                    $('#sub-child-categories').html('');
                    $('#sub-child-categories').html('<option value="">-- Please Select --</option>');
                    
                }
                                
            },
            error: function(e) {                
                console.log(e);
            }
        });
    }else{
        $('#sub-child-categories').html('<option value="">-- Please Select --</option>');
    }
}