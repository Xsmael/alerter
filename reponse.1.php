<?php
//inclusion du fichier de configuration à la base de données
include_once("connexpdo.inc.php");

//inclusion du fichier des fonctions utilisées
include("fonctionsApi.php");

//connexion à la base de données
$db=connexpdo("connexion");

header("content-type:application/json");

$transactionid     = $_POST['transactionId'];
$operateur         = $_POST['operator'];
$telephone         = $_POST['phone'];
$montant           = $_POST['cashAmount'];
$requestid         = $_POST['requestId'];
$transactionStatus = $_POST['transactionStatus'];

if(!$transactionStatus) {
	$res= array(
		'msg' => "echec validation paiement",
		'errorCode' => 0,
		'success' => false);
	echo json_encode($res);
	exit(0);
}

//recuperation des information de la transaction concerne
$queryTransaction = $db->prepare("SELECT * FROM transaction WHERE transaction.requestid=?");
if(! $queryTransaction->execute(array($requestid))) {
	//$erreurTransaction=$queryTransaction->errorInfo(); // write to log file
	$res= array(
		'msg' => "Erreur lors de la recuperation de la transaction.",
		'errorCode' => 0,
		'success' => false);
	echo json_encode($res);
	exit(0);
}

$transaction=$queryTransaction->fetch(PDO::FETCH_NUM);
$id_transaction=$transaction[0];
$code_parrain=$transaction[4];
$type=$transaction[5];
$volume=$transaction[6];
/** Selection du code en fonction du montant */
$queryCode = $db->prepare("SELECT * FROM codeautilise WHERE codeautilise.montant=? AND codeautilise.type=? AND codeautilise.volume=? LIMIT 1");

if(!$queryCode->execute(array($montant, $type, $volume) )){
	// $erreurCode=$queryCode->errorInfo();
	$res= array(
		'msg' => "Erreur lors de la recuperation du code.",
		'errorCode' => 0,
		'success' => false);
	echo json_encode($res);
	exit(0);
}


$donneesCodes=$queryCode->fetch(PDO::FETCH_NUM);
$id=$donneesCodes[0];
$code=$donneesCodes[1];
$montant=$donneesCodes[2];
$validite=$donneesCodes[3];
$description=$donneesCodes[4];
$type=$donneesCodes[5];
if(!isset($id))
{
	$res= array(
		'msg' => "aucun code trouver",
		'errorCode' => 0,
		'success' => false);
	echo json_encode($res);
	exit(0);
}


$queryCodeUtilise = $db->prepare("SELECT COUNT(requestid) FROM transaction,codeutilise WHERE transaction.id=codeutilise.transaction_id AND transaction.requestid=?");
if(!$queryCodeUtilise->execute(array($requestid))){
	// $erreur=$erreurCode->errorInfo(); array("codeErr"=>"402", "erreur"=>$erreur, "data"=> var_dump($_POST));
	$res= array(
		'msg' => "Erreur lors de la verification du transaction Id",
		'errorCode' => 0,
		'success' => false);
	echo json_encode($res);
	exit(0);
}

$donneesCodeUtilises=$queryCodeUtilise->fetch(PDO::FETCH_NUM);
if($donneesCodeUtilises[0]!=0) {
	$res= array(
		'msg' => "Doublon de request Id",
		'errorCode' => 0,
		'success' => false);
	echo json_encode($res);
	exit(0);
}
	
