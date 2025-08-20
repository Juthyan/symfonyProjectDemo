<?php

declare(strict_types=1);

namespace App\Tests\DTO\DtoResolver;

use App\DTO\DtoResolver\UserDtoResolver;
use App\DTO\UserDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserDtoResolverTest extends TestCase
{
    private $serializerMock;
    private $validatorMock;
    private UserDtoResolver $resolver;

    protected function setUp(): void
    {
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->resolver = new UserDtoResolver(
            $this->serializerMock,
            $this->validatorMock,
        );
    }

    public function testSupportsReturnsTrueForUserDto(): void
    {
        $argument = new ArgumentMetadata('user', UserDto::class, false, false, null);

        $request = new Request();

        $this->assertTrue($this->resolver->supports($request, $argument));
    }

    public function testSupportsReturnsFalseForOtherTypes(): void
    {
        $argument = new ArgumentMetadata('other', 'SomeOtherClass', false, false, null);

        $request = new Request();

        $this->assertFalse($this->resolver->supports($request, $argument));
    }

    public function testResolveYieldsDtoWhenValid(): void
    {
        $jsonContent = '{"userName":"john_doe","mail":"john@example.com"}';

        $request = new Request([], [], [], [], [], [], $jsonContent);

        $dto = new UserDto('john_doe', 'john@example.com');

        $this->serializerMock
            ->expects($this->once())
            ->method('deserialize')
            ->with($jsonContent, UserDto::class, 'json')
            ->willReturn($dto);

        // No validation errors
        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($dto)
            ->willReturn(new ConstraintViolationList());

        $argument = new ArgumentMetadata('user', UserDto::class, false, false, null);

        $result = $this->resolver->resolve($request, $argument);

        $this->assertInstanceOf(\Generator::class, $result);
        $this->assertSame($dto, $result->current());
    }

    public function testResolveThrowsExceptionOnValidationErrors(): void
    {
        $jsonContent = '{"userName":"jo","mail":"invalid-email"}';

        $request = new Request([], [], [], [], [], [], $jsonContent);

        $dto = new UserDto('jo', 'invalid-email');

        $this->serializerMock
            ->expects($this->once())
            ->method('deserialize')
            ->with($jsonContent, UserDto::class, 'json')
            ->willReturn($dto);

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Username must be at least 3 characters.', '', [], '', 'userName', 'jo'),
            new ConstraintViolation('The email invalid-email is not valid.', '', [], '', 'mail', 'invalid-email'),
        ]);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($dto)
            ->willReturn($violations);

        $argument = new ArgumentMetadata('user', UserDto::class, false, false, null);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage(json_encode([
            'userName' => 'Username must be at least 3 characters.',
            'mail' => 'The email invalid-email is not valid.'
        ]));

        iterator_to_array($this->resolver->resolve($request, $argument));
    }
}
