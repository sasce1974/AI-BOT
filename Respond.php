<?php
/**
 * Created by PhpStorm.
 * User: Saso
 * Date: 3/28/2020
 * Time: 10:35 AM
 */

class Respond
{

    private $hai_says = null;
    private $person_says = null;
    private $new_words = array();
    private $is_question;
    private $is_negation;
    private $who_is_subject;
    private $tags;
    private $user_id;


    private $bad_words_counted = 0;
    private $con = null;

    private $questions = array("who","why","what","how","where","do","did","are","does","didn't",
                                "doesn't","aren't","when","can","may","have","is","which","should",
                                "would");

    private $negations = array("not","don't","didn't","aren't","isn't","dont","didnt","arent",
                                "can't","no","neither", "none","never","nah");


    private $bad_words = array("fuck","asshole","dick","fuckoff","pussy","f\*\*\*","f\*\*",
                                "f\*\*\*\*","fuckyou","cock","shit");


    private $words_not_needed_in_tags = array('i','you','he','me','do','don\'t','dont','l\'m','l\'ll','was',
        'why','when','where','who','what','how','a','an','the','in','at','on','by','but','she','to','be','of',
        'been','is','we','it','very','many','this','that','there','those','them','mine','self');


    function __construct()
    {
        $this->con = connectPDO();
        $user = new User();
        $this->user_id = $user->id;

    }



    /**
     * @function isQuestion checks for the sentence if it contains some of the
     * question words at the beginning ($this->questions array) or a question mark "?".
     * Array is converted to string to fit in the preg_mach as a pattern.
     * @param $sentence
     * @return bool true if there is a match or "?", or false if no match.
     */
    public function isQuestion($sentence){
        $questions = implode("|", $this->questions);
        return (preg_match("/\b^($questions)\b/i", $sentence) ||
            strpos($sentence, "?"));
    }



    /**
     * @function isNegation checks for the sentence if it contains some of the
     * negation words ($this->negations array).
     * Array is converted to string to fit in the preg_mach as a pattern.
     * @param $sentence
     * @return bool true if there is a match, or false if no match.
     */
    public function isNegation($sentence){
        $negations = implode("|", $this->negations);
        return (preg_match("/\b($negations)\b/i",$sentence));
    }

    /**
     * @function whatSubject checks for the sentence who is the subject (me, you or he/she).
     *
     * @param $sentence
     * @return int 0 for "me", 1 for "you" and 2 for "he/she".
     */
    public function whatSubject($sentence){
        if(preg_match("/\b(i|^me|myself)\b/i",$sentence)) {
            $subject = 0;
        }elseif(preg_match("/\b(your|yours|you'r|you'd|you'll|u|you)\b/i",$sentence)){
            $subject = 1;
        }else{
            $subject = 2;
        }
        return $subject;
    }


    /**
     * @function hasBadWords checks for the sentence if it contains some of the
     * bad words ($this->bad_words array).
     * Array is converted to string to fit in the preg_mach as a pattern.
     * @param $sentence
     * @return bool true if there is a match, or false if no match.
     */
    public function hasBadWords($sentence){
        $bad_words = implode("|", $this->bad_words);
        return preg_match("/($bad_words)/i", $sentence);
    }

    private function updateUserBadWords(){
        global $user;
        $query = "UPDATE user SET bad_words = " . ($user->bad_words + 1) . " WHERE id = " . $user->id;
        $r = $this->con->query($query);
        if($r->rowCount() === 1){
            $_SESSION['bad_words'] = $_SESSION['bad_words'] + 1;
            return true;
        }else{
            return false;
        }
    }


    private function checkLanguage ($text){
        $ld = new Text_LanguageDetect();
        return $ld->detectSimple($text);
    }

    private function saveOutput($user_id, $text, $tags, $is_question, $who_is_subject, $is_negation){
        $insert_query = $this->con->prepare("INSERT INTO inserted (id, user_id,comment,tags,q_a,m_y_o,n_p) VALUES (NULL,?,?,?,?,?,?)");
        $insert_query->execute(array($user_id, $text, $tags, $is_question, $who_is_subject, $is_negation));
        if($insert_query->rowCount() === 1){
            return true;
        }else{
            return false;
        }
    }


