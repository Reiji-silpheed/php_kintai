<?php
class dbClass{
    public $db;
    public function __construct(){
        $this->db=new PDO('mysql:host=localhost;port=3306;dbname=kintai','root');
    }
    public function begin(){
        return $this->db->beginTransaction();
    }
    public function cmt(){
        return $this->db->commit();
    }
    public function rlb(){
        return $this->db->rollback();
    }
    public function select($sql,$param){
        $sth=$this->db->prepare($sql);
        $sth->execute($param);
        return $sth->fetchAll();
    }
    public function iud($sql,$param){
        $sth=$this->db->prepare($sql);
        $sth->execute($param);
    }
}
?>