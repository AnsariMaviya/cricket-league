<?php
namespace App\Models;

use CodeIgniter\Model;

class get_details extends Model
{
	public function __construct() {
		parent::__construct();
		$db = \Config\Database::connect();
		// print_r($db);
	} 
	public function get_countries($id='')
	{
		$extend = "";
		if($id!='')
			$extend = " where country_id=$id";
		$sql = "SELECT * FROM countries".$extend;     
		$query = $this->db->query($sql);
		return $query->getResult();
	}
	public function get_teams($id='')
	{
		$extend = "";
		if($id!='')
			$extend = " where country_id=$id";
		$sql = "SELECT * FROM teams".$extend." order by country_id,team_id";     
		$query = $this->db->query($sql);
		return $query->getResult();
	}
	public function get_players($id='')
	{
		$extend = "";
		if($id!='')
			$extend = " and a.team_id=$id";
		$sql = "SELECT * FROM players a, teams b where a.team_id=b.team_id ".$extend." order by a.team_id,a.player_id";  
		$query = $this->db->query($sql);
		return $query->getResult();
	}

	public function get_profiles($pid)
	{
		$sql = "SELECT a.*,b.*,c.name as 'country_name',c.short_name FROM players a, teams b, countries c where a.team_id=b.team_id and b.country_id=c.country_id and player_id=$pid";  
		$query = $this->db->query($sql);
		return $query->getResult();
	}

	public function get_matches($vid)
	{
		$extend = "";
		if($vid!='')
			$extend = " and a.venue_id=$vid";
		$sql = 'SELECT a.*, b.name, b.address, c.team1Name, d.team2Name FROM `match` a left join (select team_id,team_name as "team1Name" from teams) c on c.team_id = a.first_team_id join (select team_id,team_name as "team2Name" from teams) d on d.team_id = a.second_team_id, venue b WHERE a.venue_id = b.venue_id'.$extend.' ORDER BY match_id';  
		$query = $this->db->query($sql);
		return $query->getResult();
	}

	public function get_venues($vid)
	{
		$extend = "";
		if($vid!='')
			$extend = " where venue_id=$vid";
		$sql = 'SELECT * from venue '.$extend.' ORDER BY venue_id';  
		$query = $this->db->query($sql);
		return $query->getResult();
	}
}
?>