    private function saveHaiAnswer($text){

        $query = $this->con->prepare("INSERT INTO inserted (id, user_id, hai_message, comment,tags,q_a,m_y_o,n_p) VALUES
            (NULL,'Hai','{$this->user_id}',?,NULL,NULL,NULL,NULL)");
        $query->execute(array($text));
    }




    public function getConversation(){

        global $user;

        $conversation = array();
        $query = $this->con->query("SELECT id, user_id, hai_message, comment FROM inserted WHERE user_id = '{$this->user_id}'
            OR user_id = 0 AND hai_message = '{$this->user_id}' ORDER BY id DESC LIMIT 15");

        $chats = $query->fetchAll(PDO::FETCH_NUM);

        asort($chats);
        if(empty($chats)) return $conversation[0]['hai'] = "Hello " . $user->name;

        foreach($chats as $chat){

            if($chat[1]==0){
                if($chat[3]!=""){
                    $conversation[]['hai'] = $chat[3];
                    //print "<div class='message_bubble hai'>" . $chat[3] . "</div>";
                }else{
                    $conversation[]['hai'] = "I don't understand that!";
                    //print "<div class='message_bubble hai'>I don't understand that!</div>";
                }
            }elseif($chat[1] > 0){
                $conversation[]['me'] = $chat[3];
                //print "<div class='message_bubble me'>" . $chat[3] . "</div>";
            }
        }
        return $conversation;
    }






    private function processInput($input){

        $sentance = trim(filter_var($input, FILTER_SANITIZE_STRING));
        if(empty($sentance)) return false;

        $raw_words= explode(" ",$sentance);

        //$num_words = count($raw_words);

        //if there are bad words, HAI responds...
        if($this->hasBadWords($sentance)){

            //count how many times this happened...
            if($this->updateUserBadWords()){
                $this->hai_says = "Please don't be rude! ";
            }else{
                $this->hai_says = "I will try to forget what you wrote!";
            }


        }

        //Check the language - TODO doesn't work well
        /*$language = $this->checkLanguage($sentance);
        if($language != 'english'){
            $this->hai_says = "Are you are writing in " . $language . " language? I don't understand that.";
        }*/

        //Check if the sentence is question, negation and who is the subject
        // (question - true|false, negation - true|false, subject: me-0, you-1, other-2)

        $this->is_question = +$this->isQuestion($sentance);

        $this->is_negation = +$this->isNegation($sentance);

        $this->who_is_subject = $this->whatSubject($sentance);

        //filter the not needed words from the tags
        $this->new_words = array_diff($raw_words, $this->words_not_needed_in_tags);

        //filter words with only 1 letter
        $this->new_words = array_filter($this->new_words, function($a){ return strlen($a)>1;});
        $this->new_words = array_filter($this->new_words, function($a){ return !strpos($a, '&');});


        $tempArray = array();
        foreach ($this->new_words as $some_word){
            $tempArray[] = preg_replace('/[^\w]/', ',', $some_word);
        }
        $this->new_words = $tempArray;


        //making all tags in one string to prepare for database
        $this->tags = implode(",", $this->new_words);


        //insert the sentence, tags and other things in inserted table
        //if there is still no response from HAI - no wrong input!
        /*if($this->hai_says == null) {
            $this->saveOutput($this->user_id, $sentance, $this->tags, $this->is_question,
                $this->who_is_subject, $this->is_negation);
        }*/

        //returns the $person_says input or false

        return $this->person_says = $sentance;


    }

    public function testNewWords(){
        return $this->new_words;
    }


    private function hai_says(){

        //if HAI already have some answer from the function processInput,
        //then input is probably wrong so there is already answer
        if($this->hai_says != null){
            return $this->hai_says;
        }

        if($this->is_question == 1){
            $question_query = "AND q_a = " . 0; //looking for NOT questions
        }else{
            $question_query = "AND q_a = " . 1;
        }
        if($this->is_negation == 1){
            $negation_query = "AND n_p = " . 1; //NONE negation
        }else{
            $negation_query = "AND n_p = " . 0;
        }
        if($this->who_is_subject == 0){
            $subject_query = "AND m_y_o = " . 1;//if me -> YOU subject
        }elseif($this->who_is_subject == 1){
            $subject_query = "AND m_y_o = " . 0;//if you -> ME subject
        }else{
            $subject_query = "AND m_y_o = " . 2; // about others subject
        }


        $rows=array();
        foreach($this->new_words as $tag){

            $respond_query = $this->con->prepare("SELECT sentance FROM responds WHERE tags LIKE ? OR sentance LIKE ?
            {$question_query} {$negation_query} {$subject_query}");
            $respond_query->execute(array("%$tag%","%$tag%"));
            $row = $respond_query->fetch(PDO::FETCH_NUM);
            $rows[] = $row;
        }
        foreach($this->new_words as $tag){

            $respond_query = $this->con->prepare("SELECT comment FROM inserted WHERE 
            NOT user_id = 0 AND tags LIKE ? OR comment LIKE ? 
            {$question_query} {$negation_query} {$subject_query}");
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

        $choice = array_filter($choice);
        $num_records = count($choice);

        if($num_records>0){

            shuffle($choice);


            //Save the user text in the table inserted
            $this->saveOutput($this->user_id, $this->person_says, $this->tags, $this->is_question,
                $this->who_is_subject, $this->is_negation);

            //Save the HAI response into table inserted
            $this->saveHAIanswer($choice[0]);

            return $this->hai_says = $choice[0];

        }else{

            return $this->hai_says = "I don't understand that!";
        }


    }

    public function getAnswer($text){
        $conversation = array();
        $conversation['me'] = $this->processInput($text);
        $conversation['hai'] = $this->hai_says();

        return $conversation;
    }



}