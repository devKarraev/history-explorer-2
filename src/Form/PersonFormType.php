<?php


namespace App\Form;


use App\Entity\Folk;
use App\Entity\Person;

use App\Entity\User;
use App\Form\Model\PersonFormModel;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use App\Validator\UncertainNumber;
use Doctrine\ORM\Mapping\UniqueConstraint;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class PersonFormType extends AbstractType
{
    /**
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * PersonFormType constructor.
     */
    public function __construct(PersonRepository $personRepository)
    {
        $this->personRepository = $personRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Person $person */
        $person = $options['data'] ?? null;

        $user = $options['user'];

        $isEdit = $person && $person->getId();

        $formattedEstimatedBirth = null;
        $formattedEstimatedDeath = null;
        $formattedEstimatedAge = null;
        $guessedBirth = null;
        $guessedDeath = null;
        $guessedAge = null;
        $relatedPerson = null;

        if ($isEdit) {
            $formattedEstimatedBirth = $person->getBorn(true, false, true);
            $formattedEstimatedDeath = $person->getDied(true, false, true);
            $formattedEstimatedAge = $person->getAge(true, false, true);

            $guessedBirth = $person->getBorn(true, true);
            $guessedDeath = $person->getDied(true, true);
            $guessedAge = $person->getAge(true, true);

            $person->setUncertainBorn($formattedEstimatedBirth);
            $person->setUncertainDied($formattedEstimatedDeath);
        }
        $builder
            ->add('name', TextType::class)
            ->add('alternateNames', TextType::class, [
                'label' => 'Alternate Names',
                'required'   => false,
                ])
            ->add('gender', ChoiceType::class, [
                'label' => false,
                'choices'  => [
                    'Mann' => 'm',
                    'Frau' => 'w',
                    '-' => null,
                    ],
                'empty_data' => 'm',
                'invalid_message' => 'Symfony is too smart for your hacking!',
                //'placeholder' => 'Choose a gender',
                //'preferred_choices' => ['m'],
                'disabled' => $isEdit,
                ])
            ->add('uncertainBorn',TextType::class, [
                'required'  => false,
                'label' => 'Born',//($person && !$person->getBorn()  && $formattedEstimatedBirth) ? 'Year of Birth (current estimation: '. $formattedEstimatedBirth .')' : 'Year of Birth',
                'help' => 'year of birth , e.g. -1000 for for 1000 years BC',
                'attr' => ['placeholder' => ($person && !$formattedEstimatedBirth  && $guessedBirth) ? 'Estimation: '. $guessedBirth : 'Year of Birth, e.g. 100 BC']

            ])
            ->add('age', NumberType::class, [
                //'mapped' => false,
                'label' => 'Age',//($person && !$person->getAge() && $formattedEstimatedAge) ? ' Age (current estimation: '. $formattedEstimatedAge .')' : 'Age',
                'required'   => false,
                'help' => 'If you don\'t know year of death, perhaps the age?',
                'attr' => ['placeholder' => ($person && !$formattedEstimatedAge && $guessedAge) ? 'Estimation: '. $guessedAge : 'Age']
            ])
            ->add('uncertainDied', TextType::class, [
                'required'   => false,
                'label' => 'Died',//'($person && !$person->getDied() && $formattedEstimatedDeath) ? 'Year of Death (current estimation: '. $formattedEstimatedDeath .')' :'Year of Death',
                'help' => 'year of death, e.g. -1000 for for 1000 years BC',
                'attr' => ['placeholder' => ($person && !$formattedEstimatedDeath && $guessedDeath) ? 'Estimation: '. $guessedDeath :'Year of Death, e.g. 150 BC']

            ])
            ->add('livedAtTimeOfPerson', EntityType::class, [
                'required'   => false,
                'class' => Person::class,
                'placeholder' => 'lived at times of..',
                'help' => 'if you don\'t have any clue of life dates, select another person that lived in his/her time!'
            ])

          ->add('father', EntityType::class, [
              'required'   => false,
              'class' => Person::class,
              'empty_data' => null,
              'query_builder' => function (PersonRepository $p) {
                  return $p->createQueryBuilder('p')
                      ->andWhere('p.gender IN(\'m\', \'\')')
                      ->orderBy('p.name', 'ASC');},
              'invalid_message' => 'Person not known. Create a new father before'
          ])
            ->add('mother', EntityType::class, [
                'required'   => false,
                'class' => Person::class,
                'empty_data' => null,
                //'data' => $this->personRepository->getIdByGender($user, 'w', true),
                'query_builder' => function (PersonRepository $p) {
                    return $p->createQueryBuilder('p')
                        ->andWhere('p.gender IN(\'w\', \'\')')
                        ->orderBy('p.name', 'ASC');},
                'invalid_message' => 'Person not known. Create a new mother before'
            ])
            ->add('folk', EntityType::class, [
                'required'   => false,
                'class' => Folk::class,
                'empty_data' => null,
                'invalid_message' => 'Folk not known. Create a new folk before'
            ])
            ->add('progenitor', EntityType::class, [
                'required'   => false,
                'class' => Folk::class,
                'empty_data' => null,
                'multiple' => true,
                'invalid_message' => 'Folk not known. Create a new folk before'
            ])
            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'invalid_message' => 'Symfony is too smart for your hacking!',
                'constraints' => [
                    new Image([
                        'maxSize'=> '2M'
                    ]),
                    //new NotNull()
                ]
            ]);

            /*->add('reference', ReferenceSelectTextType::class, [
            'label' => 'Add new Reference',
          //      'class' => Reference::class,
                'mapped' => false,
                'empty_data' => null,
          //'placeholder' => 'e.g. 1.Mose 2,5',
         'required' => false,]
        )*/
/*
        $builder->addEventListener(

            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var Person|null $data * /
                $data = $event->getData();
                if (!$data) {
                    return;
                }

              /*  $this->setupSpecificLocationNameField(
                    $event->getForm(),
                    $data->getLocation()
                );* /
            }
        );

       /* $builder->get('reference')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) {
                $form = $event->getForm();
                dd("x");
                $this->setupSpecificLocationNameField(
                    $form->getParent(),
                    $form->getData()
                );
            }
        );*/

           /* ->add('referenceList', ReferenceSelectTextType::class, [
                'required'   => false,
                'mapped' => false,
                'label' => 'Reference',
                //'multiple' => true,
               // 'class' => Reference::class,
                'empty_data' => null,
            ]);*/
           /* ->add('children', ChildSelectTextType::class, [
                'multiple'  => true,
                'mapped' => false
            ])*/

            /*if ($options['include_x']) {
                /*$builder->add(). ...
                  */

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
           // 'data_class' => PersonFormModel::class,
           // 'include_x' => false,
            'user' => null,
        ]);


    }
}