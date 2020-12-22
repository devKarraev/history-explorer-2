<?php


namespace App\Logic;


use App\Entity\BibleBooks;
use App\Entity\Folk;
use App\Entity\FolkReference;
use App\Entity\Job;
use App\Entity\Location;
use App\Entity\LocationReference;
use App\Entity\Person;
use App\Entity\PersonReference;
use App\Entity\Reference;
use Doctrine\ORM\EntityManager;

class Leecher
{


    public function __construct()
    {

    }

    private function leechChapters()
    {
        dd("leech protection");
        $startName = '1mos1.html';
        //$startName ='joha3.html';
        echo "leeching " .$startName . "<br>";
        $url = 'http://www.combib.de/bibel/ue/{name}';

        $counter = 0;
        while($startName != null && $counter < 1000) {
            $xml = $this->leechHTML($startName, $url);//

            $kapitelVor = $title = $xml->xpath('//tr[2]//td[last()]//tr[1]//td[2]//a/@href');
         //   dump((string)$kapitelVor[0]);

            $title = $xml->xpath('//tr[4]//td[last()]//text()');


            $lastVerse = $xml->xpath('//tr[5]//td//a[last()]/text()');

            echo ((string)$title[0]) .";". ((string)$lastVerse[0]) ."<br>";
            flush();
            ob_flush();

            $startName = (string) $kapitelVor[0];

            $counter++;
        }
    }

