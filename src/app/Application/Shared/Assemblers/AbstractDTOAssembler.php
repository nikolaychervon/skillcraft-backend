<?php

namespace App\Application\Shared\Assemblers;

use App\Application\Shared\DTO\BaseDTO;
use App\Application\Shared\Exceptions\DTO\DTOAssemblyException;
use Illuminate\Support\Str;

/** @template DTO of BaseDTO */
abstract class AbstractDTOAssembler
{
    /**
     * Создаёт DTO из массива данных.
     *
     * @param array $data
     * @return DTO
     */
    public function assemble(array $data): BaseDTO
    {
        // кастомная валидация бизнес-правил (опционально)
        $this->validate($data);
        return $this->assembleDto($data);
    }

    /**
     * Основная сборка DTO через reflection.
     *
     * @param array $data
     * @return BaseDTO
     *
     */
    protected function assembleDto(array $data): BaseDTO
    {
        try {
            $dtoClass = $this->getDtoClass();
            $reflection = new \ReflectionClass($dtoClass);

            $constructor = $reflection->getConstructor();
            $dtoData = [];

            foreach ($constructor->getParameters() as $param) {
                $name = $param->getName();

                $candidates = [
                    $name,
                    Str::snake($name),
                ];

                $found = false;

                foreach ($candidates as $key) {
                    if (array_key_exists($key, $data)) {
                        $dtoData[$name] = $data[$key];
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    if ($param->isOptional()) {
                        $dtoData[$name] = $param->getDefaultValue();
                        continue;
                    }

                    throw DTOAssemblyException::missingField($name, $dtoClass);
                }
            }

            /** @var BaseDTO $dto */
            $dto = $reflection->newInstanceArgs($dtoData);
            return $dto;

        } catch (\ReflectionException $e) {
            throw DTOAssemblyException::dtoClassNotFound(
                $this->getDtoClass(),
                $e
            );
        }
    }

    /**
     * Должен вернуть класс DTO
     *
     * @return class-string<BaseDTO>
     */
    abstract protected function getDtoClass(): string;

    /**
     * Кастомная валидация бизнес-правил перед сборкой DTO
     *
     * @param array $data
     * @return void
     */
    protected function validate(array $data): void
    {
        #
    }
}
