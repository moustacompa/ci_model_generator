<?php
/*
*   MODEL GENEARTOR API
*
*   MOTHER MODEL CLASS
*
*   AUTHOR : COMPAORE MOUSTAPHA
*
*   VERSION : 2.1.3
*
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Model extends CI_Model {
	protected $table;
	protected $pk;
	protected $data;

	//Function to redefine in each class 
    public function getData(){}

	//retrieves all not deleted rows 
	public function findAll(){
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('del', 0);
        $this->db->order_by($this->pk, "ASC");
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
	}

    //retrieves some rows 
    public function findSome($nb){
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('del', 0);
        $this->db->limit($nb);
        $this->db->order_by($this->pk, "DESC");
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
    }

    //retrieves some rows 
    public function findOrder($col, $type, $nb){
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('del', 0);
        if ($nb!=(-1)) {
            $this->db->limit($nb);
        }
        $this->db->order_by($col, $type);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
    }  

    public function whereOrder($where, $col, $type, $nb){
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('del', 0);
        $this->db->where($where);
        if ($nb!=(-1)) {
            $this->db->limit($nb);
        }
        $this->db->order_by($col, $type);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
    }   

    public function findAllJson(){
        $tab = $this->findAll();
        $lst = array();
        foreach ($tab as $row) {
            $lst[] = $row->getData();
        }
        return json_encode($lst);
    }

	//retrieves all deleted rows 
	public function findDel(){
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('del', 1);
        $this->db->order_by($this->pk, "ASC");
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
	}

    public function findDelJson(){
        $tab = $this->findDel();
        $lst = array();
        foreach ($tab as $row) {
            $lst[] = $row->getData();
        }
        return json_encode($lst);
    }

    public function getLast(){
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('del', 0);
        $this->db->order_by($this->pk, "DESC");
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result()[0];
        } else {
            return null;
        }
    }

    public function getLastJson(){
        $row = $this->getLast();
        return json_encode($row->getData());
    }

	//retrieves a row using it's PK value 
	public function findPK($id) {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where($this->pk, $id);
        $this->db->where('del', 0);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result()[0];
        } else {
            return null;
        }
    }

    public function findPKJson($id){
        $row = $this->findPK($id);
        return json_encode($row->getData());
    }

	//retrieves some rows where 
    public function where($where) {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->order_by($this->pk,'ASC');
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        } elseif (is_numeric($where)) {
            $this->db->where($this->pk,$where);
        }else{
            $this->db->where($where);
        }
        //Only get not deleted
        $this->db->where('del', 0);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
    }

    //retrieves some rows where 
    public function whereSome($where, $nb) {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->order_by($this->pk,'DESC');
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        } elseif (is_numeric($where)) {
            $this->db->where($this->pk,$where);
        }else{
            $this->db->where($where);
        }
        //Only get not deleted
        $this->db->limit($nb);
        $this->db->where('del', 0);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
    }

    public function whereJson($where){
        $tab = $this->where($where);
        $lst = array();
        foreach ($tab as $row) {
            $lst[] = $row->getData();
        }
        return json_encode($lst);
    }

    public function where2($select = '',$where) {
        $select = ($select=='') ? '*' : $select ;
        $this->db->select($select);
        $this->db->from($this->table);
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        } elseif (is_numeric($where)) {
            $this->db->where($this->pk,$where);
        }else{
            $this->db->where($where);
        }
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
    }

    public function where3($select = '',$where) {
        $select = ($select=='') ? '*' : $select ;
        $this->db->select($select);
        $this->db->from($this->table);
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        } elseif (is_numeric($where)) {
            $this->db->where($this->pk,$where);
        }else{
            $this->db->where($where);
        }
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
    }

    public function deleteOne(){
        $data = $this->getData();
        $query = $this->db->query('UPDATE '.$this->table.' SET del = 1 WHERE '.$this->pk.' = '.$data[$this->pk]);
        if ($query === TRUE) {
            return $query;
        } else {
            return null;
        }
    }

    //deletes a row using it's PK value 
    public function delete($where) {
        $qwhere = '';
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $qwhere.=$key.' = '.$value.',';
            }
            $qwhere = substr($qwhere, 0,strlen($qwhere)-1);
        } elseif (is_numeric($where)) {
            $qwhere = $this->pk.' = '.$where;
        }elseif (empty($where)) {
            $qwhere = '1';
        }else{
            $qwhere = $where;
        }
        $query = $this->db->query('UPDATE '.$this->table.' SET del = 1 WHERE '.$where);
        if ($query === TRUE) {
            echo $query;
        } else {
            echo null;
        }
    }


    //saves (insert or update) modifications on a object
    public function save(){
    	$data = $this->getData();
		//check if it's an insert or an update
    	if (!array_key_exists($this->pk, $data)) {
    		//insert
            $data = $this->getData();
            $data['del'] = 0;
    		$sql = $this->db->insert_string($this->table, $data);
	        $query = $this->db->query($sql);
	        if ($query === TRUE) {
	            $return = $this->db->insert_id();
	            if (is_null($return) || $return == "") {
	                $return = TRUE;
	            }
	            return $return;
	        } else {
	            return 0;
	        }
    	}else{
    		//update
			$sql = $this->db->update_string($this->table, $this->getData(),array($this->pk => $data[$this->pk]));
            // return $sql;
            $query = $this->db->query($sql);
	        if ($query === TRUE) {
	            $return = $query;
	            return $return;
	        } else {
	            return null;
	        }
    	}
	}

    public function countAll(){
        return $this->db->count_all($this->table);
    }

    public function quote($str){
        return '\''.$str.'\'';
    }

    public function query($sql){
        $result = $this->db->query($sql);
        if ($result->num_rows()>0) {
            return $result->result();
        } else {
            return array();
        }
    }

}