<?php
/**
 * Class Postpaidmodel
 * handles controller Class Post paid requests dealing with user table in DB
 * 
 *
 * @package     EasyHotspot
 * @subpackage  Models
 * @category    Post Paid
 * @author      Rafeequl Rahman Awan
 * @copyright   Copyright (c) 2008, easyhotspot.sf.net
 * @license		http://www.gnu.org/licenses/gpl.html
 * @link 		http://easyhotspot.sourceforge.net
 * @version 	1.0
 */

class Postpaidmodel extends model {
	function Postpaidmodel(){
		parent::Model();
		
		//tables
		$this->_table= 'postpaid_account';
		$this->_table_account_list= 'postpaid_account_list';
		$this->_table_account_bill= 'postpaid_account_bill';
		$this->_table_radcheck = 'radcheck';
		$this->_table_radreply = 'radreply';
		$this->_table_radacct = 'radacct';
		$this->_table_postplan = 'postplan';
		
	}
	
	function getAccount($fields = null, $where = null, $limit = null, $order = null){
		
		($fields != null) ? $this->db->select($fields) :'';
		
		($where != null) ? $this->db->where($where) :'';
		
		($limit != null) ? $this->db->limit($limit['start'],$limit['end']) :'';

		($order != null) ? $this->db->order_by($order['field'],$order['dir']) : $this->db->order_by('id','desc');
		
		return $this->db->get($this->_table);
	}
	
	function getAccountList($fields = null, $where = null, $limit = null, $order = null){
		
		($fields != null) ? $this->db->select($fields) :'';
		
		($where != null) ? $this->db->where($where) :'';
		
		($limit != null) ? $this->db->limit($limit['start'],$limit['end']) :'';
		
		($order != null) ? $this->db->order_by($order['field'],$order['dir']) : $this->db->order_by('id','desc');
		
		return $this->db->get($this->_table_account_list);
	
	}
	
	function getAccountUsage($fields = null, $where = null, $limit = null){
		
		($fields != null) ? $this->db->select($fields) :'';
		
		($where != null) ? $this->db->where($where) :'';
		
		($limit != null) ? $this->db->limit($limit['start'],$limit['end']) :'';
		
		return $this->db->get($this->_table_account_bill);
	
	}
	
	function addAccount(){
	    $ci =& get_instance();
	    $ci->load->model('postplanmodel');
        $idletimeout = $ci->postplanmodel->getIdleTimeout()->row()->price;
		$bw_download = $ci->postplanmodel->getDownloadRate()->row()->price;
		$bw_upload = $ci->postplanmodel->getUploadrate()->row()->price;
        
		$this->db->trans_start();
		
		//data for postpaid_account
		$_POST['IdleTimeout'] = $idletimeout;
		
		//data for radcheck table
		$radcheck_value = array();
		$radcheck_value['username'] = $_POST['username'];
		$radcheck_value['value'] = $_POST['password'];
		$radcheck_value['attribute'] = 'Cleartext-Password';
		$radcheck_value['op'] = ':=';
		
		
		//data for radreply table
        //max download speed
		if($bw_download) {
		    $data['username'] = $_POST['username'];
			$data['attribute']='WISPr-Bandwidth-Max-Down';
			$data['op']=':=';
			$data['value'] = $bw_download*1000;
			$this->db->insert($this->_table_radreply,$data); // insert into radreply account table
		}

		//max upload speed
		if($bw_upload) {
		    $data['username'] = $_POST['username'];
			$data['attribute']='WISPr-Bandwidth-Max-Up';
			$data['op']=':=';
			$data['value'] = $bw_upload*1000;
			$this->db->insert($this->_table_radreply,$data); // insert into radreply account table
		}
		
		//IdleTimeout
		if($idletimeout){
		    $data['username'] = $_POST['username'];
			$data['attribute'] = 'Idle-Timeout';
			$data['op'] = ':=';
			$data['value'] = $idletimeout*60;	
			$this->db->insert($this->_table_radreply,$data); // insert into radreply account table		
		}
		
		//Simultaneous-Use
		$data['attribute'] = 'Simultaneous-Use';
		$data['op'] = ':=';
		$data['value'] = '1';
		$this->db->insert($this->_table_radcheck,$data);
		
		//Accounting status update inteval
		//FreeRadius will update the accounts usage information within the given time (in sec)
		$data['attribute'] = 'Acct-Interim-Interval';
		$data['op'] = ':=';
		$data['value'] = '120';
		$this->db->insert($this->_table_radreply,$data);
		
		
		$this->db->insert($this->_table,$_POST); //insert into postpaid account table
		$this->db->insert($this->_table_radcheck,$radcheck_value);		//insert into radcheck account table
		$this->db->trans_complete();
		
	}
	
	function deleteAccount(){
		$this->db->trans_start();
			
		$this->db->where('username',$this->uri->segment(3));
		$this->db->delete($this->_table); //delete from postpaid_account table
		
		$this->db->where('username',$this->uri->segment(3));
		$this->db->delete($this->_table_radcheck);		//delete from radcheck account table
		
		$this->db->where('username',$this->uri->segment(3));
		$this->db->delete($this->_table_radacct); //delete from radacct table
		
		$this->db->where('username',$this->uri->segment(3));
		$this->db->delete($this->_table_radreply); //delete from radreply table
			
		$this->db->trans_complete();
	}
	
	function editAccount(){
		
		$this->db->trans_start();
		
		//fields on postpaid_account table	
		$this->db->where('username',$_POST['username']);
		$this->db->update($this->_table,$_POST);
		
		//field on radcheck table
		$password = array('value' => $_POST['password']); 
		$this->db->where('username',$_POST['username']);
		$this->db->update($this->_table_radcheck,$password);
		
		//field on radreply table
		//Dowloadrate
		$bw_download = array('value' => $_POST['bw_download']);
		$this->db->where('username',$_POST['username']);
		$this->db->where('attribute','WISPr-Bandwidth-Max-Down');
		$this->db->update($this->_table_radreply,$bw_download);
		
		//Uploadrate
		$bw_upload = array('value' => $_POST['bw_upload']);
		$this->db->where('username',$_POST['username']);
		$this->db->where('attribute','WISPr-Bandwidth-Max-Up');
		$this->db->update($this->_table_radreply,$bw_upload);
		
		$this->db->trans_complete();
	}
	
	function searchAccount($limit = null){
		
		//search by username or realname
		$this->db->like('username',$this->db_session->userdata('search'));
		$this->db->orlike('realname',$this->db_session->userdata('search'));
		
		//limit
		($limit !=null ) ? $this->db->limit($limit['start'],$limit['end']) :'';
		
		return $this->db->get($this->_table_account_list);
		
	}
	
	function getAccountStatistic(){
		//account created
		$accounts = $this->postpaidmodel->getAccountList();
		$data['created'] = $accounts->num_rows();
		
		//account used
		$account_used = $this->db->query('select*from postpaid_account_list where time_used is not NULL');
		$data['used'] = $account_used->num_rows();
		
		//bill by 
		
		return $data;
	}
	

		
	
}
