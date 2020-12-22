<?php

namespace App\Validator;

use http\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UncertainNumberValidator extends ConstraintValidator
{

    public function __construct()
    {

    }

    public function validate($valueString, Constraint $constraint)
    {
        if (null === $valueString || '' === $valueString) {
            return;
        }
        if (!is_string($valueString)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new \Symfony\Component\Validator\Exception\UnexpectedValueException($valueString, 'string');

            // separate multiple types using pipes
            // throw new UnexpectedValueException($value, 'string|int');
        }

        if (!$this->getValue($valueString)) {
            // the argument must be a string or an object implementing __toString()
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $valueString)
                ->addViolation();
        }
    }

    public function getValue(?string $value) : ?int
    {
        if (preg_match(
            '/^(([\+\-]?\??)\s*(\d{1,4})\s*(\??)\s*$|\s*(\d{1,4})\s*(vor|nach|a\.?|b\.?|n\.?|v\.?)\s?(d\.?|ce?\.?|chr\.?)\s*(\??)\s*$)/',
            strtolower($value),$matches)) {
            $result = $matches[3];
            if (sizeof($matches) > 5) {
                if ($matches[6] !== "") {
                    $result = $matches[5];
                    if (in_array($matches[6], ['b', 'v', 'b.', 'v.'])) {
                        $result *= -1;
                    }
                }
            } else {
                if ($matches[2] == "-") {
                    $result *= -1;
                }
            }
            return $result;
        }
        return null;
    }

    public function isUncertain(?string $value) : bool {
        return strpos($value, '?') !== false;
    }
}
