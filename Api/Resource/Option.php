<?php

namespace Option\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Option\Api\State\OptionProductProvider;
use Propel\Runtime\Map\TableMap;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Api\Resource\AbstractTranslatableResource;
use Thelia\Api\Resource\I18nCollection;
use Thelia\Api\Resource\Product;
use Thelia\Model\Map\ProductTableMap;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/admin/options'
        ),
        new Get(
            uriTemplate: '/admin/options/{id}',
            normalizationContext: ['groups' => [self::GROUP_ADMIN_READ, self::GROUP_ADMIN_READ_SINGLE]]
        )
    ],
    normalizationContext: ['groups' => [self::GROUP_ADMIN_READ]],
    denormalizationContext: ['groups' => [self::GROUP_ADMIN_WRITE]],
    provider: OptionProductProvider::class
)]
class Option extends AbstractTranslatableResource
{
    public const GROUP_ADMIN_READ = 'admin:option:read';
    public const GROUP_ADMIN_READ_SINGLE = 'admin:option:read:single';
    public const GROUP_ADMIN_WRITE = 'admin:option:write';

    public const GROUP_FRONT_READ = 'front:option:read';
    public const GROUP_FRONT_READ_SINGLE = 'front:option:read:single';

    #[Groups([self::GROUP_ADMIN_READ])]
    public string $id;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_ADMIN_WRITE])]
    #[NotBlank(groups: [self::GROUP_ADMIN_WRITE])]
    public string $ref;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_ADMIN_WRITE])]
    #[NotBlank(groups: [self::GROUP_ADMIN_WRITE])]
    public int $taxRuleId;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_ADMIN_WRITE])]
    public I18nCollection $i18ns;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_ADMIN_WRITE])]
    #[NotBlank(groups: [Product::GROUP_ADMIN_WRITE])]
    public float $price;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_ADMIN_WRITE])]
    #[NotBlank(groups: [Product::GROUP_ADMIN_WRITE])]
    public float $promoPrice;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_ADMIN_WRITE])]
    public bool $promo = false;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_ADMIN_WRITE])]
    public ?float $weight;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_ADMIN_WRITE])]
    public int $quantity;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_ADMIN_WRITE])]
    public bool $virtual = false;

    #[Groups([self::GROUP_ADMIN_READ, self::GROUP_ADMIN_WRITE])]
    public bool $visible;

    public function setId(int $id): Option
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRef(): string
    {
        return $this->ref;
    }

    public function setRef(string $ref): Option
    {
        $this->ref = $ref;
        return $this;
    }

    public function getTaxRuleId(): int
    {
        return $this->taxRuleId;
    }

    public function setTaxRuleId(int $taxRuleId): Option
    {
        $this->taxRuleId = $taxRuleId;
        return $this;
    }

    public function getI18ns(): I18nCollection
    {
        return $this->i18ns;
    }

    public function setI18ns(I18nCollection|array $i18ns): Option
    {
        $this->i18ns = $i18ns;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): Option
    {
        $this->price = $price;
        return $this;
    }

    public function getPromoPrice(): float
    {
        return $this->promoPrice;
    }

    public function setPromoPrice(float $promoPrice): Option
    {
        $this->promoPrice = $promoPrice;
        return $this;
    }

    public function isPromo(): bool
    {
        return $this->promo;
    }

    public function setPromo(bool $promo): Option
    {
        $this->promo = $promo;
        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): Option
    {
        $this->weight = $weight;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): Option
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function isVirtual(): bool
    {
        return $this->virtual;
    }

    public function setVirtual(bool $virtual): Option
    {
        $this->virtual = $virtual;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): Option
    {
        $this->visible = $visible;
        return $this;
    }

    public static function getPropelRelatedTableMap(): ?TableMap
    {
        return new ProductTableMap();
    }

    public static function getI18nResourceClass(): string
    {
        return OptionI18n::class;
    }
}