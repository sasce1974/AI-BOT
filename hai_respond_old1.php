<?php
require_once("functions.php");
$user = new User;
if (!$user->isLoggedIn) {
    die(header("Location: login/"));
}
try{
    $sentance = trim(filter_input(INPUT_POST, "input", FILTER_SANITIZE_STRING));
    $raw_words= explode(" ",$sentance);
    $num_words = count($raw_words);
   // print "Hai: there are $num_words words <br>";
    preg_match_all("/\b(fuck|ass|dick|fuckoff|pussy|f\*\*\*|f\*\*|f\*\*\*\*|porn|xxx|fuckyou|cock|shit)\b/i", $sentance, $bad_words);
    if(min($bad_words) && max($bad_words)) {
        die("<div class='hai'>HAI says:<br>Please don't be rude!</div>");
    }

    //checking if the input is a question
    preg_match_all("/\b(^who|^why|^what|^how|^where|^Do|^Did|^Are|^Does|^Didn't|^Doesn't|^Aren't|^when|^can|^may)\b/i",$sentance,$isQuestion);
    if((min($isQuestion) && max($isQuestion)) or strpos($sentance, "?")) {
        $question = 1;
    }else{
        $question = 0;
    }
    //print "<br>is it a question:$question";
    //checling if input is negation
    preg_match_all("/\b(not|don't|didn't|aren't|isn't|dont|didnt|arent|can't)\b/i",$sentance,$is_negation);
    if(min($is_negation) && max($is_negation)){
        $negation = 1;
    }else{
        $negation = 0;
    }
    //print "<br> is negation: $negation";
    //checking who is the subject
    preg_match_all("/\b(^i)\b/i",$sentance,$me_subject);
    preg_match_all("/\b(^you|^your|^yours|^you'r|^you'd|^you'll|^u)\b/i",$sentance,$you_subject);
    if(min($me_subject) && max($me_subject)) {
        $subject = 0;
    }elseif(min($you_subject) && max($you_subject)){
        $subject = 1;
    }else{
        $subject = 2;
    }
   // print "<br>subject is: $subject<br>";
    $new_words = array_diff($raw_words,array('i','you','he','me','do','don\'t','dont','l\'m','l\'ll','was',
    'why','when','where','who','what','how','a','an','the','in','at','on','by','but','she','to','be','of',
    'been','is','we','it','very','many','this','that','there','those','them','mine','self'));
    function shortWords($param){
        return strlen($param)>1;
    }
    $new_words = array_filter($new_words, function($a){ return strlen($a)>1;});
   
    
    $tags = implode(",", $new_words);
    $tags_num = count($new_words);
    $con = connectPDO();
    $insert_query = $con->prepare("INSERT INTO inserted (id, user_id,comment,tags) VALUES (NULL,?,?,?)");
    $insert_query->execute(array($user->id, $sentance, $tags));
    $con=$insert_query=null;

    if($sentance!=""){
        $con = connectPDO();
        $rows=array();
        foreach($new_words as $tag){
            $respond_query = $con->prepare("SELECT sentance FROM responds WHERE tags LIKE ? OR tags LIKE ? OR sentance LIKE ? OR sentance LIKE ?");
            $respond_query->execute(array("%$sentance%","%$tag%","%$sentance%","%$tag%"));
            $row = $respond_query->fetch(PDO::FETCH_NUM);
            $rows[] = $row;
        }
        foreach($new_words as $tag){
            $respond_query = $con->prepare("SELECT comment FROM inserted WHERE tags LIKE ? OR tags LIKE ? OR comment LIKE ? OR comment LIKE ?");
            $respond_query->execute(array("%$sentance%","%$tag%","%$sentance%","%$tag%"));
            $row = $respond_query->fetch(PDO::FETCH_NUM);
            $rows[] = $row;
        }
        $choice = array();
        if(empty($rows)) die("I don't understant that!");
        foreach ($rows as $row1){
            foreach ($row1 as $key=>$value){
                $choice[] = $value;
            }
        }
        shuffle($choice);
        $choice = filter_var($choice[0], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $query = $con->prepare("INSERT INTO inserted (id, user_id,comment,tags) VALUES (NULL,'Hai','{$choice}',NULL)");
        $query->execute(array("{$choice}"));
        $query = $con->query("SELECT id, user_id, comment FROM inserted WHERE user_id = '{$user->id}'
                             OR user_id = 0 ORDER BY id DESC LIMIT 15");
        $chats = $query->fetchAll(PDO::FETCH_NUM);
        asort($chats);
        foreach($chats as $chat){
           //print_r($chat); 
            if($chat[1]==0){
                print "<div class='hai'>HAI says:<br>" . $chat[2] . "</div>";
            }elseif($chat[1]!=0){
                print "<div class='me'>" . $user->name . " says:<br>" . $chat[2] . "</div>";
            }
        }
        $con = $respond_query = $query = $result = null;
    }
}catch(PDOException $e){
    print "Error: " . $e->getMessage();
}
?>