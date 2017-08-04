<?php

class uploader
{
    public $name = "upload";

    public $rename = false;

    public $saveDir = "/upload";

    public $fileExt = "*";

    public $coverFile = true;

    public $fileSize = 2048000;

    public $errorMsg = '';

    public $result;

    public function save()
    {
        if ($this->checkExt() || $this->checkSize()) {
            $save_dir = $this->saveDir;
            $storageDir =  empty($this->saveDir)? date("/Ymd/", $_SERVER['REQUEST_TIME']) :"";
            if (!is_dir($save_dir . $storageDir)) {
                mkdir($save_dir . $storageDir, 0777, true);
            }
            if ($this->rename) {
                $upFile = uniqid() . "." . $this->getExt();
            } else {
                $upFile = $_FILES[$this->name]['name'];
            }
            $file = $save_dir . $storageDir . $upFile;
            if (move_uploaded_file($_FILES[$this->name]['tmp_name'], $file)) {
                $this->result['file'] = $storageDir . $upFile;
                $this->result['status'] = true;
            }
            return $this->result;
        }
        $this->errorMsg = "文件校验失败！";
        return false;
    }

    private function checkExt()
    {
        if ($this->fileExt == "*") {
            return true;
        }
        $ext = $this->getExt();
        if (!in_array(strtolower($ext), explode(",", strtolower($this->fileExt)))) {
            $this->errorMsg = "上传文件不合法！";
            return false;
        }
        return true;
    }

    private function getExt()
    {
        if (isset($_FILES[$this->name])) {
            $ext = pathinfo($_FILES[$this->name]['name']);
            return $ext['extension'];
        }
        return false;
    }

    private function checkSize()
    {
        if (isset($_FILES[$this->name])) {
            $size = $_FILES[$this->name]['size'];
            if ($size > $this->fileSize) {
                $this->errorMsg = "上传文件大小超过限制！";
                return false;
            }
            return true;
        }
        return false;
    }
}