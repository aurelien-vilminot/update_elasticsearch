<?php
/**
 *
 * @file   model.php
 *
 * @author Aurélien VILMINOT
 *
 * @date   30/06/2020
 *
 * @brief  Classe Model
 *
 **/

require 'database.php';

/**
 * Classe Model
 * Récupère les données des candidats dans la base de données RecrutonsEnsemble
 */
class Model extends Database
{
    /**
     * Renvoie les identifiants de tous les candidats ayant mis leur CV sur RecrutonsEnsemble
     * @return array
     */
    public function getIdCandidate()
    {
        $null = 'NULL';
        $params = array('filename' => $null);
        $sql = 'SELECT id 
                FROM candidate 
                WHERE filename != :filename';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, tous les type de contrats qu'il souhaite
     * @param $id
     * @return array
     */
    public function getContractsType($id)
    {
        $params = array('id' => $id);
        $sql = 'SELECT CT.name 
                FROM candidate_contract_type CC, contract_type CT
                WHERE CC.candidate_id = :id 
                AND CT.id = CC.contracttype_id';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, tous ses domaines de compétences
     * @param $id
     * @return array
     */
    public function getDomains($id)
    {
        $params = array('id' => $id);
        $sql = 'SELECT D.name 
                FROM candidate_domain CD, domain D
                WHERE CD.candidate_id = :id 
                AND D.id = CD.domain_id';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, toutes ses compétences
     * @param $id
     * @return array
     */
    public function getSkills($id)
    {
        $params = array('id' => $id);
        $sql = 'SELECT skill.name
                FROM candidate_skill CS, skill
                WHERE CS.candidate_id = :id
                AND skill.id = CS.skill_id';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, tous les univers dans lesquels il peut être amener à travailler
     * @param $id
     * @return array
     */
    public function getUniverses($id)
    {
        $params = array('id' => $id);
        $sql = 'SELECT universe.name 
                FROM candidate_universe CU, universe
                WHERE CU.candidate_id = :id 
                AND universe.id = CU.universe_id';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, ses nom, prénom, date de naissance et lieu de vie (code postal)
     * @param $id
     * @return array
     */
    public function getNamesBirthPlace($id)
    {
        $params = array('id' => $id);
        $sql = 'SELECT U.lastname, U.firstname, P.birth_date, P.city, P.postalCode 
                FROM person P, user U
                WHERE P.id = :id 
                AND P.user_id = U.id';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, son CV au format texte
     * @param $id
     * @return mixed
     */
    public function getCV($id)
    {
        $params = array('id' => $id);
        $sql = 'SELECT content
                FROM Cv
                WHERE Cv.candidate_id = :id';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll()[0][0];
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, son score au test de logique
     * @param $id
     * @return array
     */
    public function getScoreLogicTest($id)
    {
        $params = array('id' => $id, 'type' => 'logic');
        $sql = 'SELECT nb_valid
                FROM test_answer
                WHERE test_answer.candidate_id = :id
                AND version = 2
                AND t_type = :type';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, son score au test de raisonnement
     * @param $id
     * @return array
     */
    public function getScoreReasoningTest($id)
    {
        $params = array('id' => $id, 'type' => 'reasoning');
        $sql = 'SELECT nb_valid
                FROM test_answer
                WHERE test_answer.candidate_id = :id
                AND version = 2
                AND t_type = :type';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, tous les tags qui lui ont été attribués
     * @param $id
     * @return array
     */
    public function getTag($id)
    {
        $params = array('id' => $id);
        $sql = 'SELECT tag.name 
                FROM candidate_tag CA, tag
                WHERE CA.candidate_id = :id 
                AND tag.id = CA.tag_id';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, son salaire
     * @param $id
     * @return array
     */
    public function getSalary($id)
    {
        $params = array('id' => $id);
        $sql = 'SELECT salary
                FROM candidate
                WHERE id = :id';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, son avancé dans le processus de candidature
     * @param $id
     * @return array
     */
    public function getProcess($id)
    {
        $params = array('id' => $id);
        $sql = 'SELECT JAProcess.value
                FROM job_application JA, job_application_job_application_process JAJob, job_application_process JAProcess
                WHERE JA.candidate_id = :id
                AND JA.id = JAJob.job_application_id
                AND JAJob.job_application_process_id = JAProcess.id';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Renvoie, à partir de l'identifiant $id d'un candidat, ses zones géographiques de recherches
     * @param $id
     * @return array
     */
    public function getArea($id)
    {
        $params = array('id' => $id);
        $sql = 'SELECT area_id
                FROM candidate_area
                WHERE candidate_id = :id';
        $req = $this->executeRequete($sql, $params);
        return $req->fetchAll();
    }


    /**
     * Renvoie, à partir d'un code postal $postal_code_param, les coordonnées GPS correspondants. Renvoie -1 en cas d'erreur
     * @param $postal_code_param
     * @return array|int
     */
    public function getGPSLocation($postal_code_param)
    {
        // Lien de l'API
        $url_API = 'https://public.opendatasoft.com/api/records/1.0/search/?dataset=correspondance-code-insee-code-postal&q=';

        if ($postal_code_param != 0) {
            $data = $this->getLocationByCode($url_API, $postal_code_param);
            $hits = $data['nhits'];

            // Vérifie que l'API a retourné au moins un résultat. Renvoie -1 sinon
            if ($hits != 0) {
                return $this->extractInfoJSONAPI($hits, $data, $postal_code_param);
            } else {
                return -1;
            }
        }
        return -1;
    }

    /**
     * Fait un appel à l'API $url_API en y ajoutant le code postal $postal_code_param. Retourne le résultat
     * @param $url_API
     * @param $postal_code_param
     * @return mixed
     */
    private function getLocationByCode($url_API, $postal_code_param)
    {
        $data_json = file_get_contents($url_API . $postal_code_param);
        return json_decode($data_json, true);
    }

    /**
     * Renvoie, à partir du résulat $data de l'API, les coordonées GPS, le nom et le code postal de la ville
     * @param $hits
     * @param $data
     * @param $postal_code_param
     * @return array|int
     */
    private function extractInfoJSONAPI($hits, $data, $postal_code_param)
    {
        $i = 0;

        // Boucle tant qu'il y a des résultats
        while ($i < $hits) {
            $postal_code = $data['records'][$i]['fields']['postal_code'];
            $name = $data['records'][$i]['fields']['nom_comm'];

            // Vérifie que les codes postaux correspondent bien
            if (strpos($postal_code, (string)$postal_code_param) !== false) {
                $lat = $data['records'][$i]['fields']['geo_point_2d'][0];
                $long = $data['records'][$i]['fields']['geo_point_2d'][1];
                return array($lat, $long, $name, $postal_code);
            }
            ++$i;
        }
        return -1;
    }
}