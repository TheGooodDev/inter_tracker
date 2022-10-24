<?php

namespace App\DataFixtures;

use App\Entity\Challenge;
use App\Entity\Donjon;
use App\Entity\Player;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;
use PhpParser\Node\Expr\List_;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create("fr_FR");
        $this->classArray = ["ecaflip","eniripsa","iop","crâ","féca","sacrieur","sadida","osamodas","enutrof","sram","xélor","pandawa","roublard","zobal","steamer","eliotrope","huppermage","ouginak"];

    }

    public function load(ObjectManager $manager): void
    {

        $challengeList = [];
        for ($i = 0; $i < 20;$i++){
            $challenge = new Challenge();
            $challenge->setChallengeName($this->faker->sentence(2))
            ->setDescription($this->faker->realText(150));
            array_push($challengeList,$challenge);
            $manager->persist($challenge);
            $manager->flush();
        }

        for ($i = 0; $i < 20; $i++) {
            $player = new Player();
            $player->setPseudo($this->faker->userName())
            ->setClasse($this->classArray[$this->faker->numberBetween(0,count($this->classArray)-1)])
            ->setStatus(true);
            $manager->persist($player);
            $donjon = new Donjon();
            $donjon->setName($this->faker->streetName())
            ->setLevel($this->faker->numberBetween(20,200))
            ->setChallenges($this->faker->randomElement($challengeList))
            ->setStatus(true);
            $manager->persist($donjon);


            $manager->flush();
        }

    }
}
