<?php

namespace App\DataFixtures;

use App\Entity\Challenge;
use App\Entity\Classe;
use App\Entity\Donjon;
use App\Entity\Picture;
use App\Entity\Player;
use App\Entity\User;
use App\Repository\PictureRepository;
use DirectoryIterator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;
use PhpParser\Node\Expr\List_;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */

    private Generator $faker;

    /**
     * Classe Hasheant le password
     * @var UserPasswordHasherInterface
     */
    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {

        $this->faker = Factory::create("fr_FR");
        $this->classArray = ["cra","ecaflip","eliotrope","eniripsa","enutrof","feca","huppermage","iop","osamodas","ouginak","pandawa","roublard","sacrieur","sadida","sram","steamer","xelor","zobal"];
        $this->challenges = [
            "ABNEGATION",
            "ANACHORETE",
            "ARAKNOPHILE",
            "BARBARE",
            "BLITZKRIEG",
            "CASINO ROYAL",
            "CHACUN SON MONSTRE",
            "CIRCULEZ !",
            "COLLANT",
            "CONTAMINATION",
            "CRUEL",
            "DEUX POUR LE PRIX D'UN",
            "DUEL",
            "ÉCONOME",
            "ELEMENTAIRE",
            "FOCUS",
            "FOSSOYEUR",
            "HARDI",
            "INCURABLE",
            "INTOUCHABLE",
            "JARDINIER",
            "LE TEMPS QUI COURT",
            "MAINS PROPRES",
            "MYSTIQUE",
            "NOMADE",
            "ORDONNE",
            "PARTAGE",
            "PERDU DE VUE",
            "PETULANT",
            "PUSILLANIME",
            "STATUE",
            "SURVIVANT",
            "VERSATILE",
            "ZOMBIE"
        ];
        $this->description = [
            "Les joueurs ne doivent jamais recevoir de soin pendant leurs tours de jeu. Le vol de vie des sorts et des armes ne font pas échouer ce challenge.",
            "Aucun personnage ne doit terminer son tour sur une cellule adjacente d'un allié. À noter que le challenge n'échoue pas lorsqu'un personnage est porté à la fin d'un tour.",
            "Olige les joueurs à utiliser le sort Invocation d'Arakne à chaque fois qu'il est disponible.",
            "Vous devez infliger des dommages avec une arme au moins une fois par tour.",
            "Après avoir frappé un ennemi, votre équipe doit l'éliminer avant le début de son tour. Prudence avec l'utilisation des poisons et des glyphes dont les dégâts sont pris en compte juste avant le début du tour de l'ennemi.",
            "Disponible à la seule condition de posséder au moins un disciple d'Ecaflip dans votre équipe. Casino Royal oblige ce(s) dernier(s) à toujours utiliser le sort Roulette à chaque fois qu'il est disponible.",
            "chacun de vos alliés doit éliminer un monstre mais lorsque l'un de vos alliés en frappe un, aucun autre personnage ne doit attaquer le monstre frappé.",
            "Vous ne devez jamais retirer le moindre PM (point de mouvement) de tout le combat. Prenez garde au sort Odorat qui retire inévitablement des PM faisant donc échouer le challenge.",
            "Vous devez toujours terminer vos tours sur une cellule adjacente d'un de vos alliés. Les invocations sont prises en compte comme des alliés, mais ne sont pas soumises à la règle de ce challenge.",
            "Contamination oblige votre équipe à éliminer chaque allié qui subira des dégâts, sous un laps de temps de 5 tours. Ce challenge réussira pareillement si personne n'est touché pendant le combat. Attention tout de même aux sorts Mot Stimulant, Roue de la Fortune, Contrecoup, Esprit Felin, Transfert de Vie, Mot de Sacrifice et Mot Drainant dont les dégâts sont pris en compte.",
            "Les monstres doivent être tués de façon croissante (du plus petit au plus grand) en fonction de leurs niveaux. Si deux ou plusieurs monstres ont le même niveau, l'ordre n'a pas d'importance. Les invocations ennemies ne sont pas prises en compte.",
            "À chaque fois que vous tuez un adversaire, vous devez systématiquement en éliminer un autre dans le même tour.",
            "Lorsqu'un personnage attaque un ennemi, aucun autre allié ne doit le frapper pendant tout le long du combat.",
            "Vous ne pouvez utiliser qu'une seule fois la même action (corps à corps et sort) dans tout le combat. À noter que lors d'un échec critique, vous pouvez relancer le sort.",
            "Les joueurs doivent toujours utiliser le même élément d'attaque pendant tout le combat. Les dégâts infligés par Mot Stimulant, Mutilation, Roue de la Fortune, Contrecoup, Esprit Felin, Transfert de Vie, Mot de Sacrifice et Mot Drainant ne sont pas pris en compte.",
            "Lorsque vous touchez un ennemi, celui-ci doit être tué avant tous les autres.",
            "Vous devez invoquer votre Chaferfu à chaque fois qu'il est disponible",
            "Vous devez toujours terminer vos tours sur une cellule adjacente à celle d'un de vos adversaires.",
            "Incurable interdit l'utilisation des sorts et armes (comme la Baguette Rhon) de soin. Les joueurs ne doivent pas regagner de vie de tout le combat, seul un effet de vol de vie présent sur certains sorts et certaines armes est autorisé.",
            "Vous ne devez jamais subir un seul dégât de tout le combat. Les sorts Mot Stimulant, Mutilation, Roue de la Fortune, Contrecoup, Transfert de Vie, Mot de Sacrifice et Mot Drainant qui occasionnent des dégâts à son lanceur sont pris en compte et font donc échouer le challenge.",
            "Tous les joueurs doivent utiliser le sort Cawotte à chaque fois qu'il est disponible.",
            "Ce challenge n'apparaît que s'il y a un disciple de Xélor dans votre équipe. Vous ne devez jamais retirer un seul PA (point d'action) à vos ennemis que ça soit via un sort ou une arme. Odorat fait échouer ce challenge puisqu'il retire des PA.",
            "Accessible uniquement lorsqu'il y a au moins un disciple d'Osamodas, de Feca, de Sadida, de Xélor ou de Sram dans l'équipe. Vous devez achever vos ennemis sans utiliser de dégâts directs en utilisant donc des invocations, des glyphes, des poisons, des dégâts de poussés ou des renvois.",
            "Vous ne devez jamais utiliser votre arme de corps à corps de tout le combat et n'utiliser que vos sorts.",
            "Tous les PM disponibles au début du tour doivent être utilisés, c'est-à-dire que le challenge échoue si des PM sont perdus lors d'un tacle. Rester collé à un adversaire n'invalide pas le défi.",
            "Les monstres doivent être tués de façon décroissante (du plus grand au plus petit) en fonction de leurs niveaux. Si deux ou plusieurs monstres ont le même niveau, l'ordre n'a pas d'importance.",
            "Chaque membre de votre équipe doit achever un mob, vos invocations ne sont pas prises en compte dans l'équipe mais peuvent empêcher la réalisation de ce challenge dès lors qu'ils peuvent tuer un monstre. Si l'un de vos alliés meurt sans avoir tué un monstre, le challenge échouera.",
            "Vous ne devez jamais retirer de portée à un quelconque adversaire de tout le combat. Attention au sort Marteau de Moon qui retire 1 PO, c'est un détail souvent oublié par les joueurs.",
            "Pétulant demande à tous les joueurs d'utiliser la totalité de leurs PA (point d'action) à la fin de leur tour.",
            "Vous ne devez jamais finir votre tour sur une cellule adjacente d'un ennemi. L'effet de 'Passe ton tour' que certains monstres peuvent infliger ne fait pas échouer ce challenge.",
            "Les joueurs doivent terminer leur tour sur la case où ils l'ont commencés, les déplacements sont donc possibles lors du tour.",
            "Il faut que tous les membres de votre équipe survivent au combat.",
            "Versatile oblige les joueurs à n'utiliser qu'une seule fois la même action pendant un tour de jeu. À noter que lors d'un échec critique, vous pouvez relancer le sort.",
            "Ce challenge impose à chaque joueur de n'utiliser qu'un seul PM (point de mouvement) par tour. On remarque qu'Odorat fait échouer ce challenge de par la perte de PM qu'il inflige."
        ];


        $this->donjon = [
            "CRYPTE DE KARDORIM",
            "CHÂTEAU ENSABLÉ",
            "GRANGE DU TOURNESOL AFFÂMÉ",
            "COUR DU BOUFTOU ROYAL",
            "DONJON DES TOFUS",
            "DONJON DES BWORKS",
            "DONJON DES SQUELETTES",
            "CACHE DE KANKREBLATH",
            "DONJON DES SCARAFEUILLES",
            "MAISON FANTÔME",
            "DONJON DES FORGERONS",
            "GROTTE HESQUE",
            "NID DU KWAKWA",
            "ACADEMIE DES GOBS",
            "DONJON DES LARVES",
            "DONJON DES BLOPS",
            "DONJON DES GELÉES",
            "VILLAGE KANIBOUL",
            "CHÂTEAU DU WA WABBIT",
            "LES PITONS ROCHEUX DES CRAQUELEUR",
            "ÉPREUVE DE DRAEGNERYS",
            "ARCHE D'OTOMAÏ",
            "LABORATOIRE DE BRUMEN TINCTORIAS",
            "CIMETIÈRE DES MASTODONTES",
            "TERRIER DU WA WABBIT",
            "DOMAINE ANCESTRAL",
            "CHAPITEAU DES MAGIK RIKTUS",
            "BÂTEAU DU CHOUQUE",
            "ANTRE DE LA REINE NYÉE",
            "ANTRE DU DRAGON COCHON",
            "THÉÂTRE DE DRAMAK",
            "REPAIRE DU KHARNOZOR",
            "CAVERNE DU KOULOSSE",
            "FABRIQUE DE MALLÉFISK",
            "TANIÈRE DU MEULOU",
            "ARBRE DE MOON",
            "BAMBUSAIE DE DAMADRYA",
            "BIBLIOTHÈQUE DU MAÎTRE CORBAC",
            "MIAUSOLÉE DU POUNICHEUR",
            "DONJON DES RATS DE BONTA",
            "DONJON DES RATS DE BRÂKMAR",
            "GOULET DU RASBOUL",
            "ANTRE DU BLOP MULTICOLOR ROYAL",
            "ANTRE DE CROCABULIA",
            "MÉGALITHE DE FRAKTALE",
            "CENTRE DU LABYRINTHE DU MINOTOROR",
            "SERRE DU ROYALMOUTH",
            "REPAIRE DU SKEUNK",
            "TOFULAILLER ROYAL",
            "RING DU CAPITAINE ÉKARLATE",
            "CAVERNE D'EL PIKO",
            "VOLIÈRE DE LA HAUTE TRUCHE",
            "VALLÉE DE LA DAME DES EAUX",
            "ATELIER DU TANUKOUÏ SAN",
            "CLAIRIÈRE DU CHÊNE MOU",
            "FABRIQUE DE FOUX D'ARTIFICE",
            "DOJO DU VENT",
            "EXCAVATION DU MANSOT ROYAL",
            "LABORATOIRE DU TYNRIL",
            "EPAVE DU GROSLANDAIS VIOLENT",
            "GALERIE DU PHOSSILE",
            "DONJON DES RATS DU CHÂTEAU D'AMAKNA",
            "GROTTE DE KANIGROULA",
            "CANOPÉE DU KIMBO",
            "SALLE DU MINOTOT",
            "HYPOGÉE DE L'OBSIDIANTRE",
            "TOMBE DU SHOGUN TOFUGAWA",
            "PLATEAU DE USH",
            "DEMEURE DES ESPRITS",
            "BOYAU DU PÈRE VER",
            "TANIÈRE GIVREFOUX",
            "HOROLOGIUM DE XLII",
            "GROTTE DU BWORKER",
            "ANTRE DU KORRIANDRE",
            "ANTRE DU KRALAMOURE GÉANT",
            "TEMPLE DU GRAND OUGAH",
            "CAVE DU TOXOLIATH",
            "CAMP DU COMTE RAZOFF",
            "ANTICHAMBRE DES GLOURSONS",
            "MINE DE SAKAÏ",
            "CAVERNES DU KOLOSSO",
            "PYRAMIDE D'OMBRE",
            "MANOIR DES KATREPAT",
            "TOUR DE BETHEL",
            "VAISSEAU DU CAPITAINE MÉNO",
            "DÉFI DU CHALOEIL",
            "LA TOUR DE LA CLEPSYDRE",
            "ARBRE DE MORT",
            "PALAIS DE DANTINÉA",
            "BRASSERIE DU ROI DAZAK",
            "TRÔNE DE SANG",
            "BELVÉDÈRE D'ILYZAELLE",
            "SALONS PRIVÉS DE KLIME",
            "TEMPLE DE KOUTOULOU",
            "AQUADÔME DE MERKATOR",
            "SENTENCE DE LA BALANCE",
            "FORGEFROIDE DE MISSIZ FRIZZ",
            "LABORATOIRE DE NILEZA",
            "VENTRE DE LA BALEINE",
            "SOUVENIR D'IMAGIRO",
            "TRÔNE DE LA COUR SOMBRE",
            "MÉMOIRE D'ORUKAM",
            "PALAIS DU ROI NIDAS",
            "FERS DE LA TYRANNIE",
            "TOUR DE SOLAR",
            "TRANSPORTEUR DE SYLARGH",
            "CHAMBRE DE TAL KASHA",
            "OEIL DE VORTEX",
            "SANCTUAIRE DE TORKÉLONIA"

        ];

        $this->donjonLevel =[
            10,
            20,
            20,
            30,
            40,
            40,
            40,
            40,
            40,
            50,
            50,
            50,
            50,
            50,
            50,
            60,
            60,
            60,
            70,
            70,
            70,
            70,
            80,
            80,
            90,
            90,
            90,
            90,
            100,
            100,
            100,
            100,
            100,
            100,
            100,
            110,
            110,
            110,
            110,
            110,
            110,
            120,
            120,
            120,
            120,
            120,
            120,
            120,
            130,
            130,
            130,
            130,
            130,
            140,
            140,
            140,
            140,
            140,
            150,
            150,
            150,
            160,
            160,
            160,
            160,
            160,
            160,
            170,
            170,
            170,
            170,
            180,
            180,
            180,
            180,
            180,
            190,
            190,
            190,
            190,
            190,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200,
            200
        ];

        $this->picturesArray = [];
        

        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        foreach (new DirectoryIterator('./public/assets/pictures') as $fileInfo) {
            if($fileInfo->isDot()) continue;
            array_push($this->picturesArray, $fileInfo->getFilename());
        }
        $userNumber = 10;
        sort($this->picturesArray,SORT_NATURAL);
        

       

        //Authenticated Admin
        $adminUser = new User();
        $password = "password";
        $adminUser->setEmail('admin')
        ->setRoles(["ROLE_ADMIN"])
        ->setPassword($this->userPasswordHasher->hashPassword($adminUser,$password));
        $manager->persist($adminUser);
        $manager->flush();
        //Authenticated users
        for ($i=0; $i <  $userNumber; $i++) { 
            $userUser = new User();
            $password = $this->faker->password(2,6);
            $userUser->setEmail($this->faker->username() . '@' . $password)
            ->setRoles(["ROLE_USER"])
            ->setPassword($this->userPasswordHasher->hashPassword($userUser,$password));
            $manager->persist($userUser);
            $manager->flush();
        }

        $allPictures = [];
        foreach ($this->picturesArray as $fileName){
            $picture = new Picture();
            $picture->setFile(new File("./public/assets/pictures/" . $fileName))
            ->setRealPath($fileName)
            ->setRealName(substr($fileName, strpos($fileName, "_") + 1))
            ->setPublicPath("assets/pictures/" . $fileName)
            ->setMimeType("image/png")
            ->setStatus(true);
            $manager->persist($userUser);
            $manager->flush();
            array_push($allPictures,$picture);
        }
        
        $count = 0;
        for ($i = 0; $i < count($this->classArray);$i++){
            $classe = new Classe();
            $classe->setName($this->classArray[$i])

            ->setStatus(true);
            if ($count < count($allPictures)){
                $classe->setPicture($allPictures[$count]);
            }
            $manager->persist($classe);
            $manager->flush();
            $count++;
        }

        $challengeList = [];
        for ($i = 0; $i < count($this->challenges);$i++){
            $challenge = new Challenge();
            $challenge->setChallengeName($this->challenges[$i])

            ->setDescription($this->description[$i]);
            if ($count < count($allPictures)){
                $challenge->setPicture($allPictures[$count]);
            }
            array_push($challengeList,$challenge);
            $manager->persist($challenge);
            $manager->flush();
            $count++;
        }


        for ($i = 0; $i < count($this->donjon);$i++) {
            $donjon = new Donjon();
            $donjon->setName($this->donjon[$i])
            ->setLevel($this->donjonLevel[$i])
            ->setChallenges($this->faker->randomElement($challengeList))
            ->setStatus(true);
            if ($count < count($allPictures)){
                $donjon->setPicture($allPictures[$count]);
            }
            $manager->persist($donjon);
            $manager->flush();
            $count++;
        }
    }
}
