<?php
require_once("functions.php");
$user = new User;
if (!$user->isLoggedIn) {
    die(header("Location: login/"));
}

    //checking if the input is a question
    function checkQuestion($sen){
        preg_match_all("/\b(^who|^why|^what|^how|^where|^Do|^Did|^Are|^Does|^Didn't|^Doesn't|^Aren't|^when|^can|^may|^have|^is|^which)\b/i",$sen,$isQuestion);
        if((min($isQuestion) && max($isQuestion)) or strpos($sen, "?")) {
            $question = 1;
        }else{
            $question = 0;
        }
        return $question;
    }

        //checling if input is negation
    function checkNegation($sen){
        preg_match_all("/\b(not|don't|didn't|aren't|isn't|dont|didnt|arent|can't|no)\b/i",$sen,$is_negation);
        if(min($is_negation) && max($is_negation)){
            $negation = 1;
        }else{
            $negation = 0;
        }
        return $negation;
    }

        //checking who is the subject
    function checkSubject($sen){
        preg_match_all("/\b(^i|^Where I|^When I|^Can I|^Could I|May I|Who I)\b/i",$sen,$me_subject);
        preg_match_all("/\b(^you|^your|^yours|^you'r|^you'd|^you'll|^u|you)\b/i",$sen,$you_subject);
        if(min($me_subject) && max($me_subject)) {
            $subject = 0;
        }elseif(min($you_subject) && max($you_subject)){
            $subject = 1;
        }else{
            $subject = 2;
        }
        return $subject;
    }
    
    if(isset($_POST['from_user']) && $_POST['from_user']!=""){
        $sentance = trim(filter_input(INPUT_POST, "input", FILTER_SANITIZE_STRING));
        $raw_words= explode(" ",$sentance);
        $num_words = count($raw_words);
       // print "Hai: there are $num_words words <br>";
        preg_match_all("/\b(fuck|ass|dick|fuckoff|pussy|f\*\*\*|f\*\*|f\*\*\*\*|porn|xxx|fuckyou|cock|shit)\b/i", $sentance, $bad_words);
        if(min($bad_words) && max($bad_words)) {
            die("<div class='hai'>HAI says:<br>Please don't be rude!</div>");
        }
    
        //checking if the input is a question
        $question = checkQuestion($sentance);
        //print "<br>is it a question:$question";
        //checling if input is negation
        $negation = checkNegation($sentance);
        //print "<br> is negation: $negation";
        //checking who is the subject - you, me or other
        $subject = checkSubject($sentance);
       // print "<br>subject is: $subject<br>";
        $new_words = array_diff($raw_words,array('i','you','he','me','do','don\'t','dont','l\'m','l\'ll','was',
        'why','when','where','who','what','how','a','an','the','in','at','on','by','but','she','to','be','of',
        'been','is','we','it','very','many','this','that','there','those','them','mine','self'));
        function shortWords($param){
            return strlen($param)>1;
        }
        $new_words = array_filter($new_words, function($a){ return strlen($a)>1;});
        if($sentance=="" || $sentance==" ") $sentance = "Undefined";
        $tempArray = array();
        foreach ($new_words as $some_word){
            $tempArray[] = preg_replace('/[^\w]/', '', $some_word);
        }
        $new_words = $tempArray;
        
        $tags = implode(",", $new_words); //making all tags in one string
        $tags_num = count($new_words);
        $con = connectPDO();
        $insert_query = $con->prepare("INSERT INTO inserted (id, user_id,comment,tags,q_a,m_y_o,n_p) VALUES (NULL,?,?,?,?,?,?)");
        $insert_query->execute(array($user->id, $sentance, $tags, $question, $subject, $negation));
        //$query = $con->query("SELECT comment FROM inserted WHERE user_id = $user->id ORDER BY id DESC LIMIT 1");
        //$result = $query->fetch(PDO::FETCH_ASSOC);
        //$user_response = $result['comment'];
        //print "<div class='me'>" . $user->name . " says:<br>" . $user_response . "</div>";
        //print "<div class='me'>" . $user->name . " says:<br>" . $sentance . "</div>";
        $insert_query=$query = $result = null;
    }

  //  if(isset($_POST['from_hai']) && $_POST['from_hai']!=""){
        //$con = connectPDO();
        //$query = $con->query("SELECT comment, tags FROM inserted WHERE user_id = $user->id ORDER BY id DESC LIMIT 1");
        //$result = $query->fetch(PDO::FETCH_ASSOC);
        //$sentance = $result['comment'];
        //$tags = $result['tags'];
        //$question = checkQuestion($sentance);
        //$negation = checkNegation($sentance);
        //$subject = checkSubject($sentance);
        
        if($question == 1){
            $question_query = "AND q_a = 0"; //looking for NOT questions
        }else{
            $question_query = "AND q_a = 1";
        }
        if($negation == 1){
            $negation_query = "AND n_p = 1"; //NONE negation
        }else{
            $negation_query = "AND n_p = 0";
        }
        if($subject == 0){
            $subject_query = "AND m_y_o = 1";//if me -> YOU subject
        }elseif($subject == 1){
            $subject_query = "AND m_y_o = 0";//if you -> ME subject
        }else{
            $subject_query = "AND m_y_o = 2"; // about others subject
        }
        
        $rows=array();
        foreach($new_words as $tag){
        /*    $respond_query = $con->prepare("SELECT sentance FROM responds WHERE tags LIKE ? OR tags LIKE ?
            OR sentance LIKE ? OR sentance LIKE ? $question_query $negation_query $subject_query");
            $respond_query->execute(array("%$sentance%","%$tag%","%$sentance%","%$tag%")); */
            $respond_query = $con->prepare("SELECT sentance FROM responds WHERE tags LIKE ? OR sentance LIKE ?
            {$question_query} {$negation_query} {$subject_query}");
            $respond_query->execute(array("%$tag%","%$tag%"));
            $row = $respond_query->fetch(PDO::FETCH_NUM);
            $rows[] = $row;
        }
        foreach($new_words as $tag){
         /*   $respond_query = $con->prepare("SELECT comment FROM inserted WHERE tags LIKE ? OR tags LIKE ?
            OR comment LIKE ? OR comment LIKE ? $question_query $negation_query $subject_query");
            $respond_query->execute(array("%$sentance%","%$tag%","%$sentance%","%$tag%")); */
            $respond_query = $con->prepare("SELECT comment FROM inserted WHERE tags LIKE ?
            OR comment LIKE ? {$question_query} {$negation_query} {$subject_query}");
            $respond_query->execute(array("%$tag%","%$tag%"));
            $row = $respond_query->fetch(PDO::FETCH_NUM);
            $rows[] = $row;
        }
        $choice = array();
        foreach ($rows as $row1){
            foreach ((array)$row1 as $key=>$value){
                $choice[] = $value;
            }
        }
        
        //$all_choices = array_diff($choice, array(""," ","\n"));
        $choice = array_filter($choice);
        $num_records = count($choice);
        if($num_records>0){
            shuffle($choice);
            $query = $con->prepare("INSERT INTO inserted (id, user_id, hai_message, comment,tags,q_a,m_y_o,n_p) VALUES
            (NULL,'Hai','{$user->id}',?,NULL,NULL,NULL,NULL)");
            $query->execute(array($choice[0]));
            $query = $con->query("SELECT id, user_id, hai_message, comment FROM inserted WHERE user_id = '{$user->id}'
            OR user_id = 0 AND hai_message = '{$user->id}' ORDER BY id DESC LIMIT 15");
            $chats = $query->fetchAll(PDO::FETCH_NUM);
            asort($chats);
            foreach($chats as $chat){
               //print_r($chat); 
                if($chat[1]==0){
                    if($chat[3]!=""){
                       /* print "<div class='hai' style='font-size:50%;'>HAI says: $num_records<br>";
                        for($i=0;$i<$num_records;$i++){
                            print $choice[$i] . "<br>";
                        }
                        print "</div>"; */
                        print "<div class='hai'>HAI:<br>" . $chat[3] . "</div>";
                    }else{
                      /*  print "<div class='hai' style='font-size:50%;'>HAI says: $num_records<br>";
                        for($i=0;$i<$num_records;$i++){
                            print $choice[$i] . "<br>";
                        }
                        print "</div>"; */
                        print "<div class='hai'>HAI:<br>I don't understand that!</div>";
                    }
                }elseif($chat[1]!=0){
                    print "<div class='me'>You:<br>" . $chat[3] . "</div>";
                }
            }
        }else{
            print "<div class='hai'>HAI:<br>I don't understand that!</div>";
        }
        
       // print "<div class='hai'>HAI says:<br>" . $sentance . "<br>" . $tags . "</div>";
        
       // print "<div class='hai'>HAI says:<br>" . $choice . "</div>";
        //$con = $respond_query = $query = $result = null;
        $con = $query = $result = null;
   // }
//}catch(PDOException $e){
//    print "Error: " . $e->getMessage();
//}
?>