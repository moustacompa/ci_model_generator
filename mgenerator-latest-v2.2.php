<?php 

	/*
	*	Auteur  : COMPAORE MOUSTAPHA
	*	URL     :	http://www.moustacompa.com
	*	Version : 2.2
	*/

	define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
	
	$db['default'] = array(
		'port'	=> '3306',
		'hostname' => 'localhost',
		'username' => 'root',
		'password' => '',
		'database' => 'dgsi',
		'dbdriver' => 'mysql',		
	);

	$tables = array('form_form1','form_form2','form_usr');
	//$tables = '*';

	//include_once 'config/database.php';

	$modelPath = __DIR__.'/models';

	$driver=$db['default']['dbdriver'];
	$host=$db['default']['hostname'];
	$dbname=$db['default']['database'];
	$user=$db['default']['username'];
	$pass=$db['default']['password'];
	$port=$db['default']['port'];
	$connexion = new PDO($driver.':host='.$host.';dbname='.$dbname.';port='.$port,$user,$pass);

	if($connexion){
		echo 'Connected to DB.';
		$nbTables = 0;
		$rnb=$connexion->query("select count(*) as 'nb' from information_schema.tables where table_schema = '".$dbname."'");
		while ($lg = $rnb->fetch(PDO::FETCH_ASSOC)) {   
			$nbTables = $lg['nb'];
		}

		$requete="SHOW TABLES";
		$resultat=$connexion->query($requete);
		$i=1;
		while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) {   
			$prim = '';
			$className = $ligne['Tables_in_'.$dbname];
			if (is_array($tables)) {
				if (!in_array($className, $tables)) {
					continue;
				}
			}
			//first letter in upper 
			$className=strtoupper(substr($className, 0, 1)).substr($className, 1, strlen($className)-1);
			echo '-----------------------------------
			'.$i.'/'.$nbTables.' - Table '.$className.'
			';
			$filename = $modelPath.'/'.$className.'.php';
		   	$file = fopen( $filename, "w+" );
		   	if( $file == false ) {
		      echo ( "Error in opening new file" );
		      exit();
		   	}
			$requete='DESC '.$ligne['Tables_in_'.$dbname];
			$res2=$connexion->query($requete);
			//properties
			fwrite( $file, "<?php \n");
			fwrite( $file, "require_once 'Model.php';\n");
			fwrite( $file, "if (!defined('BASEPATH'))\n");
			fwrite( $file, "\texit('No direct script access allowed');\n\n");
			fwrite( $file, "class ".$className." extends Model {\n\n");
		   	$lProp = $res2->fetchAll(PDO::FETCH_ASSOC);
			foreach ($lProp as $prop) {
				fwrite( $file, "\t\tprotected $".$prop['Field'].";\n");
				if ($prop['Key']=="PRI") {
					$prim = $prop['Field'];
				}
				// echo $line2['Field'].' '.$line2['Type'].',  ';
				 // var_dump($line2);
			}
			fwrite( $file, "\n");

			//constructor
			fwrite( $file, "\tpublic function __construct(){\n");
				fwrite($file, "\t\t");
				fwrite( $file, '$this->table = "'.$ligne['Tables_in_'.$dbname].'";');
				fwrite($file, "\t\n\t\t");
				fwrite( $file, '$this->pk = "'.$prim.'";');
			fwrite( $file, "\n\t}\n\n");



			//getters and setters
			foreach ($lProp as $prop) {
				//getters
				$propName=strtoupper(substr($prop['Field'], 0, 1)).substr($prop['Field'], 1, strlen($prop['Field'])-1);
				fwrite( $file, "\tpublic function get".$propName."(){\n");
					// fwrite( $file, );
					fwrite( $file, "\t\t".'return $this->'.$prop['Field'].';'."\n");
				fwrite( $file, "\t}\n\n");

				//setters
				fwrite( $file, "\tpublic function set".$propName."($".$prop['Field']."){\n");
					// fwrite( $file, );
					fwrite( $file, "\t\t".'$this->'.$prop['Field'].'=$'.$prop['Field'].';'."\n");
				fwrite( $file, "\t}\n\n");
				 // var_dump($line2);

				//check if it is a fk
				if ($prop['Key']=='MUL') {
					$fkCls=strtoupper(substr($prop['Field'], 2, 1)).substr($prop['Field'], 3, strlen($prop['Field'])-1);
					fwrite( $file, "\tpublic function get".$fkCls."(){\n");
						// fwrite( $file, );
					fwrite( $file, "\t\t".'if (empty($this->'.$prop['Field'].')) return null;'."\n");
					fwrite( $file, "\t\t".'return $this->'.$fkCls.'->findPk($this->'.$prop['Field'].');'."\n");
					fwrite( $file, "\t}\n\n");
				}
			}

			//getting N-1 or N-N links
			$sqlFK = "select TABLE_NAME from`INFORMATION_SCHEMA`.`COLUMNS` where COLUMN_NAME = '".$prim."' and TABLE_SCHEMA = '".$dbname."' and TABLE_NAME != '".$ligne['Tables_in_'.$dbname]."'";

			$resFk=$connexion->query($sqlFK);
			$lstFk = $resFk->fetchAll(PDO::FETCH_ASSOC);
			foreach ($lstFk as $lfk) {   
				$fkName=strtoupper(substr($lfk['TABLE_NAME'], 0, 1)).substr($lfk['TABLE_NAME'], 1, strlen($lfk['TABLE_NAME'])-1);
				fwrite( $file, "\tpublic function get".$fkName."s(){\n");
				fwrite( $file, "\t\t".'return $this->'.$fkName.'->where(\''.$prim.' =\'. $this->'.$prim.');'."\n");
				fwrite( $file, "\t}\n\n");
			}

			//clearing
			foreach ($lstFk as $lfk) {   
				$fkName=strtoupper(substr($lfk['TABLE_NAME'], 0, 1)).substr($lfk['TABLE_NAME'], 1, strlen($lfk['TABLE_NAME'])-1);
				fwrite( $file, "\tpublic function clear".$fkName."s(){\n");
				fwrite( $file, "\t\t".'return $this->'.$fkName.'->delete(\''.$prim.' =\'. $this->'.$prim.');'."\n");
				fwrite( $file, "\t}\n\n");
			}

			//getSome
			foreach ($lstFk as $lfk) {
				$fkName=strtoupper(substr($lfk['TABLE_NAME'], 0, 1)).substr($lfk['TABLE_NAME'], 1, strlen($lfk['TABLE_NAME'])-1);
				fwrite( $file, "\tpublic function getSome".$fkName."(".'$nb'."){\n");
				fwrite( $file, "\t\t".'$tmp = $this->'.$fkName.'->findSome($nb);'."\n");
				fwrite( $file, "\t}\n\n");
			}

			//getFirst
			foreach ($lstFk as $lfk) {
				$fkName=strtoupper(substr($lfk['TABLE_NAME'], 0, 1)).substr($lfk['TABLE_NAME'], 1, strlen($lfk['TABLE_NAME'])-1);
				fwrite( $file, "\tpublic function getFirst".$fkName."(){\n");
				fwrite( $file, "\t\t".'$tmp = $this->'.$fkName.'->where(\''.$prim.' =\'. $this->'.$prim.');'."\n");
				fwrite( $file, "\t\t".'return (count($tmp)>0) ? $tmp[0] : null ;'."\n");
				fwrite( $file, "\t}\n\n");
			}

			//getLast
			foreach ($lstFk as $lfk) {  
				$fkName=strtoupper(substr($lfk['TABLE_NAME'], 0, 1)).substr($lfk['TABLE_NAME'], 1, strlen($lfk['TABLE_NAME'])-1);
				fwrite( $file, "\tpublic function getLast".$fkName."(){\n");
				fwrite( $file, "\t\t".'$tmp = $this->'.$fkName.'->where(\''.$prim.' =\'. $this->'.$prim.');'."\n");
				fwrite( $file, "\t\t".'return (count($tmp)>0) ? $tmp[count($tmp)-1] : null ;'."\n");
				fwrite( $file, "\t}\n\n");
			}

			//getData
			fwrite( $file, "\tpublic function getData(){\n");
				fwrite( $file, "\t\t".'$ret = array();'."\n");
				fwrite( $file, "\t\t".'if ($this->'.$prim.'!=null) {'."\n");
					fwrite( $file, "\t\t\t".'$ret['."'".$prim."'".'] = $this->'.$prim.";\n");
				fwrite( $file, "\t\t}\n");

				foreach ($lProp as $prop) {
					if ($prop['Field']!=$prim) {
						fwrite( $file, "\t\t".'$ret['."'".$prop['Field']."'".'] = $this->'.$prop['Field'].";\n");
					}
				}
				fwrite( $file, "\t\t".'$this->data = $ret;'."\n");
				fwrite( $file, "\t\t".'return $this->data;'."\n");
			fwrite( $file, "\t}\n\n");
			// $tab= $res2->fetchAll(PDO::FETCH_ASSOC);
			// echo $i.' - '.$ligne['Tables_in_'.$dbname];   

			//findPk
			fwrite( $file, "\tpublic function findPk(".'$id'."){\n");
				fwrite( $file, "\t\t".'$tab = parent::findPK($id);'."\n");
				fwrite( $file, "\t\t".'if($tab==null) return null;'."\n");
				fwrite( $file, "\t\t".'$obj = new '.$className.'();'."\n");
				foreach ($lProp as $prop) {
					$propName=strtoupper(substr($prop['Field'], 0, 1)).substr($prop['Field'], 1, strlen($prop['Field'])-1);
					fwrite( $file, "\t\t".'$obj->set'.$propName.'($tab->'.$prop['Field'].");\n");
				}
				fwrite( $file, "\t\t".'return $obj;'."\n");
			fwrite( $file, "\t}\n\n");

			//getLast
			fwrite( $file, "\tpublic function getLast(){\n");
				fwrite( $file, "\t\t".'$tab = parent::getLast();'."\n");
				fwrite( $file, "\t\t".'$obj = new '.$className.'();'."\n");
				foreach ($lProp as $prop) {
					$propName=strtoupper(substr($prop['Field'], 0, 1)).substr($prop['Field'], 1, strlen($prop['Field'])-1);
					fwrite( $file, "\t\t".'$obj->set'.$propName.'($tab->'.$prop['Field'].");\n");
				}
				fwrite( $file, "\t\t".'return $obj;'."\n");
			fwrite( $file, "\t}\n\n");

			//findAll
			fwrite( $file, "\tpublic function findAll(){\n");
				fwrite( $file, "\t\t".'$tab = parent::findAll();'."\n");
				fwrite( $file, "\t\t".'$lst = array();'."\n");
				fwrite( $file, "\t\t".'foreach ($tab as $row) {'."\n");
					fwrite( $file, "\t\t\t".'$obj = new '.$className.'();'."\n");
					foreach ($lProp as $prop) {
						$propName=strtoupper(substr($prop['Field'], 0, 1)).substr($prop['Field'], 1, strlen($prop['Field'])-1);
						fwrite( $file, "\t\t\t".'$obj->set'.$propName.'($row->'.$prop['Field'].");\n");
					}
				fwrite( $file, "\t\t\t".'$lst[] = $obj;'."\n");
				fwrite( $file, "\t\t}\n");
				fwrite( $file, "\t\t".'return $lst;'."\n");
			fwrite( $file, "\t}\n\n");


			//findSome
			fwrite( $file, "\tpublic function findSome(".'$nb'."){\n");
				fwrite( $file, "\t\t".'$tab = parent::findSome($nb);'."\n");
				fwrite( $file, "\t\t".'$lst = array();'."\n");
				fwrite( $file, "\t\t".'foreach ($tab as $row) {'."\n");
					fwrite( $file, "\t\t\t".'$obj = new '.$className.'();'."\n");
					foreach ($lProp as $prop) {
						$propName=strtoupper(substr($prop['Field'], 0, 1)).substr($prop['Field'], 1, strlen($prop['Field'])-1);
						fwrite( $file, "\t\t\t".'$obj->set'.$propName.'($row->'.$prop['Field'].");\n");
					}
				fwrite( $file, "\t\t\t".'$lst[] = $obj;'."\n");
				fwrite( $file, "\t\t}\n");
				fwrite( $file, "\t\t".'return $lst;'."\n");
			fwrite( $file, "\t}\n\n");


			//findOrder
			fwrite( $file, "\tpublic function findOrder(".'$col, $type, $nb'."){\n");
				fwrite( $file, "\t\t".'$tab = parent::findOrder($col, $type, $nb);'."\n");
				fwrite( $file, "\t\t".'$lst = array();'."\n");
				fwrite( $file, "\t\t".'foreach ($tab as $row) {'."\n");
					fwrite( $file, "\t\t\t".'$obj = new '.$className.'();'."\n");
					foreach ($lProp as $prop) {
						$propName=strtoupper(substr($prop['Field'], 0, 1)).substr($prop['Field'], 1, strlen($prop['Field'])-1);
						fwrite( $file, "\t\t\t".'$obj->set'.$propName.'($row->'.$prop['Field'].");\n");
					}
				fwrite( $file, "\t\t\t".'$lst[] = $obj;'."\n");
				fwrite( $file, "\t\t}\n");
				fwrite( $file, "\t\t".'return $lst;'."\n");
			fwrite( $file, "\t}\n\n");

			//where
			fwrite( $file, "\tpublic function where(".'$where'."){\n");
				fwrite( $file, "\t\t".'$tab = parent::where($where);'."\n");
				fwrite( $file, "\t\t".'$lst = array();'."\n");
				fwrite( $file, "\t\t".'foreach ($tab as $row) {'."\n");
					fwrite( $file, "\t\t\t".'$obj = new '.$className.'();'."\n");
					foreach ($lProp as $prop) {
						$propName=strtoupper(substr($prop['Field'], 0, 1)).substr($prop['Field'], 1, strlen($prop['Field'])-1);
						fwrite( $file, "\t\t\t".'$obj->set'.$propName.'($row->'.$prop['Field'].");\n");
					}
				fwrite( $file, "\t\t\t".'$lst[] = $obj;'."\n");
				fwrite( $file, "\t\t}\n");
				fwrite( $file, "\t\t".'return $lst;'."\n");
			fwrite( $file, "\t}\n\n");

			//whereOrder
			fwrite( $file, "\tpublic function whereOrder(".'$where, $col, $type, $nb'."){\n");
				fwrite( $file, "\t\t".'$tab = parent::whereOrder($where, $col, $type, $nb);'."\n");
				fwrite( $file, "\t\t".'$lst = array();'."\n");
				fwrite( $file, "\t\t".'foreach ($tab as $row) {'."\n");
					fwrite( $file, "\t\t\t".'$obj = new '.$className.'();'."\n");
					foreach ($lProp as $prop) {
						$propName=strtoupper(substr($prop['Field'], 0, 1)).substr($prop['Field'], 1, strlen($prop['Field'])-1);
						fwrite( $file, "\t\t\t".'$obj->set'.$propName.'($row->'.$prop['Field'].");\n");
					}
				fwrite( $file, "\t\t\t".'$lst[] = $obj;'."\n");
				fwrite( $file, "\t\t}\n");
				fwrite( $file, "\t\t".'return $lst;'."\n");
			fwrite( $file, "\t}\n\n");

			//whereSome
			fwrite( $file, "\tpublic function whereSome(".'$where, $nb'."){\n");
				fwrite( $file, "\t\t".'$tab = parent::whereSome($where, $nb);'."\n");
				fwrite( $file, "\t\t".'$lst = array();'."\n");
				fwrite( $file, "\t\t".'foreach ($tab as $row) {'."\n");
					fwrite( $file, "\t\t\t".'$obj = new '.$className.'();'."\n");
					foreach ($lProp as $prop) {
						$propName=strtoupper(substr($prop['Field'], 0, 1)).substr($prop['Field'], 1, strlen($prop['Field'])-1);
						fwrite( $file, "\t\t\t".'$obj->set'.$propName.'($row->'.$prop['Field'].");\n");
					}
				fwrite( $file, "\t\t\t".'$lst[] = $obj;'."\n");
				fwrite( $file, "\t\t}\n");
				fwrite( $file, "\t\t".'return $lst;'."\n");
			fwrite( $file, "\t}\n\n");

			$i++;
			//end of class
			fwrite( $file, "}");
		   	fclose( $file );
		}

			echo "----------------------------------- \nModel generated successfuly";
	}else{
		echo 'Error Connection to DB';
	}

?>