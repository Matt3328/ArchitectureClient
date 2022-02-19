<?php

function request($verb, $url, $content)
{
    // crée un point de communication (une socket) et retourne une instance de Socket Une connexion typique réseau est composée de deux sockets : une qui joue le rôle de client et l'autre celui du serveur
    $client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    //  Crée une connexion sur un socket
    if (socket_connect($client, "127.0.0.1", 5000) === false) {
        // Écrit une chaîne formatée dans un flux
        fprintf(STDERR, socket_strerror(socket_last_error($client)));
        return false;
    }
    socket_write($client, "$verb /" . urlencode($url) . " HTTP/1.1\r\n");
    socket_write($client, "Content-Type: text/plain\r\n");
    socket_write($client, "Content-Length: " . strlen($content) . "\r\n\r\n");
    //  Écrit dans un socket
    socket_write($client, "$content\r\n");
    $c = 0;
    for (;;) {
        //  Lit des données d'un socket
        $s = @socket_read($client, 4096, PHP_NORMAL_READ);
        // foreach inputs of the players
        if ($s === false) {
            break;
        }
        if ($s === '\r' || $s === '\n') {
            $c++; // compter les lignes vides
            /* if ($c === 2) {
               il faut récupérer les valeurs depuis le serveur avec leur clés d'ici
            }*/
        } else {
            // pas de valeurs enregistré depuis le serveur
            $c = 0;
        }
        echo $s;
        //var_dump($s);
    }
    socket_close($client);
    return true;
}

//$test = [];
// for (;;) {
//     $content = readline("Key/Text (Q to quit) :");
//     if ($content === false || in_array($content, ['Q', 'q'])) {
//         break;
//     }
//     $items = explode('/', $content);
//     if (count($items) > 1) {
//         request("POST", $items[0], $items[1]);
//         request("GET", "", "");
//     }
// }

//ici permet de récuperer les valeurs entrée de chaque joueur, il faut saisir le clé
request("GET", "", "");
$nom = readline("Votre nom :");
echo "Jeu commence \r\n";
$jeu = new Jeu();
// q pour quitter
$jeuContinue = 1;
while ($jeuContinue) {
    // dans le terminal :
    $input = readline("Faites votre choix: 1 pour Pierre , 2 pour Ciseau , 3 pour Papier , q pour quitte le jeu \r\n");
    // ici permet d'enregistrer le nom + l'option de joueur
    request("POST", $nom, $input);

    switch ($input) {
        case 1:  // Pierre
        case 2:  // Ciseau
        case 3:  // Papier
            $choiceJ1 = 1;
            $choiceJ2 = 1;

            $resultat = $jeu->batte($choiceJ1, $choiceJ2);
            echo "Votre choix est :" . $jeu->choix[$choiceJ1] . "\r\n";
            echo "Le choix d'autre joueur est :" . $jeu->choix[$choiceJ2] . "\r\n";
            echo "Et vous avez" . $jeu->resultats[$resultat];
            break;

        case "q":
            break;
    }
}

class Logique
{
    public $previous = null;
    public $next = null;
    public $data = null;
}
class Jeu
{
    // shifumi
    public $choix = [1 => 'Pierre', 2 => 'Ciseau', 3 => 'Papier'];
    public $resultats = ['perdu', 'gagné', 'l\'égalité'];
    public $logiqueList = [];
    public $nmbTour = 0;
    public $joueur1NmbWin = 0;
    public $joueur2NmbWin = 0;
    public $joueur1NmbLose = 0;
    public $joueur2NmbLose = 0;
    public $joueur1NmbTie = 0;
    public $joueur2NmbTie = 0;
    public function __construct()
    {
        for ($i = 1; $i < 4; $i++) {
            $logique = new Logique();
            $logique->data = $i;
            if ($i == 1) {
                // cas Pierre 1: 
                // previous(Perdu) : 3 ->papier 
                $logique->previous = 3;
                // next(Gagné) : 2->ciseau
                $logique->next = $i + 1;
            } elseif ($i == 3) {
                // cas Papier 3: 
                // previous(Perdu) : 2->ciseau
                $logique->previous = $i - 1;
                // next(Gagné) : 1->pierre
                $logique->next = 1;
            } else {
                // cas Ciseau 2: 
                // previous(Perdu) : 1 -> pierre
                $logique->previous = $i - 1;
                // next(Gagné) : 3-> papier
                $logique->next = $i + 1;
            }
            $this->logiqueList[$i] = $logique;
        }
    }
    public function batte($joueur1, $joueur2)
    {
        $this->nmbTour += 1;
        //cas égalité
        if ($joueur1 == $joueur2) {
            $this->joueur1NmbTie += 1;
            $this->joueur2NmbTie += 1;
            return 2;
        } else {
            // joueur1 gagne && joueur2 perd
            if (($this->logiqueList[$joueur1])->next == $joueur2) {
                $this->joueur1NmbWin += 1;
                $this->joueur2NmbLose += 1;
                return 1;
                // sinon :
            } else {
                $this->joueur1NmbLose += 1;
                $this->joueur2NmbWin += 1;
                return 0;
            }
        }
    }
}
