<?php

namespace App\GraphQL\Mutations\Auth;

use App\Exceptions\ValidationException;
use App\Models\User;
use Carbon\Carbon;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\Events\Verified;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class EmailVerification
{
    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the
     *     parent field. In this case, it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary
     *     data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information
     *     about the query itself, such as the execution state, the field name,
     *     path to the field from the root, and more.
     *
     * @return mixed
     * @throws \App\Exceptions\ValidationException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $decodedToken = json_decode(base64_decode($args['token']));
        $expiration = decrypt($decodedToken->expiration);
        $email = decrypt($decodedToken->hash);

       if (Carbon::parse($expiration) < now()) {
            throw new ValidationException([
                'token' => __('The token is invalid'),
            ], 'Validation Error');
        }

        try {
            $user = User::where('email', $email)->firstOrFail();

            if($user->hasVerifiedEmail()){
                throw new ValidationException([
                    'token' => __('Email address already verified'),
                ], 'Validation Error');
            }

            $user->markEmailAsVerified();
            event(new Verified($user));

            $access_token = auth()->login($user);

            $expires_in = auth()->factory()->getTTL() * 60;
            $token_type = 'bearer';

            return compact('user', 'access_token', 'token_type', 'expires_in');
        } catch (ValidationException $e) {
            throw new ValidationException([
                'token' => __('The token is invalid'),
            ], 'Validation Error');
        }
    }

    public function resend($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = User::where('email', $args['email'])->first();

        if(!$user){
            throw new ValidationException([
                'token' => __('No user could be found with this email address'),
            ], 'Validation Error');
        }

        if($user->hasVerifiedEmail()){
            throw new ValidationException([
                'token' => __('Email address already verified'),
            ], 'Validation Error');
        }

        $user->sendEmailVerificationNotification();

        return [
            'status' => 'success',
            'message' => 'verification link resent',
        ];
    }
}
