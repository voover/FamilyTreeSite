<?php
header("Content type: application/json; charset=utf-8");

require_once('DB.php');
DB::init('familytree', 'localhost', 'root', 'per78FLI');  

$relations = array();

function saveRelation($source, $destination, $type) {
	global $relations;
	$relation = new stdClass();
	$relation->source = (int)$source;
	$relation->destination   = (int)$destination;
	$relation->type = $type;
	$relations[] = $relation;
}

function addRelation($source, $destination, $relation) {
	$source = DB::getItem("SELECT * FROM nodes WHERE id=$source");
	switch($relation) {
		case 'mother':
			DB::query("UPDATE nodes SET motherId=$destination WHERE id=".$source['id']);			
			if ($source['fatherId']) addRelation($source['fatherId'], $destination, 'spouse');		
			else saveRelation($source['id'], $destination, $relation);
			break;
		case 'father':
			DB::query("UPDATE nodes SET fatherId=$destination WHERE id=".$source['id']);
			if ($source['motherId']) addRelation($source['motherId'], $destination, 'spouse');
			else saveRelation($source['id'], $destination, $relation);
			break;			
		case 'spouse':
			DB::query("UPDATE nodes SET spouseId=$destination WHERE id=".$source['id']);
			DB::query("UPDATE nodes SET spouseId=".$source['id']." WHERE id=$destination");
			saveRelation($source['id'], $destination, $relation);
			saveRelation($destination, $source['id'], $relation);
			if ($source['sex']) {
				$children = DB::getList("SELECT * FROM nodes WHERE fatherId = ".$source['id']);
				DB::query("UPDATE nodes SET motherId=$destination WHERE fatherId = ".$source['id']);
				
				foreach($children as $child) {
					saveRelation($child['id'], $destination, 'mother');			
				}
				
			} else {
				$children = DB::getList("SELECT * FROM nodes WHERE motherId = ".$source['id']);
				DB::query("UPDATE nodes SET fatherId=$destination WHERE motherId = ".$source['id']);			
				
				foreach($children as $child) {
					saveRelation($child['id'], $destination, 'father');			
				}				
			}
			break;
	}
}


$id 	   = $_REQUEST['id'];
$firstName = $_REQUEST['firstName'];
$lastName  = $_REQUEST['lastName'];
$birthDate = $_REQUEST['birthDate'];
$deathDate = $_REQUEST['deathDate'];
$sex       = $_REQUEST['sex'];

$relation  = $_REQUEST['relation'];
$source    = $_REQUEST['source'];

if ($id) {
//	echo "UPDATE nodes SET firstName='$firstName', lastName='$lastName', birthDate='$birthDate', deathDate=" . ($deathDate?"'$deathDate'":"null") . ", sex='$sex' WHERE id=$id";
	DB::query("UPDATE nodes SET firstName='$firstName', lastName='$lastName', birthDate='$birthDate', deathDate=" . ($deathDate?"'$deathDate'":"null") . ", sex='$sex' WHERE id=$id");	
} else {
	DB::query("INSERT INTO nodes(firstName, lastName, birthDate, deathDate, sex) VALUES('$firstName', '$lastName', '$birthDate', " .($deathDate?"'$deathDate'":"null") . ", '$sex')");	
	$id = DB::last_id();
}

$nodesList = DB::getList("SELECT * FROM nodes WHERE id=$id");
$tree = DB::getItem('SELECT * FROM trees WHERE id=1');

$nodes = array();

foreach ($nodesList as $node) {
	$nodeObj = new stdClass;
	$nodeObj->id		= (int)$node['id'];
	$nodeObj->firstName = $node['firstName'];
	$nodeObj->lastName  = $node['lastName'];
	$nodeObj->sex       = (bool)($node['sex']);
	if ($node['birthDate']) $nodeObj->birthDate = $node['birthDate'];
	if ($node['deathDate']) $nodeObj->deathDate = $node['deathDate'];
	
	$nodes[] = $nodeObj;	
}

if ($relation) {
	addRelation($source, $id, $relation);
}

$result = new stdClass();
$result->nodes = $nodes;
$result->relations = $relations;
$result->defaultRoot = (int)$tree['defaultRoot'];

echo json_encode($result);

