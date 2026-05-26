<?php
class dbClass{
    public function connect(){
        $db=new PDO('mysql:host=localhost;port=3306;dbname=kintai','root');
        return $db;
    }
    public function begin(){
        return $this->connect()->beginTransaction();
    }
    public function cmt(){
        return $this->connect()->commit();
    }
    public function rlb(){
        return $this->connect()->rollback();
    }
    public function select($sql,$param){
        $sth=$this->connect()->prepare($sql);
        $sth->execute($param);
        return $sth->fetch();
    }
    public function iud($sql,$param){
        $sth=$this->connect()->prepare($sql);
        $sth->execute($param);
        return $sth->rowCount();
    }
}
?>