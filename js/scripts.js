
//error debugging function
onerror = errorHandler;
function errorHandler(message, url, line)  {
    out  = "Sorry, an error was encountered.\n\n";
    out += "Error: " + message + "\n";
    out += "URL: "   + url     + "\n";
    out += "Line: "  + line    + "\n\n";
    out += "Click OK to continue.\n\n";
    alert(out);
    return true;
}


// On "Enter" key or "Send" button, send the message
$("#input").keypress(function(e){
    if (e.which===13) {
        e.preventDefault();
        sendMessage();
    }
});
function sendMessage() {
    var message = $("#input").val().trim();
    if(message !=="") {
        $.post("hai_respond.php", {"from_user": "user", "input": message}, showMe);
    }
}

/*
function writing(){
    var counter = 1, text = 'Writing.';
    setInterval(function () {
        if(counter < 3){
            text = text + '.';
            counter++;
        }else{
            text = 'Writing.';
            counter = 1;
        }
        return text;
    }, 300);

}
*/
function showMe(data, textStatus) {
    $("#hai_status").html("<span class='loading'>Writing</span>");

    function responseLength(data) {
        return (data.length - 31) - data.lastIndexOf("<div class='hai'>");
    }
    setTimeout(function () {
        $("#hai_status").html("");
        $("#messages").html(data);
        scrollDown();
    }, responseLength(data) * 200);

    //   $("#messages").append(data);
    // var n = $("#messages div").length;
    //   $("#messages").append(n);
    $("#input").val("");

    //   removeMessages();
}
function scrollDown() {
    $('#messages').stop().animate({
        scrollTop: $('#messages')[0].scrollHeight
    }, 150);
}
function removeMessages() {
    if ($("#messages div").length>10) {
        $("#messages div:lt(2)").remove();
    }
}

//Fade out a message from the session
var messages = document.getElementsByClassName('message');
if(messages.innerHTML != ""){
    setTimeout(function(){
        messages.innerHTML = "";
        $(".message").fadeOut(1500);
    }, 5000);
}