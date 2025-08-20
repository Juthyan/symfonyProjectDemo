<?php

declare(strict_types=1);

namespace App\Tests\DTO\DtoResolver;

use App\DTO\BoardDto;
use App\DTO\DtoResolver\BoardDtoResolver;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BoardDtoResolverTest extends MockeryTestCase
{
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private BoardDtoResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = \Mockery::mock(SerializerInterface::class);
        $this->validator = \Mockery::mock(ValidatorInterface::class);

        $this->resolver = new BoardDtoResolver($this->serializer, $this->validator);
    }

    public function testSupportsReturnsTrueForBoardDto(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata('boardDto', BoardDto::class, false, false, null);

        $this->assertTrue($this->resolver->supports($request, $argument));
    }

    public function testSupportsReturnsFalseForOtherClass(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata('other', \stdClass::class, false, false, null);

        $this->assertFalse($this->resolver->supports($request, $argument));
    }

    public function testResolveYieldsValidDto(): void
    {
        $jsonContent = '{"name": "My Board", "userRoleIds": [1, 2]}';
        $request = new Request([], [], [], [], [], [], $jsonContent);
        $argument = new ArgumentMetadata('boardDto', BoardDto::class, false, false, null);

        $dto = new BoardDto('My Board', [1, 2]);

        $this->serializer
            ->expects('deserialize')
            ->once()
            ->with($jsonContent, BoardDto::class, 'json')
            ->andReturn($dto);

        $this->validator
            ->expects('validate')
            ->once()
            ->with($dto)
            ->andReturn(new ConstraintViolationList());

        $result = $this->resolver->resolve($request, $argument);
        $this->assertInstanceOf(\Generator::class, $result);

        $this->assertSame($dto, iterator_to_array($result)[0]);
    }

    public function testResolveThrowsOnValidationErrors(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class);

        $jsonContent = '{"name": "", "userRoleIds": [1]}';
        $request = new Request([], [], [], [], [], [], $jsonContent);
        $argument = new ArgumentMetadata('boardDto', BoardDto::class, false, false, null);

        $dto = new BoardDto('', [1]);

        $this->serializer
            ->expects('deserialize')
            ->once()
            ->with($jsonContent, BoardDto::class, 'json')
            ->andReturn($dto);

        $violationList = new ConstraintViolationList([
            new \Symfony\Component\Validator\ConstraintViolation(
                'This value should not be blank.',
                null,
                [],
                '',
                'name',
                ''
            ),
        ]);

        $this->validator
            ->expects('validate')
            ->once()
            ->with($dto)
            ->andReturn($violationList);

        iterator_to_array($this->resolver->resolve($request, $argument));
    }
}
