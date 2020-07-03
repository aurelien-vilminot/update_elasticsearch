<?php
/**
 *
 * @file   database.php
 *
 * @author Aurélien VILMINOT
 *
 * @date   30/06/2020
 *
 * @brief  Classe Database
 *
 **/

abstract class Database {

    /**
     * Contient l'instance PDO
     */
    private $bdd;

    /**
     * Exécute une requête SQL $sql avec, en option, le(s) paramètre(s) $params
     * @param $sql
     * @param null $params
     * @return bool|false|PDOStatement
     */
    public function executeRequete($sql, $params = null) {
        // Si pas de paramètres, exécution immédiate
        if ($params == null) {
            $resultat = $this->getBdd()->query($sql);
        } else {
            try {
                $this->getBdd()->beginTransaction();            // Début de transaction
                $resultat = $this->getBdd()->prepare($sql);     // Requête préparée
                foreach ($params as $key => $value) {           // Lie toutes les valeurs de la requête
                    $myKey = ':' . $key;
                    if (is_int($value))
                        $resultat->bindValue($myKey, intval($value), PDO::PARAM_INT);
                    elseif (is_bool($value))
                        $resultat->bindValue($myKey, boolval($value), PDO::PARAM_BOOL);
                    elseif (is_string($value))
                        $resultat->bindValue($myKey, strval($value), PDO::PARAM_STR);
                }
                $resultat->execute();
                $this->getBdd()->commit();
            }
            catch(Exception $e) {
                $this->getBdd()->rollBack();                    // Annulation et remise à l’état initial en cas d’erreur
            }
        }
        return $resultat;
    }

    /**
     * Renvoie l'instance PDO de la base de données si elle n'est pas déjà créée
     * @return PDO
     */
    private function getBdd() {
        if ($this->bdd == null) {
            $dsn = 'mysql:host=vilminot.fr; dbname=viau8608_re';
            $database = new PDO($dsn, 'viau8608_aurelien_re', '5YxZKb2Tsw3eKJAr');
            $database->exec('SET CHARACTER SET utf8');
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->bdd = $database;
        }
        return $this->bdd;
    }
}