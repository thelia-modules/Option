<?php

namespace Option\Model\Api;

use OpenApi\Annotations as OA;
use OpenApi\Model\Api\BaseApiModel;

/**
 * @OA\Schema(
 *     schema="Option",
 *     title="Option",
 *     description="Option model"
 * )
 */
class Option extends BaseApiModel
{
    /**
     * @OA\Property(
     *    type="integer"
     * )
     */
    protected int $id;

    /**
     * @OA\Property(
     *    type="string"
     * )
     */
    protected string $title;

    /**
     * @OA\Property(
     *    type="string"
     * )
     */
    protected string $code;

    /**
     * @OA\Property(
     *    type="float"
     * )
     */
    protected float $price;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}