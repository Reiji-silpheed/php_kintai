<?php
class Message{
    private $errorMessage = "";
    public function setError($message){
        $this->errorMessage = $message;
    }
    public function getError(){
        if($this->errorMessage != ""){
            return "<div class='invalid-feedback'>{$this->errorMessage}</div>";
        }
    }
    public function invalid(){
        if ($this->errorMessage!==""){
            return "is-invalid";
        }
    }
}
?>