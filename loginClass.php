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
            if(isset($_POST['searchBtn'])){
                if(!empty($_POST['number-search-post'])){
                    $list[]='employee_no=:employee_no';
                    $param['employee_no']=$_POST['number-search-post'];
                }
                if(!empty($_POST['name-search-post'])){
                    $list[]='employee_name=:employee_name';
                    $param['employee_name']=$_POST['name-search-post'];
                }
                if(!empty($_POST['mail-search-post'])){
                    $list[]='email=:email';
                    $param['email']=$_POST['mail-search-post'];
                }
                if(!empty($_POST['calendar-search-post'])){
                    $list[]='start_date=:start_date';
                    $param['start_date']=$_POST['calendar-search-post'];
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