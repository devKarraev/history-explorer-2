<?php


namespace App\Form;


use App\Entity\Location;
use App\Repository\UserRepository;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;


class LocationFormType extends AbstractType
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * PersonFormType constructor.
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Person $location */
        $location = $options['data'] ?? null;

        $isEdit = $location && $location->getId();

        // dd(($person && $person->getBorn() == null && $estimatedBirth != null));
        $builder
            ->add('name', TextType::class)
            ->add(
                'todayKnownAs',
                EntityType::class,
                [
                    'required' => false,
                    'label' => '',
                    'class' => Location::class,
                    'placeholder' => 'select today\'s name',
                ]
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => 'Type',
                    'choices' => [
                        'Area' => 'area',
                        'Castle' => 'castle',
                        'Cave' => 'cave',
                        'Country' => 'country',
                        'Desert' => 'desert',
                        'Forest' => 'forest',
                        'Gate' => 'gate',
                        'Island' => 'island',
                        'Kingdom' => 'kingdom',
                        'Lake' => 'lake',
                        'Location' => 'location',
                        'Mountain' => 'mountain',
                        'Mountains' => 'mountains',
                        'River' => 'river',
                        'Sea' => 'sea',
                        'Town' => 'town',
                        'well' => 'well',
                        'Valley' => 'valley',
                    ],
                    'empty_data' => 'etc',
                    'invalid_message' => 'Symfony is too smart for your hacking!',
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    //'mapped' => false,
                    'label' => 'Description',
                    //($person && !$person->getAge() && $estimatedAge) ? ' Age (current estimation: '. $estimatedAge .')' : 'Age',
                    'required' => false,
                    'help' => 'Type something interesting',
                ]
            )
            ->add(
                'lat',
                NumberType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'lon',
                NumberType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'imageFile',
                FileType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'invalid_message' => 'Symfony is too smart for your hacking!',
                    'constraints' => [
                        new Image(
                            [
                                'maxSize' => '2M'
                            ]
                        ),
                        //new NotNull()
                    ]
                ]
            );

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

        /* if ($options['include_x']) {
             /*$builder->add(). ...
               */

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
            'include_x' => false,
        ]);
    }
}
