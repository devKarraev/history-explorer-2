<?php


namespace App\Form;


use App\Entity\Event;
use App\Entity\Location;
use App\Repository\UserRepository;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Image;

class EventFormType extends AbstractType
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * EventFormType constructor.
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event = $options['data'] ?? null;

        $isEdit = $event && $event->getId();

        $formattedEstimatedTime = null;
        $guessedTime = null;

        if ($isEdit) {
            $formattedEstimatedTime = $event->getYear(false, false, true);
            $guessedTime = $event->getYear(true, true, true);
            $event->setUncertainTime($formattedEstimatedTime);

            $after = $event->getHappenedAfter();
            $before = $event->getHappenedBefore();
        } else {
            $after = $options['after'] ?? null;
            $before = $options['before'] ?? null;
        }
        $builder
            ->add('name', TextType::class)
            ->add(
                'happenedAfter',
                EntityType::class,
                [
                    'required' => true,
                    'label' => 'Happened directly after..',
                    'class' => Event::class,
                    'choice_attr' => function($choice, $key, $value) use ($event){
                       return ($event && $event->getId() == $value) ? ['class' => 'd-none'] : [];
                    },
                    'data' => $after,

                ]
            )
            ->add(
                'happenedBefore',
                EntityType::class,
                [
                    'required' => true,
                    'label' => 'Happened directly before..',
                    'class' => Event::class,
                    'choice_attr' => function($choice, $key, $value) use ($event){
                        return ($event && $event->getId() == $value) ? ['class' => 'd-none'] : [];

                    },
                   'data' => $before,
                ]
            )
             ->add(
                'uncertainTime',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'year',
                    //'($person && !$person->getDied() && $formattedEstimatedDeath) ? 'Year of Death (current estimation: '. $formattedEstimatedDeath .')' :'Year of Death',
                    'help' => 'e.g. -1000? for aprox. 1000 years BC',
                    'attr' => ['placeholder' => ($event && !$formattedEstimatedTime && $guessedTime) ? 'Estimation: '.$guessedTime : 'Year of event, e.g. 150 BC?']
                ]
            )
            ->add(
                'location',
                EntityType::class,
                [
                    'label' => 'Location',
                    'required' => false,
                    'class' => Location::class,
                ]
            )
            ->add(
                'hide',
                CheckboxType::class,
                [
                    'label' => 'Support Point (don\'t show)',
                    'required' => true,
                ]
            )
            ->add(
                'relativeTime',
                TextType::class,
                [
                    'label' => 'Relative time' ,
                    'required' => false,
                    'help' => 'e.g. -2.5: happened ~2.5 years before next event',
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

            /*$builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                    function (FormEvent $event) {
                    /** @var Event|null $data * /
                        $data = $event->getData();
                        if(!$data) {
                            return;
                        }
                        $this->getNextEvent(
                            $event->getForm(),
                            $data->getHappenedAfter(),
                            true
                        );
                    });*/


           /* $builder->get('happenedBefore')->addEventListener(
               FormEvents::POST_SUBMIT,
               function (FormEvent $event) {
                   $form = $event->getForm();
                   /*$this->getNextEvent(
                       $form->getParent(),
                       $form->getData()
                   );* /
                   dd($form);
               }
           );
           $builder->get('happenedAfter')->addEventListener(
               FormEvents::POST_SUBMIT,
               function (FormEvent $event) {
                   $form = $event->getForm();

                   $this->getNextEvent(
                       $form->getParent(),
                       $form->getData()
                   );
               }
           );*/
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'include_x' => false,
            'before' => null,
            'after' => null,
        ]);
    }
}
