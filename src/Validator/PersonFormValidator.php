<?php

namespace App\Validator;

use App\Entity\Person;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PersonFormValidator extends ConstraintValidator
{

    public function validate($value, Constraint $constraint)
    {
        $form = $this->context->getRoot();

        $father = $form->get('father')->getData();
        $mother = $form->get('mother')->getData();

        if (!$father && !$mother) {
            return;
        }

        $parentsData = [
            'father' => [
                'borned' => $father === null ? null : $father->getBorn(true, true),
                'died' => $father === null ? null : $father->getDied(true, true)
            ],
            'mother' => [
                'borned' => $mother === null ? null : $mother->getBorn(true, true),
                'died' => $mother === null ? null : $mother->getDied(true, true)
            ]
        ];

        $isValid = true;

        foreach ($parentsData as $parent) {
            if ($parent['borned'] == null) continue;

            if ($value - $parent['borned'] > 15 && $value < $parent['died']) {
                continue;
            } else {
                $isValid = false;
                break;
            }
        }

        if ($isValid) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
