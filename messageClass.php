<?php
class Message{
    /* 連想配列にすることで複数エラーが出ても出力できるようにしている */
    public $error=array();
    public function setError($key,$message){
        $this->error[$key] = $message;
    }
    public function getError($key){
        if(isset($this->error[$key])){
            return "<div class='invalid-feedback'>{$this->error[$key]}</div>";
        }
    }
    public function invalid($key){
        if (isset($this->error[$key])){
            return "is-invalid";
        }
    }
    public function alert($message){
        return "<div class='alert alert-warning' role='alert'>{$message}</div>";
    }
}
?>