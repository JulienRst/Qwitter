<?php

	require_once('ircmaxell/ircmaxell.php'); //crypter - décrypter les mots de passes

	/* 

		L'objet database contient toutes les fonctions qui permettent d'obtenir des 
		informations dans la bdd, d'en ajouter, d'en enlever.

	*/

	class database{

		//dbname / host / login_bdd / mdp_bdd permettent sont les informations nécessaires pour se connecter à la BDD.

		private $dbname = "qwitter";
		private $host = "localhost";
		private $login_bdd = "root";
		private $mdp_bdd = "";

		// La variable $pdo va nous permettrent de stocker le pont entre le php et la BDD
		protected $pdo;

		/* ---

			Entrée : #
			Sortie : #
			Action : Va mettre dans la variable $pdo l'objet PDO en utilisant les informations de connexion.

		--- */

		private function connectBdd(){
			$this->pdo = new PDO("mysql:host=".$this->host.";dbname=".$this->dbname,$this->login_bdd,$this->mdp_bdd) or die (
				"Impossible de se connecter"
			);
		}

		/* ---
		
			Entrée : #
			Sortie : #
			Action : Lance la fonction connectBdd() et attribue à l'objet PDO des flags d'erreur pour faciliter le débuggage.

		--- */

		
		public function __construct(){
			$this->connectBdd();
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

		/* ---
		
			Entrée : #
			Sortie : #
			Action : #

		--- */

		private function report_error(){
			//
		}

		/* ////////////////// INSCRITPION | CONNEXION ///////////////////// */

		/* ---
		
			Entrée : Les informations sur l'utilisateur à enregistrer
				-key est un identifiant unique qui associé au mail permettra plus tard de vérifier l'adresse mail d'un
				utilisateur par mail.
			Sortie : #
			Action : Insertion dans la BDD de l'utilisateur à l'aide de ses infos. On va crypter le mot de passe avant de 
			le rentrer afin d'éviter qu'il y est des informations en clair dans la base.
			Si l'insertion se passe mal, on affiche le message d'erreur.

		--- */


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

		/* ---
		
			Entrée : combinaison mail / mot de passe en clair
			Sortie : tableau d'information contenant si l'utilisateur est connecté ou non, l'id de l'utilisateur connecté | un message d'erreur 
			expliquant pourquoi la connexion à échouée
			Action : On récupère les infos de l'utlisateur à l'aide son adresse mail
			On test le mot de passe pour savoir s'il est bien à l'origine du mot de passe crypté en bdd.
			Si c'est bon on retourne [VRAI,id de l'utlisateur connecté]
			Sinon [FAUX,Message d'erreur]

		--- */

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

		/* ---
		
			Entrée : Une adresse mail
			Sortie : VRAI | FAUX
			Action : On cherche dans la bdd si l'adresse mail est dans la bdd, si elle y est on renvoie VRAI, sinon FAUX

		--- */

		public function isThisMailInDb($u_mail){
			$stmt = $this->pdo->prepare("SELECT mail FROM user WHERE mail = ?");
			$stmt->execute(array($u_mail));

			if($stmt->rowcount() != 0){
				return true;
			} else {
				return false;
			}
		}

		/* /////////////////////// GET USER /////////////////////////////// */

		/* ---
		
			Entrée : mail d'un utilisateur
			Sortie : id de l'utilisateur
			Action : Si le mail existe dans la base, on renvoie son id. Sinon on renvoie "Adresse Introuvable"

		--- */

		public function getIdFromMail($mail){
			if($this->isThisMailInDb($mail)){
				$stmt = $this->pdo->prepare("SELECT id FROM user WHERE mail = :mail");
				$stmt->bindParam(':mail',$mail);
				$stmt->execute();
			} else {
				return "Adresse introuvable";
			}
		}

		/* ---
		
			Entrée : id d'un utilisateur
			Sortie : tableau d'information de l'utilisateur
			Action : On vérifie que l'id est bien un nombre
			On récupère les données de l'utilisateur dans la bdd,
			On transforme son age à l'aide d'une fonction getAge (expliquée plus bas dans ce fichier)
			On ajoute à ce tableau : nombre de Qwitt | Reqwitt | Favoris (qui sont des informations présentent sur le profil d'une personne)
			Ainsi que son nombre d'abonnés | abonnement 

		--- */

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

		/* ---
		
			Entrée : une chaine de caractère
			Sortie : la liste des utilisateurs possédant cette chaine de caractères dans son nom | prénom | pseudo
			Action : On recherche dans la base de données les utilisateurs correspondant à la chaine de caractères. 
			On recherche sur trois points : name | surname | pseudo
			Une fois le tableau de résultat obtenu de la requête, on met une majuscule à la première lettre du nom et du prénom 
			de chaque utilisateur renvoyé car ils sont stockés en minuscule dans la BDD

		--- */

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
		
		/* /////////////////////// GET INFO ON USER | MESSAGE /////////////////////////////// */

		/* ---
		
			Entrée : Infos à récupérer / Id sur lequel récupérer les infos
			Sortie : Le nombre de Qwitt/Reqwitt/Favoris/Abonnement/Abonnés
			Action : On vérifie que le $type rentré est un de ceux que l'on attend
			Puis on cherche dans la bdd le nombre de résultat et on renvoie le résultat. 

		--- */

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

		/* /////////////////////// GET MESSAGE /////////////////////////////// */

		/* ---
		
			Entrée : id d'un utilisateur
			Sortie : tableau contenant les qwitts d'un utilisateur
			Action : On va chercher dans la table qwitt tous les qwitt postés par l'utilisateur 
			Puis on crée un objet qwitt pour chaque message (voir la description de l'objet qwitt plus bas)
			Et on retourne le tableau contenant les objets qwitt (plus "simplement" exploitable qu'un tableau de tableau pour l'affichage)

			/!\ Même fonctionnement : getMessageFromUser | getFavorisFromUser | getReqwittFromUser /!\

		--- */

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

		/* ---
		
			Entrée : id d'un qwitt
			Sortie : tableau d'information du qwitt
			Action : On récupère un qwitt à l'aide de son identifiant en bdd et on retourne le résultat

		--- */

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

		/* /////////////////////// ADD MESSAGE /////////////////////////////// */

		/* ---
		
			Entrée : id de l'utilsateur qui post / timestamp du qwitt / contenu du qwitt
			Sortie : rien ou un message d'erreur en cas de malfonction
			Action : On insére le qwitt dans la bdd !

			/!\ Même fonctionnement pour addMessage / addFavoris / addReqwitt /!\

		--- */

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

		/* /////////////////////// CHANGE USER /////////////////////////////// */

		/* ---
		
			Entrée : mail / clef de vérification
			Sortie : tableau de résultat (success : TRUE | FALSE / error)
			Action : On va récupérer l'utilisateur à l'aide de son adresse mail
			On compare la clef de l'utilisateur à celle envoyé par la fonction, 
			si ce sont les mêmes on met met l'utilisateur en tant que vérifié dans la base
			Sinon on renvoie un message d'erreur

		--- */

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

		/* ---
		
			Entrée : new profil est un tableau avec les informations rentrées dans les
			paramètres par l'utilisateur, et l'id de l'utilisateur
			Sortie : un message d'erreur !
			Action : On va checker chaque entrée du tableau avec l'entrée dans la bdd, et 
			si elles sont différentes ont update le profil de l'utilisateur dans la bdd 
			Pour le mot de passe il faut regarder s'il est bien source du mot de passe crypté 
			dans la bdd.

		--- */

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

		/* /////////////////////// RELATION  /////////////////////////////// */

		/* ---
		
			Entrée : id de l'user connecté / id de l'user que l'on regarde
			Sortie : VRAI / FAUX
			Action : On regarde dans la bdd si User connecté suit l'user que l'on regarde

		--- */

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

		/* ---
		
			Entrée : id de l'user connecté / id de l'user à suivre
			Sortie : message d'erreur s'il faut
			Action : On ajoute à la bdd un lien de suivi entre l'user connecté et l'user à suivre
			On renvoie un message d'erreur si ça plante

		--- */

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

		/* ---
		
			Entrée : id de l'user connecté / id de l'user à ne plus suivre
			Sortie : message d'erreur s'il faut
			Action : On retire à la bdd le lien de suivi entre l'user connecté et l'user suivi
			On renvoie un message d'erreur si ça plante

		--- */

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

	/*

		L'objet qwitt est en extends database, ce qui veut dire qu'il a accès aux méthodes
		de l'objet database. 

		Cet objet peut contenir au choix un qwitt | reqwitt | favoris

	*/

	class qwitt extends database{
		//On ne peut pas modifier ces valeurs après le __construct, on peut seulement les récupérer (READ ONLY)
		private $idMsg; //id du qwitt
		private $idUser; //id de l'utilisateur qui a qwitté
		private $date; //timestamp du qwitt
		private $message; //message en cas d'un qwitt | qwitt reqwitté ou favoris 
		private $nbReQwitt; //Nombre de reqwitt sur le message
		private $nbFav; //Nombre de fav sur le message
		private $user; //tableau d'information sur l'utilisateur
		protected $pdo; //Objet pdo que l'on récupère à la création 
		private $type; //Type de qwitt : normal | reqwitt | favoris
		private $reqwitt_message; //Message dans le reqwitt 

		/* ---
		
			Entrée : tableau de l'objet, objet pdo, type du qwitt
			Sortie : #
			Action : En fonction du type de l'objet on récupère les infos nécessaires à 
			la création de l'objet qwitt

		--- */

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

		/* ---
		
			Entrée : id du message / ce que l'on recherche
			Sortie : Le nombre de reqwitt | favoris
			Action : On va chercher dans la bdd le nombre de reqwitt ou de favoris 
			qui ont été fait avec le message donné (enfin l'id) en entrée

		--- */

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

	//convertit un timestamp en une date affichable
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

	//Envoie d'un mail avec la clef et le mail en get dans l'url pour vérifier un utilisateur
	function sendConfirmMail($mail,$key){
		$to      = $mail;
		$subject = 'Validez votre inscription sur Qwitter !';
		$message = 'Bonjour à toi Qwitterien afin de vérifier les informations que tu nous as donné il te suffit de cliquer sur le lien dans le mail !';
		$message.= 'Voici le lien : http://www.julien-rousset.fr/qwitter/php/user_verify.php?key='.$key.'&mail='.$mail;
		$headers = 'MIME-Version: 1.0';
   		mail($to, $subject, $message, $headers);
	}
?>