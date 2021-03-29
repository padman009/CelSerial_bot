<?php
class Database {

    private $dbname;
    private $filename;
    private $db;

    function __construct($dbname){
        $this->dbname = $dbname;
        $this->filename = "../my_database/{$this->dbname}.json";
        $this->connect();
    }

    private function connect(){
        $this->db = json_decode(file_get_contents($this->filename),true);
    }

    public function check_connection(){
        return file_exists($this->filename);
    }

    public function save(){
        $file = fopen($this->filename, "w");
        fwrite($file, json_encode($this->db));
        fclose($file);
    }

    public function get($query, $limit = 0){
        $res = [];

        $execStr = "return ";
        $execStr .= (isset($query[1]) ?
                preg_replace_callback("/:[A-Za-z]+/",
                    function ($matches){
                        return '$row[\''.strtolower(substr($matches[0], 1))."']";
                    }, $query[1])
                : "true" ).";";
        foreach ($this->db as $id => $row){
            if(eval($execStr)){
                foreach ($row as $columns => $value){
                    if($query[0] == "*" || array_search($columns, $query[0]) !== false) {
                        $match[$columns] = $value;
                    }
                }

                $res[] = $match;
            }

            if($limit > 0 && sizeof($res) == $limit){
                break;
            }
        }

        return $res;
    }
}

$db = new Database('sdunet');
echo json_encode($db->get([['name', 'id'], ':id == 190107023']));

//echo preg_replace("/:[a-z]+/", '$col', "select :col == 4 && :ret == 3");
//$arr = [];
//preg_match_all("/:[a-z]+/", "select :col == 4 && :ret == 3", $arr);
//echo json_encode($arr);
//$col = 0;
//$rat = 0;
//$row = ['col' => $col, 'rat' => $rat];
//$str = preg_replace_callback("/:[A-Za-z]+/",
//    function ($matches){
//        return '$row[\''.strtolower(substr($matches[0], 1))."']";
//    },
//    ":col == 0 || :rat == 3");
//echo $str.PHP_EOL;
//echo eval("return ".$str.";") ? "1" : "0";
?>