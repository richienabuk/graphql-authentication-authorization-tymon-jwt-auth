<?php

namespace App\GraphQL\Mutations\Auth;

use App\Models\Role;
use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Register
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
        $input = collect($args)->except('password_confirmation')->toArray();
        $user = User::create([
            'username' => $input['username'],
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'password' => Hash::make($input['password']),
        ]);

        $user_role = strtolower( $input['user_type'] ) === Role::ROLE_EDITOR ? Role::ROLE_EDITOR : Role::ROLE_AUTHENTICATED;
        $role = Role::select('id')->where('name', $user_role)->first();
        $user->roles()->attach($role);

        if ($user instanceof MustVerifyEmail) {
            $user->sendEmailVerificationNotification();

            event(new Registered($user));

            return [
                'status' => 'success',
                'message' => 'Check your email for link to verify your account',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Registration successful',
        ];
    }
}
