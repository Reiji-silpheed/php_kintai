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
    public function searchCheck($number,$name,$mail,$start_date){
        $list=array();
        $where='';
        $param=array();
        $sql="SELECT * FROM m_employee";
        if(isset($_GET['searchBtn'])){
            if(!empty($_GET['number-search-get'])){
                $list[]='employee_no=:employee_no';
                $param['employee_no']=$_GET['number-search-get'];
            }
            if(!empty($_GET['name-search-get'])){
                $list[]='employee_name=:employee_name';
                $param['employee_name']=$_GET['name-search-get'];
            }
            if(!empty($_GET['mail-search-get'])){
                $list[]='email=:email';
                $param['email']=$_GET['mail-search-get'];
            }
            if(!empty($_GET['calendar-search-get'])){
                $list[]='start_date=:start_date';
                $param['start_date']=$_GET['calendar-search-get'];
            }
            if(!empty($list)){
                $where=implode(' and ',$list);
                $sql.=" WHERE {$where}";
            }
        } 
        $result=$this->select($sql,$param);
        if ($result==false){
            return false;
        }
        else{
            return true;
        }
    }
    public function holidaySearchCheck($date,$name){
        $list=array();
        $where='';
        $param=array();
        $sql='SELECT * FROM m_holiday';
        if(isset($_GET['searchBtn'])){
            if(!empty($_GET['holidayDate'])){
                $list[]='yyyymmdd=:yyyymmdd';
                $param['yyyymmdd']=$_GET['holidayDate'];
            }
            if(!empty($_GET['holidayName'])){
                $list[]='holiday_name=:holiday_name';
                $param['holiday_name']=$_GET['holidayName'];
            }
            if(!empty($list)){
                $where=implode(' and ',$list);
                $sql.=" WHERE {$where}";
            }
        }
        $result=$this->select($sql,$param);
        if ($result==false){
            return false;
        }
        else{
            return true;
        }
    }
}

?>