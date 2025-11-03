<?php
// Démarrer la session seulement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function dd($valeur){
    echo '<pre>';
    var_dump($valeur);
    echo '</pre>';
    die();
}

function dump($valeur){
    echo '<pre>';
    var_dump($valeur);
    echo '</pre>';
   
}

try
{
    //Connect to database.
    
    // Essayer d'abord avec quick-shopping, puis quick_shopping si erreur
    try {
        $bdd = new PDO('mysql:host=localhost;dbname=quick-shopping','root','');
    } catch (PDOException $e) {
        // Si ça ne marche pas, essayer sans nom de base pour créer la base
        $bdd = new PDO('mysql:host=localhost','root','');
        $bdd->exec("CREATE DATABASE IF NOT EXISTS `quick-shopping`");
        $bdd->exec("USE `quick-shopping`");
    }
    $bdd->setAttribute(\PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


}
catch(PDOException $e)
{
    die("Error: ".$e->getMessage());
}


?>