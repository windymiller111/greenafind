$(function(){
    getCategories();
    getChildCategories();
    getSubChildCategories();

    $(document).on('change', '#master-categories', function () {
        getCategories();
        return false;
    });    

    $(document).on('change', '#categories', function () {
        getChildCategories();
        return false;
    });    

    $(document).on('change', '#child-categories', function () {
        getSubChildCategories();
        return false;
    });    
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
                    $('#categories, #child-categories').html('<option value="">-- Please Select --</option>');
                }              
            },
            error: function(e) {                
                console.log(e);
            }
        });
    }else{
        $('#categories, #child-categories').html('<option value="">-- Please Select --</option>');
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


//resaturant resgister
    jQuery(document).ready(function($){
        $('.trigger_registration_model').on('click', function(){
        $('#becomePartnerModal .action_form').css('display','block');
        $('#becomePartnerModal .action_thankyou').css('display','none');
    });
        var _ROOT = "https://"+window.location.hostname+"/";

        $.validator.addMethod("noSpace", function(value, element) { 
            return value.indexOf(" ") < 0 && value != ""; 
        }, "No space please and don't leave it empty");

        $('form#insert_form').validate({
            rules: {
            "firstname": {required: true, maxlength:15, noSpace: true},
            "lastname": {required: true, maxlength:15, noSpace: true},
            "email": {required: true, remote: _ROOT+'users/chkemailexist'},
            "phone_number": {required: true, digits: true, minlength:10, maxlength:12, remote: _ROOT+'users/chkphoneexist'},
            "restaurant_name": {required: true},
            "address": {required: true},
        },
        messages: {
           "firstname": {required: "Please enter your first name."},
            "lastname": {required: "Please enter your last name."},
            "email": {required: "Please enter your email.", remote:"This email is already exist. Please use another email."},
            "phone_number": {required: "Please enter your phone number.", remote:"This phone number is already exist. Please use another one."},
            "restaurant_name": {required: "Please enter your restaurant name."},
            "address": {required: "Please enter your address."}
        },

        submitHandler: function(form)
        {
            var formData = jQuery("form#insert_form").serialize();
            var ajax_url = ajaxUrl;
            //$('#loading').html("<img src='"+image+"' />");
            $('#loading').html("loading........");

            $.ajax({
                type: "POST",
                async: true,
                url: ajax_url,
                data: formData,
                success: function(responseData) {
                    //console.log(responseData);
                    if(responseData == 1){
                        $('#loading').html("").hide();
                        document.getElementById("insert_form").reset();
                        $('#becomePartnerModal .action_form').css('display','none');
                        $('#becomePartnerModal .action_thankyou').css('display','block');
                        //$('#becomePartnerModal .close').trigger('click');
                    }else if (responseData == 2) {
                        $('#loading').html("loading........");
                    }
                }
            });
            return false;
        }
    });

        //addform validation
    $('form#addResturant').validate({
      rules: {
            "firstname": {required: true},
            "lastname": {required: true},
            "email": {required: true, remote: _ROOT+'users/chkemailexist'},
            "user_profile[phone_number]": {required: true, digits: true, remote: _ROOT+'users/adminchkphoneexist'},
            "user_profile[description]": {required: true}
        },
         messages: {
           "firstname": {required: "Please enter your first name."},
           "lastname": {required: "Please enter your last name."},
            "email": {required: "Please enter your email.", remote:"This email is already exist. Please use another email."},
            "user_profile[phone_number]": {required: "Please enter your phone number.", remote:"This phone number is already exist. Please use another one."},
            "user_profile[description]": {required: "Please enter your requirement."}
            
        },
     });

    //edit form validatin
    $('form#editResturant').validate({
      rules: {
            "user_profile[restaurant_name]": {required: true},
            "user_profile[address]": {required: true},
           // "user_profile[profile_image]": {required: true}
        },
         messages: {
           "user_profile[restaurant_name]": {required: "Please enter your restaurant name."},
            "user_profile[address]": {required: "Please enter your address."},
           // "user_profile[profile_image]": {required: "Please enter your image."}
            
        },
     });

     //change passord validation
     $('form#changPassword').validate({
      rules: {
            "oldpassword": {required: true},
            "password": {required: true},
            "confirm_password": {required: true, equalTo: "input[name='password']"}
        },
         messages: {
           "oldpassword": {required: "Please enter your old password."},
            "password": {required: "Please enter your new password."},
            "confirm_password": {required: "Please enter your confirm password.",  equalTo: "New Password and confirm password does not match."}
            
        },
     });

        //flash message hide
        $(".success").show();
        setTimeout(function() { $(".success").hide(); }, 5000);    


    //newsleter check email
    $("#newsleter").click(function(){
       var newsEmail = $('#emaiId').val();
       if(IsEmail(newsEmail)==false){
          $('#invalid_email').html('Please Enter valid Email-id !');
          return false;
        }
        var news_url = newsUrl;
        $.ajax({
            type: "POST",
            url: news_url,
            data: {newsEmail : newsEmail},
            success: function(responseData) {
                console.log(responseData);
                if(responseData == 1){
                    // console.log('yes');
                    $('.myResultDiv').html("You are Successfully Subscribe !");
                }
            }
        });
    });

    function IsEmail(email) {
        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if(!regex.test(email)) {
            return false;
        }else{
            return true;
        }
    }

        //close/open chek status admin
        $('.restStatus').change(function() {
            var item = $(this);
            var itemKey = $(this).attr('keyId');
            var status = item.val();
            if(status == 1){
                $(".times"+itemKey).attr("readonly", false); 
            }else{
                $(".times"+itemKey).attr("readonly", true); 
            }
        });
});