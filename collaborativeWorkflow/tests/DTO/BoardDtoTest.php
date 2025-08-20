<?php

declare(strict_types=1);

namespace App\Tests\DTO;

use App\DTO\BoardDto;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BoardDtoTest extends MockeryTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidBoardDto(): void
    {
        $dto = new BoardDto('Project Board', [1, 2]);

        $errors = $this->validator->validate($dto);

        $this->assertCount(0, $errors);
    }

    public function testBlankName(): void
    {
        $dto = new BoardDto('', [1]);

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertSame('This value should not be blank.', $errors[0]->getMessage());
    }

    public function testTooLongName(): void
    {
        $dto = new BoardDto(str_repeat('a', 256), []);

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertSame('This value is too long. It should have 255 characters or less.', $errors[0]->getMessage());
    }

    public function testInvalidUserRoleIds(): void
    {
        $dto = new BoardDto('Valid Name', [1, 'wrong', 3]);

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertSame('This value should be of type integer.', $errors[0]->getMessage());
    }
}
