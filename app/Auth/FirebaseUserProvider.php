<?php

namespace App\Auth;

use App\Services\FirestoreService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use RuntimeException;

class FirebaseUserProvider implements UserProvider
{
    public function __construct(
        private FirestoreService $firestore
    ) {}

    public function retrieveById($identifier)
    {
        $document = $this->firestore->getDocumentOrNull(
            'users',
            (string) $identifier
        );

        if ($document === null) {
            return null;
        }

        $fields = $document['fields'] ?? [];

        return new FirebaseUser([
            'id' => (string) $identifier,
            'name' => $fields['name']['stringValue'] ?? '',
            'email' => $fields['email']['stringValue'] ?? '',
            'role' => $fields['role']['stringValue'] ?? 'user',
            'status' => $fields['status']['stringValue'] ?? 'locked',
            'employee_code' => $fields['employee_code']['stringValue'] ?? null,
            'password' => null,
            'remember_token' => null,
        ]);
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        return null;
    }

    public function updateRememberToken(
        Authenticatable $user,
        #[\SensitiveParameter] $token
    ) {
        // Không sử dụng remember token.
    }

    public function retrieveByCredentials(
        #[\SensitiveParameter] array $credentials
    ) {
        return null;
    }

    public function validateCredentials(
        Authenticatable $user,
        #[\SensitiveParameter] array $credentials
    ): bool {
        return false;
    }

    public function rehashPasswordIfRequired(
        Authenticatable $user,
        #[\SensitiveParameter] array $credentials,
        bool $force = false
    ) {
        // Mật khẩu được Firebase Authentication quản lý.
    }
}
