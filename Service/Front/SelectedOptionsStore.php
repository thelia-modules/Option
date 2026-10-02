<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Option\Service\Front;

/**
 * The options a visitor picked, carried from the submitted add-to-cart form to the
 * listener that attaches them to the cart line.
 *
 * Both happen in the same request: the fieldset is part of the core CART_ADD form, so
 * its values are submitted with the product, and TheliaEvents::CART_ADDITEM fires while
 * that submission is still being handled. Nothing is persisted between requests, which
 * is the whole point of this class replacing a session key: what the visitor sees on
 * the page and what reaches the cart cannot drift apart.
 *
 * The value of an option says how it was picked: true for a ticked box, the text the
 * visitor typed for a customizable one. An option that carries no value is absent, so
 * being present is being selected.
 */
final class SelectedOptionsStore
{
    /** @var array<int, array<int, true|string>> values by option id, by product id */
    private array $values = [];

    /**
     * @param array<int|string, mixed> $values value by option id
     */
    public function set(int $productId, array $values): void
    {
        $kept = [];

        foreach ($values as $optionId => $value) {
            $normalised = $this->normalise($value);

            if ((int) $optionId > 0 && null !== $normalised) {
                $kept[(int) $optionId] = $normalised;
            }
        }

        $this->values[$productId] = $kept;
    }

    /**
     * @return array<int, true|string> value by option id
     */
    public function forProduct(int $productId): array
    {
        return $this->values[$productId] ?? [];
    }

    /**
     * An empty text is not a selection: a customizable option the visitor left blank is
     * dropped the same way an unticked box is.
     */
    private function normalise(mixed $value): true|string|null
    {
        if (true === $value) {
            return true;
        }

        if (!\is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);

        return '' === $text ? null : $text;
    }
}
