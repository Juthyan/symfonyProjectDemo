<?php

declare(strict_types=1);

namespace App\DTO\DtoResolver;

use App\DTO\UserDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserDtoResolver implements ValueResolverInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return UserDto::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $dto = $this->serializer->deserialize($request->getContent(), UserDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            throw new BadRequestHttpException(json_encode($messages));
        }

        yield $dto;
    }
}
