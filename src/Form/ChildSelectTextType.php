<?php

namespace App\Form;

use App\Form\DataTransformer\ChildrenTransformer;
use App\Repository\PersonRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChildSelectTextType extends AbstractType
{
    private $personRepository;

    public function __construct(PersonRepository $personRepository)
    {
        $this->personRepository = $personRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ChildrenTransformer($this->personRepository
       // ,$options['finder_callback'])
        ));

    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'invalid_message' => 'Hmm, child not found!',
            /*'finder_callback' => function(PersonRepository $personRepository, string $name) {
                return $personRepository->findOneBy(['name' => $name]);
            },*/
        ]);
    }

}