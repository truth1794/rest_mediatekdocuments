<?php
include_once("ConnexionPDO.php");

/**
 * Classe de construction des requêtes SQL à envoyer à la BDD
 */
class AccessBDD {
	
//    public $login="u555127890_userdocs";
//    public $mdp="Azerty85";
//    public $bd="u555127890_docsmediatek86";
//    public $serveur="127.0.0.1";
//    public $port="3306";	
//    public $conn = null;
	
   public $login="root";
   public $mdp="";
   public $bd="mediatek86";
   public $serveur="localhost";
   public $port="3306";	
   public $conn = null;

    /**
     * constructeur : demande de connexion à la BDD
     */
    public function __construct(){
        try{
            $this->conn = new ConnexionPDO($this->login, $this->mdp, $this->bd, $this->serveur, $this->port);
        }catch(Exception $e){
            throw $e;
        }
    }

    /**
     * récupération de toutes les lignes d'une table
     * @param string $table nom de la table
     * @return lignes de la requete
     */
    public function selectAll($table){
        if($this->conn != null){
            switch ($table) {
                case "livre" :
                    return $this->selectAllLivres();
                case "dvd" :
                    return $this->selectAllDvd();
                case "revue" :
                    return $this->selectAllRevus();
                case "exemplaire" :
                    return $this->selectAllExemplairesRev();
                case "commandedocument" :
                    return $this->selectAllCommandesDocument();
                case "abonnementsecheance" :
                    return $this->selectAllAbonnementsEcheance();
                default:
                    // cas d'un select portant sur une table simple, avec tri sur le libellé
                    return $this->selectAllTableSimple($table);
            }			
        }else{
            return null;
        }
    }

    /**
     * récupération d'une ligne d'une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à récupérer
     * @return ligne de la requete correspondant à l'id
     */	
    public function selectOne($table, $id){
        if($this->conn != null){
            switch($table){
                case "exemplaire" :
                    return $this->selectAllExemplairesRev($id);
                case "dvd" :
                    return $this->selectAllDvd($id);
                case "revue" :
                    return $this->selectAllRevus($id);
                case "commandedocument" :
                    return $this->selectAllCommandesDocument($id);
                case "abonnement" :
                    return $this->selectAllAbonnementsRevues($id);
                case "exemplairesdocument":
                    return $this->selectAllExemplaires($id);
                case "user":
                    return $this->selectOneUser($id);
                default:
                    // cas d'un select portant sur une table simple			
                    $param = array(
                        "id" => $id
                    );
                    return $this->conn->query("select * from $table where id=:id;", $param);					
            }				
        }else{
                return null;
        }
    }
   
    /**
     * récupération de toutes les lignes de d'une table simple (sans jointure) avec tri sur le libellé
     * @param type $table
     * @return lignes de la requete
     */
    public function selectAllTableSimple($table){
        $req = "select * from $table order by libelle;";		
        return $this->conn->queryAll($req);		
    }

    /**
     * récupération de toutes les lignes de la table Livre et les tables associées
     * @return lignes de la requete
     */
    public function selectAllLivres(){
        $req = "select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from livre l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";		
        return $this->conn->queryAll($req);
    }	

    /**
     * récupération de toutes les lignes de la table DVD et les tables associées
     * @return lignes de la requete
     */
    public function selectAllDvd(){
        $req = "select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from dvd l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";	
        return $this->conn->queryAll($req);
    }	

    /**
     * récupération de toutes les lignes de la table Revue et les tables associées
     * @return lignes de la requete
     */
    public function selectAllRevus(){
        $req = "select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from revue l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->queryAll($req);
    }	

    /**
     * récupération de tous les exemplaires d'une revue
     * @param string $id id de la revue
     * @return lignes de la requete
     */
    public function selectAllExemplairesRev($id){
        $param = array(
                "id" => $id
        );
        $req = "select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $req .= "from exemplaire e join document d on e.id=d.id ";
        $req .= "where e.id =:id ";
        $req .= "order by e.dateAchat DESC";		
        return $this->conn->queryAll($req, $param);
    }
    
    /**
     * récupération de toutes les commandes d'un document
     * @param string $id id du document concerné
     * @return lignes de la requete
     */
    public function selectAllCommandesDocument($id) {
        $param = array (
            "id" => $id
                );
        $req = "select l.nbExemplaire, l.idLivreDvd, l.idSuivi, s.libelle, l.id, max(c.dateCommande) as dateCommande, sum(c.montant) as montant ";
        $req .= "from commandedocument l join suivi s on s.id=l.idSuivi ";
        $req .= "left join commande c on l.id=c.id ";
        $req .= "where l.idLivreDvd =:id ";
        $req .= "group by l.id ";
        $req .= "order by dateCommande DESC";
        return $this->conn->query($req, $param);
    }
    
