<?php
	//Pour A : Les bindParam remplace les :item par la valeur du deuxième paramètre

	require_once('ircmaxell/ircmaxell.php'); //crypter - décrypter les mots de passes

	class database{

		private $dbname = "qwitter";
		private $host = "localhost";
		private $login_bdd = "root";
		private $mdp_bdd = "";
		protected $pdo; // Pont entre la BDD et le code php

		private function connectBdd(){
			$this->pdo = new PDO("mysql:host=".$this->host.";dbname=".$this->dbname,$this->login_bdd,$this->mdp_bdd) or die (
				"Impossible de se connecter"
			);
		}

		// Se lance a la construction de l'objet
		public function __construct(){
			$this->connectBdd();
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

		private function report_error(){
			//
		}

		public function isThisMailInDb($u_mail){
			$stmt = $this->pdo->prepare("SELECT mail FROM user WHERE mail = ?");
			$stmt->execute(array($u_mail));

			if($stmt->rowcount() != 0){
				return true;
			} else {
				return false;
			}
		}

		public function insertNewUser($name,$surname,$pseudo,$birthday,$mail,$password,$key){
			$password = cryptmdp($password); //On crypte le mot de passe
			//On prépare la requette d'insertion d'un utilisateur.
			$stmt = $this->pdo->prepare("INSERT INTO user(name,surname,pseudo,birthday,mail,password,verifKey) VALUES(:name,:surname,:pseudo,:birthday,:mail,:password,:key)");
			//On remplace tous les :item par la bonne valeur.
			$stmt->bindParam(':name',$name);
			$stmt->bindParam(':surname',$surname);
			$stmt->bindParam(':pseudo',$pseudo);
			$stmt->bindParam(':birthday',$birthday);
			$stmt->bindParam(':mail',$mail);
			$stmt->bindParam(':password',$password);
			$stmt->bindParam(':key',$key);
			//On essaie de faire passer la requête, et si elle rate on affiche un message d'erreur
			try {
				$stmt->execute();
			} catch (Exception $e){
				exit("Error line ".$e->getLine()." : ".$e->getMessage());
			} 
		}

		public function testConnection($mail,$mdp){
			if($this->isThisMailInDb($mail)){
				$stmt = $this->pdo->prepare("SELECT * FROM user WHERE mail = :mail");
				$stmt->bindParam(':mail',$mail);
				$stmt->execute();
				//Fetch permet de récupérer la première ligne du résultat de la requête
				$user = $stmt->fetch();
				$db_mdp = $user["password"];

				if($user["verified"] == 1){
					if(testmdp($mdp,$db_mdp)){
						return array("connected" => true,"id" => $user["id"]);
					} else {
						return array("connected" => false,"error" => "Mot de passe incorrect !");
					}
				} else {
					return array("connected" => false,"error" => "Ce compte n'est pas vérifié ! Regardez vos mails ou redemandez une vérification en <a href='php/sendVerifMail.php?id=".$user['id']."'>cliquant ici.");
				}
			} else {
				return array("connected" => false,"error" => "Adresse introuvable !");
			}
		}

		public function getIdFromMail($mail){
			if($this->isThisMailInDb($mail)){
				$stmt = $this->pdo->prepare("SELECT id FROM user WHERE mail = :mail");
				$stmt->bindParam(':mail',$mail);
				$stmt->execute();
			} else {
				return "Adresse introuvable";
			}
		}

		public function getCurrentUser($id){
			if(is_int(intval($id))){
				$stmt = $this->pdo->prepare("SELECT * FROM user WHERE id = :idUser");
				$stmt->bindParam(':idUser',$id);
				$stmt->execute();
				$user = $stmt->fetch();

				$user["birthday"] = getAge($user["birthday"]);
				$user["nbQwitts"] = $this->getNumberOf("qwitt",$id);
				$user["nbReqwitts"] = $this->getNumberOf("reqwitt",$id);
				$user["nbFavoris"] = $this->getNumberOf("favoris",$id);
				$user["name"] = ucfirst($user["name"]);//Upper Case first, première lettre en majuscule
				$user["surname"] = ucfirst($user["surname"]);
				$user["nbFollow"] = $this->getNumberOf("abo",$id);
				$user["nbAbo"] = $this->getNumberOf("follow",$id);

				return $user;
			} else {
				$this->report_error();
				return 0;
			}
		}

		public function getUserByName($name){
			$name = '%'.$name.'%';
			$stmt = $this->pdo->prepare("SELECT id,name,surname,pseudo,url_pic FROM user WHERE name LIKE :name1 or surname LIKE :name2 or pseudo LIKE :name3");
			$stmt->bindParam(':name1',$name);
			$stmt->bindParam(':name2',$name);
			$stmt->bindParam(':name3',$name);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			for($i=0;$i<$stmt->rowcount();$i++){
				$result[$i]["name"] = ucfirst($result[$i]["name"]);
				$result[$i]["surname"] = ucfirst($result[$i]["surname"]);
			}
			return $result;
		}
		// faire les comptes
		public function getNumberOf($type,$id){
			if($type == "qwitt" || $type == "reqwitt" || $type == "favoris"){
				$stmt = $this->pdo->prepare("SELECT count(*) FROM $type WHERE idUser = :idUser");//Dans la table du "type donnée" on va compter toutes les occurences de l'id donné en paramètre
				$stmt->bindParam(':idUser',$id);
				$stmt->execute() or die("Error");
				$line = $stmt->fetch();
				return $line[0];
			} else if($type == "abo" || $type == "follow"){
				//dans la table follow on a QUI suit QUOI, si on veut les abonnements on compte le nombre d'occ dans QUI
				// et si on veut le nombre d'abonné on compte le nombre d'occ dans QUOI 
				if($type == "abo"){
					$spec= "idAbo";
				} else if($type == "follow"){
					$spec="idUser";
				}
				$stmt = $this->pdo->prepare("SELECT count(*) FROM follow WHERE $spec = :idUser");
				$stmt->bindParam(":idUser",$id);
				$stmt->execute() or die ("ERROR");
				$line = $stmt->fetch();

				return  $line[0];
			} else {
				$this->report_error();
			}
		}

		public function addMessage($idUser,$date,$message){
			$stmt = $this->pdo->prepare("INSERT INTO qwitt (idUser,date,message) VALUES (:idUser,:date,:message)");
			$stmt->bindParam(':idUser',$idUser);
			$stmt->bindParam(':date',$date);
			$stmt->bindParam(':message',$message);
			try {
				$stmt->execute();
			} catch (Exception $e) {
				return ("Error line ".$e->getLine()." : ".$e->getMessage());
			}
		}

		public function getMessageFromUser($id){
			$stmt = $this->pdo->prepare("SELECT * FROM qwitt WHERE idUser = :id ORDER BY date DESC");
			$stmt->bindParam(':id',$id);
			try {
				$stmt->execute();
			} catch (Exception $e) {
				return ("Error line ".$e->getLine()." : ".$e->getMessage());
			}
			$tab_message = $stmt->fetchAll(PDO::FETCH_ASSOC);//pas prendre en compte les numéros de place dans le tableau
			for($i=0;$i<$stmt->rowcount();$i++){
				$tab_message[$i] = new qwitt($tab_message[$i],$this->pdo);
			}
			return $tab_message;
		}

		public function getMessageFromId($id){
			$stmt = $this->pdo->prepare("SELECT * FROM qwitt WHERE id = :id ORDER BY date DESC");
			$stmt->bindParam(':id',$id);
			try {
				$stmt->execute();
			} catch (Exception $e) {
				return ("Error line ".$e->getLine()." : ".$e->getMessage());
			}
			
			$message = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $message[0];
		}

		public function getFavorisFromUser($id){
			$stmt = $this->pdo->prepare("SELECT * FROM favoris WHERE idUser = :id ORDER BY date DESC");
			$stmt->bindParam(':id',$id);
			try {
				$stmt->execute();
			} catch (Exception $e) {
				return ("Error line ".$e->getLine()." : ".$e->getMessage());
			}
			$tab_message = $stmt->fetchAll(PDO::FETCH_ASSOC);
			for($i=0;$i<$stmt->rowcount();$i++){
				$tab_message[$i] = new qwitt($tab_message[$i],$this->pdo,'favoris');
			}
			return $tab_message;
		}

		public function getReqwittFromUser($id){
			$stmt = $this->pdo->prepare("SELECT * FROM reqwitt WHERE idUser = :id ORDER BY date DESC");
			$stmt->bindParam(':id',$id);
			try {
				$stmt->execute();
			} catch (Exception $e) {
				return ("Error line ".$e->getLine()." : ".$e->getMessage());
			}
			$tab_message = $stmt->fetchAll(PDO::FETCH_ASSOC);
			for($i=0;$i<$stmt->rowcount();$i++){
				$tab_message[$i] = new qwitt($tab_message[$i],$this->pdo,'reqwitt');
			}
			return $tab_message;
		}

		public function addFavoris($idUser,$idMsg,$date){
			$stmt = $this->pdo->prepare("INSERT INTO favoris (idUser,idMessage,date) VALUES(:idUser,:idMessage,:date)");
			$stmt->bindParam(':idUser',$idUser);
			$stmt->bindParam(':idMessage',$idMsg);
			$stmt->bindParam(':date',$date);

			try {
				$stmt->execute();
			} catch (Exception $e) {
				return ("Error line ".$e->getLine()." : ".$e->getMessage());
			}
		}

		public function addReqwitt($idUser,$idMsg,$date,$message){
			$stmt = $this->pdo->prepare("INSERT INTO reqwitt (idUser,idMessage,date,message) VALUES(:idUser,:idMessage,:date,:message)");
			$stmt->bindParam(':idUser',$idUser);
			$stmt->bindParam(':idMessage',$idMsg);
			$stmt->bindParam(':date',$date);
			$stmt->bindParam(':message',$message);

			try {
				$stmt->execute();
			} catch (Exception $e) {
				return ("Error line ".$e->getLine()." : ".$e->getMessage());
			}
		}

		public function verifyUser($mail,$key){
			$idUser = $this->getIdFromMail($mail);
			$user = $this->getCurrentUser($idUser);

			if($user['verifKey'] == $key){
				$stmt = $this->pdo->prepare("UPDATE user SET verified = 1 WHERE id = :idUser");
				$stmt->bindParam(':idUser',$idUser);
				try {
					$stmt->execute();
				} catch (Exception $e) {
					return array("success" => false, "error" => "Error line ".$e->getLine()." : ".$e->getMessage());
				}
				return array("success" => true);
			} else {
				return array("success" => false,"error" => "Clef non valide, contactez l'administrateur du site");
			}

		}

		public function changeProfil($new_profil,$idUser){
			$actual_profile = $this->getCurrentUser($idUser);
			$error = "";
			foreach ($new_profil as $key => &$value) {
				if($value != '' && $value != ' '){
					if($key != "password"){
						if($actual_profile[$key] != $value){
							$stmt = $this->pdo->prepare("UPDATE user SET $key = :value WHERE id = :idUser");
							$stmt->bindParam(':idUser',$idUser);
							if($key == "surname" || $key == "name"){
								$value = strtolower($value);
							}
							$stmt->bindParam(':value',$value);
							try{
								$stmt->execute();
							} catch(Exception $e){
								$error += "$key : Error line ".$e->getLine()." : ".$e->getMessage();
							}
						}
					} else {
						if(!testmdp($value,$actual_profile['password'])){
							$stmt = $this->pdo->prepare("UPDATE user SET password = :value WHERE id = :idUser");
							$stmt->bindParam(':idUser',$idUser);
							$value = cryptmdp($value);
							$stmt->bindParam(':value',$valeur);
							try{
								$stmt->execute();
							} catch(Exception $e){
								$error += "$key : Error line ".$e->getLine()." : ".$e->getMessage();
							}
						}
					}
				}
			}
			return $error;
		}

		public function isAboTo($idUser,$idProfil){
			$stmt = $this->pdo->prepare("SELECT * FROM follow WHERE idUser = :idUser and idAbo = :idProfil");
			$stmt->bindParam(":idUser",$idUser);
			$stmt->bindParam(":idProfil",$idProfil);
			try {
				$stmt->execute();
			} catch (Exception $e) {
				return ("Error line ".$e->getLine()." : ".$e->getMessage());
			}
			if($stmt->rowcount() == 1){
				return true;
			} else {
				return false;
			}
		}

		public function addFollow($idUser,$idAbo){
			$stmt = $this->pdo->prepare("INSERT INTO follow(idUser,idAbo) VALUES(:idUser,:idAbo)");
			$stmt->bindParam(':idUser',$idUser);
			$stmt->bindParam(':idAbo',$idAbo);
			try {
				$stmt->execute();
			} catch (Exception $e) {
				return ("Error line ".$e->getLine()." : ".$e->getMessage());
			}
		}

		public function removeFollow($idUser,$idAbo){
			$stmt = $this->pdo->prepare("DELETE FROM follow WHERE idUser = :idUser and idAbo = :idAbo");
			$stmt->bindParam(':idUser',$idUser);
			$stmt->bindParam(':idAbo',$idAbo);
			try {
				$stmt->execute();
			} catch (Exception $e) {
				return ("Error line ".$e->getLine()." : ".$e->getMessage());
			}
		}
	}

	class qwitt extends database{
		//On ne peut pas modifier ces valeurs après le __construct, on peut seulement les récupérer (READ ONLY)
		private $idMsg;
		private $idUser;
		private $date;
		private $message;
		private $nbReQwitt;
		private $nbFav;
		private $user;
		protected $pdo;
		private $type;
		private $reqwitt_message;

		public function __construct($qwitt,$pdo,$type='normal'){
			$this->idMsg = $qwitt["id"];
			$this->idUser = $qwitt["idUser"];
			$this->date = timestampToDate($qwitt["date"]);
			$this->pdo = $pdo;
			$this->type = $type;

			if($type == 'normal'){
				$this->nbReQwitt = $this->getCaracOfMessage($this->idMsg,"reqwitt");
				$this->nbFav = $this->getCaracOfMessage($this->idMsg,"favoris");
				$this->message = $qwitt["message"];
			} else if($type == 'favoris'){
				$idMessageFav = $qwitt['idMessage'];
				//On met un qwitt dans un qwitt pour récupérer les infos du message mis en favoris
				$this->message = new qwitt($this->getMessageFromId($idMessageFav),$this->pdo);
				$this->user = $this->getCurrentUser($this->message->getIdUser());
			} else if($type == "reqwitt"){
				$idMessageRq = $qwitt['idMessage'];
				$this->message = new qwitt($this->getMessageFromId($idMessageRq),$this->pdo);
				$this->user = $this->getCurrentUser($this->message->getIdUser());
				$this->reqwitt_message = $qwitt["message"];
			}
		}

		//Methode get
		public function getIdMsg(){return $this->idMsg;}
		public function getIdUser(){return $this->idUser;}
		public function getDate(){return $this->date;}
		public function getMessage(){return $this->message;}
		public function getNbReQwitt(){return $this->nbReQwitt;}
		public function getNbFav(){return $this->nbFav;}
		public function getType(){return $this->type;}
		public function getUser(){return $this->user;}
		public function getReqwittMessage(){return $this->reqwitt_message;}

		private function getCaracOfMessage($idMsg,$type){
			if($type == "reqwitt" || $type == "favoris"){
				//Recuperer le nombre de reqwitt et de favoris
				$stmt = $this->pdo->prepare("SELECT count(*) FROM $type WHERE idMessage = :idMsg");
				$stmt->bindParam(':idMsg',$idMsg);
				$stmt->execute();
				$stmt = $stmt->fetch();
				return $stmt[0];
			}
		}
	}
	//convertir une date en un age
	function getAge($date){
		$birthDate = $date;
		$birthDate = explode("-", $birthDate);
		$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md") ? ((date("Y") - $birthDate[0]) - 1) : (date("Y") - $birthDate[0]));
		return $age;
	}

	function timestampToDate($timestamp){
		$date = "le ".date("d/m/y \à H\hi", $timestamp);
		return $date;
	}

	//password_hash - password verify sont présent dans la librairi ircmaxell
	function cryptmdp($mdp){
		$mdp = password_hash($mdp,PASSWORD_BCRYPT);
		return $mdp;
	}

	function testmdp($mdp,$db_mdp){
		if(password_verify($mdp,$db_mdp)){
			return true;
		} else {
			return false;
		}
	}

	function sendConfirmMail($mail,$key){
		$to      = $mail;
		$subject = 'Validez votre inscription sur Qwitter !';
		$message = 'Bonjour à toi Qwitterien afin de vérifier les informations que tu nous as donné il te suffit de cliquer sur le lien dans le mail !';
		$message.= 'Voici le lien : http://www.julien-rousset.fr/qwitter/php/user_verify.php?key='.$key.'&mail='.$mail;
		$headers = 'MIME-Version: 1.0';
   		mail($to, $subject, $message, $headers);
	}
?>