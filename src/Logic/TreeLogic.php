<?php


namespace App\Logic;

use App\Entity\Person;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use phpDocumentor\Reflection\Types\Boolean;

class TreeLogic
{
    /**
     * @var EntityManager
     */
    private $em;

    public function estimateAgesAction(EntityManager $em, User $user)
    {
        try {
            $this->em = $em;
            $repositoryPerson = $this->em->getRepository(Person::class);

            $repositoryPerson->resetCalcEstimations();

            $this->em->flush();
            $this->em->clear(Person::class);

            $persons = $repositoryPerson->getAllOrderedByBirth($user, false);
           // dd($persons);
           // $this->estimateAges($persons, true);
            $this->estimateAges($persons, false);
            $this->estimateAges($persons, false);
           // $this->em->flush();
           // $persons = $repositoryPerson->getAllOrderedByBirth($user, true);
           // dd($persons);
           /* for($i = 0;$i < 10;++$i )
                $this->estimateAges($persons, false);*/

           $this->em->flush();
           $this->em->clear(Person::class);
          //  dump("2");
         //   flush();
          //  ob_flush();

            $this->estimateGenerationAges();
            $persons = $repositoryPerson->getAllOrderedByBirth($user, true);
            $this->estimateDeath($persons);

            $this->em->flush();
            $this->em->clear(Person::class);
           // dd("finished");
        }
        catch (\Exception $ex) {
            dd($ex);
        }
    }

    private function estimateGenerationAges() {
        $repositoryPerson = $this->em->getRepository(Person::class);
        $data = $repositoryPerson->getAvgAgeInGeneration();
        $agesInGenerations =[];
        foreach ($data as $agesInGeneration ) {
            if($agesInGeneration['leafLevel'] !== null) {
                $agesInGenerations[$agesInGeneration['leafLevel']] = $agesInGeneration;
            }
        }

        $persons = $repositoryPerson->findAll();
        foreach ($persons as $person ) {
            $generation = $person->getLeafLevel();
            /*if($person->getName() == "Eva")
                dd($agesInGenerations);*/
            if($generation !== null) {

                $agesInGeneration = $agesInGenerations[$generation];


                if ($person->getBorn(true, true) === null && isset($agesInGeneration['born'])) {

                    $person->setBornCalculated($agesInGeneration['born']);
                    echo "b: " . $person->getName() . " " .$agesInGeneration['born'];


                }
                if ($person->getDied(true, true) === null && isset($agesInGeneration['born']) && isset($agesInGeneration['age'])) {
                    $person->setDiedCalculated($agesInGeneration['born'] + $agesInGeneration['age']);
                    echo "d: ". $person->getName() . " " .($agesInGeneration['born'] + $agesInGeneration['age']);
                }
            }
            $this->em->persist($person);
        }

        $this->em->flush();
        $this->em->clear(Person::class);
    }
    public function calcLeafAction(EntityManager $em)
    {
        //dd("no!");
        $this->em = $em;
        $repositoryPerson = $this->em->getRepository(Person::class);

        $repositoryPerson->resetLeaves();
        $persons = $repositoryPerson->findAll();

        $personByFather = [];
        $personByMother = [];

        foreach($persons as $person)
        {
            $fid = $person->getFather();
            if ($fid !== null) {

                $personByFather[$fid->getId()][] = $person;
                $personByParent[$fid->getId()][] = $person;
            }
            $mid = $person->getMother();
            if ($mid !== null) {

                $personByMother[$mid->getId()][] = $person;
                $personByParent[$mid->getId()][] = $person;
            }
        }

       // $startPersons = $repositoryPerson->findNoParents();

        //foreach ($startPersons as $startPerson)
        {
           // $startPerson = $startPersons[0];
            $startPerson = $repositoryPerson->findOneBy(['name' => 'Adam (Mann von Eva)']);

            $currentLevel = 0;
            $currentIndex = 0;
            $out = false;
            $this->childrenIterate($currentIndex, 0, $personByParent, $startPerson);
        }

        for($i = 0;$i<20;$i++)
        {
            foreach($persons as $person) {

                if(!$person->getLeafLevel() ) {

                    $b = false;
                    foreach($person->getChildren() as $child) {
                        if($child->getLeafLevel() !== null) {
                            $person->setLeafLevel($child->getLeafLevel() - 1);
                            break;
                        }
                    }
                }
            }
        }
        $this->em->flush();
        $this->em->clear(Person::class);
    }

