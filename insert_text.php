<?php
require_once("functions.php");
$user = new User;
if (!$user->isLoggedIn) {
    die(header("Location: login/"));
}
?>
<form action="" method="post">
    <input type="url" name="url">
    <button type="submit">OK</button>
</form>
<?php
try{
    function readPage($url){
        return file_get_contents($url);
    }
    $file = array();
    if(isset($_POST['url']) && $_POST['url']!=""){
        $url = filter_input(INPUT_POST, "url", FILTER_SANITIZE_URL);
        $file = readPage($url);
        
        function sanitizeString($var)  {
            $var = strip_tags($var);
            $var = htmlentities($var);
            $var = stripslashes($var);
            return $var;
        }
        $file = sanitizeString($file);
        //$sentances = explode(".",$file);
        $sentances = preg_split("/(\.|\?|\!)/",$file);
        print "<pre>";
        print_r($sentances);
        print "</pre>";
        foreach($sentances as $sentance){
 
    $sentance = trim($sentance);
    $raw_words= explode(" ",$sentance);

    //checking if the input is a question
    preg_match_all("/\b(^who|^why|^what|^how|^where|^Do|^Did|^Are|^Does|^Didn't|^Doesn't|^Aren't|^when|^have|^is|^which|^may|^can)\b/i",$sentance,$isQuestion);
    if((min($isQuestion) && max($isQuestion)) or strpos($sentance, "?")) {
        $question = 1;
    }else{
        $question = 0;
    }
    //checling if input is negation
    preg_match_all("/\b(no|not|don't|didn't|aren't|isn't|dont|didnt|arent)\b/i",$sentance,$is_negation);
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
    $new_words = array_diff($raw_words,array(' ','i','you','he','me','do','don\'t','dont','l\'m','l\'ll','was',
    'why','when','where','who','what','how','a','an','the','in','at','on','by','but','she','to','be','of',
    'been','is','we','it','very','many','this','that','there','those','them','mine','self'));

    $new_words = array_filter($new_words, function($a){ return strlen($a)>1;});

    $tags = implode(",", $new_words);

    $tags_num = count($new_words);
    $con = connectPDO();
    $insert_query = $con->prepare("INSERT INTO responds (id, sentance,tags,q_a, m_y_o,n_p) VALUES (NULL,?,?,?,?,?)");
    $insert_query->execute(array($sentance, $tags,$question,$subject,$negation));
    $con=$insert_query=null;
        } //end foreach
    } //end isset
}catch(PDOException $e){
    print "Error: " . $e->getMessage();
}
?>