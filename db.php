<?php
    class Database{
        private $hostname = 'localhost';// mysql-72898-0.cloudclusters.net
        private $username = 'b90622t9_db';
        private $password = '9eKEGr&L';
        private $database = 'b90622t9_db';
        private $port = 3306;
        
        private function connect(){
            $mysqli = new mysqli($this->hostname, $this->username, $this->password, $this->database, $this->port);
            
            if ($mysqli->connect_error) {
                die('Connect Error (' . $mysqli->connect_errno . ')  '. $mysqli->connect_error);
            }
            return $mysqli;
        }
        
        public function setUser($chatID, $fullName, $date){
            $chatID = mysqli_real_escape_string($this->connect(), $chatID);
            $fullName = mysqli_real_escape_string($this->connect(), addslashes($fullName));
            
            $query = mysqli_query($this->connect(), "INSERT INTO `users` (`tg_id`, `full_name`, `date`) VALUES ($chatID, '$fullName', $date)");
        }
        
        public function getUser($chatID){
            $chatID = mysqli_real_escape_string($this->connect(), $chatID);
            
            $query = mysqli_query($this->connect(), "SELECT * FROM `users` WHERE `tg_id` = $chatID LIMIT 1");
            if (mysqli_num_rows($query) > 0) {
                return mysqli_fetch_assoc($query);
            } else {
                return false;
            }
        }

        public function addReferral($ID, $referrals, $balance){
            $ID = mysqli_real_escape_string($this->connect(), $ID);
            $referrals = (int)$referrals + 1;
            $balance = (int)$balance + 500;

            $query = mysqli_query($this->connect(), "UPDATE `users` SET `referrals` = $referrals, `balance` = $balance WHERE id = $ID");
        }
        
        public function botStatistics(){
            $query = mysqli_query($this->connect(), "SELECT * FROM `users`");
            $output = [];
            $today = strtotime('00:00');
            $count = $todayCount = $referrals = 0;

            while ($user = mysqli_fetch_assoc($query)) {
                if ($user['date'] - $today > 0 ) {
                    $todayCount += 1;
                }
                $count += 1;
                $referrals += $user['referrals'];
            }
            array_push($output, ['count' => $count, 'todayCount' => $todayCount, 'referrals' => $referrals]);
            return $output;
        }

        public function editBalance($chatID, $balance){
            $chatID = mysqli_real_escape_string($this->connect(), $chatID);
            $balance = (int)$balance;

            $query = mysqli_query($this->connect(), "UPDATE `users` SET `balance` = $balance WHERE tg_id = $chatID");
        }
    }
    $db = new Database;
    echo "connected";
    echo json_encode($db->botStatistics(), JSON_PRETTY_PRINT);
?>