    private function estimateDeath($persons)
    {
             // estimate death
        /** @var Person $person */
        foreach($persons as $person) {
echo $person->getName();
            // muss vor kind geboren sein
            //
            //if($person->getBornEstimated() === null)
             {
                $maxAge = $person->getBornCalculated();
echo ' b:';
                foreach ($person->getChildren() as $child) {

                    $b = $child->getBornCalculated();
                    echo ' c: '.  $child->getName() . ' ' . $b . ' ';
                    if($b !== null) {
                        if ($maxAge === null )
                            $maxAge = $b-20;
                        else
                            $maxAge = min($maxAge, $b-20);
                    }
                    if($maxAge !== null){
                        $person->setBornCalculated($maxAge);
                        echo ' b -->' . $maxAge;
                        $this->em->persist($person);
                    }
                }

            }
            if($person->getDiedEstimated() === null) {

                $minAge = null;
                $b = $person->getBorn(true, true);
                if($b !== null) {
                    $minAge = $b + 40;
                }
                /** @var Person $child */
                foreach ($person->getChildren() as $child) {

                    $b = $child->getBornCalculated();
                    echo ' c: '.  $child->getName() . ' ' . $b . ' ';
                    if($b !== null) {
                        $minAge = max($minAge, $b+20);
                    }
                }
                if($minAge !== null){
                    $person->setDiedCalculated($minAge);
                    echo ' d -->' . $minAge;
                    $this->em->persist($person);
                }
            }
            echo "<br>";
        }

        $this->em->flush();
    }