    public function leechPersons(EntityManager $em)
    {
        dd("leech protection");
        $startName = //'/bibel/index.php?title=Kategorie:Person#';
         '/bibel/index.php?title=Kategorie:Person&pagefrom=Parschandata#mw-pages';
        //$startName ='joha3.html';
        echo "leeching " .$startName . "<br>";

        $startAt = 'Zefanja (Sohn des Kuschi)';//'Amos (Prophet)' ;//'Amittai';
        $url = 'http://www.web-fortbildung.de{name}';

        $pageCounter = 0;

        while($startName != null && $pageCounter < 10) {

            $xml = $this->leechHTML($startName, $url);//

            $content = $xml->xpath(
                '//body//div[@class=\'mw-category-group\']//a[contains(@href,\'/bibel/index.php/\')]'
            );

            foreach ($content as $c) {

                $href = ((string)$c->attributes()['href']);
                $title = ((string)$c->attributes()['title']);
                echo $title."\t";

                if ($startAt !== null) {
                    if ($title !== $startAt) {
                        echo "skipping ".$title."<br>";
                        continue;
                    }
                    $startAt = null;
                }

                $articleXml = $this->leechHTML($href, $url);

                $articleP = $articleXml->xpath('//body//div[@id=\'mw-content-text\']');

                $description = $articleP[0]->xpath('//p[1]')[0]->asXML();
                $quellen = $articleP[0]->xpath('//p[last()]')[0]->asXML();

               // dump($description, $quellen);
               // echo $description."\t";

                //dump($matches);
                /** @var Person $person */
                $person = $em->getRepository("Person.php")->findOneBy(['name' => $title]);
                if (!$person) {
                    $person = new Person();
                    $person->setName($title);
                    $person->setApproved(false);
                }
                $person->setDescription($description);

                // gender
                $gender = "";
                $genders = [
                    'Mann' => 'm',
                    'einer der Männer' =>'m',
                    'Sohn'=> 'm',
                    'Vater'=> 'm',
                    'Bruder'=> 'm',
                    'Frau' => 'w',
                    'Mädchen' => 'w',
                    'Tochter'=> 'w',
                    'Mutter'=> 'w',
                    'Schwester'=> 'w',
                    'Engel'=> 'angle',
                    'Gott'=> 'god',
                    'Einer' => 'm',
                ];
                $firstWords =  strtolower($title . ' ' . substr($description, 0, 50));
                foreach ($genders as $typeKey => $typeValue) {
                    if (strpos($firstWords, strtolower($typeKey)) !== false) {
                        $gender = $typeValue;
                        break;
                    }
                }
                if ($gender == "") {
                    dump('gender?', $description);
                   // $gender = 'm';
                }
                $person->setGender($gender);

                $jobList =[];
                $jobs = [
                    'König' => 'king',
                    'Königin'=> 'queen',
                    'Fischer'=> 'fisherman',
                    'Hirte'=> 'shepherd',
                    'Amme' => 'nurse',
                    'Arzt' => 'doctor',
                    'Bäcker' => 'baker',
                    'Baumeister' => 'builder',
                 'Dieb'=> 'thief',
                'Diener'=> 'servant',
                'Erzieher' => 'educator',
                'Geldwechler' => 'money changer',
                'Gesandter'=> 'messanger',
                'Gesetzgelehrter'=> 'lawyer',
                'Geschichtsschreiber'=>'historian',
                'Hebamme'=> 'midwife',
                'Kämmerer'=> 'chamberlain',
                'Knecht'=> 'servant',
                'Kaiser' => 'emperor',
                'Krämer'=>'chandler',
                'Künstler'=>'artist',
                'Läufer'=>'runner',
                'Lanzenträger'=>'lancer',
                'Laibeigene'=>'loave',
                'Magd'=>'maid',
                'Maurer'=>'bricklayer',
                'Mundschenk'=>'goblet',
                'Leibwache'=>'bodyguard',
                'Prokonsul'=>'proconsul',
                'Rabsake'=>'rabsake',
                'Rabsaris'=>'rabsaris',
                'Räuber'=>'robber',
                'Redner'=>'speaker',
                'Rutenträger'=>'rod holder',
                'Sämann'=>'sower',
                'Saitenspieler'=>'string player',
                'Salbenmischer'=>'ointment mixer',
                'Schafzüchter'=>'shepherd',
                'Schriftgelehrte'=>'scribes',
                'Seher'=>'seer',
                'Silberschmied'=>'silversmith',
                'Speisemeister'=>'food master',
                'Stadtschreiber'=>'city clerk',
                'Statthalter'=>'governor',
                'Tagelöhner'=>'day loborer',
                'Töpfer'=> 'potter',
                'Torhüter'=> 'gatekeeper',
                'Vierfürst'=>'fourprince',
                'Vorsteher'=>'head',
                'Waffenträger'=>'weapon bearer',
                'Walker'=> 'fuller',
                'Weber'=>'weaver',
                'Wechsler'=>'money changer',
                'Weingärtner'=>'vine dresser',
                'Werkleute'=>'workers',
                'Zauberer'=>'magician',
                'Zeltmacher'=>'tent maker',
                'Zimmermann'=>'carpenter',
                'Zöllner'=>'tax collector'
                ];
                //dd($type);

                foreach ($jobs as $typeKey => $typeValue) {
                    if (strpos($title . ' ' . $description, $typeKey) !== false) {
                        $jobList[] = $typeValue;
                        echo $typeValue ." ";
                    }
                }

                foreach ($jobList as $jobName) {

                    $job = $em->getRepository("App:Job")->findOneBy(['name' => $jobName]);
                    if(!$job) {
                        $job = new Job();
                        $job->setName($jobName);
                        $em->persist($job);
                    }
                    $person->addJob($job);
                }

                preg_match_all('/\(\d+\)\s? (\d?\.?\D+)\s(\d+)\.(\d+)/', $quellen, $matches);
                if (sizeof($matches) > 0) {
                    for ($i = 0; $i < sizeof($matches[0]); $i++) {
                        $bookName = str_replace('z.B.', '', $matches[1][$i]);
                        $bookName = str_replace(' ', '', $bookName);
                        $bookName = str_replace('Moses', 'Mose', $bookName);
                        $bookName = str_replace('Mosde', 'Mose', $bookName);
                        $bookName = str_replace('Chronikbis', 'Chronik', $bookName);
                        $bookName = str_replace('AmosÜberschrift;Amos', 'Amos', $bookName);
                        $bookName = str_replace('HoseaÜberschrift;Hosea', 'Hosea', $bookName);
                        $bookName = str_replace('JoelÜberschrift;Joel', 'Joel', $bookName);
                        $bookName = str_replace('MaleachiÜberschrift;Maleachi', 'Maleachi', $bookName);
                        $bookName = str_replace('ZefanjaÜberschrift;Zefanja', 'Zefanja', $bookName);
                        $bookName = str_replace('Chroniken', 'Chronik', $bookName);

                      /*  $bookName = str_replace('Amos;', 'Amos', $bookName);
                        $bookName = str_replace('AmosAmos;', 'Amos', $bookName);*/


                        $book = $em->getRepository("App:BibleBooks")->findOneBy(['name' => $bookName]);
                        if ($book) {
                            $reference = $em->getRepository("App:Reference")->findOneBy(
                                [
                                    'book' => $book,
                                    'chapter' => $matches[2][$i],
                                    'verse' => $matches[3][$i]
                                ]
                            );

                            //dump($reference);
                            if ($reference == null) {

                                $reference = new Reference();
                                $reference->setBook($book)->setChapter($matches[2][$i])->setVerse(
                                    $matches[3][$i]
                                );
                                $reference->setIsBibleRef(1);
                                $reference->generatUrl();
                                $em->persist($reference);
                            }

                            /** @var PersonReference $personReference */
                            $personReference = $em->getRepository("App:PersonReference")->findOneBy(
                                [
                                    'person' => $person,
                                    'reference' => $reference
                                ]
                            );
                            if (!$personReference) {
                               /// dump($book.' lr not found');
                                $personReference = new PersonReference();
                                $personReference->setType('test');
                                $personReference->setPerson($person);
                                $personReference->setReference($reference);

                                $person->addPersonReference($personReference);
                                $em->persist($personReference);
                                $em->persist($person);
                                $em->flush();
                            } else {

                            }
                        } else {
                            dd($bookName." book unknown");
                        }
                    }

                    $em->persist($person);
                    $em->flush();


                }

            }


            $pageCounter++;
            $nextPage = $xml->xpath(
                '//body//div[@id=\'mw-pages\']//a[contains(@href,\'/bibel/index.php?title=Kategorie:Person&pagefrom\')]'
            );
            //$content = $xml->xpath('body//ul');
            $startName = $nextPage[1]->attributes()['href'];
           // dd($startName);
        }
    }


