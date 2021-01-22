<?php

namespace App\Logic;

use App\Entity\Event;
use App\Entity\Person;
use App\Form\EventFormType;
use App\Form\PersonFormType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

class AdminChanges
{
    private const CHANGES_INFO = [
        'event' => [
            'formType' => EventFormType::class,
            'repository' => Event::class,
            'formOptions' => [],
            'templateView' => 'admin_changes/event-edit.html.twig'
        ],
        'person' => [
            'formType' => PersonFormType::class,
            'formOptions' => ['user' => ''],
            'repository' => Person::class,
            'templateView' => 'admin_changes/person-edit.html.twig'
        ]
    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * AdminChanges constructor.
     *
     * @param EntityManagerInterface $em
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $em;
    }

    /**
     * @param $type
     * @param $entity
     * @param $user
     * @return mixed
     */
    public function getFormType($type, $entity, $user)
    {
        $options = $this->setFormOptions(self::CHANGES_INFO[$type]['formOptions'], $user);
        return $this->container->get('form.factory')->create(self::CHANGES_INFO[$type]['formType'], $entity, $options);
    }

    /**
     * @param $options
     * @param $user
     * @return mixed
     */
    private function setFormOptions($options, $user)
    {
        if (!empty($options)) {
            $options['user'] = $user;
        }
        return $options;
    }

    /**
     * @param $type
     * @param $templateOptions
     * @param $q
     * @param $user
     * @param $entity
     * @return mixed
     */
    public function setTemplateOptions($type, $templateOptions, $q, $user, $entity)
    {
        if ($type === 'person') {
            $this->setPersonTemplateOptions($templateOptions, $q, $user, $entity);
        } elseif ($type === 'event') {
            $this->setEventTemplateOptions($templateOptions, $q, $user, $entity);
        }
        return $templateOptions;
    }

    /**
     * @param $templateOptions
     * @param $q
     * @param $user
     * @param $entity
     * @return mixed
     */
    private function setPersonTemplateOptions(&$templateOptions, $q, $user, $entity)
    {
        $addChildren = [];
        if(strlen($q) > 0) {
            $addChildren = $this->em->getRepository(Person::class)->findAllPossibleChildren($user, $entity, $q);
        }
        $templateOptions['addchildren'] = $addChildren;
        $templateOptions['person'] = $entity;
        return $templateOptions;
    }

    /**
     * @param $templateOptions
     * @param $q
     * @param $user
     * @param $entity
     * @return mixed
     */
    private function setEventTemplateOptions(&$templateOptions, $q, $user, $entity)
    {
        $addPersons = [];
        $addPersons = $this->em->getRepository(Person::class)->findAllPossibleEventPeople($user, $entity, $q, 100);
        $templateOptions['event'] = $entity;
        $templateOptions['addpersons'] = $addPersons;
        return $templateOptions;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getTemplateView($type)
    {
        return self::CHANGES_INFO[$type]['templateView'];
    }

    /**
     * @param $id
     * @param $type
     * @return object|null
     */
    public function getEntityOfType($id, $type)
    {
        return $this->em->getRepository(self::CHANGES_INFO[$type]['repository'])->find($id);
    }
}
