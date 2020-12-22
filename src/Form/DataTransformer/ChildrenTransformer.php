<?php


namespace App\Form\DataTransformer;


use App\Repository\PersonRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ChildrenTransformer implements DataTransformerInterface
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
      /*  dump('transform');
dd($value);
 	if (null === $value) {
            return '';
        }

        if (!$value instanceof Person) {
            throw new \LogicException('The ChildrenSelectTextType can only be used with Person
	    objects');
        }

        return $value->getChildren();*/
    }

    public function reverseTransform($value)
    {
       /* dd($value);
        if(!$value) {
            return;
        }
        $callback = $this->finderCallback;
        $children = $callback($this->personRepository, $value);
        if (!$user) {
            throw new TransformationFailedException(sprintf('No user found with email "%s"', $value));
        }

        return $user;*/
    }
}