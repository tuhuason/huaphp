<?php

class validator
{
    protected $rules;

    private $data;

    protected $error;

    protected $message;

    public function __construct()
    {
        $data = array_merge($_GET,$_POST);
        unset($data['_s']);
        $this->data = $this->removeXSS($data);
    }

    private function removeXSS($data)
    {
        if (!is_array($data)) {
            if (mb_detect_encoding($data, "UTF-8")) {
                return htmlspecialchars(trim($data));
            }
            return htmlspecialchars(trim($data), ENT_QUOTES, 'GB2312');
        }
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $this->removeXSS($v);
            } else {
                $data[$k] = htmlspecialchars(trim($v));
            }
        }
        return $data;
    }

    public function data($key = "")
    {
        $this->__validate($this->data);
        if (empty($key)) {
            return $this->data;
        }
        return empty($this->data[$key]) ? "" : $this->data[$key];
    }

    static function raw($key = "")
    {
        return (new self())->data($key);
    }

    private function __validate($data)
    {
        if (!empty($this->rules)) {
            foreach ($this->rules as $key => $rule) {
                $funcNames = explode("|", $rule);
                foreach ($funcNames as $funcName) {
                    $func = explode(":", $funcName);
                    if (!empty($func[0])) {
                        switch ($func[0]) {
                            case "required":
                                empty($data[$key]) && $this->error($key . ".required");
                                break;
                            case "min":
                                mb_strlen($data[$key]) <= $func[1] && $this->error($key . ".min");
                                break;
                            case "max":
                                mb_strlen($data[$key]) >= $func[1] && $this->error($key . ".max");
                                break;
                            case "match":
                                !preg_match('/' . $func[1] . '/', $data[$key]) && $this->error($key . ".match");
                                break;
                            case "compare":
                                $isset = isset($data[$func[1]]);
                                if (($isset && $data[$key] != $data[$func[1]]) || (!$isset && $data[$key] != $func[1])) {
                                    $this->error($key . ".compare");
                                }
                                break;
                            case "insert":
                                $data[$key] = $func[1];
                                break;
                            case "mul":
                                $data[$key] = $data[$key] * $func[1];
                                break;
                            case "replace":
                                $data[$key] = empty($data[$key]) ? $func[1] : $data[$key];
                                break;
                            case "copy":
                                $data[$func[1]] = $data[$key];
                                break;
                            case "rename":
                                $data[$func[1]] = $data[$key];
                                unset($data[$key]);
                                break;
                            case "combine":
                                $data[$key] = $data[$key] . $data[$func[1]];
                                break;
                            default:
                                $data[$key] = $func[0]($data[$key]);
                                if ($data[$key] == false) {
                                    $this->error($key . "." . $func[0]);
                                }
                                break;
                        }
                    }
                }
            }
        }
        $this->data = $data;
        if (!empty($this->error)) {
            throw new \Exception($this->error[0]);
        }
    }

    private function error($m)
    {
        if (isset($this->message[$m])) {
            $this->error[] = $this->message[$m];
        }
    }
}
