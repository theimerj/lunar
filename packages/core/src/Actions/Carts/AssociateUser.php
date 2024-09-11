<?php

namespace Lunar\Actions\Carts;

use Lunar\Actions\AbstractAction;
use Lunar\Base\LunarUser;
use Lunar\Models\Cart;
use Lunar\Models\Contracts\Cart as CartContract;

class AssociateUser extends AbstractAction
{
    /**
     * Execute the action
     *
     * @param  string  $policy
     */
    public function execute(CartContract $cart, LunarUser $user, $policy = 'merge'): self
    {
        /** @var Cart $userCart */
        if ($policy == 'merge') {
            $userCart = Cart::modelClass()::whereUserId($user->getKey())->active()->unMerged()->latest()->first();
            if ($userCart) {
                app(MergeCart::class)->execute($cart, $userCart);
            }
        }

        if ($policy == 'override') {
            $userCart = Cart::modelClass()::whereUserId($user->getKey())->active()->unMerged()->latest()->first();
            if ($userCart && $userCart->id != $cart->id) {
                $userCart->update([
                    'merged_id' => $userCart->id,
                ]);
            }
        }

        $cart->update([
            'user_id' => $user->getKey(),
            'customer_id' => $user->latestCustomer()?->getKey(),
        ]);

        return $this;
    }
}