    public function leechFolks(EntityManager $em)
    {
        //dd("leech protection");
        $startName = '/bibel/index.php?title=Kategorie:Volk&pagefrom=Samariter#mw-pages';
        //$startName ='joha3.html';
        echo "leeching " .$startName . "<br>";

        $startAt = null;//'Aaroniter';
        $url = 'http://www.web-fortbildung.de{name}';

        $pageCounter = 0;

        while($startName != null && $pageCounter < 10) {

            $xml = $this->leechHTML($startName, $url);//

            $content = $xml->xpath(
                '//body//div[@class=\'mw-category-group\']//a[contains(@href,\'/bibel/index.php/\')]'
            );

            foreach ($content as $c) {

                $href = ((string)$c->attributes()['href']);
                $title = ((string)$c->attributes()['title']);
                echo $title."\t";

                if ($startAt !== null) {
                    if ($title !== $startAt) {
                        echo "skipping ".$title."<br>";
                        continue;
                    }
                    $startAt = null;
                }

                $articleXml = $this->leechHTML($href, $url);//

                $articleP = $articleXml->xpath('//body//div[@id=\'mw-content-text\']');

                $description = $articleP[0]->xpath('//p[1]')[0]->asXML();
                $quellen = $articleP[0]->xpath('//p[last()]')[0]->asXML();

                echo $description."\t";

                //dump($matches);
                /** @var Folk $folk */
                $folk = $em->getRepository("App:Folk")->findOneBy(['name' => $title]);
                if (!$folk) {
                    $folk = new Folk();
                    $folk->setName($title);
                    $folk->setApproved(false);
                    $folk->setType("test");
                }
                $folk->setDescription($description);

                $type = "";
                $types = [
                    'Volk' => 'folk',
                    'Stamm'=> 'tribe',
                    'Bewohner'=> 'citizen'
                ];
                foreach ($types as $typeKey => $typeValue) {
                    if (strpos($description, $typeKey) !== false) {
                        $type = $typeValue;
                        break;
                    }
                }
                if ($type == "") {
                    dump($description);
                }
                //dd($type);
                $folk->setType($type);
                preg_match_all('/\(\d+\)\s? (\d?\.?\D+)\s(\d+)\.(\d+)/', $quellen, $matches);
                if (sizeof($matches) > 0) {
                    for ($i = 0; $i < sizeof($matches[0]); $i++) {
                        $bookName = str_replace('z.B.', '', $matches[1][$i]);
                        $bookName = str_replace(' ', '', $bookName);


                        $book = $em->getRepository("App:BibleBooks")->findOneBy(['name' => $bookName]);
                        if ($book) {
                            $reference = $em->getRepository("App:Reference")->findOneBy(
                                [
                                    'book' => $book,
                                    'chapter' => $matches[2][$i],
                                    'verse' => $matches[3][$i]
                                ]
                            );

                            //dump($reference);
                            if ($reference == null) {

                                $reference = new Reference();
                                $reference->setBook($book)->setChapter($matches[2][$i])->setVerse(
                                    $matches[3][$i]
                                );
                                $reference->setIsBibleRef(1);
                                $reference->generatUrl();
                                $em->persist($reference);
                            }

                            /** @var FolkReference $folkReference */
                            $folkReference = $em->getRepository("App:FolkReference")->findOneBy(
                                [
                                    'folk' => $folk,
                                    'reference' => $reference
                                ]
                            );
                            if (!$folkReference) {
                                dump($book.' lr not found');
                                $folkReference = new FolkReference();
                                $folkReference->setType('test');
                                $folkReference->setFolk($folk);
                                $folkReference->setReference($reference);

                                $folk->addFolkReference($folkReference);
                                $em->persist($folkReference);
                                $em->persist($folk);
                                $em->flush();
                            } else {
                                dump('lr found');
                            }
                        } else {
                            dd($bookName." book unknown");
                        }
                    }

                    $em->persist($folk);

                    $em->flush();

                }
            }
        }
    }


