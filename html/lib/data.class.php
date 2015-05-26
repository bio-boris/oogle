<?php

class data{

	public $database;
	public $organisms;

	public function __construct()
	{
		$this->database= new medoo(array(
					'database_type' => 'mysql',
					'database_name' => __MYSQL_DATABASE__,
					'server' => __MYSQL_HOST__,
					'username' => __MYSQL_USER__,
					'password' => __MYSQL_PASSWORD__,
					'charset' => 'utf8',
					// optional
					//'port' => 3306,
					// driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
					'option' => array(
						PDO::ATTR_CASE => PDO::CASE_NATURAL
						)
					));

	}
	public function getChromosomes($organism){
		if(!isset($organism)){
			return array();
		}
		$chromosomes = $this->database->select("chromosomes","chromosome",array('org'=>"$organism"));
#sort($chromosomes,SORT_NATURAL);
		natsort($chromosomes);
		$new_chr;
		foreach($chromosomes as $chr){
			$stripped = $this->stripChr($chr);
			$new_chr[$chr] = $stripped == $chr ? $chr : "$stripped ($chr)";
		}	
		return isset($new_chr)? $new_chr : array();
#print "Select chromosome from chromosomes where org=$organism";

	}

	public function validate($_POST){
		#print_r($_POST);
		$validate = array();
		if(! $this->validate_helper($_POST['organism_query'])){
			return array('error'=>'Please select a query organism');
		}
		if(! $this->validate_helper($_POST['intervals_query'])){
			return array('error'=>"Please select intervals on the organism for <b> {$_POST["organism_query"]} </b>");
			$valid_intervals = $this->validate_intervals($_POST['intervals_query']);
			if($valid_intervals == false){
				return array('error'=>'Invalid intervals entered for <b>' . $_POST['organism_subject'] . '</b>');
			}	
		}
		if(! $this->validate_helper($_POST['organism_subject'])){
			return array('error'=>'Please select a subject organism');
		}
		if($_POST['organism_query'] == $_POST['organism_subject']){
			return array('error'=>'Please select two different organisms');
		}

		if(!isset($_POST['whole_genome'])){
			if(! $this->validate_helper($_POST['intervals_subject'])){
				return array('error'=>'Please select an interval on <b>' . $_POST['organism_subject'] . '</b> or <b><i>search entire genome</i></b>');
			}
			$valid_intervals = $this->validate_intervals($_POST['intervals_query']);
			if($valid_intervals == false){
				return array('error'=>'Invalid intervals entered for <b>' . $_POST['organism_subject'] . '</b>');
			}	
		}
		if(isset($_POST['expect']) && is_int($_POST['expect'])){
			return array('error'=>'Please select an integer for e-value ');
		}
	}
	
	private function validate_intervals($intervals){
		print "About to validate $intervals";
		$intervals = str_replace(" ","\n",$intervals);
		$intervals = str_replace(",","\n",$intervals);
		$intervals = str_replace("\r","\n",$intervals);
		$intervals = explode("\n",$intervals);

		foreach($intervals as $interval){
			if(strpos(':',$interval)===false || strpos('-')===false){
				return false;
			}
			if(preg_match('[^\d]:',$interval) || preg_match(':[^\d]')){
				return false;
			}
		}
		

		return true;
	}

	private function validate_helper($variable){
		return (isset($variable) && strlen($variable) > 0 && $variable !='Select Organism');

	}


	function stripChr($chr){
		$chr = preg_replace('/chromosome/i','',"$chr");
		$chr = preg_replace('/chr/i','',"$chr");
		$chr = preg_replace('/gm([0-9]{2})/i',"$1","$chr");
		$chr = preg_replace('/A([0-9]{2})/i',"$1","$chr");

#Delete scaffolds and contigs?
		return $chr;

	}

#Returns a list of Key Value Pairs of organisms
	public function getOrganisms(){
		$organismsInfo =  $this->database->select("Organism_info", "*");;
		$organisms = array();
		foreach ($organismsInfo as $row){
			$name = $row['name'];
			$type = $row['type'];
			$common = $row['common'];
			$latin = $row['latin'];
			$organisms[$name] = "$latin ($common) ($type)";
		}
		array_multisort($organisms);
		return $organisms;
	}




}


?>


