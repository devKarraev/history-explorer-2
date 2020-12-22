<?php


namespace App\Logic;

use App\Entity\Folk;
use App\Entity\Location;
use App\Entity\Person;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class SyntaxLogic
{
    /**
     * @var EntityManager
     */
    private $em;

    public function folkHelper(EntityManager $em)
    {
        dd(" protection");
        $this->em = $em;
        $repositoryFolk = $this->em->getRepository(Folk::class);
        $repositoryPerson = $this->em->getRepository(Person::class);
        $folks = $repositoryFolk->findAll();
        foreach($folks as $folk) {
            $name = $folk->getName();
            $description = $folk->getDescription();

            $re1 = '/von <a href="\/bibel\/index.php\/.{1,50}title="(.{1,50})">.{1,20}abstammt/m';
            $re2 = '/Stamm der <a href="\/bibel\/index.php\/.{1,50}title="(.{1,50})">/m';

            if (preg_match_all($re1, $description, $matches, PREG_SET_ORDER, 0)) {

                echo $name . " stammt ab von ". $matches[0][1]."<br>";
                $father = $repositoryPerson->findOneBy(['name'=>$matches[0][1]]);
                if($father) {
                    $father->addProgenitor($folk);
                    $em->persist($father);
                } else{
                    dump($description, 'father ' . $matches[0][1] .' not found');
                }

            }
            if (preg_match_all($re2, $description, $matches, PREG_SET_ORDER, 0)) {
                //dd($matches);
                echo $name . " stammt ab von Stamm ". $matches[0][1]."<br>";
                $parentFolk = $repositoryFolk->findOneBy(['name' => $matches[0][1]]);
                if($parentFolk) {
                    $folk->setFatherFolk($parentFolk);
                    $em->persist($folk);
                } else{
                    dump($description, 'parentFolk not found');
                }

            }
        }
        $em->flush();
        dd(" END");
    }

    public function latlngReader(EntityManager $em, User $user)
    {
        $completeurl = "../public/input/bibleplaces.kml";
        $xml = simplexml_load_file($completeurl);

        //levenshtein

        $placemarks = $xml->Document->Placemark;
        $repositoryLocation = $em->getRepository(Location::class);

        $notapprovednames = $repositoryLocation->findBy(['approved' => '0']);
$data =[];

        $foundCounter = 0;
        $notFoundCounter = 0;

        for ($i = 0; $i < sizeof($placemarks); $i++) {
            $name = $placemarks[$i]->name;
            $name = str_replace(['~', '?', '>'], [''], $name);
            $name = trim($name);
            $p = $placemarks[$i]->Point->coordinates;
            $c = explode(',', $p);
            $data[$name] = $c;
        }

        for ($i = 0; $i < sizeof($notapprovednames); $i++) {
            $l = $notapprovednames[$i];
               // ->andWhere('l.approved = 0')

            foreach ($data as $name => $c) {
                $distance = levenshtein($l->getName(), $name);
                //if($distance < 3) {
                if($distance / strlen($l->getName()) < 0.36) {

                    $l->setLat($c[1])->setLon($c[0])->setApproved(1);
                    $em->persist($l);
                    $foundCounter++;
                    echo "<b>".$l->getName()."</b> ";
                    echo $name.': '.$distance / strlen($l->getName()) .'<br>';
                }
            }
        }
        dd();
        $em->flush();
        dd($foundCounter, $notFoundCounter);
    }

    public function childrenHelper(EntityManager $em)
    {
        dd("protection");
        $this->em = $em;
        $repositoryPerson = $this->em->getRepository(Person::class);
        $persons = $repositoryPerson->findAll();
       // $persons = $repositoryPerson->findBy(['name' => 'Achsa (Tochter des Kaleb)' ]);

        /**
         * @var Person $person
         */
        foreach($persons as $person) {
            $shortName = $name =  $person->getName();
            $description = $person->getDescription();
//echo $shortName .  "<br";
            $pos = strpos($name, '(');
            if ($pos!== false) {
                $shortName = substr($name, 0, $pos-1);
            }

            $parents = [];
            $sonOrDaughter = '';
            $re = '/^.{0,50}'. $shortName. '.{0,40}.(Kind|Sohn|Söhne|Tochter|Töchter|Nachkomme).{0,10}<a href="\/bibel\/index.php\/.{1,50}title="(.{1,50})">.{1,50}und <a href="\/bibel\/index.php\/.{1,50}title="(.{1,50})">/m';
            $g = 'ab';

            if (preg_match_all($re, $description, $matches, PREG_SET_ORDER, 0)) {
               // echo $name . ' 2 parents match!' . $matches[0][2] . " und ". $matches[0][3]."   " .$description ."<br>";

                $parents[] = $matches[0][2];
                $parents[] = $matches[0][3];
                $g = in_array($matches[0][1], ['Sohn','Söhne']) ? 'm' : (in_array($matches[0][1], ['Tochter','Töchter'])  ? 'w' : '');

            } else {
                $re = '/^.{0,50}'. $shortName. '.{0,40}.(Kind|Sohn|Söhne|Tochter|Töchter|Nachkomme).{0,10}<a href="\/bibel\/index.php\/.{1,50}title="(.{1,50})">/m';
               // dump( $description);
                if (preg_match_all($re, $description, $matches, PREG_SET_ORDER, 0)) {
                   ;
                //    echo $name.' 1 parent match!'.$matches[0][2]. "   " . $description . "<br>";
                    $parents[] = $matches[0][2];
                    $g = in_array($matches[0][1], ['Sohn','Söhne']) ? 'm' : (in_array($matches[0][1], ['Tochter','Töchter'])  ? 'w' :'');

                }
                else {
                    echo "nothing found in " . $description  . "<br>";

                }
              //  dd($re, $description);

            }
if($shortName == 'Milka') {
    //dump($re);
   // dd($description);
}

            /* @var Person */
           foreach ($parents as $parentName ) {


               if($g !== '' && $person->getGender() !== $g) {
                   dd ($parentName . ' '. $g . '' . $person->getGender() .' ' . $description);
               }
               if($g === '') {
                   echo ($g . '' . $person->getGender() .' ' . $description) . "<br>";
               }
               $parent = $repositoryPerson->findOneBy(['name' => $parentName]);
               $pg = [];
             //  dump($pg);
             //  dump($parent);
               if($parent) {
                   if ($parent->getGender() == "m") {
                       if(isset( $pg['m']))
                           dd("Father already set");
                     //  dd($person);
                       $person->setFather($parent);
                       $pg[] ='m';

                   } else {
                       if ($parent->getGender() == "w") {
                           if(isset( $pg['w']))
                               dd("mother already set");
                           $person->setMother($parent);
                           $pg[] ='w';
                       } else {
                           echo "parent gender unknown.";
                           dump($person, $parent);
                       }
                   }
               }
           }
           if(sizeof($parents) > 0)
           {
               $em->persist($person);
           }




          /*  $re = '/(Sohn|Tochter|Nachkomme) (von|des|der) <a href="\/bibel\/index\.php\/(.*)".*\/a> und <a href="\/bibel\/index\.php\/(.*)"/mU';
            if (preg_match_all($re, $description, $matches, PREG_SET_ORDER, 0)) {

                $parentName = str_replace("_", " ", $matches[0][4]);
                $parentName = str_replace("%C3%A4", "ä", $parentName);
                /**
                 * @var Person
                 * /
                $parent = $repositoryPerson->findOneBy(['name' => $parentName]);

                if($parent) {
                    if($person->getGender()=="m")
                    {
                        $person->setFather($parent);
                        $em->persist($person);
                    }
                    else if ($person->getGender()=="w") {

                        $person->setMother($parent);
                        $em->persist($person);
                    } else {
                        //dump($parentName);
                    }

                } else {
                    //dd($description);
                }
            }*/
                flush();


        }
        $em->flush();
        dd(" END");
        foreach($persons as $person) {
            $name =  $person->getName();
            $description = $person->getDescription();
            $re = '/Vater von <a href="\/bibel\/index\.php\/(.*)"/mU';
            if (preg_match_all($re, $description, $matches, PREG_SET_ORDER, 0)) {
                $childName = str_replace("_", " ", $matches[0][1]);
                $childName = str_replace("%C3%A4", "ä", $childName);
                /**
                 * @var Person
                 */
                $child = $repositoryPerson->findOneBy(['name' => $childName]);

                if($child) {
                    if($person->getGender()=="")
                    {
                        $person->setGender("m");
                        $em->persist($person);
                        dump("set gender of " .$name );
                    }
                    else if ($person->getGender()=="w") {

                        dd("!");
                    } else {
                        if($child->getFather() != null && $child->getFather() != $person) {

                            dump("fail ".  $person->getName() ." != ". $child->getFather()->getName());
                        } else {
                            $child->setFather($person);
                            $em->persist($child);
                        }
                    }

                } else {
                    dd("?" . $description);
                }
            }
            $re = '/Mutter von <a href="\/bibel\/index\.php\/(.*)"/mU';
            if (preg_match_all($re, $description, $matches, PREG_SET_ORDER, 0)) {
                $childName = str_replace("_", " ", $matches[0][1]);
                $childName = str_replace("%C3%A4", "ä", $childName);
                /**
                 * @var Person
                 */
                $child = $repositoryPerson->findOneBy(['name' => $childName]);

                if($child) {
                    if($person->getGender()=="")
                    {
                        $person->setGender("w");
                        $em->persist($person);
                        dump("set gender of " .$name );
                    }
                    else if ($person->getGender()=="m") {

                        dump ("gender fail " .  $person->getName() . ' '. $description);
                    } else {
                        if($child->getMother() != null && $child->getMother() != $person) {

                            dump("fail ".  $person->getName() ." != ". $child->getMother()->getName());
                        } else {
                            $child->setMother($person);
                            $em->persist($child);
                        }
                    }

                } else {
                    dd("?" . $description);
                }
            }
        }


        $em->flush();
        dd();
    }

    public function genderHelper(EntityManager $em)
    {
        $this->em = $em;
        $repositoryPerson = $this->em->getRepository(Person::class);
        $persons = $repositoryPerson->getAllGenderless();

        /**
         * @var Person $person
         */
        foreach($persons as $person) {
            $name =  $person->getName();
            $description = $person->getDescription();
            $males = ['(der', 'König', 'Oberster', 'Torhüter', 'Prophet'];
            $found = false;
            foreach ($males as $male) {

                if (strpos($name, $male) !== false) {
                    $person->setGender('m');
                    $this->em->persist($person);
                    $found = true;
                    break;
                }
            }
            if(!$found) {
                $males = ['der Mann von', 'ist ein ', 'war ein ', 'war der ', 'ist der ', 'war ein', 'seine Frau ', 'Seine Frau',
                    'Sein Sohn', 'sein Sohn', 'Seine Tochter', 'seine Tochter', 'Seine Söhne', 'seine Söhne', 'Seine Töchter', 'seine Töchter',
                    'iner der'];
                foreach ($males as $male) {

                    if (strpos($description, $male) !== false) {
                        $person->setGender('m');
                        $this->em->persist($person);
                        $found = true;
                        break;
                    }
                }
            }

            if(!$found) {
                $females = ['die Frau von ', 'ist eine ', 'war die ', 'war eine ', 'ist die ', 'war eine', 'ihr Mann ', 'Ihr Mann',
                    'Ihr Sohn', 'ihr Sohn', 'Ihre Tochter', 'ihre Tochter', 'Ihre Söhne', 'ihre Söhne', 'Ihre Töchter', 'ihre Töchter',
                'ine der'];
                foreach ($females as $female) {

                    if (strpos($description, $female) !== false) {
                        $person->setGender('w');
                        $this->em->persist($person);
                        $found = true;
                        break;
                    }
                }
            }


        }
        $this->em->flush();

        dd();

    }
}
