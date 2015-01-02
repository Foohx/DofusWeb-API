# DofusWeb API

DofusWeb API est une petite librairie HTTP non officiel vous permettant d'intéragir avec les services web de la société Ankama.

* Connexion/Déconnexion sur [dofus.com](http://dofus.com/fr)
* Récupération du Pseudo
* Récupération de l'abonnement et de la date d'expiration
* Récupération des Ogrines
* Récupération des Kroz
* Récupération nombre d'Ankabox
* Listing des personnages (Nom, Race, Niveau et Serveur)
* Connexion/Déconnexion sur [account.ankama.com](https://account.ankama.com) (Gestion de compte)
* Indication SHIELD activé ou non
* Indication compte certifié ou non
* Obtention de l'identifiant
* Obtention de l'identité (Nom, Prénom ainsi que Date de naissance)
* Obtention de l'email
* Obtention des numéros de téléphone
* Obtention du mot de passe (caché)
* Obtention de l'adresse postal principale
* Conservation de la connexion (refresh possible)
* Sauvegarde des cookies (fichier)

---

**Important**: Merci de respecter vos utilisateurs et de ne pas récupérer d'informations sans leur accord préalable !!! 

---

# Installation
DofusWeb API requiert au minimum PHP `v5.4+` ainsi que l'extension `CURL`.

### Github

Récupérer les sources de la librairie :

```bash
$ git clone https://github.com/Foohx/DofusWeb-API.git
```

Puis l'inclure dans vos scripts :

```bash
require_once '/path/to/lib/dofuswebapi.php';
```

# Utilisation

### Initialiser la class

Pour s'initialiser DofusWeb API à besoin de deux chaines de caractère. La première étant le nom de compte et la seconde étant le mot de passe.

```php
$hDofus = new DofusWeb_API('username', 'password');
```

Il faut ensuite définir un fichier qui va permettre de conserver la connexion au site :

```php
$hDofus->setCookie('/path/to/file/for/cookie.txt')
```

Voilà ! Vous êtes maintenant prêt pour partir à la chasse aux informations. 

### Collecter des informations sur Dofus.com

Connexion au compte avec les identifiants précédents :

```php
$hDofus->reqDofusLogin();
```

Récupération des informations du compte :

```php
$hDofus->collectDofusData();
```

Vous pouvez accéder aux informations collecté via l'attribut `$dataDofus`. Cet attribut est un tableau multi-dimensionnel ayant pour structure :

```php
array(
	'account' => array(
		'nickname' 			=> 'Pseudo-Forum',
		'subscription' 		=> true/false,
		'subs_expiration' 	=> 'JJ/MM/AAAA',
		'ogrines' 			=> 1337,
		'kroz' 				=> 137,
		'ankabox' 			=> 0,
	),
	'characters' => array(
		array(
			'class'			=> 'Zobal',
			'level'			=> 10,
			'name' 			=> 'NomPerso 1',
			'server'		=> 'NomDuServeur'
		),
		array(
			'class'			=> 'Cra',
			'level'			=> 94,
			'name' 			=> 'NomPerso 2',
			'server'		=> 'NomDuServeur'
		),
		array(
			'class'			=> 'NomRace',
			'level'			=> 200,
			'name' 			=> 'NomPerso 3',
			'server'		=> 'NomDuServeur'
		)
		// etc..
	)
);
```

Pour les néophytes le tableau s'utilise ainsi :

```php
echo "Ogrines : " . $hDofus->dataDofus['account']['ogrines'] . "<br />";
echo "Kroz : " . $hDofus->dataDofus['account']['kroz'] . "<br />";
// ...
echo "Perso 1 : " . $hDofus->dataDofus['characters'][0]['name'] . "<br />";
echo "Niveau : " . $hDofus->dataDofus['characters'][0]['level'] . "<br />";
echo "Perso 2 : " . $hDofus->dataDofus['characters'][1]['name'] . "<br />";
echo "Niveau : " . $hDofus->dataDofus['characters'][1]['level'] . "<br />";
// etc...
```

Si vous voulez lister tous les personnages ou compter combien le compte en possède :

```php
$nombrePersonnages = count($hDofus->dataDofus['characters']);
for ($i=0; $i < $nombrePersonnages; $i++)
{
	echo "Race : " . $hDofus->dataDofus['characters'][$i]['class'] . "<br />";
	echo "Nom : " . $hDofus->dataDofus['characters'][$i]['name'] . "<br />";
	echo "Niveau : " . $hDofus->dataDofus['characters'][$i]['level'] . "<br />";
	echo "Serveur : " . $hDofus->dataDofus['characters'][$i]['server'] . "<br /><br />";
}
```

Une fois que vous en avez terminé avec les requêtes vous pouvez clore votre session en vous déconnectant :

```php
$hDofus->reqAnkamaLogout();
```

### Collecter des informations sur Ankama.com

```php
$hDofus->reqAnkamaLogin();
$hDofus->reqAnkamaHome();
$hDofus->collectAnkamaData();
$hDofus->reqAnkamaLogout();
```

Structure de l'attribut `$dataAccount` :

```php
array(
	'security' 		=> 0,
  	'account' => array(
     	'nickname' 	=> 'Pseudo-Forum',
     	'firstname' => 'Prénom',
     	'lastname' 	=> 'Nom',
     	'birth' 	=> 'JJ Mois AAAA',
     	'email' 	=> 'email@exemple.com',
     	'portable' 	=> '06******00',
     	'fixe' 		=> '05******00',
     	'password' 	=> '**********',
     	'address' 	=> 'ADRESSE | POSTALE | CP | VILLE',
     	'certified' => true / false
    )
);
```

Exemple :

```php
echo "Bonjour Mr. " . $hDofus->dataAccount['account']['lastname'] . "<br />";
echo "Votre email de contact : " . $hDofus->dataAccount['account']['email'] . "<br />";

if ($hDofus->dataAccount['security'] == 3)
	echo "Votre compte est protégé par le SHIELD !";
```

# Class

### Attributs

* `body` - Contient le code source de la dernière requête effectué
* `code` - Code HTTP de la dernière requête
* `dataAccount` - Informations extraites du site account.ankama.com
* `dataDofus` -	Informations extraite du site dofus.com
* `errors` - Contient une liste d'erreurs (array)

### Fonctions

Toutes ces fonctions retournent `true` en cas de succès ou `false` en cas d'échec. Les fonctions débutant par `req` effectuent des requêtes HTTP et retourne des informations dans `body` et `code`. En cas de problèmes / d'erreurs des détails sont disponible dans l'attribut `errors`

* `askIsConnected($reload=false)` - Vérifie que l'utilisateur est connecté
* `collectAnkamaData()` - Récupère des informations et le stock dans `dataAccount`
* `collectDofusData()` - Récupère des informations et le stock dans `dataDofus`
* `setCookie($path_to_file)` - Indique dans quel fichier stocker les cookies
* `setLogin($username, $password)` - Permet de changer les identifiants de connexion
* `getCookie()` - Récupère le nom du fichier de cookie courant
* `getLogin()` - Récupère les identifiants de connexion de la class
* `reqAnkamaHome()` - Execute une requête `GET` sur la page d'accueil d'Ankama
* `reqAnkamaLogin()` - Execute une requête `POST` afin de s'identifier sur Ankama
* `reqAnkamaLogout()` - Execute une requête `GET` afin de se déconnecter d'Ankama
* `reqDofusHome()` - Execute une requête `GET` sur la page d'accueil de Dofus
* `reqDofusLogin()` - Execute une requête `POST` afin de s'identifier sur Dofus
* `reqDofusLogout()` - Execute une requête `GET` afin de se déconnecter de Dofus
