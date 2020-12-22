<?php


namespace App\Form\DataTransformer;


use App\Entity\Person;
use App\Repository\PersonRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PersonTransformer implements DataTransformerInterface
{

    /**
     * @var PersonRepository
     */
    private $personRepository;
    /**
     * @var callable
     */
    private $finderCallback;

    /**
     * ChildrenTransformer constructor.
     */
    public function __construct(PersonRepository $personRepository, callable $finderCallback)
    {

        $this->personRepository = $personRepository;
        $this->finderCallback = $finderCallback;
    }

    public function transform($value)
    {
       if (null === $value) {
           return '';
       }
       if (!$value instanceof Person) {
             throw new \LogicException('The ParentSelectTextType can only be used with Person
          objects');
       }

       return $value->getName();
    }

    public function reverseTransform($value)
    {
         if(!$value) {
             return;
         }
         $callback = $this->finderCallback;
         $person = $callback($this->personRepository, $value);
         if (!$person) {
             throw new TransformationFailedException(sprintf('No user found with name "%s"', $value));
         }

         return $person;
    }
}