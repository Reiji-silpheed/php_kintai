<?php
    require_once './dbClasss.php';
    class login extends dbClass{
        public function loginCheck($employee_no,$password){
            $result=$this->select('SELECT * FROM m_employee WHERE employee_no=:employee_no and password=:password',['employee_no'=>$employee_no,'password'=>$password]);
            if ($result==false){
                return false;
            }
            else{
                return true;
            }
        }
    }
?>