     /**
     * récupération de tout les abonnements d'une revue
     * @param string $id id de l'abonnement de la revue concernée
     * @return lignes de la requete
     */
    public function selectAllAbonnementsRevues($id){
        $param = array(
                "id" => $id
        );
        $req = "select c.id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue  ";
        $req .= "from commande c join abonnement a ON c.id=a.id ";
        $req .= "where a.idRevue= :id  ";
        $req .= "order by c.dateCommande DESC  ";
        return $this->conn->queryAll($req, $param);
    }

    /**
     * récupération de tout les abonnements arrivant à échéance dans 30 jours
     * @return lignes de la requête
     */
    public function selectAllAbonnementsEcheance(){
        $req ="select a.dateFinAbonnement, a.idRevue, d.titre ";
        $req .="from abonnement a ";
        $req .="join revue r on a.idRevue = r.id ";
        $req .="join document d on r.id = d.id ";
        $req .="where datediff(current_date(), a.dateFinAbonnement) < 30 ";
        $req .="order by a.dateFinAbonnement ASC; ";
        return $this->conn->queryAll($req);
    }   
    
    /**
    * Récupération de tous les exemplaires d'un document
    * @param string $id id du document concerné
    * @return lignes de la requête
    */
    public function selectAllExemplaires($id){
    $param = array(
                "id" => $id
        );
        $req = "select ex.id, ex.numero, ex.dateAchat, ex.photo, ex.idEtat, et.libelle ";
        $req .= "from exemplaire ex JOIN etat et ON ex.idEtat = et.id ";
        $req .= "where ex.id = :id ";
        $req .= "order by ex.dateAchat DESC";       
        return $this->conn->query($req, $param);
    }
    
    /**
     * verifie si l'utilisateur est bien dans la base de donnee
     * @param string $login de l'utilisateur
     * @return reponse de la requete
     */
    public function selectOneUser($login)
{
        $param = array(
            "login" => $login
        );
        $req = "SELECT u.login, u.pwd, u.idService, s.libelle ";
        $req .= "FROM utilisateur u JOIN service s ON s.id = u.idService ";
        $req .= "WHERE u.login = :login";
    return $this->conn->queryAll($req, $param);
}   

    /**
     * suppresion d'une ou plusieurs lignes dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs
     * @return true si la suppression a fonctionné
     */	
    public function delete($table, $champs){
        if($this->conn != null){
            // construction de la requête
            $requete = "delete from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);   
            return $this->conn->execute($requete, $champs);		
        }else{
            return null;
        }
    }
    
    /**
     * ajout d'une ligne dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */	
    public function insertOne($table, $champs){
        if($this->conn != null && $champs != null){
            // construction de la requête
            $requete = "insert into $table (";
            foreach ($champs as $key => $value){
                $requete .= "$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);
            $requete .= ") values (";
            foreach ($champs as $key => $value){
                $requete .= ":$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);
            $requete .= ");";	
            return $this->conn->execute($requete, $champs);		
        }else{
            return null;
        }
    }

    /**
    * Modifie une ligne dans une table
    * prise en compte du numéro d'exemplaire spécifique
    * @param string $table nom de la table
    * @param string $id id de la ligne à modifier
    * @param array $champs nom et valeur de chaque champ de la ligne
    * @return true si la modification a fonctionné
    */ 
    public function updateOne($table, $id, $champs){
        if($this->conn != null && $champs != null){
            switch($table){
                case "exemplairesdocument":
                    $champsExemplaire = [
                        'id' => $champs['Id'],
                        'numero' => $champs['Numero'],
                        'dateAchat' => $champs['DateAchat'],
                        'photo' => $champs['Photo'],
                        'idEtat' => $champs['IdEtat']
                    ];
                    $requete = "UPDATE exemplaire SET ";
                    foreach ($champsExemplaire as $key => $value) {
                        $requete .= "$key=:$key,";
                    }
                    $requete = substr($requete, 0, strlen($requete)-1);
                    $requete .= " WHERE id=:id AND numero=:numero;";
                    $champsExemplaire['numero'] = $id;
                    $updateExemplaire = $this->conn->execute($requete, $champsExemplaire);   
                    if(!$updateExemplaire){
                        return null;
                    }
                default:
                    $champs['id'] = $id;
                    $requete = "UPDATE $table SET ";
                    foreach ($champs as $key => $value) {
                        $requete .= "$key=:$key,";
                    }
                    $requete = substr($requete, 0, strlen($requete)-1);
                    $requete .= " WHERE id=:id;";
                    return $this->conn->execute($requete, $champs);                 
            }
        }
        else
        {
            return null;
        }
    }

}