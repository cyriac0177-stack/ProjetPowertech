<?php
/**
 * Détection automatique de la structure de la base de données
 */

function getUserIdColumn() {
    static $column = null;
    
    if ($column === null) {
        global $bdd;
        try {
            // Tester avec user_id d'abord
            $stmt = $bdd->query("SHOW COLUMNS FROM users LIKE 'user_id'");
            if ($stmt->fetch()) {
                $column = 'user_id';
                return $column;
            }
            
            // Tester avec id
            $stmt = $bdd->query("SHOW COLUMNS FROM users LIKE 'id'");
            if ($stmt->fetch()) {
                $column = 'id';
                return $column;
            }
            
            // Par défaut
            $column = 'id';
        } catch (Exception $e) {
            $column = 'id';
        }
    }
    
    return $column;
}


