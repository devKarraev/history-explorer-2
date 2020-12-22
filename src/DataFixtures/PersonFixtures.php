<?php

namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\Reference;
use App\Repository\PersonRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PersonFixtures extends BaseFixture implements DependentFixtureInterface
{


   /* private static $articleTitles = [
        'Why Asteroids Taste Like Bacon',
        'Life on Planet Mercury: Tan, Relaxing and Fabulous',
        'Light Speed Travel: Fountain of Youth or Fallacy',
    ];

    private static $articleImages = [
        'asteroid.jpeg',
        'mercury.jpeg',
        'lightspeed.png',
    ];

    private static $articleAuthors = [
        'Mike Ferengi',
        'Amy Oort',
    ];*/

    public function loadData(ObjectManager $manager)
    {
        $references = [];
        for($refCount = 0; $refCount < 50;$refCount++)
        {
            $reference = new Reference();
            $reference->setChapter($this->faker->numberBetween(1,10));
            $reference->setVerse($this->faker->numberBetween(1,10));
            $reference->setBook('1.Mose');
            $manager->persist($reference);
            $references[] = $reference;
        }

        $manager->flush();

        $startIndex = 0;
        $nrOfPeopleInGeneration = 10;
        for($generation = 0; $generation < 5;$generation++) {

            $personRepo = $manager->getRepository(Person::class);
            $parents = $personRepo->findBy(['leafLevel' => $generation-1]);

            $fathers = $personRepo->findBy(['leafLevel' => $generation-1, 'gender' => 'm']);
            $mothers = $personRepo->findBy(['leafLevel' => $generation-1, 'gender' => 'f']);

            $nrOfCouples = intval(min(sizeof($mothers), sizeof($fathers)) *  $this->faker->numberBetween(70, 90)/ 100.0);

            // randomly create couples of former generations
            $couples = [];

            $mothers = $this->faker->randomElements($mothers, $nrOfCouples);
            $fathers = $this->faker->randomElements($fathers, $nrOfCouples);

            for ($coupleIndex = 0; $coupleIndex < min(sizeof($mothers), sizeof($fathers));$coupleIndex++)
            {
                $couples[] = ['father' => $fathers[$coupleIndex], 'mother' => $mothers[$coupleIndex]];
            }

            $this->createMany(

                $nrOfPeopleInGeneration,
                strval($generation),
                //startIndex,
                function () use ($generation, $couples,$references) {

                    $person = new Person();
                    $male = $this->faker->boolean(66);
                    $person->setName($this->faker->name($male ? 'male' : 'female'))
                        ->setGender($male ? 'm' : 'f');
                    $person->setLeafLevel($generation) ;

                    $hasParents = true;//$this->faker->boolean(90);

                    $minParentsAge = $generation * 30 + 1800;
                    if($hasParents)
                    {
                        $parents = $this->faker->randomElement($couples);
                        if($parents !== null) {
                            $person->setFather($parents['father']);
                            $person->setMother($parents['mother']);

                            if($parents['mother']->getBorn()) {
                                $minParentsAge = min($minParentsAge, $parents['mother']->getBorn());
                            }
                            if($parents['father']->getBorn()) {
                                $minParentsAge = min($minParentsAge, $parents['father']->getBorn());
                            }
                        }
                    }

                    if($this->faker->boolean(40))
                    {
                        $born = $minParentsAge + $this->faker->numberBetween(20,40);
                        $person->setBorn($born);
                        $person->setDied($born + $this->faker->numberBetween(50,90));
                    }

                    // publish most articles
                    if ($this->faker->boolean(70)) {
                        $person->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
                    }
                    if ($this->faker->boolean(50)) {
                        $person->setImage(
                            sprintf(
                                '%02d.png',
                                $this->faker->numberBetween(1, 25)
                            )
                        );
                    }

                    for($refs = 0 ;$refs < $this->faker->numberBetween(-2,3);$refs++) {
                        $person->addReference($this->faker->randomElement($references));
                    }
                    return $person;
                }
            );
            $startIndex+=$nrOfPeopleInGeneration;
            $nrOfPeopleInGeneration = intval($nrOfPeopleInGeneration*1.5);
            $manager->flush();
        }


        /*$this->createMany(Person::class, 50, function(Person $person, $count) {
            $person->setName($this->faker->name('female'))
                ->setGender("w");

            // publish most articles
            if ($this->faker->boolean(70)) {
                $person->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
            }

            /*$person->setAuthor($this->faker->randomElement(self::$articleAuthors))
                ->setHeartCount($this->faker->numberBetween(5, 100))
                ->setImageFilename($this->faker->randomElement(self::$articleImages))
            ;* /
        });
        $manager->flush();*/
    }

    public function getDependencies()
    {
        return [
            ReferenceFixtures::class,
        ];
    }
}
