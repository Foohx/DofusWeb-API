# DofusWeb API

DofusWeb API est une petite librairie non officiel vous permettant d'intéragir avec les services web de la société Ankama.

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

### Installation depuis GitHub
DofusWeb API requiert au minimum PHP `v5.4+`.

Récupérer les sources de la librairie :

```bash
$ git clone http:// 
```

Puis l'inclure dans vos scripts :

```bash
require_once '/path/to/lib/dofuswebapi.class.php';
```

### Initialiser de la class

Pour s'initialiser DofusWeb API à besoin de deux chaines de caractère. La première étant le nom de compte et la seconde étant le mot de passe.

```php
$hDofus = new DofusWeb_API('username', 'password');
```

Il faut ensuite définir un fichier qui va permettre de conserver la connexion au site :

```php
$hDofus->setCookie('./temp.txt')
```

Voilà ! Vous êtes maintenant prêt pour partir à la chasse aux informations. 

### Collecte sur Dofus.com

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
		'subscription' 		=> 'Abonné', // Ou 'Non Abonné'
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
echo "Ogrines : " . $dataDofus['account']['ogrines'] . "<br />";
echo "Kroz : " . $dataDofus['account']['kroz'] . "<br />";
// ...
echo "Perso 1 : " . $dataDofus['characters'][0]['name'] . "<br />";
echo "Niveau : " . $dataDofus['characters'][0]['level'] . "<br />";
echo "Perso 2 : " . $dataDofus['characters'][1]['name'] . "<br />";
echo "Niveau : " . $dataDofus['characters'][1]['level'] . "<br />";
// etc...
```

Une fois que vous en avez terminé avec les requêtes vous pouvez clore votre session en vous déconnectant :

```php
$hDofus->reqAnkamaLogout();
```

### Collecte sur Ankama.com

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

Petit exemple :

```php
echo "Bonjour Mr. " . $hDofus->dataAccount['account']['lastname'] . "<br />";
echo "Votre email de contact : " . $hDofus->dataAccount['account']['email'] . "<br />";
if ($hDofus->dataAccount['security'] == 3)
	echo "Votre compte est protégé par le SHIELD !";
```
