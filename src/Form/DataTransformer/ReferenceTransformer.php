<?php


namespace App\Form\DataTransformer;


use App\Entity\Reference;
use App\Repository\BibleBooksRepository;
use App\Repository\ReferenceRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ReferenceTransformer implements DataTransformerInterface
{

    /**
     * @var BibleBooksRepository
     */
    private $bibleBooksRepository;
    /**
     * @var callable
     */
    private $finderCallback;

    /**
     * ChildrenTransformer constructor.
     */
    public function __construct(BibleBooksRepository $bibleBooksRepository, callable $finderCallback)
    {
        $this->bibleBooksRepository = $bibleBooksRepository;
        $this->finderCallback = $finderCallback;
    }

    public function transform($value)
    {
       if (null === $value) {
           return '';
       }
       if (!$value instanceof Reference) {
             throw new \LogicException('The ReferenceSelectTextType can only be used with Reference
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
         $reference = $callback($this->bibleBooksRepository, $value);
         if (!$reference) {
             throw new TransformationFailedException(sprintf('No user found with name "%s"', $value));
         }

         return $reference;
    }
}