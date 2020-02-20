<?php

/**
 * @author JonnyBo
 * @copyright 2020
 */
require('mysql.class.php');

class Tender extends DB {
    
    public $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function saveTender() {
        if (!$_POST['name'] || !$_POST['code'] || !$_POST['year']) {
            die('Не все параметры переданы');
        }
        $result = false;
        $id = intval($_POST['id']);
        $this->db->reset();
        $this->db->assign('date', date('Y-m-d H:i:s'));
        $this->db->assign('name', trim(strip_tags($_POST['name'])));
        $this->db->assign('code', trim(strip_tags($_POST['code'])));
        $this->db->assign('year', intval($_POST['year']));
        if ($id) {
            $this->db->update('tenders', 'WHERE id='.$id);
            $result = $id;
        } else {
            $this->db->insert('tenders');
            $result = $this->db->insert_id();
        }
        return $result;
    }
    
    public function getTender() {
        $id = intval($_REQUEST['id']);
        $this->db->query("SELECT * FROM tenders WHERE id = ".$id." LIMIT 1;");
        return $this->db->movenext();
    }
    
    public function deleteTender() {
        $id = intval($_POST['id']);
        $this->db->query("DELETE FROM tenders WHERE id = ".$id.";");
    }
    
    public function getAllTenders() {
        $this->db->query("SELECT * FROM tenders ORDER BY date DESC;");
        return $this->db->get_records();
    }  
}

$db = new DB();
$db->init('localhost', 'root', '', 'devcom', 'utf8');
$tender = new Tender($db);
if (isset($_GET['new']) || isset($_GET['update'])) {
    if ($tender->saveTender() > 0) {
        $tenders = $tender->getAllTenders();
        echo json_encode($tenders);
    }
} elseif (isset($_GET['del'])) {
    $tender->deleteTender();
    $tenders = $tender->getAllTenders();
    echo json_encode($tenders);
} elseif (isset($_GET['edit'])) {
    $tender = $tender->getTender();
    echo json_encode($tender);
} else {
    $tenders = $tender->getAllTenders();
    echo json_encode($tenders);
}

?>