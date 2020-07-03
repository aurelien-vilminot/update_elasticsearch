<?php
/**
 *
 * @file   index.php
 *
 * @author Aurélien VILMINOT
 *
 * @date   30/06/2020
 *
 * @brief  Gestion du transfert des données entre la base de données et Elasticsearch
 *
 **/

require_once 'model.php';

/* MAIN */
transferData();
/* END MAIN */

/**
 * Ajoute ou met à jour les candidats dans Elasticsearch
 */
function transferData() {
    $model = new Model();
    $candidates = $model->getIdCandidate();

    // Traite chaque candidat
    foreach ($candidates as $candidate) {
        $id = $candidate[0];
        $idElastic = getID($id);
        $tabContract = $model->getContractsType($id);
        $tabDomain = $model->getDomains($id);
        $tabSkill = $model->getSkills($id);
        $tabUniverse = $model->getUniverses($id);
        $tabInfos = $model->getNamesBirthPlace($id);
        $tabTag = $model->getTag($id);
        $cv = $model->getCV($id);
        $scoreLogicTest = $model->getScoreLogicTest($id);
        $scoreReasoningTest = $model->getScoreReasoningTest($id);
        $salary = $model->getSalary($id);
        $tabProcess = $model->getProcess($id);
        $tabArea = $model->getArea($id);

        if (!empty($salary[0][0]) && strlen($salary[0][0]) < 5) {
            $salary[0][0] *= 1000;
        }

        if (!empty($tabProcess[0][0]) != 0) {
            $max = $tabProcess[0][0];
            for ($i = 0 ; $i < count($tabProcess[0]) ; ++$i) {
                if ($i != 0 && $max < $tabProcess[0][$i]) {
                    $max = $tabProcess[0][$i];
                }
            }
        }

        if ($tabInfos[0][4] != (string)0 && strlen($tabInfos[0][4]) == 5) {
            $location = locationTreatment($tabInfos[0][4]);
            if ($location == -1) {
                $location = array();
                $location[0] = 0.0;
                $location[1] = 0.0;
                $location[2] = 'null';
                $location[3] = 'null';
            }
        } else {
            $location = array();
            $location[0] = 0.0;
            $location[1] = 0.0;
            $location[2] = 'null';
            $location[3] = 'null';
        }

        $tabContractJSON = array();
        $tabDomainJSON = array();
        $tabSkillJSON = array();
        $tabUniverseJSON = array();
        $tabTagJSON = array();
        $tabAreaJSON = array();

        foreach ($tabContract as $contract) {
            array_push($tabContractJSON, $contract[0]);
        }

        foreach ($tabDomain as $domain) {
            array_push($tabDomainJSON, $domain[0]);
        }

        foreach ($tabSkill as $skillID) {
            array_push($tabSkillJSON, $skillID[0]);
        }

        foreach ($tabUniverse as $universe) {
            array_push($tabUniverseJSON, $universe[0]);
        }

        foreach ($tabTag as $tag) {
            array_push($tabTagJSON, $tag[0]);
        }

        foreach ($tabArea as $area) {
            array_push($tabAreaJSON, $area[0]);
        }

        echo $idElastic . '<br>';

        if (strlen($idElastic) == 0) {
            $params = [
                'candidate_id' => $id,
                'lastname' => strlen($tabInfos[0][0]) != 0 ? $tabInfos[0][0] : 'null',
                'firstname' => strlen($tabInfos[0][1]) != 0 ? $tabInfos[0][1] : 'null',
                'code_city' => $location[3],
                'place' => $location[2],
                'location' => [
                    'lat' => $location[0],
                    'lon' => $location[1]
                ],
                'birth' => strlen($tabInfos[0][2]) != 0 ? $tabInfos[0][2] : '1000-01-01',
                'cv' => $cv,
                'domain' => count($tabDomainJSON) != 0 ? $tabDomainJSON : 'null',
                'skills' => count($tabSkillJSON) != 0 ? $tabSkillJSON : 'null',
                'universe' => count($tabUniverseJSON) != 0 ? $tabUniverseJSON : 'null',
                'tag' => count($tabTagJSON) != 0 ? $tabTagJSON : 'null',
                'contract' => count($tabContractJSON) != 0 ? $tabContractJSON : 'null',
                'score_test' => [
                    'logic' => count($scoreLogicTest) != 0 ? (int)$scoreLogicTest[0][0] : 0,
                    'reasoning' => count($scoreReasoningTest) != 0 ? (int)$scoreReasoningTest[0][0] : 0,
                    'total' => count($scoreLogicTest) != 0 && count($scoreReasoningTest) != 0 ? $scoreLogicTest[0][0] + $scoreReasoningTest[0][0] : 0
                ],
                'salary'=> count($salary) != 0 ? $salary[0][0] : 'null' ,
                'state_process' => isset($max) ? $max : 'null',
                'area' => count($tabAreaJSON) != 0 ? $tabAreaJSON : 'null',
            ];
            echo 'Candidat : ' . $id. '<br>';
            echo 'Place : ' . $location[0] . ' ; ' . $location[1] . '<br>';
            postData($params);
            echo '<br><br>';
        } else {
            $data = [
                'doc' => [
                    'candidate_id' => $id,
                    'lastname' => strlen($tabInfos[0][0]) != 0 ? $tabInfos[0][0] : 'null',
                    'firstname' => strlen($tabInfos[0][1]) != 0 ? $tabInfos[0][1] : 'null',
                    'code_city' => $location[3],
                    'place' => $location[2],
                    'location' => [
                        'lat' => $location[0],
                        'lon' => $location[1]
                    ],
                    'birth' => strlen($tabInfos[0][2]) != 0 ? $tabInfos[0][2] : '1000-01-01',
                    'cv' => $cv,
                    'domain' => count($tabDomainJSON) != 0 ? $tabDomainJSON : 'null',
                    'skills' => count($tabSkillJSON) != 0 ? $tabSkillJSON : 'null',
                    'universe' => count($tabUniverseJSON) != 0 ? $tabUniverseJSON : 'null',
                    'tag' => count($tabTagJSON) != 0 ? $tabTagJSON : 'null',
                    'contract' => count($tabContractJSON) != 0 ? $tabContractJSON : 'null',
                    'score_test' => [
                        'logic' => count($scoreLogicTest) != 0 ? (int)$scoreLogicTest[0][0] : 0,
                        'reasoning' => count($scoreReasoningTest) != 0 ? (int)$scoreReasoningTest[0][0] : 0,
                        'total' => count($scoreLogicTest) != 0 && count($scoreReasoningTest) != 0 ? $scoreLogicTest[0][0] + $scoreReasoningTest[0][0] : 0
                    ],
                    'salary'=> count($salary) != 0 ? $salary[0][0] : 'null' ,
                    'state_process' => isset($max) ? $max : 'null',
                    'area' => count($tabAreaJSON) != 0 ? $tabAreaJSON : 'null',                ]
            ];
            echo 'Candidat : ' . $id . ' ';
            updateData($idElastic, $data);
            echo '<br>';
        }
        // Destruction des variables afin d'éviter que l'interpréteur PHP ne les réutilisent telles quelles à la prochaine boucle sur le candidat suivant
        unset($salary, $tabProcess, $id, $max, $data, $i, $location, $idElastic);
    }
}

