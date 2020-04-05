
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

$(document).ready(function(){
    $.post("respondController.php", {'all_conversation':'all'}, showAll);
});

function showAll(data, textStatus) {

    $("#messages").html(data);
    scrollDown();


    //   $("#messages").append(data);
    // var n = $("#messages div").length;
    //   $("#messages").append(n);
    $("#input").val("");

    //   removeMessages();
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
        $.post("respondController.php", {"from_user": "user", "input": message}, showMe);
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
    $("#hai_status").html("<span class='loading'></span>");

    if(data == "<div class='message_bubble hai'>BYEBYE</div>"){
        document.location = '/login/logout.php';
    }


    $("#messages").append(data);
    $("#messages").children(".me").removeClass('squized');
    scrollDown();
    setTimeout(squiz, responseLength(data) * 30);
    function squiz (){
        $("#hai_status").html("");
        $("#messages").children(".hai").removeClass('squized');
        scrollDown();
    }

    function responseLength(data) {
        //var length = (data.length - 31) - data.lastIndexOf("<div class='message_bubble hai'>");
        return (data.length > 200) ? 200 : data.length;
    }

    /*setTimeout(function () {
        $("#hai_status").html("");
        $("#messages").html(data);
        $("#messages").append(data);
        scrollDown();
    }, responseLength(data) * 200);*/

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


/** Register the Service Worker */
/*

if ("serviceWorker" in navigator) {
    window.addEventListener("load", function() {
        navigator.serviceWorker
            .register("/serviceWorker.js")
            .then(res => console.log("service worker registered"))
            .catch(err => console.log("service worker not registered", err))
    })
}*/
