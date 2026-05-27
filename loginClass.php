<?php
    require_once './dbClass.php';
    class login extends dbClass{
        public function loginCheck($email,$password){
            $result=$this->select('SELECT * FROM m_employee WHERE email=:email and password=:password',['email'=>$email,'password'=>$password]);
            if ($result==false){
                return false;
            }
            else{
                return true;
            }
        }
    }
?>