    public function leechLocations(EntityManager $em)
    {
        dd("leech protection");
        $startName = '/bibel/index.php?title=Kategorie:Ort#';
        //$startName ='joha3.html';
        echo "leeching " .$startName . "<br>";

        $startAt = 'Zion (Berg)';
        $url = 'http://www.web-fortbildung.de{name}';

        $pageCounter = 0;

        while($startName != null && $pageCounter < 10) {


           // $kapitelVor = $title = $xml->xpath('//tr[2]//td[last()]//tr[1]//td[2]//a/@href');

            $xml = $this->leechHTML($startName, $url);//

            // $content = $xml->xpath('//body//*[@id=\'bodyContent\']//*[@id=\'mw-content-text\']//div[@class=\'mw-category-group\']');
            $content = $xml->xpath(
                '//body//div[@class=\'mw-category-group\']//a[contains(@href,\'/bibel/index.php/\')]'
            );

            foreach ($content as $c) {

                $href = ((string)$c->attributes()['href']);
                $title = ((string)$c->attributes()['title']);
                echo $title."\t";

                if ($startAt !== null) {
                    if ($title !== $startAt) {
                        echo "skipping ".$title."<br>";
                        continue;
                    }
                    $startAt = null;
                }

                $articleXml = $this->leechHTML($href, $url);//

                $articleP = $articleXml->xpath('//body//div[@id=\'mw-content-text\']');

                $description = $articleP[0]->xpath('//p[1]')[0]->asXML();
                $quellen = $articleP[0]->xpath('//p[last()]')[0]->asXML();

                echo $description."\t";

                //dump($matches);
                /** @var Location $location */
                $location = $em->getRepository("App:Location")->findOneBy(['name' => $title]);
                if (!$location) {
                    $location = new Location();
                    $location->setName($title);
                    $location->setApproved(false);
                    $location->setType("test");
                }
                $location->setDescription($description);

                $type = "";
                $types = [
                    'Stadt' => 'town',
                    'Dorf'=>'town',
                    'Wald'=>'forest',
                    'Fluss' => 'river',
                    'Fluß' => 'river',
                    'Bach' => 'river',
                    'Quelle' => 'well',
                    'Brunnen' => 'well',
                    'Wüste' => 'desert',
                    'Land' => 'country',
                    'Fluss' => 'river',
                    'Höhe' => 'mountain',
                    'Hügel' => 'mountain',
                    'Berg' => 'mountain',
                    'Tal' => 'valley',
                    'Gebirge' => 'mountains',
                    'Höhle' => 'cave',
                    'Ebene' => 'area',
                    'Gebiet' => 'area',
                    'Königreich' => 'kingdom',
                    'Tor' => 'gate',
                    'Meer' => 'sea',
                    'Teich' => 'lake',
                    'See' => 'lake',
                    'Festung' => 'castle',
                    'Burg' => 'castle',
                    'Palast' => 'castle',
                    'Insel' => 'island',
                    'Ort' => 'location',

                ];
                foreach ($types as $typeKey => $typeValue) {
                    if (strpos($description, $typeKey) !== false) {
                        $type = $typeValue;
                        break;
                    }
                }
                if ($type == "") {
                    dump($description);
                }
                //dd($type);
                $location->setType($type);
                preg_match_all('/\(\d+\)\s? (\d?\.?\D+)\s(\d+)\.(\d+)/', $quellen, $matches);
                if (sizeof($matches) > 0) {
                    for ($i = 0; $i < sizeof($matches[0]); $i++) {
                        $bookName = str_replace('z.B.', '',$matches[1][$i]);
                        $bookName = str_replace(' ', '',$bookName);


                        $book = $em->getRepository("App:BibleBooks")->findOneBy(['name' =>$bookName ]);
                        if ($book) {
                            $reference = $em->getRepository("App:Reference")->findOneBy(
                                [
                                    'book' => $book,
                                    'chapter' => $matches[2][$i],
                                    'verse' => $matches[3][$i]
                                ]
                            );

                            //dump($reference);
                            if ($reference == null) {

                                $reference = new Reference();
                                $reference->setBook($book)->setChapter($matches[2][$i])->setVerse(
                                    $matches[3][$i]
                                );
                                $reference->setIsBibleRef(1);
                                $reference->generatUrl();
                                $em->persist($reference);
                            }

                            /** @var LocationReference $locationReference */
                            $locationReference = $em->getRepository("App:LocationReference")->findOneBy(
                                [
                                    'location' => $location,
                                    'reference' => $reference
                                ]
                            );
                            if (!$locationReference) {
                                dump($book . ' lr not found');
                                $locationReference = new LocationReference();
                                $locationReference->setType('test');
                                $locationReference->setLocation($location);
                                $locationReference->setReference($reference);

                                $location->addLocationReference($locationReference);
                                $em->persist($locationReference);
                                $em->persist($location);
                                $em->flush();
                            } else {
                                dump('lr found');
                            }
                        } else {
                            dump($bookName . " book unknown");
                        }
                    }

                    $em->persist($location);

                    $em->flush();

                }
            }

            $pageCounter++;
            $nextPage = $xml->xpath(
                '//body//div[@id=\'mw-pages\']//a[contains(@href,\'/bibel/index.php?title=Kategorie:Ort&pagefrom\')]'
            );
            //$content = $xml->xpath('body//ul');
            $startName = $nextPage[1]->attributes()['href'];
        }



dd();
            $title = $xml->xpath('//tr[4]//td[last()]//text()');


            $lastVerse = $xml->xpath('//tr[5]//td//a[last()]/text()');

            echo ((string)$title[0]) .";". ((string)$lastVerse[0]) ."<br>";
            flush();
            ob_flush();

            $startName = (string) $kapitelVor[0];

            $counter++;
       // }
    }
    public function leech() {

        //leechChapters();
      //  $this->leechLocations();
        /*
        $startName = '1mos1.html';
        //$startName ='joha3.html';
        echo "leeching " .$startName . "<br>";


        /*$counter = 0;
        while($startName != null && $counter < 1000) {
            $xml = $this->leechHTML($startName);//

            $kapitelVor = $title = $xml->xpath('//tr[2]//td[last()]//tr[1]//td[2]//a/@href');
         //   dump((string)$kapitelVor[0]);

            $title = $xml->xpath('//tr[4]//td[last()]//text()');


            $lastVerse = $xml->xpath('//tr[5]//td//a[last()]/text()');

            echo ((string)$title[0]) .";". ((string)$lastVerse[0]) ."<br>";
            flush();
            ob_flush();

            $startName = (string) $kapitelVor[0];

            $counter++;
        }

        dd();
        $found = false;
        $iRow = 0;
        $maxTd = 0;
        $generation = 0;*/
    }
    protected function leechHTML($id, $url)
    {



        //$url = str_replace('{name}', urlencode($id), $url);
        $url = str_replace('{name}', $id, $url);
//dump($url);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $html = curl_exec($ch);

        curl_close($ch);

        $_dom = new \DOMDocument("1.0");
        @$_dom->loadHTML(str_replace('&nbsp;', '', $html) );
        $xml = simplexml_import_dom($_dom);

        return $xml;
    }
}
