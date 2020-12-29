<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PersonFormAgeValidator extends ConstraintValidator
{
    /**
     * Validate person age by 'mother' and 'father' birth day and death day.
     *
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\PersonFormAge */

        if (null === $value || '' === $value) {
            return;
        }

        $form = $this->context->getRoot();

        $father = $form->get('father')->getData();
        $mother = $form->get('mother')->getData();

        if (!$father && !$mother) {
            return;
        }

        $parentsData = [
            'father' => ['borned' => null, 'died' => null],
            'mother' => ['borned' => null, 'died' => null]
        ];

        if ($father !== null) {
            $parentsData['father']['borned'] = $father->getBorn(true, true);
            $parentsData['father']['died'] = $father->getDied(true, true);
        }

        if ($mother !== null) {
            $parentsData['mother']['borned'] = $mother->getBorn(true, true);
            $parentsData['mother']['died'] = $mother->getDied(true, true);
        }

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
