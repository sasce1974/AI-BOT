//Basic JavaScript Form Validation. Change the ID and Class names acording your form on your web page

//Initialization of submit form by ID:"userForm" NOT to submit in case of errors, and to provide info
$(document).ready(function() {
    $("#newUserForm").submit(function(e) {
        removeFeedback();
        var errors = validateForm();
        if (errors == "") {
            return true;
        } else {
            provideFeedback(errors);
            e.preventDefault();
            return false;
        }
    });
    
    function validateForm() {
        var errorFields = new Array();
        //Check required fields have something in them
        if ($("#ime").val() == "") {
            errorFields.push("ime");
        }
        if ($("#email").val() == "") {
            errorFields.push("email");
        }
        if ($("#password1").val() == "") {
            errorFields.push("password1");
        }
        if ($("#password2").val() != $("#password1").val()) {
            errorFields.push("password2");
        }
        //very basic e-mail check, just an @ symbol
        //if (!($("#email").val().indexOf(".") > 2) && ($("#email").val().indexOf("@"))) {
        if (!(($("#email").val()).match(/^.+@.+\..{2,4}$/))) {
            errorFields.push("email");
        }
        if ($("#phone").val() != "") {
            var phoneNum = $("#phone").val();
           // phoneNum.replace(/[^0-9]/g, "");
            //if (phoneNum.length != 10) { //Exactly 10 numbers! This can be changed
            //if (!(phoneNum.match(/^\d{3}-\d{3}-\d{3,4}$/))) {
            if (!(phoneNum.match(/^\d{9,11}$/))) {
                errorFields.push("phone");
            }
            if (!$("input[name=phonetype]:checked").val()) { //Check if the checkbox is not checked.
                errorFields.push("phonetype");
            }
        }
        return errorFields;
    } //end function validateForm
                 
    function provideFeedback(incomingErrors) {
        for (var i = 0; i < incomingErrors.length; i++) {
            $("#" + incomingErrors[i]).addClass("errorClass");
            $("#" + incomingErrors[i] + "Error"). removeClass("errorFeedback");
        }
        $("#errorDiv").html("Errors encountered");       
    }
    
    function removeFeedback() {
        $("#errorDiv").html("");
        $("input").each(function() {
            $(this).removeClass("errorClass");
        });
        $(".errorSpan").each(function() {
            $(this).addClass("errorFeedback");
        });
    }
});


var messages = document.getElementsByClassName('message');
if(messages.innerHTML != ""){
    setTimeout(function(){
        messages.innerHTML = "";
        $(".message").fadeOut(1500);
    }, 5000);
}