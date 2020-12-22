<?php

namespace App\Form;

use App\Form\DataTransformer\PersonTransformer;
use App\Repository\PersonRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class PersonSelectTextType extends AbstractType
{
    private $personRepository;
    private $router;

    public function __construct(PersonRepository $personRepository, RouterInterface $router)
    {
        $this->personRepository = $personRepository;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new PersonTransformer(
            $this->personRepository,
            $options['finder_callback']
        ));
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults([
            'invalid_message' => 'Hmm, person not found!',
            'finder_callback' => function(PersonRepository $personRepository, string $name) {
                return $personRepository->findOneBy(['name' => $name]);
            },/*
            'attr' => [
                'class' => 'js-person-autocomplete',
                'data-autocomplete-url' => $this->router->generate('person_utility_fathers')
            ]*/
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $attr = $view->vars['attr'];
        $class = isset($attr['class']) ? $attr['class'] . ' ' : '';
        $class .= 'js-person-autocomplete';
        $attr['class'] = $class;
        $attr['data-autocomplete-url'] = $this->router->generate('person_utility_fathers');
      //  $attr['data-autocomplete-url'] = $this->router->generate('person_utility_mothers');
        //$attr['data-autocomplete-url-children'] = $this->router->generate('person_utility_children');
        $view->vars['attr'] = $attr;
    }


}