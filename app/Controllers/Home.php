<?php 
namespace App\Controllers;

use App\Models\get_details;
use Config\Services;
use CodeIgniter\Model;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class Home extends BaseController
{
	protected $get_details;
    protected $session = null;
    protected $udata=null;
    protected $db = null;
    public function __construct() {
        $this->db = \Config\Database::connect();
        $this->get_details = new get_details();
        $this->session = Services::session();
        helper('url');
    }
	public function index($id='')
	{
		$get_country = $this->get_details->get_countries($id);
		$data['country']=$get_country;
		echo view('header');
		echo view('country',$data);
		echo view('footer');
	}
	public function teams($cid='')
	{
		$get_teams = $this->get_details->get_teams($cid);
		$data['teams']=$get_teams;
		echo view('header');
		echo view('teams',$data);
		echo view('footer');
	}
	public function players($tid='')
	{
		$get_players = $this->get_details->get_players($tid);
		$data['players']=$get_players;
		echo view('header');
		echo view('players',$data);
		echo view('footer');
	}

	public function profiles($pid='')
	{
		$get_profiles = $this->get_details->get_profiles($pid);
		$data['profiles']=$get_profiles;
		echo view('header');
		echo view('profiles',$data);
		echo view('footer');
	}

	public function matches($vid="")
	{
		$get_matches = $this->get_details->get_matches($vid);
		$data['matches']=$get_matches;
		echo view('header');
		echo view('matches',$data);
		echo view('footer');
	}

	public function venues($vid='')
	{
		$get_venues = $this->get_details->get_venues($vid);
		$data['venues']=$get_venues;
		echo view('header');
		echo view('venue',$data);
		echo view('footer');
	}

	public function outcomes($vid='')
	{
		$get_outcomes = $this->get_details->get_matches($vid);
		$data['matches']=$get_outcomes;
		echo view('header');
		echo view('outcome',$data);
		echo view('footer');
	}
	//--------------------------------------------------------------------

}
