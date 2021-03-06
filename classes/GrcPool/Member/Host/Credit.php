<?php
class GrcPool_Member_Host_Credit_OBJ extends GrcPool_Member_Host_Credit_MODEL {
	public function __construct() {
		parent::__construct();
	}

}

class GrcPool_Member_Host_Credit_DAO extends GrcPool_Member_Host_Credit_MODELDAO {
	/**
	 *
	 * @param int $projectId
	 * @param int $dbid
	 * @return NULL|GrcPool_MemberHostCredit_OBJ
	 */
	public function initWithAccountIdAndDbid(int $accountId,int $dbid) {
		return $this->fetch(array($this->where('accountId',$accountId),$this->where('hostDbid',$dbid)));
	}
	
	/**
	 * 
	 * @return int
	 */
	public function getNumberOfActiveHosts():int {
		$sql = 'select count(*) as howMany from '.$this->getFullTableName().' where avgCredit > 0';
		$result = $this->query($sql);
		return $result[0]['howMany'];
	}
	
	/**
	 * 
	 * @param int $accountId
	 */
	public function setMagToZeroForAccountId(int $accountId) {
		$sql = "update ".$this->getFullTableName()." set mag = 0 where accountId = '".addslashes($accountId)."'";
		$this->executeQuery($sql);
	}
	
	/**
	 * @return float
	 */
	public function getTotalMag() {
		$sql = 'select sum(mag) as totalMag from '.$this->getFullTableName();
		$result = $this->query($sql);
		return $result[0]['totalMag'];
	}
	
	/**
	 * 
	 * @return float
	 */
    public function getTotalOwed() {
        $sql = 'select sum(owed) as totalOwed from '.$this->getFullTableName();
        $result = $this->query($sql);
        return $result[0]['totalOwed'];
	}
	
	/**
	 * 
	 * @param int $limit
	 * @return mixed
	 */
	public function getProjectStats($limit = 0) {
		$sql = 'select accountId,sum(mag) as totalMag,count(*) as howMany from '.$this->getFullTableName().' where mag > 0 group by accountId order by totalMag desc '.($limit?'limit '.$limit:'');
		$results = $this->query($sql);
		$projects = array();
		foreach ($results as $result) {
			if (!isset($projects[$result['accountId']])) {
				$projects[$result['accountId']] = array();
				$projects[$result['accountId']]['mag'] = 0;
				$projects[$result['accountId']]['hostCount'] = 0;
			}
			$projects[$result['accountId']]['mag'] = $result['totalMag'];
			$projects[$result['accountId']]['hostCount'] = $result['howMany'];
		}
		return $projects;
	}
	
	/**
	 * 
	 * @return GrcPool_Member_Host_Credit_OBJ[]
	 */
 	public function getOwedWithNoOwner() {
 		$sql = 'select * from '.$this->getFullTableName().' where (hostDbid,accountId) not in (select hostDbid,accountId from '.Constants::DATABASE_NAME.'.member_host_project) and owed > 0';
 		return $this->queryObjects($sql);
 	}

}
