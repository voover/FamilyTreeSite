<?php
header("Content type: application/json; charset=utf-8");

require_once('DB.php');

DB::init('familytree', 'localhost', 'root', 'per78FLI');  

$nodesList = DB::getList('SELECT * FROM nodes');
$tree = DB::getItem('SELECT * FROM trees WHERE id=1');

$nodes = array();
$relations = array();
foreach ($nodesList as $node) {
	$nodeObj = new stdClass;
	$nodeObj->id		= (int)$node['id'];
	$nodeObj->firstName = $node['firstName'];
	$nodeObj->lastName  = $node['lastName'];
	$nodeObj->sex       = (bool)($node['sex']);
	if ($node['birthDate']) $nodeObj->birthDate = $node['birthDate'];
	if ($node['deathDate']) $nodeObj->deathDate = $node['deathDate'];
	
	$nodes[] = $nodeObj;
	
	if ($node['motherId']) {
		$relation = new stdClass();
		$relation->source = (int)$node['id'];
		$relation->destination   = (int)$node['motherId'];
		$relation->type = 'mother';
		$relations[] = $relation;
	}
	
	if ($node['fatherId']) {
		$relation = new stdClass();
		$relation->source = (int)$node['id'];
		$relation->destination   = (int)$node['fatherId'];
		$relation->type = 'father';
		$relations[] = $relation;
	}
	
	if ($node['spouseId']) {
		$relation = new stdClass();
		$relation->source = (int)$node['id'];
		$relation->destination   = (int)$node['spouseId'];
		$relation->type = 'spouse';
		$relations[] = $relation;
	}		
}

$result = new stdClass();
$result->nodes = $nodes;
$result->relations = $relations;
$result->defaultRoot = (int)$tree['defaultRoot'];

echo json_encode($result);