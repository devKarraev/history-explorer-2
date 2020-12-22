<?php

namespace App\Form;

use App\Form\DataTransformer\ReferenceTransformer;
use App\Repository\BibleBooksRepository;
use App\Repository\ReferenceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class ReferenceSelectTextType extends AbstractType
{
    private $bibleBooksRepository;
    private $router;

    public function __construct(BibleBooksRepository $bibleBooksRepository, RouterInterface $router)
    {
        $this->bibleBooksRepository = $bibleBooksRepository;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ReferenceTransformer(
            $this->bibleBooksRepository,
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
            'invalid_message' => 'Hmm, reference not found!',
            'finder_callback' => function(BibleBooksRepository $bibleBooksRepository, string $name) {
                return $bibleBooksRepository->findByName($name);
            },
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $attr = $view->vars['attr'];
        $class = isset($attr['class']) ? $attr['class'] . ' ' : '';
        $class .= 'js-reference-autocomplete';
        $attr['class'] = $class;
        $attr['data-autocomplete-url'] = $this->router->generate('books_utility');
        $view->vars['attr'] = $attr;

    }


}