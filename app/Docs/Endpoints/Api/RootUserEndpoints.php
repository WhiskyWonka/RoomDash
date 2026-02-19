<?php

namespace App\Docs\Endpoints\Api;

use App\Http\Requests\RootUser\RootUserChangePasswordRequest;
use App\Http\Requests\RootUser\RootUserStoreRequest;
use App\Http\Requests\RootUser\RootUserUpdateRequest;
use App\Http\Requests\RootUser\RootUserUploadAvatarRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

interface RootUserEndpoints
{
    #[OA\Get(
        path: '/root-users',
        summary: 'List root users',
        description: 'Returns a paginated list of root users',
        operationId: 'listRootUsers',
        tags: ['Root Users'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'sort_field', in: 'query', required: false, schema: new OA\Schema(type: 'string', default: 'created_at')),
            new OA\Parameter(name: 'sort_direction', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of root users',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'users',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/RootUserSummary')
                                ),
                                new OA\Property(
                                    property: 'meta',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                        new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                        new OA\Property(property: 'total', type: 'integer', example: 50),
                                    ]
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Not authenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 403,
                description: '2FA verification required',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function index(Request $request): JsonResponse;

    #[OA\Get(
        path: '/root-users/{id}',
        summary: 'Get root user details',
        description: 'Returns detailed information for a single root user',
        operationId: 'showRootUser',
        tags: ['Root Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Root user details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/RootUserDetail'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Root user not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(string $id): JsonResponse;

    #[OA\Post(
        path: '/root-users',
        summary: 'Create a new root user',
        description: 'Creates a root user and sends a verification email',
        operationId: 'storeRootUser',
        tags: ['Root Users'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'first_name', 'last_name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', maxLength: 50, example: 'johndoe'),
                    new OA\Property(property: 'first_name', type: 'string', maxLength: 255, example: 'John'),
                    new OA\Property(property: 'last_name', type: 'string', maxLength: 255, example: 'Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'S3cur3P@ss!'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'S3cur3P@ss!'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Root user created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Root user created successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/RootUserDetail'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function store(RootUserStoreRequest $request): JsonResponse;

    #[OA\Put(
        path: '/root-users/{id}',
        summary: 'Update a root user',
        description: 'Updates root user information. Re-sends verification email if email changes.',
        operationId: 'updateRootUser',
        tags: ['Root Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'first_name', 'last_name', 'email'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', maxLength: 50, example: 'johndoe'),
                    new OA\Property(property: 'first_name', type: 'string', maxLength: 255, example: 'John'),
                    new OA\Property(property: 'last_name', type: 'string', maxLength: 255, example: 'Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Root user updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Root user updated successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/RootUserDetail'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Root user not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function update(RootUserUpdateRequest $request, string $id): JsonResponse;

    #[OA\Delete(
        path: '/root-users/{id}',
        summary: 'Delete a root user',
        description: 'Permanently deletes a root user. Cannot delete yourself or the last active user.',
        operationId: 'destroyRootUser',
        tags: ['Root Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Root user deleted'),
            new OA\Response(
                response: 403,
                description: 'Cannot delete own account',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Root user not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 409,
                description: 'Cannot delete last active root user',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function destroy(Request $request, string $id): JsonResponse;

    #[OA\Patch(
        path: '/root-users/{id}/password',
        summary: 'Change root user password',
        description: 'Changes a root user\'s password. When changing own password, current_password is required.',
        operationId: 'changeRootUserPassword',
        tags: ['Root Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password', description: 'Required when changing own password'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'N3wS3cur3P@ss!'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'N3wS3cur3P@ss!'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password changed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Password changed successfully'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Current password is incorrect',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Root user not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function changePassword(RootUserChangePasswordRequest $request, string $id): JsonResponse;

    #[OA\Patch(
        path: '/root-users/{id}/deactivate',
        summary: 'Deactivate a root user',
        description: 'Sets a root user as inactive. Cannot deactivate the last active user.',
        operationId: 'deactivateRootUser',
        tags: ['Root Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User deactivated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User deactivated'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Root user not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 409,
                description: 'Cannot deactivate last active root user',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function deactivate(Request $request, string $id): JsonResponse;

    #[OA\Patch(
        path: '/root-users/{id}/activate',
        summary: 'Activate a root user',
        description: 'Sets a root user as active',
        operationId: 'activateRootUser',
        tags: ['Root Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User activated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User activated'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Root user not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function activate(Request $request, string $id): JsonResponse;

    #[OA\Post(
        path: '/root-users/{id}/resend-verification',
        summary: 'Resend verification email',
        description: 'Resends the email verification link to the root user',
        operationId: 'resendRootUserVerification',
        tags: ['Root Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Verification email sent',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Verification email sent'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'User is already verified',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Root user not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function resendVerification(Request $request, string $id): JsonResponse;

    #[OA\Post(
        path: '/root-users/{id}/avatar',
        summary: 'Upload user avatar',
        description: 'Uploads a square avatar image (webp, png, jpg, jpeg, max 1MB)',
        operationId: 'uploadRootUserAvatar',
        tags: ['Root Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['avatar'],
                    properties: [
                        new OA\Property(property: 'avatar', type: 'string', format: 'binary', description: 'Square image (webp/png/jpg/jpeg, max 1MB)'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avatar uploaded',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'avatarUrl', type: 'string', format: 'uri', example: '/storage/avatars/abc123.jpg'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Root user not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function uploadAvatar(RootUserUploadAvatarRequest $request, string $id): JsonResponse;

    #[OA\Delete(
        path: '/root-users/{id}/avatar',
        summary: 'Delete user avatar',
        description: 'Removes the avatar from the root user',
        operationId: 'deleteRootUserAvatar',
        tags: ['Root Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avatar deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Avatar deleted'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Root user not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function deleteAvatar(Request $request, string $id): JsonResponse;
}
