<?php

namespace App\GraphQL\Mutations\Auth;

use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

class Login
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args The arguments that were passed into the field.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context Arbitrary data that is shared between all fields of a single query.
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = User::where('username', $args['login'])
            ->orWhere('email', $args['login'])
            ->first();

        if (!$user) {
            throw new AuthenticationException("User is not registered");
        }

        if(!$user->hasVerifiedEmail()){
            throw new AuthenticationException('Kindly verify your email');
        }

        if (!$access_token = auth()->attempt(['email' => $user->email, 'password' => $args['password']])) {
            throw new AuthenticationException('Invalid password');
        }

        $expires_in = auth()->factory()->getTTL() * 60;
        $token_type = 'bearer';

        return compact('user', 'access_token', 'token_type', 'expires_in');
    }
}