/**
 * Encode au format JSON le tableau $data passé en paramètre puis envoie ces données sur Elasticsearch avec la librairie cURL
 * @param array $data
 */
function postData(array $data) {
    $payload = json_encode($data);

    // Prépare une nouvelle ressource cURL POST
    $ch = curl_init('localhost:9200/re2/_doc/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    // Configure le HTTP Header pour la requête POST
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload))
    );

    // Envoie la requête POST et affiche le résultat
    var_dump(curl_exec($ch));

    // Ferme la session cURL
    curl_close($ch);
}

/**
 * Encode au format JSON le tableau $data passé en paramètre puis met à jour sur Elasticsearch l'enregistrement identifié par le paramètre $id
 * @param $id
 * @param array $data
 */
function updateData($id, array $data) {
    $payload = json_encode($data);

    // Prépare une nouvelle ressource cURL POST pour l'update
    $ch = curl_init('localhost:9200/re2/_update/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    // Configure le HTTP Header pour la requête POST
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload))
    );

    // Envoie la requête POST et affiche le résultat
    var_dump(curl_exec($ch));

    // Ferme la session cURL
    curl_close($ch);
}

/**
 * Renvoie, à partir de l'identifiant $candidate_id d'un candidat, l'identifiant ce ce même candidat dans Elasticsearch
 * @param $candidate_id
 * @return string
 */
function getID($candidate_id): string {
    // Tableau permettant à Elasticsearch de trouver le candidat possédant l'identifiant $candidate_id
    $data = [
        'query' => [
            'match_phrase' => [
                'candidate_id' => $candidate_id
            ]
        ]
    ];

    // Préparation de la requête POST pour la recherche
    $payload = json_encode($data);

    $ch = curl_init('localhost:9200/re2/_search/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload))
    );

    // Le résultat de la recherche est récupéré
    $result = curl_exec($ch);

    curl_close($ch);

    // Extraction de l'identifiant Elasticsearch
    $pos_id = strpos($result, '_id') + 6;
    $lenght = strpos($result, '"', $pos_id) - $pos_id;
    return substr($result, $pos_id, $lenght);
}

/**
 * Renvoie, à partir d'un code postal $postal_code, les coordonnées GPS de la ville. Renvoie -1 en cas d'erreur
 * @param int $postal_code
 * @return array|int
 */
function locationTreatment(int $postal_code) {
    // Vérifie que le code postal est conforme
    if (!is_int($postal_code) || strlen((string)$postal_code) != 5) {
        return -1;
    }

    $model = new Model();
    return $model->getGPSLocation($postal_code);
}