    private function estimateAges($persons, $strict) {
        /** @var Person $person */
        $p2 =[];
        {
            foreach($persons as $person)
            {
                $upGenerations = 0;
                $downGenerations = 0;
//echo $person->getName()."<br>";
                if ($person->getBorn(true, true) === null) {
                    $valueUp = $this->iterateAge($person, $upGenerations, true, true);
                    $valueDown = $this->iterateAge($person, $downGenerations, false, true);
           //         echo  $upGenerations .' ' .$valueUp . ' ' . $downGenerations . ' ' . $valueDown .'<br>';

                    if ($valueUp !== null ) {
                         if ($valueDown !== null) {
                             $guessed = $valueUp + ($valueDown - $valueUp) * ($upGenerations-1)/ ($upGenerations + $downGenerations - 2);
                             $person->setBornCalculated($guessed);
                        } else {
                            $p2[] = $person;
                           // if(!$strict)// /*&& $upGenerations <=2*/)
                           /* if($strict == 123)
                            {
                                $guessed = $valueUp + 40 * $upGenerations;
                                echo $guessed ." " .  $valueUp ." " . $upGenerations ."<br>";

                                /*if ($person->getFather() && $person->getFather()->getDied(true, true) < $guessed) {

                                    $fb = $person->getFather()->getBorn(true, true);
                                    $fd = $person->getFather()->getDied(true, true);
                                    if ($fb != null) {
                                        if ($fd != null) {
                                            $guessed = ($fb + $fd) / 2;
                                        } else {
                                            $guessed = $fb + 40;
                                        }
                                    }
                                    if ($fd != null) {
                                        $guessed = ($fb + $fd) / 2;
                                    }
                                }* /
                                $person->setBornCalculated($guessed);
                            }*/
                        }
                    }
                    else// if ($valueDown !== null )
                    {
                        //&& $strict == 123) {
                       /*if($strict === 123) {
                            $guessed = $valueDown - 40 * ($downGenerations - 1);
                            /* if ($person->getFather() && $person->getFather()->getDied(true, true) < $guessed) {

                                 $fb = $person->getFather()->getBorn(true, true);
                                 $fd = $person->getFather()->getDied(true, true);
                                 if ($fb != null) {
                                     if ($fd != null) {
                                         $guessed = ($fb + $fd) / 2;
                                     } else {
                                         $guessed = $fb + 40;
                                     }
                                 }
                                 if ($fd != null) {
                                     $guessed = ($fb + $fd) / 2;
                                 }
                             }* /
                            $person->setBornCalculated($guessed);
                        }*/
                        $p2[] = $person;

                    } /*else {

                    }*/
                } else {
                    if($person->getBorn())
                        $person->setBornEstimated($person->getBorn());
                    if($person->getBorn(true))
                        $person->setBornCalculated($person->getBorn(true));
                }

               /* $upGenerations = 0;
                $downGenerations = 0;*/
                if ($person->getDied(true, true) === null) {
                   /* $valueUp = $this->iterateAge($person, $upGenerations, true, false);
                    $valueDown = $this->iterateAge($person, $downGenerations, false, false);
                    if ($valueUp != null) {
                        if ($valueDown != null) {
                            $guessed = $valueUp + ($valueDown - $valueUp) * ($upGenerations-1)/ ($upGenerations + $downGenerations - 2);
                            $bornGuessed = $person->getBorn(true, true);
                            //if(!$bornGuessed || $guessed > $bornGuessed) {
                                $person->setDiedCalculated($guessed);
                            //} else {
              //                  echo $guessed . " < " . $person ."br";
                         //   }
                        } else {
                            if($strict == 123)
                            {
                                $guessed = $valueUp + 40 * $upGenerations;
                                $person->setDiedCalculated($guessed);
                            }
                        }
                    }*/


                }
                else {
                    if($person->getDied())
                        $person->setDiedEstimated($person->getDied());
                    if($person->getDied(true))
                        $person->setDiedCalculated($person->getDied(true));
                }
                $this->em->persist($person);
             //   $this->em->flush();
            }
          /*  flush();
            ob_flush();*/
        }

        echo "********************************************<br>";
       // for($i = 0;$i<2;$i++) {
            // äste
            $p3 = [];
            foreach ($p2 as $person) {
                if($person->getName() === 'Ahab')
                    echo $person->getName()."<br>";

                $upGenerations = 0;
                $downGenerations = 0;
                //       echo $person->getName()." " .$person->getLeafLevel() ."<br>";

                $valueUp = $this->iterateAge($person, $upGenerations, true, true);
                $valueDown = $this->iterateAge($person, $downGenerations, false, true);

                if ($valueUp) {
                    $person->setBornCalculated($valueUp + ($upGenerations - 1) * 40);

                    $this->em->persist($person);
                   // if($person->getName() == 'Ahab') dd($valueUp, $valueDown, $upGenerations, $downGenerations);
                } else {
                    if ($valueDown) {
                        $person->setBornCalculated($valueDown - ($downGenerations - 1) * 40);
                        $this->em->persist($person);
                       // echo $valueDown." ".$downGenerations."<br>";
                    }
                    else {
                        $p3[] = $person;
                    }
                }
            }
           // dd($p3);
            $p2 = $p3;
       // }

        $this->em->flush();
       // dd();

    }
    private function iterateAge(Person $person, &$generations, bool $up = true, bool $born) : ? int
    {
       // $echo = $generations == 0;
        $result = null;
        $generations++;
        if($born) {
            if ($person->getBorn(true, true)) {
                   $result =  $person->getBorn(true, true);
            }
        } else {
            if ($person->getDied(true, true)) {
                $result = $person->getDied(true, true);
            }
        }
        if(!$result) {
            if ($up) {
                if ($person->getFather()) {
                    $result = $this->iterateAge($person->getFather(), $generations, true, $born);
                } else {
                    if ($person->getMother()) {
                        $result = $this->iterateAge($person->getMother(), $generations, true, $born);
                    }
                }
            } else {
                foreach ($person->getChildren() as $child) {
                    $generations2 = $generations;
                    $result = $this->iterateAge($child, $generations2, false, $born);
                    if ($result !== null) {
                        $generations = $generations2;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    private function childrenIterate(&$currentIndex, $currentLevel, &$personByParent, Person &$currentPerson)
    {
       // dd("no");
        //echo $currentIndex .")".$currentPerson->getName() ."(".$currentLevel.")<br>";
       // flush();
       // ob_flush();
        if($currentPerson->getLeafLevel()  && $currentPerson->getLeafLevel() > $currentLevel)
        {
        //    dump($currentLevel ." < " .$currentPerson->getLeafLevel() . " ". $currentPerson->getName());
           return;
        }
        $currentPerson->setLeafStart($currentIndex);
        $currentPerson->setLeafLevel($currentLevel);
//dd($personByFather);
        if (isset($personByParent[$currentPerson->getId()])) {

            $children = $personByParent[$currentPerson->getId()];
           /* if ($this->checkStopped()){
                echo "stopped"; die;
            }*/
            foreach ($children as $child) {
                $currentIndex++;
                $this->childrenIterate($currentIndex, $currentLevel+1, $personByParent, $child);
            }
        }
        $currentPerson->setLeafOut($currentIndex++);
        //  echo "m".$currentPerson->getName();
        $this->em->persist($currentPerson);
//        $this->em->merge($currentPerson);

    }
}
