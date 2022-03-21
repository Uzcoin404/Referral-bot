<?php 
    class UserData {
        public $chat_id;

        public function __construct($chat_id){
            $this->chat_id = $chat_id;
        }

        public function setData($where, $data){
            file_put_contents("users/$where/$this->chat_id.json", $data);
        }
        public function getData($where){
            return file_get_contents("users/$where/$this->chat_id.json");
        }
    }
    
?>