$query = $db->prepare("INSERT INTO codeutilise (id,code,montant,validite,type,codeparrain,transactionid,transaction_id)
VALUES(:id,:code,:montant,DATE_ADD(NOW(), INTERVAL :validite DAY),:type,:codeparrain,:transactionid,:id_transaction)");
$result=$query->execute(array(":id" => NULL, ":code" => $code, ":montant" => $montant, ":validite" => $validite, ":type" => $type, ":codeparrain" => $code_parrain, ":transactionid" => $transactionid, ":id_transaction" => $id_transaction));
if(!$result){
	// $erreur=$query->errorInfo();$erreur_json = array("codeErr"=>"402", "erreur"=>$erreur, "data"=> var_dump($_POST));
	$res= array(
		'msg' => "Echect de l'insertion",
		'errorCode' => 0,
		'success' => false);
	echo json_encode($res);
	exit(0);
}


$deleteQuery = $db->prepare("DELETE FROM codeautilise WHERE id=?");
if(!$deleteQuery->execute(array($id))){
	// $erreur=$deleteQuery->errorInfo();
	$res= array(
		'msg' => "Erreur lors de la suppression de code",
		'errorCode' => 0,
		'success' => false);
	echo json_encode($res);
	exit(0);
}
		
PLJ7UTQWD6QM7
$message="Votre code de ticket est: ".$code.", valide pour ".$description.". Expire dans ".$validite." jour". ($validite>1) ? 's' : '' ;
// $message="Votre code de ticket est: PLJ7UTQWD6QM7 , valide pour 30jours. Expire dans 30 jours";
http://'.160.226.184.146.':'.PORTNEOGATE.'?operator='.$fields['operator'].'&destinataire='.$fields['destinataire'].'&user=ubc&code=5vH38zCX&message='.$fields['message'];
http://160.226.184.146:3004/?operator=orange&destinataire=64342626&user=ubc&code=5vH38zCX&message=Votre code de ticket est: PLJ7UTQWD6QM7 , valide pour 30jours. Expire dans 30 jours

$smsResult= sendMsg(array(
    "operator" => $operateur, 
    "destinataire" => $telephone, 
    "message" => $message
));

//  $smsResult ; handle the result from msg Result

$res= array(
    'msg' => $smsResult,
    'errorCode' => 0,
    'success' => true);  //cannot be sure success is true until  we handle the $smsResult properley
echo json_encode($res);

if(!$code_parrain) exit(0);  // S'il n'y as pas de code parrain tout s'arrete ici.


/** Gestion du Parrainage et quotas */

$getCodeParrain = $db->prepare("SELECT COUNT(codeparrain) FROM codeutilise WHERE codeparrain=?");
if(!$getCodeParrain->execute(array($code_parrain))){
	// $erreur=$getCodeParrain->errorInfo();	$erreur_json = array("codeErr"=>"402", "erreur"=>$erreur, "data"=> var_dump($_POST));
	$res= array(
		'msg' => "Erreur lors de la verification du quota parrain. code parrain: $code_parrain",
		'errorCode' => 0,
		'success' => false);
	echo json_encode($res);
    exit(0);
}

$nombreParrainage=$getCodeParrain->fetch(PDO::FETCH_NUM);
if($nombreParrainage[0]%NBRPARRAINAGE==0){

    $getCodeBonus = $db->prepare("SELECT * FROM codeautilise WHERE type='bonus' LIMIT 1");
    $getCodeBonus->execute();
    
    $getParrain = $db->prepare("SELECT * FROM parrain WHERE code=? LIMIT 1");
    $getParrain->execute(array($code_parrain));


    $donneesCodesBonus=$getCodeBonus->fetch(PDO::FETCH_NUM);
    $codeBonus=$donneesCodesBonus[1];

    $donneesParrain=$getParrain->fetch(PDO::FETCH_NUM);
    $telephoneBonus=$donneesParrain[2];

    $deleteQuery = $db->prepare("DELETE FROM codeautilise WHERE code=?");
    if(!$deleteQuery->execute(array($codeBonus))){
        // $erreur=$deleteQuery->errorInfo();
        $res= array(
            'msg' => "Erreur lors de la suppression de code",
            'errorCode' => 0,
            'success' => false);
        echo json_encode($res);
        exit(0);
    }

    $message="Merci pour votre colaboration.Votre bonus de parrainage est: ".$codeBonus;
    $smsResult= sendMsg(array(
        "operator" => $operateur, 
        "destinataire" => $telephoneBonus, 
        "message" => $messageBonus
    ));
    
    //  $smsResult ; handle the result from msg Result
    
    $res= array(
        'msg' => $smsResult,
        'errorCode' => 0,
        'success' => true);  //cannot be sure success is true until  we handle the $smsResult properley
    echo json_encode($res);  
}

?>